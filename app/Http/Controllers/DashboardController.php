<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $query = Article::query()->when(auth()->user()->role === 'penulis', fn ($q) => $q->where('author_id', auth()->id()));
        $counts = (clone $query)->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');
        $recent = (clone $query)->with(['author', 'category'])->latest('updated_at')->limit(7)->get();
        $viewQuery = DB::table('article_view_daily')->whereIn('article_id', (clone $query)->select('id'));
        $views = (clone $viewQuery)->sum('views');
        $start = now()->timezone('Asia/Jakarta')->startOfDay()->subDays(6);
        $end = $start->copy()->addDays(6);
        $daily = (clone $viewQuery)->whereBetween('date', [$start->toDateString(), $end->toDateString()])->selectRaw('date, SUM(views) as total')->groupBy('date')->pluck('total', 'date');
        $traffic = collect(range(0, 6))->map(fn ($offset) => [
            'label' => $start->copy()->addDays($offset)->locale('id')->translatedFormat('D'),
            'date' => $start->copy()->addDays($offset)->format('d M'),
            'total' => (int) ($daily[$start->copy()->addDays($offset)->toDateString()] ?? 0),
        ]);
        $weekViews = $traffic->sum('total');
        $previousViews = (clone $viewQuery)->whereBetween('date', [$start->copy()->subDays(7)->toDateString(), $start->copy()->subDay()->toDateString()])->sum('views');
        $growth = $previousViews > 0 ? round(($weekViews - $previousViews) / $previousViews * 100, 1) : null;
        $topArticles = app(\App\Services\PopularArticles::class)->query(auth()->user()->role === 'penulis' ? auth()->id() : null)->limit(5)->get();

        return view('admin.dashboard', compact('counts', 'recent', 'views', 'traffic', 'weekViews', 'growth', 'topArticles'));
    }
}
