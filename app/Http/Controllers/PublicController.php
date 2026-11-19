<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class PublicController extends Controller
{
    public function home()
    {
        $picks = Article::published()->with('category')->whereIn('id', DB::table('featured_placements')->where('location', 'pick')->select('article_id'))->limit(6)->get();
        $popular = Article::published()->with('category')->joinSub(DB::table('article_view_daily')->selectRaw('article_id,SUM(views) as total')->where('date', '>=', now()->subDays(6)->toDateString())->groupBy('article_id'), 'v', 'articles.id', '=', 'v.article_id')->orderByDesc('total')->orderByDesc('articles.published_at')->orderByDesc('articles.id')->limit(5)->get(['articles.*', 'v.total']);
        $popularTitle = $popular->isEmpty() ? 'Terbaru' : 'Terpopuler';
        if ($popular->isEmpty()) {
            $popular = Article::published()->latest('published_at')->latest('id')->limit(5)->get();
        }
        $featured = $popular->take(5);
        $latest = Article::published()->with('category')->whereNotIn('id', $featured->pluck('id'))->latest('published_at')->latest('id')->limit(12)->get();
        $channels = Category::where('active', true)->with(['articles' => fn ($q) => $q->published()->latest('published_at')->limit(4)])->orderBy('position')->get()->filter(fn ($c) => $c->articles->isNotEmpty());

        return view('public.home', compact('featured', 'latest', 'picks', 'popular', 'popularTitle', 'channels'));
    }

    public function listing(Request $r, ?string $kind = null, ?string $slug = null)
    {
        $r->validate(['page' => 'nullable|integer|min:1|max:100000', 'q' => 'nullable|string|max:100', 'category_id' => 'nullable|integer']);
        $q = Article::published()->with(['author', 'category']);
        $title = 'Berita terbaru';
        $description = 'Kabar terbaru dari ruang redaksi.';
        if ($kind === 'kanal') {
            $entity = Category::where('slug', $slug)->where('active', true)->firstOrFail();
            $q->where('category_id', $entity->id);
            $title = $entity->name;
            $description = $entity->description;
        }
        if ($kind === 'tag') {
            $entity = Tag::where('slug', $slug)->where('active', true)->firstOrFail();
            $q->whereHas('tags', fn ($q) => $q->where('tags.id', $entity->id));
            $title = '#'.$entity->name;
        }
        if ($kind === 'penulis') {
            $entity = User::where('slug', $slug)->firstOrFail();
            $q->where('author_id', $entity->id);
            $title = $entity->name;
            $description = $entity->bio;
        }
        if ($r->filled('category_id')) {
            $q->where('category_id', $r->integer('category_id'));
        }
        if ($r->is('cari')) {
            $title = 'Pencarian';
            $term = trim($r->input('q', ''));
            if (mb_strlen($term) < 2) {
                $q->whereRaw('1=0');
                $description = 'Masukkan sedikitnya 2 karakter untuk mencari berita.';
            } else {
                $q->where(fn ($q) => $q->where('title', 'like', '%'.$term.'%')->orWhere('body', 'like', '%'.$term.'%'));
                $description = 'Hasil untuk “'.$term.'”';
            }
        }
        $articles = $q->latest('published_at')->latest('id')->paginate(20)->withQueryString();

        return view('public.list', compact('articles', 'title', 'description'));
    }

    public function article(Request $r, int $id, string $slug)
    {
        $article = Article::with(['author', 'category', 'tags'])->findOrFail($id);
        if ($article->status !== 'published' || ! $article->published_at || $article->published_at->isFuture()) {
            abort($article->published_at ? 410 : 404);
        }
        if ($slug !== $article->slug) {
            return redirect($article->url, 301);
        }
        $related = Article::published()->where('id', '!=', $id)->where(fn ($q) => $q->where('category_id', $article->category_id)->orWhereHas('tags', fn ($q) => $q->whereIn('tags.id', $article->tags->pluck('id'))))->latest('published_at')->limit(6)->get();

        return view('public.article', compact('article', 'related'));
    }

    public function view(Request $r, int $id)
    {
        $a = Article::published()->findOrFail($id);
        if (! $r->user() && ! preg_match('/bot|crawler|spider|slurp/i', $r->userAgent() ?? '')) {
            try {
                $key = 'view:'.hash('sha256', $r->session()->getId().':'.$a->id);
                if (Cache::add($key, true, 1800)) {
                    DB::table('article_view_daily')->insertOrIgnore(['article_id' => $id, 'date' => now()->toDateString(), 'views' => 0]);
                    DB::table('article_view_daily')->where('article_id', $id)->where('date', now()->toDateString())->increment('views');
                }
            } catch (\Throwable $e) {
                report($e);
            }
        }

        return response()->noContent();
    }

    public function page(string $slug)
    {
        $page = DB::table('pages')->where('slug', $slug)->where('active', true)->first();
        abort_unless($page, 404);

        return view('public.page', compact('page'));
    }

    public function rss()
    {
        return response()->view('public.rss', ['articles' => Article::published()->with('author')->latest('published_at')->limit(50)->get()])->header('Content-Type', 'application/rss+xml; charset=UTF-8');
    }

    public function sitemap(Request $r)
    {
        $r->validate(['page' => 'nullable|integer|min:1']);
        $page = $r->integer('page', 0);

        return response()->view('public.sitemap', ['page' => $page, 'pages' => (int) ceil(Article::published()->count() / 1000), 'articles' => $page ? Article::published()->orderBy('id')->skip(($page - 1) * 1000)->take(1000)->get() : collect()])->header('Content-Type','application/xml; charset=UTF-8');
    }
}
