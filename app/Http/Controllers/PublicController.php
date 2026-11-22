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
        $data = Cache::remember('site:home_curation', 120, function () {
            $placementQuery = fn ($location) => Article::published()->with('category')
                ->join('featured_placements as placement', 'articles.id', '=', 'placement.article_id')
                ->where('placement.location', $location)->orderBy('placement.position')->orderBy('placement.id')->select('articles.*');
            $picks = $placementQuery('pick')->limit(6)->get();
            $popular = app(\App\Services\PopularArticles::class)->query()->limit(5)->get();
            $popularTitle = $popular->isEmpty() ? 'Terbaru' : 'Terpopuler';
            if ($popular->isEmpty()) {
                $popular = Article::published()->with('category')->latest('published_at')->latest('id')->limit(5)->get();
            }
            $featured = $placementQuery('headline')->limit(5)->get();
            if ($featured->isEmpty() && ! DB::table('site_settings')->where('key', 'home_headlines_managed')->where('value', '1')->exists()) {
                $featured = $popular->take(5);
            }
            $latest = Article::published()->with('category')->whereNotIn('id', $featured->pluck('id'))->latest('published_at')->latest('id')->limit(12)->get();
            $channels = Category::where('active', true)
                ->whereHas('articles', fn ($query) => $query->published())
                ->withCount(['articles' => fn ($query) => $query->published()])
                ->with(['articles' => fn ($query) => $query->published()->with('category')->latest('published_at')->latest('id')->limit(8)])
                ->orderByDesc('articles_count')->orderBy('position')->orderBy('id')->limit(9)->get();

            $trendingTopics = Category::where('categories.active', true)
                ->join('trending_topics as trending', 'categories.id', '=', 'trending.category_id')
                ->where('trending.active', true)->whereHas('articles', fn ($q) => $q->published())
                ->with(['articles' => fn ($q) => $q->published()->with('category')->latest('published_at')->latest('id')->limit(8)])
                ->orderBy('trending.position')->orderBy('trending.id')->limit(9)->get(['categories.*']);

            return compact('featured', 'latest', 'picks', 'popular', 'popularTitle', 'channels', 'trendingTopics');
        });

        return view('public.home', $data);
    }

    public function listing(Request $r, ?string $kind = null, ?string $slug = null)
    {
        $r->validate([
            'page' => 'nullable|integer|min:1|max:100000',
            'q' => 'nullable|string|max:100',
            'category_id' => 'nullable|integer',
            'tanggal' => 'nullable|date_format:Y-m-d',
            'tahun' => 'nullable|integer|min:2000|max:2099',
            'bulan' => 'nullable|integer|min:1|max:12',
        ]);
        $q = Article::published()->with(['author', 'category']);
        $title = 'Berita terbaru';
        $description = 'Kabar terbaru dari ruang redaksi.';

        if ($r->is('arsip')) {
            $title = 'Arsip Berita';
            $description = 'Telusuri dokumentasi dan arsip pemberitaan berdasarkan waktu penerbitan.';
        }

        if ($r->filled('tanggal')) {
            $q->whereDate('published_at', $r->input('tanggal'));
            $title = 'Arsip: ' . \Carbon\Carbon::parse($r->input('tanggal'))->locale('id')->translatedFormat('d F Y');
            $description = 'Kumpulan berita yang diterbitkan pada ' . \Carbon\Carbon::parse($r->input('tanggal'))->locale('id')->translatedFormat('l, d F Y') . '.';
        } elseif ($r->filled('tahun')) {
            $q->whereYear('published_at', $r->integer('tahun'));
            if ($r->filled('bulan')) {
                $q->whereMonth('published_at', $r->integer('bulan'));
                $monthName = \Carbon\Carbon::createFromDate($r->integer('tahun'), $r->integer('bulan'), 1)->locale('id')->translatedFormat('F Y');
                $title = 'Arsip: ' . $monthName;
                $description = 'Kumpulan berita yang diterbitkan pada bulan ' . $monthName . '.';
            } else {
                $title = 'Arsip Tahun ' . $r->integer('tahun');
                $description = 'Kumpulan berita yang diterbitkan sepanjang tahun ' . $r->integer('tahun') . '.';
            }
        }
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

        $filterCategories = collect();
        $selectedCategory = null;
        if ($r->is('terbaru') || $r->is('arsip')) {
            $selectedCategoryId = (int) $r->input('category_id');
            if ($selectedCategoryId > 0) {
                $selectedCategory = Category::find($selectedCategoryId);
            }
            // Ambil kategori terpopuler/terbanyak memiliki artikel aktif (top 15) untuk pill cepat
            $filterCategories = Cache::remember('site:filter_popular_categories', 300, function () {
                return Category::where('active', true)
                    ->withCount(['articles' => fn ($q) => $q->published()])
                    ->orderByDesc('articles_count')
                    ->orderBy('position')
                    ->limit(15)
                    ->get(['id', 'name', 'slug']);
            });
        }

        // Ambil berita trending (berdasarkan view harian 7 hari terakhir atau fallback terpopuler/terbaru)
        $trendingArticles = app(\App\Services\PopularArticles::class)->query()->limit(5)->get();

        // Ambil berita pilihan/lainnya untuk sidebar widget
        $otherArticles = Cache::remember('site:listing_others', 300, function () use ($trendingArticles) {
            return Article::published()->with('category')
                ->whereNotIn('id', $trendingArticles->pluck('id'))
                ->inRandomOrder()
                ->limit(4)
                ->get();
        });

        return view('public.list', compact('articles', 'title', 'description', 'filterCategories', 'selectedCategory', 'trendingArticles', 'otherArticles'));
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

        // Ambil berita trending untuk sidebar detail artikel
        $trendingArticles = app(\App\Services\PopularArticles::class)->query()->limit(5)->get();

        // Ambil berita pilihan/lainnya untuk sidebar detail artikel
        $otherArticles = Cache::remember('site:listing_others', 300, function () use ($trendingArticles) {
            return Article::published()->with('category')
                ->whereNotIn('id', $trendingArticles->pluck('id'))
                ->inRandomOrder()
                ->limit(4)
                ->get();
        });

        return view('public.article', compact('article', 'related', 'trendingArticles', 'otherArticles'));
    }

    public function view(Request $r, int $id)
    {
        $a = Article::published()->findOrFail($id);
        if (! $r->user() && ! preg_match('/bot|crawler|spider|slurp/i', $r->userAgent() ?? '')) {
            try {
                $key = 'view:'.hash('sha256', $r->session()->getId().':'.$a->id);
                if (Cache::add($key, true, 1800)) {
                    DB::table('article_view_daily')->insertOrIgnore(['article_id' => $id, 'date' => now()->timezone('Asia/Jakarta')->toDateString(), 'views' => 0]);
                    DB::table('article_view_daily')->where('article_id', $id)->where('date', now()->timezone('Asia/Jakarta')->toDateString())->increment('views');
                }
            } catch (\Throwable $e) {
                report($e);
            }
        }

        return response()->noContent();
    }

    public function searchSuggest(Request $r)
    {
        $q = trim((string) $r->input('q', ''));
        if (mb_strlen($q) < 2) {
            return response()->json(['articles' => [], 'topics' => []]);
        }

        $articles = Article::published()->with('category')
            ->where(function ($query) use ($q) {
                $query->where('title', 'like', "%{$q}%")
                      ->orWhere('summary', 'like', "%{$q}%");
            })
            ->latest('published_at')
            ->limit(6)
            ->get(['id', 'title', 'slug', 'category_id', 'published_at', 'hero_media_id'])
            ->map(function ($a) {
                return [
                    'id' => $a->id,
                    'title' => $a->title,
                    'url' => $a->url,
                    'category' => $a->category?->name,
                    'published' => $a->published_at?->locale('id')->diffForHumans(),
                    'image' => $a->hero_media_id ? "/media/{$a->hero_media_id}/480" : null,
                ];
            });

        $topics = Category::where('active', true)
            ->where('name', 'like', "%{$q}%")
            ->orderBy('name')
            ->limit(5)
            ->get(['id', 'name', 'slug']);

        return response()->json([
            'articles' => $articles,
            'topics' => $topics,
        ]);
    }

    public function searchCategories(Request $r)
    {
        $q = trim((string) $r->input('q', ''));
        if (mb_strlen($q) < 1) {
            return response()->json([]);
        }

        $categories = Category::where('active', true)
            ->where('name', 'like', "%{$q}%")
            ->orderBy('name')
            ->limit(10)
            ->get(['id', 'name', 'slug']);

        return response()->json($categories);
    }

    public function page(string $slug)
    {
        $page = DB::table('pages')->where('slug', $slug)->where('active', true)->first();
        abort_unless($page, 404);

        if ($slug === 'tentang') {
            return view('public.about', compact('page'));
        }

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
