{{-- Modal Layar Penuh Pencarian Interaktif --}}
<div class="kr-search-modal" id="krSearchModal" aria-hidden="true" role="dialog" aria-modal="true" aria-labelledby="krSearchModalTitle">
    <div class="kr-search-backdrop" id="krSearchBackdrop"></div>
    <div class="kr-search-modal-container">
        {{-- Header Modal Search --}}
        <div class="kr-search-header-bar">
            <div class="wrap kr-search-header-inner">
                <button type="button" class="kr-search-back-btn" id="krSearchCloseBtn" aria-label="Kembali dan tutup pencarian" title="Kembali">
                    <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="19" y1="12" x2="5" y2="12"></line>
                        <polyline points="12 19 5 12 12 5"></polyline>
                    </svg>
                </button>
                <form action="/cari" method="GET" class="kr-search-dialog-form" id="krSearchDialogForm" role="search">
                    <span class="kr-search-dialog-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="11" cy="11" r="8"></circle>
                            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                        </svg>
                    </span>
                    <input type="text" id="krSearchModalInput" name="q" class="kr-search-input-field" placeholder="Ketik kata kunci berita, tokoh, atau topik..." autocomplete="off" spellcheck="false" maxlength="100">
                    <button type="submit" class="kr-search-submit-btn">
                        <span>Cari</span>
                    </button>
                </form>
            </div>
        </div>

        {{-- Body Content Modal --}}
        <div class="kr-search-modal-scroll">
            <div class="wrap kr-search-body-inner">
                {{-- Live Search Result State (Akan muncul saat mengetik) --}}
                <div class="kr-search-live-results" id="krSearchLiveResults" style="display: none;">
                    <div class="kr-search-section-head">
                        <h3 class="kr-search-section-title">
                            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                            Saran Hasil Pencarian
                        </h3>
                        <span class="kr-search-count-pill" id="krSearchLiveCount">0 Ditemukan</span>
                    </div>

                    {{-- Saran Topik Terkait --}}
                    <div class="kr-search-topic-suggestions" id="krSearchTopicSuggestions" style="display: none;">
                        <span class="kr-search-topics-label">Topik Terkait:</span>
                        <div class="kr-search-topics-list" id="krSearchTopicsList"></div>
                    </div>

                    {{-- Daftar Artikel Hasil Live Search --}}
                    <div class="kr-search-live-grid" id="krSearchLiveGrid"></div>
                </div>

                {{-- Default State (Riwayat, Trending & Berita Populer) --}}
                <div class="kr-search-default-panel" id="krSearchDefaultPanel">
                    {{-- Row 1: Riwayat Pencarian & Topik Trending --}}
                    <div class="kr-search-meta-row">
                        {{-- Riwayat Pencarian (Recent Searches) --}}
                        <div class="kr-search-history-col" id="krSearchHistoryCol" style="display: none;">
                            <div class="kr-search-block-head">
                                <h4 class="kr-search-block-title">
                                    <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 14 16"/></svg>
                                    Riwayat Pencarian
                                </h4>
                                <button type="button" class="kr-search-history-clear" id="krSearchHistoryClear">Hapus Semua</button>
                            </div>
                            <div class="kr-search-history-chips" id="krSearchHistoryChips"></div>
                        </div>

                        {{-- Rekomendasi Topik Populer --}}
                        @if(!empty($navigation) && $navigation->isNotEmpty())
                        <div class="kr-search-trending-topics-col">
                            <div class="kr-search-block-head">
                                <h4 class="kr-search-block-title">
                                    <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg>
                                    Topik Hangat
                                </h4>
                            </div>
                            <div class="kr-search-trending-chips">
                                @foreach($navigation->take(8) as $topic)
                                    <a href="/kanal/{{ $topic->slug }}" class="kr-search-topic-chip">
                                        <span class="kr-search-topic-hash">#</span>{{ $topic->name }}
                                    </a>
                                @endforeach
                            </div>
                        </div>
                        @endif
                    </div>

                    {{-- Row 2: Berita Trending & Populer --}}
                    <div class="kr-search-discovery-row">
                        {{-- Kolom Kiri: Berita Trending --}}
                        <div class="kr-search-trending-articles-box">
                            <div class="kr-search-section-head">
                                <h3 class="kr-search-section-title">
                                    <span class="kr-search-flame-icon" aria-hidden="true">🔥</span>
                                    Berita Trending Saat Ini
                                </h3>
                                <a href="/terbaru" class="kr-search-see-all">Lihat Semua &rarr;</a>
                            </div>
                            <div class="kr-search-article-list">
                                @if(!empty($searchTrendingArticles))
                                    @foreach($searchTrendingArticles as $idx => $tArticle)
                                        <article class="kr-search-article-card">
                                            <span class="kr-search-rank-number">{{ str_pad($idx + 1, 2, '0', STR_PAD_LEFT) }}</span>
                                            <div class="kr-search-card-content">
                                                @if($tArticle->category)
                                                    <a href="/kanal/{{ $tArticle->category->slug }}" class="kr-search-card-category">{{ $tArticle->category->name }}</a>
                                                @endif
                                                <h4 class="kr-search-card-title">
                                                    <a href="{{ $tArticle->url }}">{{ $tArticle->title }}</a>
                                                </h4>
                                                <div class="kr-search-card-meta">
                                                    <time datetime="{{ $tArticle->published_at?->toIso8601String() }}">{{ $tArticle->published_at?->locale('id')->diffForHumans() }}</time>
                                                </div>
                                            </div>
                                            @if($tArticle->hero_media_id)
                                                <a href="{{ $tArticle->url }}" class="kr-search-card-thumb" tabindex="-1" aria-hidden="true">
                                                    <img src="/media/{{ $tArticle->hero_media_id }}/480" width="80" height="60" alt="" loading="lazy">
                                                </a>
                                            @endif
                                        </article>
                                    @endforeach
                                @endif
                            </div>
                        </div>

                        {{-- Kolom Kanan: Pilihan Populer Lainnya --}}
                        <div class="kr-search-popular-articles-box">
                            <div class="kr-search-section-head">
                                <h3 class="kr-search-section-title">
                                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                                    Paling Banyak Dibaca
                                </h3>
                            </div>
                            <div class="kr-search-article-list">
                                @if(!empty($searchPopularArticles))
                                    @foreach($searchPopularArticles as $pArticle)
                                        <article class="kr-search-article-card kr-search-article-card--compact">
                                            <div class="kr-search-card-content">
                                                @if($pArticle->category)
                                                    <a href="/kanal/{{ $pArticle->category->slug }}" class="kr-search-card-category">{{ $pArticle->category->name }}</a>
                                                @endif
                                                <h4 class="kr-search-card-title">
                                                    <a href="{{ $pArticle->url }}">{{ $pArticle->title }}</a>
                                                </h4>
                                                <div class="kr-search-card-meta">
                                                    <time datetime="{{ $pArticle->published_at?->toIso8601String() }}">{{ $pArticle->published_at?->locale('id')->diffForHumans() }}</time>
                                                </div>
                                            </div>
                                            @if($pArticle->hero_media_id)
                                                <a href="{{ $pArticle->url }}" class="kr-search-card-thumb" tabindex="-1" aria-hidden="true">
                                                    <img src="/media/{{ $pArticle->hero_media_id }}/480" width="80" height="60" alt="" loading="lazy">
                                                </a>
                                            @endif
                                        </article>
                                    @endforeach
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
