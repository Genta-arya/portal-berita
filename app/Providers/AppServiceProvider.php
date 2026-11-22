<?php

namespace App\Providers;

use App\Models\Category;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer(['layouts.public', 'layouts.admin'], function ($view) {
            if ($view->name()==='layouts.public') {
                $view->with('tickerArticles', \App\Models\Article::published()->latest('published_at')->latest('id')->limit(10)->get(['id','title','slug']));
                $view->with('archiveMonths', Cache::remember('site:archive_months', 600, function () {
                    return \App\Models\Article::published()
                        ->selectRaw('YEAR(published_at) as year, MONTH(published_at) as month, COUNT(*) as count')
                        ->groupBy('year', 'month')
                        ->orderByDesc('year')
                        ->orderByDesc('month')
                        ->limit(12)
                        ->get();
                }));
                $view->with('searchTrendingArticles', Cache::remember('site:search_modal_trending', 300, function () {
                    $popular = \App\Models\Article::published()->with('category')
                        ->joinSub(DB::table('article_view_daily')->selectRaw('article_id,SUM(views) as total')->where('date', '>=', now()->subDays(7)->toDateString())->groupBy('article_id'), 'v', 'articles.id', '=', 'v.article_id')
                        ->orderByDesc('total')->orderByDesc('articles.published_at')->orderByDesc('articles.id')
                        ->limit(5)->get(['articles.*', 'v.total']);
                    return $popular->isNotEmpty() ? $popular : \App\Models\Article::published()->with('category')->latest('published_at')->latest('id')->limit(5)->get();
                }));
                $view->with('searchPopularArticles', Cache::remember('site:search_modal_popular', 300, function () {
                    return \App\Models\Article::published()->with('category')->latest('published_at')->skip(5)->limit(4)->get();
                }));
            }
            $view->with('navigation', Cache::remember('site:navigation', 60, fn () => Category::where('active', true)->orderBy('position')->get()));
            $view->with('settings', Cache::remember('site:settings', 60, fn () => DB::table('site_settings')->pluck('value', 'key')));
            $view->with('breaking', DB::table('breaking_news')->where('active', true)->where('starts_at', '<=', now())->where('ends_at', '>', now())->latest('id')->first());
        });
        Paginator::useBootstrapFive();
    }
}
