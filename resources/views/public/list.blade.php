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
                <div class="kr-channel-filter-wrap">
                    <div class="kr-channel-top-row">
                        <div class="kr-channel-pills" role="tablist" aria-label="Filter Topik Berita">
                            <a href="/terbaru" class="kr-channel-pill {{ !request('category_id') ? 'is-active' : '' }}">
                                Semua
                            </a>
                            @if($selectedCategory && !$filterCategories->contains('id', $selectedCategory->id))
                                <a href="/terbaru?category_id={{ $selectedCategory->id }}" class="kr-channel-pill is-active">
                                    {{ $selectedCategory->name }}
                                </a>
                            @endif
                            @foreach($filterCategories as $c)
                                <a href="/terbaru?category_id={{ $c->id }}" class="kr-channel-pill {{ request('category_id') == $c->id ? 'is-active' : '' }}">
                                    {{ $c->name }}
                                </a>
                            @endforeach
                        </div>

                        {{-- Search Autocomplete Box untuk 1000+ Topik --}}
                        <div class="kr-category-search-box" id="krCategorySearchBox">
                            <div class="kr-category-search-input-wrap">
                                <svg class="kr-category-search-icon" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M9 3.5a5.5 5.5 0 100 11 5.5 5.5 0 000-11zM2 9a7 7 0 1112.452 4.391l3.328 3.329a.75.75 0 11-1.06 1.06l-3.329-3.328A7 7 0 012 9z" clip-rule="evenodd" />
                                </svg>
                                <input type="text" id="krCategorySearchInput" placeholder="Cari topik..." autocomplete="off" aria-label="Cari topik berita">
                                <button type="button" id="krCategorySearchClear" class="kr-category-search-clear" style="display: none;" aria-label="Hapus pencarian">&times;</button>
                            </div>
                            <div class="kr-category-dropdown" id="krCategoryDropdown" style="display: none;"></div>
                        </div>
                    </div>
                </div>

                <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const box = document.getElementById('krCategorySearchBox');
                    const input = document.getElementById('krCategorySearchInput');
                    const clearBtn = document.getElementById('krCategorySearchClear');
                    const dropdown = document.getElementById('krCategoryDropdown');
                    if (!input || !dropdown) return;

                    let debounceTimer = null;

                    input.addEventListener('input', function() {
                        const q = this.value.trim();
                        clearBtn.style.display = q ? 'block' : 'none';
                        clearTimeout(debounceTimer);

                        if (q.length < 1) {
                            dropdown.style.display = 'none';
                            dropdown.innerHTML = '';
                            return;
                        }

                        debounceTimer = setTimeout(() => {
                            fetch(`/api/categories/search?q=${encodeURIComponent(q)}`)
                                .then(res => res.json())
                                .then(data => {
                                    if (!data || data.length === 0) {
                                        dropdown.innerHTML = '<div class="kr-category-item-empty">Tidak ada topik ditemukan</div>';
                                        dropdown.style.display = 'block';
                                        return;
                                    }
                                    dropdown.innerHTML = data.map(item => `
                                        <a href="/terbaru?category_id=${item.id}" class="kr-category-item">
                                            <span>${item.name}</span>
                                            <svg viewBox="0 0 20 20" fill="currentColor" width="14" height="14">
                                                <path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd"/>
                                            </svg>
                                        </a>
                                    `).join('');
                                    dropdown.style.display = 'block';
                                })
                                .catch(() => {
                                    dropdown.style.display = 'none';
                                });
                        }, 250);
                    });

                    clearBtn.addEventListener('click', function() {
                        input.value = '';
                        clearBtn.style.display = 'none';
                        dropdown.style.display = 'none';
                        dropdown.innerHTML = '';
                        input.focus();
                    });

                    document.addEventListener('click', function(e) {
                        if (box && !box.contains(e.target)) {
                            dropdown.style.display = 'none';
                        }
                    });

                    input.addEventListener('focus', function() {
                        if (dropdown.innerHTML.trim().length > 0) {
                            dropdown.style.display = 'block';
                        }
                    });

                    // Drag-to-scroll & wheel scroll di Desktop untuk daftar pill kategori
                    const pillsContainer = document.querySelector('.kr-channel-pills');
                    if (pillsContainer) {
                        let isDown = false;
                        let startX;
                        let scrollLeft;

                        pillsContainer.addEventListener('mousedown', (e) => {
                            isDown = true;
                            startX = e.pageX - pillsContainer.offsetLeft;
                            scrollLeft = pillsContainer.scrollLeft;
                        });
                        pillsContainer.addEventListener('mouseleave', () => { isDown = false; });
                        pillsContainer.addEventListener('mouseup', () => { isDown = false; });
                        pillsContainer.addEventListener('mousemove', (e) => {
                            if (!isDown) return;
                            e.preventDefault();
                            const x = e.pageX - pillsContainer.offsetLeft;
                            const walk = (x - startX) * 1.5;
                            pillsContainer.scrollLeft = scrollLeft - walk;
                        });

                        // Wheel scroll support (scroll mouse wheel vertikal geser horizontal)
                        pillsContainer.addEventListener('wheel', (e) => {
                            if (e.deltaY !== 0) {
                                e.preventDefault();
                                pillsContainer.scrollLeft += e.deltaY;
                            }
                        }, { passive: false });
                    }
                });
                </script>
            @endif
            <div class="news-feed news-feed--mixed">
                @forelse($articles as $item)
                    @php
                        // Memberikan variasi desain setiap beberapa artikel untuk vibe editorial dinamis
                        // Index 0: Lead Featured Card (besar)
                        // Index 4, 9: Highlight Card beraksen
                        // Lainnya: Feed standar
                        $cardDesign = 'feed';
                        if ($loop->first && $articles->currentPage() === 1) {
                            $cardDesign = 'lead';
                        } elseif (in_array($loop->iteration, [5, 11])) {
                            $cardDesign = 'compact-accent';
                        }
                    @endphp

                    @if($cardDesign === 'lead')
                        <article class="reader-card reader-card--lead {{ !$item->hero_media_id ? 'reader-card--text' : '' }}">
                            @if($item->hero_media_id)
                                <a class="story-image story-image--lead story-image--with-watermark" href="{{ $item->url }}">
                                    <img src="/media/{{ $item->hero_media_id }}/480" width="700" height="400" alt="{{ $item->hero_alt }}" loading="eager">
                                    <span class="thumbnail-watermark" aria-hidden="true">
                                        <img src="/images/logo-kapuasraya.png" width="100" height="24" alt="KAPUAS RAYA">
                                    </span>
                                    <span class="kr-lead-badge"><span class="kr-live-dot"></span> BERITA UTAMA</span>
                                </a>
                            @endif
                            <div class="reader-card-body reader-card-body--lead">
                                <div class="story-meta">
                                    <a href="/kanal/{{ $item->category?->slug }}">{{ $item->category?->name }}</a>
                                    @if($item->sponsored)<span>Konten sponsor</span>@endif
                                    <time datetime="{{ $item->published_at?->toIso8601String() }}">{{ $item->published_at?->timezone('Asia/Jakarta')->format('d M Y, H:i') }} WIB</time>
                                </div>
                                <h2 class="reader-lead-title"><a href="{{ $item->url }}">{{ $item->title }}</a></h2>
                                <p class="reader-lead-summary">{{ $item->summary }}</p>
                            </div>
                        </article>
                    @elseif($cardDesign === 'compact-accent')
                        <article class="reader-card reader-card--accent {{ !$item->hero_media_id ? 'reader-card--text' : '' }}">
                            <div class="reader-card-accent-badge">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                                <span>PILIHAN</span>
                            </div>
                            <div class="reader-card-body">
                                <div class="story-meta"><a href="/kanal/{{ $item->category?->slug }}">{{ $item->category?->name }}</a></div>
                                <h3><a href="{{ $item->url }}">{{ $item->title }}</a></h3>
                                <p>{{ $item->summary }}</p>
                                <time datetime="{{ $item->published_at?->toIso8601String() }}">{{ $item->published_at?->timezone('Asia/Jakarta')->format('d M Y, H:i') }} WIB</time>
                            </div>
                            @if($item->hero_media_id)
                                <a class="story-image" href="{{ $item->url }}">
                                    <img src="/media/{{ $item->hero_media_id }}/480" width="160" height="110" alt="{{ $item->hero_alt }}" loading="lazy">
                                </a>
                            @endif
                        </article>
                    @else
                        @include('public.card', ['variant' => 'feed'])
                    @endif

                    @if($loop->iteration === 6)
                        @include('public.banner', ['slot' => 'list_middle'])
                    @endif
                @empty
                    <div class="reader-empty"><h2>Belum ada berita yang sesuai.</h2><a href="/terbaru">Lihat berita terbaru</a></div>
                @endforelse
            </div>
            {{ $articles->onEachSide(1)->links('public.pagination') }}
        </section>

        {{-- SIDEBAR KANAN: Berita Trending & Berita Pilihan Lainnya --}}
        <aside class="listing-aside">
            {{-- WIDGET 1: Berita Trending --}}
            @if(isset($trendingArticles) && $trendingArticles->isNotEmpty())
                <div class="kr-sidebar-card kr-trending-widget">
                    <div class="kr-sidebar-header">
                        <div class="kr-sidebar-title-wrap">
                            <span class="kr-trending-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M23 6l-9.5 9.5-5-5L1 18"></path>
                                    <path d="M17 6h6v6"></path>
                                </svg>
                            </span>
                            <h3>Sedang Trending</h3>
                        </div>
                        <span class="kr-badge-pulse">HOT</span>
                    </div>
                    <ol class="kr-ranked-list">
                        @foreach($trendingArticles as $item)
                            <li class="kr-ranked-item">
                                <span class="kr-rank-num kr-rank-num--{{ $loop->iteration <= 3 ? 'top' : 'normal' }}">{{ $loop->iteration }}</span>
                                <div class="kr-ranked-body">
                                    @if($item->category)
                                        <a class="kr-ranked-category" href="/kanal/{{ $item->category->slug }}">{{ $item->category->name }}</a>
                                    @endif
                                    <h4><a href="{{ $item->url }}">{{ $item->title }}</a></h4>
                                    <time datetime="{{ $item->published_at?->toIso8601String() }}">{{ $item->published_at?->timezone('Asia/Jakarta')->diffForHumans() }}</time>
                                </div>
                                @if($item->hero_media_id)
                                    <a class="kr-ranked-thumb" href="{{ $item->url }}" aria-hidden="true" tabindex="-1">
                                        <img src="/media/{{ $item->hero_media_id }}/480" alt="" width="68" height="54" loading="lazy">
                                    </a>
                                @endif
                            </li>
                        @endforeach
                    </ol>
                </div>
            @endif

            {{-- WIDGET BANNER IKLAN --}}
            @include('public.banner', ['slot' => 'sidebar'])

            {{-- WIDGET 2: Berita Pilihan Lainnya (Desain Kartu Miniatur Grid) --}}
            @if(isset($otherArticles) && $otherArticles->isNotEmpty())
                <div class="kr-sidebar-card kr-other-stories-widget">
                    <div class="kr-sidebar-header">
                        <div class="kr-sidebar-title-wrap">
                            <span class="kr-other-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="12" cy="12" r="10"></circle>
                                    <polygon points="12 6 12 12 16 14"></polygon>
                                </svg>
                            </span>
                            <h3>Kabar Menarik Lainnya</h3>
                        </div>
                    </div>
                    <div class="kr-other-stories-list">
                        @foreach($otherArticles as $item)
                            <article class="kr-other-card">
                                @if($item->hero_media_id)
                                    <a class="kr-other-thumb" href="{{ $item->url }}">
                                        <img src="/media/{{ $item->hero_media_id }}/480" width="120" height="80" alt="{{ $item->hero_alt }}" loading="lazy">
                                    </a>
                                @endif
                                <div class="kr-other-content">
                                    @if($item->category)
                                        <a class="kr-other-cat" href="/kanal/{{ $item->category->slug }}">{{ $item->category->name }}</a>
                                    @endif
                                    <h4><a href="{{ $item->url }}">{{ $item->title }}</a></h4>
                                    <time datetime="{{ $item->published_at?->toIso8601String() }}">{{ $item->published_at?->timezone('Asia/Jakarta')->translatedFormat('d M Y') }}</time>
                                </div>
                            </article>
                        @endforeach
                    </div>
                </div>
            @endif
        </aside>
    </div>
@endsection
