<?php

use App\Http\Controllers\ArticleController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ManagementController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\PublicController;
use App\Http\Middleware\EnsureEditorialSession;
use App\Models\Article;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

Route::get('/login', fn () => response()->view('auth.login')->header('Cache-Control', 'private, no-store')->header('X-Robots-Tag', 'noindex'))->name('login');
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth');
Route::get('/media/{media}/{variant}', [MediaController::class, 'show'])->whereNumber('media');
Route::middleware(['auth', EnsureEditorialSession::class])->prefix('admin')->group(function () {
    Route::get('/', function () {
        $q = Article::query();
        if (auth()->user()->role === 'penulis') {
            $q->where('author_id', auth()->id());
        }
        $counts = (clone $q)->selectRaw('status,count(*) as total')->groupBy('status')->pluck('total', 'status');
        $recent = $q->latest('id')->limit(8)->get();
        $views = DB::table('article_view_daily')->whereIn('article_id', Article::when(auth()->user()->role === 'penulis', fn ($q) => $q->where('author_id', auth()->id()))->select('id'))->sum('views');

        return view('admin.dashboard', compact('counts', 'recent', 'views'));
    })->name('admin');
    Route::get('/articles', [ArticleController::class, 'index']);
    Route::post('/articles', [ArticleController::class, 'store']);
    Route::get('/articles/{article}/edit', [ArticleController::class, 'edit']);
    Route::put('/articles/{article}', [ArticleController::class, 'update']);
    Route::post('/articles/{article}/transition', [ArticleController::class, 'transition']);
    Route::get('/articles/{article}/preview', [ArticleController::class, 'preview']);
    Route::post('/articles/{article}/restore/{revision}', [ArticleController::class, 'restore'])->whereNumber('revision');
    Route::delete('/articles/{article}', [ArticleController::class, 'destroy']);
    Route::post('/articles/{article}/assign', [ArticleController::class, 'assign']);
    Route::get('/media', [MediaController::class, 'index']);
    Route::post('/media', [MediaController::class, 'store'])->middleware('throttle:20,1');
    Route::delete('/media/{media}', [MediaController::class, 'destroy']);
    Route::put('/media/{media}', [MediaController::class, 'update']);
    Route::get('/manage/{module}', [ManagementController::class, 'index']);
    Route::post('/manage/{module}', [ManagementController::class, 'store']);
    Route::delete('/manage/{module}/{id}', [ManagementController::class, 'destroy']);
});
Route::get('/', [PublicController::class, 'home']);
Route::get('/terbaru', [PublicController::class, 'listing']);
Route::get('/cari', [PublicController::class, 'listing'])->middleware('throttle:60,1');
Route::get('/{kind}/{slug}', [PublicController::class, 'listing'])->where('kind', 'kanal|tag|penulis');
Route::get('/berita/{id}/{slug}', [PublicController::class, 'article'])->whereNumber('id');
Route::post('/berita/{id}/view', [PublicController::class, 'view'])->middleware('throttle:60,1');
Route::get('/halaman/{slug}', [PublicController::class, 'page']);
Route::get('/rss.xml',[PublicController::class, 'rss']);
Route::get('/sitemap.xml',[PublicController::class, 'sitemap']);
