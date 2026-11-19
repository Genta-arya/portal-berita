@extends('layouts.public')
@section('title', $title.' — Portal Berita')
@section('description', $description ?? '')

@section('content')
    <div class="reader-breadcrumb"><a href="/">Beranda</a><span aria-hidden="true">/</span><span>{{ $title }}</span></div>
    <div class="listing-grid">
        <section class="listing-panel" aria-labelledby="listing-title">
            <header class="listing-header">
                <h1 id="listing-title">{{ $title }}</h1>
                @if($description)<p>{{ $description }}</p>@endif
            </header>
            @if(request()->is('terbaru'))
                <form action="/terbaru" class="channel-filter">
                    <label for="filter-channel">Pilih kategori</label>
                    <select id="filter-channel" name="category_id">
                        <option value="">Semua kategori</option>
                        @foreach($filterCategories as $c)
                            <option value="{{ $c->id }}" @selected(request('category_id') == $c->id)>{{ $c->name }}</option>
                        @endforeach
                    </select>
                    <button>Tampilkan</button>
                </form>
            @endif
            <div class="news-feed">
                @forelse($articles as $item)
                    @include('public.card', ['variant' => 'feed'])
                @empty
                    <div class="reader-empty"><h2>Belum ada berita yang sesuai.</h2><a href="/terbaru">Lihat berita terbaru</a></div>
                @endforelse
            </div>
            {{ $articles->links() }}
        </section>
        <aside class="listing-aside">@include('public.banner',['slot'=>'sidebar'])</aside>
    </div>
@endsection
