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
            $view->with('navigation', Cache::remember('site:navigation', 60, fn () => Category::where('active', true)->orderBy('position')->get()));
            $view->with('settings', Cache::remember('site:settings', 60, fn () => DB::table('site_settings')->pluck('value', 'key')));
            $view->with('breaking', DB::table('breaking_news')->where('active', true)->where('starts_at', '<=', now())->where('ends_at', '>', now())->latest('id')->first());
        });
        Paginator::useBootstrapFive();
    }
}
