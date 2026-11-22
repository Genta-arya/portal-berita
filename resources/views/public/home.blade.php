@extends('layouts.public')

@section('content')
    <h1 class="sr-only">Beranda berita</h1>
    @include('public.banner', ['slot' => 'home_top'])

    @if($trendingTopics->isNotEmpty())
        <section class="topic-shelf" aria-label="Trending Topics" data-topic-shelf>
            <div class="topic-shelf-header">
                <div class="topic-shelf-title">
                    <span class="topic-shelf-badge">
                        <span class="pulse-beacon" aria-hidden="true">
                            <span class="pulse-beacon-ring"></span>
                            <span class="pulse-beacon-dot"></span>
                        </span>
                        Trending Topik
                    </span>
                    <h2 class="sr-only">Trending Topik</h2>
                </div>
                <div class="topic-shelf-controls">
                    <button type="button" class="topic-nav-btn" data-topic-prev aria-label="Geser topik ke kiri" title="Sebelumnya">
                        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15 18l-6-6 6-6"/></svg>
                    </button>
                    <button type="button" class="topic-nav-btn" data-topic-next aria-label="Geser topik ke kanan" title="Berikutnya">
                        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 6l6 6-6 6"/></svg>
                    </button>
                </div>
            </div>
            <div class="topic-shelf-scroller">
                <ul class="topic-list" data-topic-track tabindex="0">
                    @foreach($trendingTopics->take(9) as $channel)
                        @php
                            $image = $channel->articles->firstWhere('hero_media_id', '!=', null);
                        @endphp
                        <li>
                            <a href="/kanal/{{ $channel->slug }}" class="topic-link" data-story-topic="{{ $channel->id }}" aria-haspopup="dialog">
                                <span class="topic-image-wrap">
                                    <span class="topic-image" aria-hidden="true">
                                        @if($image)
                                            <img src="/media/{{ $image->hero_media_id }}/480" alt="" width="80" height="80" loading="lazy">
                                        @else
                                            <span class="topic-initials">{{ mb_strtoupper(mb_substr($channel->name, 0, 2)) }}</span>
                                        @endif
                                    </span>
                                </span>
                                <span class="topic-meta">
                                    <strong class="topic-name">{{ mb_strtoupper($channel->name) }}</strong>
                                </span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        </section>
        @include('public.topic-stories')
    @endif

    <div class="frontpage-grid">
        @if($featured->isNotEmpty())
            <section class="frontpage-hero" aria-label="Sorotan utama">
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
        @php
            $firstChannel = $channels->first();
            $otherChannels = $channels->skip(1);
        @endphp
        <section class="channel-section" aria-label="Topik Berita">
            {{-- KATEGORI 1: FLEX SLIDE FULL-WIDTH --}}
            @if($firstChannel)
                <section class="channel-block channel-block--flex-slide" data-flex-slider aria-label="Topik Utama: {{ $firstChannel->name }}">
                    <div class="channel-flex-header">
                        <div class="channel-flex-title-wrap">
                            <span class="channel-flex-pill">Topik Utama</span>
                            <h3><a href="/kanal/{{ $firstChannel->slug }}">{{ $firstChannel->name }}</a></h3>
                        </div>
                        <div class="channel-flex-controls">
                            <button type="button" class="channel-flex-nav-btn" data-flex-prev aria-label="Geser ke kiri" title="Sebelumnya">
                                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15 18l-6-6 6-6"/></svg>
                            </button>
                            <button type="button" class="channel-flex-nav-btn" data-flex-next aria-label="Geser ke kanan" title="Berikutnya">
                                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 6l6 6-6 6"/></svg>
                            </button>
                        </div>
                    </div>
                    <div class="channel-flex-track" data-flex-track tabindex="0">
                        @foreach($firstChannel->articles as $item)
                            <article class="channel-flex-card">
                                @if($item->hero_media_id)
                                    <a class="channel-flex-image" href="{{ $item->url }}">
                                        <img src="/media/{{ $item->hero_media_id }}/480" width="360" height="210" alt="{{ $item->hero_alt }}" loading="lazy">
                                        <span class="thumbnail-watermark" aria-hidden="true">
                                            <img src="/images/logo-kapuasraya.png" width="95" height="22" alt="KAPUAS RAYA">
                                        </span>
                                    </a>
                                @endif
                                <div class="channel-flex-body">
                                    <div class="story-meta">
                                        <time datetime="{{ $item->published_at?->toIso8601String() }}">{{ $item->published_at?->timezone('Asia/Jakarta')->format('d M Y') }}</time>
                                    </div>
                                    <h4><a href="{{ $item->url }}">{{ $item->title }}</a></h4>
                                    @if($item->summary)
                                        <p>{{ Str::limit($item->summary, 85) }}</p>
                                    @endif
                                </div>
                            </article>
                        @endforeach
                    </div>
                    <div class="channel-flex-footer">
                        <a href="/kanal/{{ $firstChannel->slug }}" class="channel-flex-view-all-btn">
                            Lihat semua berita {{ $firstChannel->name }}
                            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14m-7-7 7 7-7 7"/></svg>
                        </a>
                    </div>
                </section>
            @endif

            {{-- 8 SISA KATEGORI: GRID GENAP 2 KOLOM (4 PASANG) DENGAN 4 VARIASI DESAIN --}}
            <div class="channel-sections-diverse">
                @foreach($otherChannels as $index => $channel)
                    @php
                        $layoutType = $index % 4;
                    @endphp
                    
                    {{-- VARIATION 0: Big Hero Card with Top Banner Image & Excerpt, followed by mini compacts --}}
                    @if($layoutType === 0)
                        <section class="channel-block channel-block--hero-lead">
                            <div class="channel-title">
                                <h3><a href="/kanal/{{ $channel->slug }}">{{ $channel->name }}</a></h3>
                                <a href="/kanal/{{ $channel->slug }}">Lihat semua</a>
                            </div>
                            <div class="channel-articles">
                                @foreach($channel->articles as $item)
                                    @if($loop->first && $item->hero_media_id)
                                        <article class="channel-lead-card">
                                            <a class="channel-lead-image" href="{{ $item->url }}">
                                                <img src="/media/{{ $item->hero_media_id }}/480" width="480" height="270" alt="{{ $item->hero_alt }}" loading="lazy">
                                                <span class="thumbnail-watermark" aria-hidden="true">
                                                    <img src="/images/logo-kapuasraya.png" width="110" height="26" alt="KAPUAS RAYA">
                                                </span>
                                            </a>
                                            <div class="channel-lead-body">
                                                <div class="story-meta">
                                                    <a href="/kanal/{{ $channel->slug }}">{{ $channel->name }}</a>
                                                    <time datetime="{{ $item->published_at?->toIso8601String() }}">{{ $item->published_at?->timezone('Asia/Jakarta')->format('d M Y, H:i') }} WIB</time>
                                                </div>
                                                <h4><a href="{{ $item->url }}">{{ $item->title }}</a></h4>
                                                @if($item->summary)
                                                    <p>{{ Str::limit($item->summary, 115) }}</p>
                                                @endif
                                            </div>
                                        </article>
                                    @else
                                        @include('public.card', ['variant' => 'compact'])
                                    @endif
                                @endforeach
                            </div>
                        </section>

                    {{-- VARIATION 1: Grid of 2 Vertical Cards side-by-side with full images --}}
                    @elseif($layoutType === 1)
                        <section class="channel-block channel-block--split-cards">
                            <div class="channel-title">
                                <h3><a href="/kanal/{{ $channel->slug }}">{{ $channel->name }}</a></h3>
                                <a href="/kanal/{{ $channel->slug }}">Lihat semua</a>
                            </div>
                            <div class="channel-split-grid">
                                @foreach($channel->articles->take(2) as $item)
                                    <article class="channel-grid-card">
                                        @if($item->hero_media_id)
                                            <a class="channel-grid-image" href="{{ $item->url }}">
                                                <img src="/media/{{ $item->hero_media_id }}/480" width="480" height="260" alt="{{ $item->hero_alt }}" loading="lazy">
                                                <span class="thumbnail-watermark" aria-hidden="true">
                                                    <img src="/images/logo-kapuasraya.png" width="95" height="22" alt="KAPUAS RAYA">
                                                </span>
                                            </a>
                                        @endif
                                        <div class="channel-grid-body">
                                            <div class="story-meta">
                                                <time datetime="{{ $item->published_at?->toIso8601String() }}">{{ $item->published_at?->timezone('Asia/Jakarta')->format('d M Y') }}</time>
                                            </div>
                                            <h4><a href="{{ $item->url }}">{{ $item->title }}</a></h4>
                                        </div>
                                    </article>
                                @endforeach
                            </div>
                            @if($channel->articles->count() > 2)
                                <div class="channel-mini-list">
                                    @foreach($channel->articles->skip(2) as $item)
                                        @include('public.card', ['variant' => 'compact'])
                                    @endforeach
                                </div>
                            @endif
                        </section>

                    {{-- VARIATION 2: Overlay Image Card with dark gradient and bold white text --}}
                    @elseif($layoutType === 2)
                        <section class="channel-block channel-block--overlay">
                            <div class="channel-title">
                                <h3><a href="/kanal/{{ $channel->slug }}">{{ $channel->name }}</a></h3>
                                <a href="/kanal/{{ $channel->slug }}">Lihat semua</a>
                            </div>
                            <div class="channel-articles">
                                @if($first = $channel->articles->first())
                                    <article class="channel-overlay-card">
                                        @if($first->hero_media_id)
                                            <img src="/media/{{ $first->hero_media_id }}/480" width="480" height="280" alt="{{ $first->hero_alt }}" class="channel-overlay-bg" loading="lazy">
                                            <span class="thumbnail-watermark" aria-hidden="true">
                                                <img src="/images/logo-kapuasraya.png" width="105" height="24" alt="KAPUAS RAYA">
                                            </span>
                                        @endif
                                        <div class="channel-overlay-content">
                                            <span class="overlay-badge">{{ $channel->name }}</span>
                                            <h4><a href="{{ $first->url }}">{{ $first->title }}</a></h4>
                                            <time datetime="{{ $first->published_at?->toIso8601String() }}">{{ $first->published_at?->timezone('Asia/Jakarta')->format('d M Y, H:i') }} WIB</time>
                                        </div>
                                    </article>
                                @endif
                                @foreach($channel->articles->skip(1) as $item)
                                    @include('public.card', ['variant' => 'compact'])
                                @endforeach
                            </div>
                        </section>

                    {{-- VARIATION 3: Numbered Editorial Ranking List with red accented badges --}}
                    @else
                        <section class="channel-block channel-block--ranked">
                            <div class="channel-title">
                                <h3><a href="/kanal/{{ $channel->slug }}">{{ $channel->name }}</a></h3>
                                <a href="/kanal/{{ $channel->slug }}">Lihat semua</a>
                            </div>
                            <ol class="channel-ranked-list">
                                @foreach($channel->articles as $item)
                                    <li class="channel-ranked-item">
                                        <span class="ranked-badge">0{{ $loop->iteration }}</span>
                                        <div class="ranked-content">
                                            <div class="story-meta">
                                                <time datetime="{{ $item->published_at?->toIso8601String() }}">{{ $item->published_at?->timezone('Asia/Jakarta')->format('d M Y') }}</time>
                                            </div>
                                            <h4><a href="{{ $item->url }}">{{ $item->title }}</a></h4>
                                        </div>
                                        @if($item->hero_media_id)
                                            <a class="ranked-thumb" href="{{ $item->url }}">
                                                <img src="/media/{{ $item->hero_media_id }}/480" width="75" height="60" alt="{{ $item->hero_alt }}" loading="lazy">
                                            </a>
                                        @endif
                                    </li>
                                @endforeach
                            </ol>
                        </section>
                    @endif
                @endforeach
            </div>
        </section>
    @endif
@endsection
