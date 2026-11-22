<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaveArticleRequest;
use App\Models\Article;
use App\Models\Category;
use App\Models\Tag;
use App\Policies\ArticlePolicy;
use App\Services\EditorialService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ArticleController extends Controller
{
    public function index(Request $r)
    {
        $q = Article::with(['author', 'category']);
        if ($r->user()->role === 'penulis') {
            $q->where('author_id', $r->user()->id);
        }
        $statusCounts = (clone $q)->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');
        foreach (['status', 'category_id', 'author_id'] as $field) {
            if ($r->filled($field)) {
                $q->where($field, $r->input($field));
            }
        }
        if ($r->filled('q')) {
            $q->where('title', 'like', '%'.mb_substr($r->string('q'), 0, 100).'%');
        }
        if ($r->filled('date')) {
            $r->validate(['date' => 'date_format:Y-m-d']);
            $q->whereDate('created_at', $r->date);
        }

        return view('admin.articles', ['statusCounts' => $statusCounts, 'articles' => $q->latest('id')->paginate(20)->withQueryString(), 'categories' => Category::orderBy('position')->get()]);
    }

    public function store(Request $r, EditorialService $service)
    {
        $r->validate(['title' => 'required|string|max:255']);
        $a = DB::transaction(function () use ($r, $service) {
            $a = new Article(['title' => $r->title, 'slug' => Str::slug($r->title) ?: Str::random(12)]);
            $a->author_id = $r->user()->id;
            $a->save();
            $service->revision($a, $r->user(), 'Buat draft');

            return $a;
        });

        return redirect('/admin/articles/'.$a->id.'/edit');
    }

    public function edit(Request $r, Article $article)
    {
        abort_unless((new ArticlePolicy)->view($r->user(), $article), 403);

        return view('admin.edit', ['article' => $article->load('tags', 'revisions'), 'categories' => Category::orderBy('position')->get(), 'notes' => DB::table('editorial_notes')->where('article_id', $article->id)->latest('id')->get()]);
    }

    public function update(SaveArticleRequest $r, Article $article, EditorialService $service)
    {
        $data = $r->validated();
        $a = $service->save($article, $r->user(), $data, $r->boolean('autosave'));

        return $r->expectsJson() ? response()->json(['version' => $a->version, 'saved_at' => now()->toIso8601String()]) : back()->with('success', 'Artikel disimpan.');
    }

    public function transition(Request $r, Article $article, EditorialService $service)
    {
        $r->validate(['version' => 'required|integer', 'action' => 'required|string', 'note' => 'nullable|string|max:2000', 'scheduled_at' => 'nullable|date']);
        $service->transition($article, $r->user(), $r->input('action'), $r->integer('version'), $r->input('note'), $r->input('scheduled_at'));

        return back()->with('success', 'Status artikel diperbarui.');
    }

    public function preview(Request $r, Article $article)
    {
        abort_unless((new ArticlePolicy)->view($r->user(), $article), 403);

        return view('public.article', ['article' => $article->load('author', 'category', 'tags'), 'related' => collect(), 'preview' => true]);
    }

    public function restore(Request $r, Article $article, int $revision, EditorialService $service)
    {
        abort_unless((new ArticlePolicy)->update($r->user(), $article), 403);
        $r->validate(['version' => 'required|integer']);
        $snapshot = $article->revisions()->findOrFail($revision)->snapshot;
        $fields = ['title', 'slug', 'summary', 'body', 'category_id', 'hero_media_id', 'hero_alt', 'hero_caption', 'hero_credit', 'guest_author', 'seo_title', 'seo_description', 'sponsored'];
        $data = collect($snapshot)->only($fields)->all();
        $data['version'] = $r->integer('version');
        $data['note'] = 'Pulihkan revisi '.$revision;
        $data['tags'] = Tag::whereIn('id', $snapshot['tags'] ?? [])->pluck('name')->implode(',');
        $service->save($article, $r->user(), $data);

        return back()->with('success', 'Revisi dipulihkan sebagai versi baru.');
    }

    public function assign(Request $r, Article $article, EditorialService $service)
    {
        abort_unless($r->user()->isAdministrator(), 403);
        $r->validate(['author_id' => 'required|exists:users,id', 'version' => 'required|integer']);
        DB::transaction(function () use ($r, $article, $service) {
            $a = Article::lockForUpdate()->findOrFail($article->id);
            abort_if($a->version !== $r->integer('version'), 409);
            $a->author_id = $r->integer('author_id');
            $a->version++;
            if (in_array($a->status, ['approved', 'scheduled'])) {
                $a->status = 'draft';
                $a->approved_version = null;
                $a->scheduled_at = null;
            }
            $a->save();
            $service->revision($a, $r->user(), 'Ganti penulis penanggung jawab');
            $service->audit($r->user(), 'assign_author', 'article', $a->id);
        });

        return back()->with('success', 'Penulis diperbarui dan dicatat dalam audit.');
    }

    public function destroy(Request $r, Article $article, EditorialService $service)
    {
        DB::transaction(function () use ($r, $article, $service) {
            $a = Article::lockForUpdate()->findOrFail($article->id);
            abort_unless((new ArticlePolicy)->delete($r->user(), $a), 403);
            abort_if($a->version !== $r->integer('version'), 409);
            $a->delete();
            $service->audit($r->user(),'archive','article',$a->id);
        });

        return redirect('/admin/articles');
    }
}
