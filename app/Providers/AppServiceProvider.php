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
                $tickerArticles = \App\Models\Article::published()->latest('published_at')->latest('id')->limit(10)->get(['id','title','slug']);
                $activeBreaking = DB::table('breaking_news')->where('active',true)->where('starts_at','<=',now())->where('ends_at','>',now())->latest('id')->get();
                $view->with('tickerArticles', $tickerArticles);
                $view->with('tickerIsBreaking', $activeBreaking->isNotEmpty());
                $view->with('tickerItems', $activeBreaking->isNotEmpty()
                    ? $activeBreaking->map(fn ($item) => (object)['title'=>$item->text,'url'=>$item->url])
                    : $tickerArticles);

                $view->with('archiveMonths', Cache::remember('site:archive_months', 600, function () {
                    return \App\Models\Article::published()
                        ->selectRaw(DB::connection()->getDriverName() === 'sqlite'
                            ? "CAST(strftime('%Y', published_at) AS INTEGER) as year, CAST(strftime('%m', published_at) AS INTEGER) as month, COUNT(*) as count"
                            : 'YEAR(published_at) as year, MONTH(published_at) as month, COUNT(*) as count')
                        ->groupBy('year', 'month')
                        ->orderByDesc('year')
                        ->orderByDesc('month')
                        ->limit(12)
                        ->get();
                }));
                $view->with('searchTrendingArticles', app(\App\Services\PopularArticles::class)->query()->limit(5)->get());
                $view->with('searchPopularArticles', app(\App\Services\PopularArticles::class)->query()->limit(5)->get());
            }
            $view->with('navigation', Cache::remember('site:navigation', 60, fn () => Category::where('active', true)->orderBy('position')->get()));
            $view->with('settings', Cache::remember('site:settings', 60, fn () => DB::table('site_settings')->pluck('value', 'key')));
            $view->with('breaking', DB::table('breaking_news')->where('active', true)->where('starts_at', '<=', now())->where('ends_at', '>', now())->latest('id')->first());
        });
        Paginator::useBootstrapFive();
    }
}
