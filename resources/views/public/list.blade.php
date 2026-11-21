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
                        <div class="kr-channel-pills" role="tablist" aria-label="Filter Kategori Berita">
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

                        {{-- Search Autocomplete Box untuk 1000+ Kategori --}}
                        <div class="kr-category-search-box" id="krCategorySearchBox">
                            <div class="kr-category-search-input-wrap">
                                <svg class="kr-category-search-icon" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M9 3.5a5.5 5.5 0 100 11 5.5 5.5 0 000-11zM2 9a7 7 0 1112.452 4.391l3.328 3.329a.75.75 0 11-1.06 1.06l-3.329-3.328A7 7 0 012 9z" clip-rule="evenodd" />
                                </svg>
                                <input type="text" id="krCategorySearchInput" placeholder="Cari kategori..." autocomplete="off" aria-label="Cari kategori berita">
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
                                        dropdown.innerHTML = '<div class="kr-category-item-empty">Tidak ada kategori ditemukan</div>';
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
            <div class="news-feed">
                @forelse($articles as $item)
                    @include('public.card', ['variant' => 'feed'])
                @empty
                    <div class="reader-empty"><h2>Belum ada berita yang sesuai.</h2><a href="/terbaru">Lihat berita terbaru</a></div>
                @endforelse
            </div>
            {{ $articles->onEachSide(1)->links('public.pagination') }}
        </section>
        <aside class="listing-aside">@include('public.banner',['slot'=>'sidebar'])</aside>
    </div>
@endsection
