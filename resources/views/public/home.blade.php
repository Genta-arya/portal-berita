@extends('layouts.public')

@section('content')
    <h1 class="sr-only">Beranda berita</h1>
    @include('public.banner', ['slot' => 'home_top'])

    @if($channels->isNotEmpty())
        <section class="topic-shelf" aria-label="Kategori berita">
            <ul class="topic-list">
                @foreach($channels as $channel)
                    @php($image = $channel->articles->firstWhere('hero_media_id', '!=', null))
                    <li>
                        <a href="/kanal/{{ $channel->slug }}" class="topic-link">
                            <span class="topic-image" aria-hidden="true">
                                @if($image)
                                    <img src="/media/{{ $image->hero_media_id }}/480" alt="" width="80" height="80" loading="lazy">
                                @else
                                    {{ mb_strtoupper(mb_substr($channel->name, 0, 2)) }}
                                @endif
                            </span>
                            <span>{{ $channel->name }}</span>
                        </a>
                    </li>
                @endforeach
            </ul>
        </section>
    @endif

    <div class="frontpage-grid">
        @if($featured->isNotEmpty())
            <section class="frontpage-hero" aria-labelledby="headline-heading">
                <div class="section-bar"><h2 id="headline-heading">Sorotan hari ini</h2></div>
                @include('public.headline-carousel')
            </section>
        @endif

        @if($popularTitle === 'Terpopuler')
            <aside class="frontpage-aside" aria-labelledby="popular-heading">
                <div class="popular-panel">
                    <div class="section-bar"><h2 id="popular-heading">Terpopuler</h2><span>7 hari terakhir</span></div>
                    <ol class="ranked-stories">
                        @foreach($popular as $item)
                            <li>
                                <span class="rank-number" aria-hidden="true">{{ $loop->iteration }}</span>
                                <div>
                                    @if($item->category)<a class="section-kicker" href="/kanal/{{ $item->category->slug }}">{{ $item->category->name }}</a>@endif
                                    <h3><a href="{{ $item->url }}">{{ $item->title }}</a></h3>
                                </div>
                                @if($item->hero_media_id)
                                    <a class="story-image" href="{{ $item->url }}"><img src="/media/{{ $item->hero_media_id }}/480" alt="{{ $item->hero_alt }}" width="80" height="64" loading="lazy"></a>
                                @endif
                            </li>
                        @endforeach
                    </ol>
                </div>
                @include('public.banner', ['slot' => 'sidebar'])
            </aside>
        @else
            <div class="frontpage-aside">@include('public.banner', ['slot' => 'sidebar'])</div>
        @endif

        <section class="latest-section" aria-labelledby="latest-heading">
            <div class="section-bar"><h2 id="latest-heading">Berita terbaru</h2><a href="/terbaru">Lihat semua</a></div>
            <div class="news-feed">
                @forelse($latest as $item)
                    @include('public.card', ['variant' => 'feed'])
                    @if($loop->iteration === 3)@include('public.banner', ['slot' => 'list_middle'])@endif
                @empty
                    <p class="reader-empty-note">{{ $featured->isEmpty() ? 'Redaksi sedang menyiapkan berita pertama.' : 'Belum ada berita terbaru lainnya.' }}</p>
                @endforelse
            </div>
        </section>
    </div>

    @if($picks->isNotEmpty())
        <section class="editors-section" aria-labelledby="picks-heading">
            <div class="section-bar"><h2 id="picks-heading">Pilihan redaksi</h2></div>
            <div class="reader-card-grid">
                @foreach($picks as $item)@include('public.card', ['variant' => 'tile'])@endforeach
            </div>
        </section>
    @endif

    @if($channels->isNotEmpty())
        <section class="channel-section" aria-labelledby="channels-heading">
            <div class="section-bar"><h2 id="channels-heading">Berita per kategori</h2></div>
            <div class="channel-sections">
                @foreach($channels as $channel)
                    <section class="channel-block">
                        <div class="channel-title"><h3><a href="/kanal/{{ $channel->slug }}">{{ $channel->name }}</a></h3><a href="/kanal/{{ $channel->slug }}">Lihat semua</a></div>
                        @foreach($channel->articles as $item)@include('public.card', ['variant' => 'compact'])@endforeach
                    </section>
                @endforeach
            </div>
        </section>
    @endif
@endsection
