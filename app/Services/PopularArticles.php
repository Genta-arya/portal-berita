<?php

namespace App\Services;

use App\Models\Article;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class PopularArticles
{
    public function dates(): array
    {
        $today = now()->timezone('Asia/Jakarta')->startOfDay();
        return [$today->copy()->subDays(6)->toDateString(), $today->toDateString()];
    }

    public function query(?int $authorId = null): Builder
    {
        $views = DB::table('article_view_daily')->whereBetween('date', $this->dates())
            ->selectRaw('article_id, SUM(views) as total_views')->groupBy('article_id')->havingRaw('SUM(views) > 0');
        return Article::published()->with('category')->when($authorId, fn ($q) => $q->where('author_id', $authorId))
            ->joinSub($views, 'popularity', 'articles.id', '=', 'popularity.article_id')
            ->orderByDesc('popularity.total_views')->orderByDesc('articles.published_at')->orderByDesc('articles.id')
            ->select('articles.*', 'popularity.total_views');
    }
}
