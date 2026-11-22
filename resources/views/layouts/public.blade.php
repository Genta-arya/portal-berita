<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <link rel="preload" href="/images/logo-kapuasraya.png" as="image" type="image/png">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script>
        (function(){
            try {
                var t = localStorage.getItem('portal_theme') || 'light';
                if(t === 'dark'){
                    document.documentElement.setAttribute('data-theme','dark');
                    document.documentElement.classList.add('dark');
                } else {
                    document.documentElement.setAttribute('data-theme','light');
                    document.documentElement.classList.remove('dark');
                }
            } catch(e){}
        })();

        window.toggleThemeMode = function(event) {
            var btn = document.getElementById('theme-toggle-btn');
            var isDark = document.documentElement.getAttribute('data-theme') === 'dark';
            var next = isDark ? 'light' : 'dark';

            var rect = btn ? btn.getBoundingClientRect() : { left: window.innerWidth / 2, top: 0, width: 0, height: 0 };
            var x = (event && event.clientX) ? event.clientX : (rect.left + rect.width / 2);
            var y = (event && event.clientY) ? event.clientY : (rect.top + rect.height / 2);
            var endRadius = Math.hypot(
                Math.max(x, window.innerWidth - x),
                Math.max(y, window.innerHeight - y)
            );

            if (btn) {
                btn.classList.add('is-blooming');
                setTimeout(function() { btn.classList.remove('is-blooming'); }, 850);
            }

            var applyTheme = function() {
                document.documentElement.setAttribute('data-theme', next);
                if (next === 'dark') {
                    document.documentElement.classList.add('dark');
                } else {
                    document.documentElement.classList.remove('dark');
                }
                try { localStorage.setItem('portal_theme', next); } catch(e){}
                if (btn) {
                    btn.setAttribute('aria-pressed', next === 'dark' ? 'true' : 'false');
                    btn.setAttribute('aria-label', next === 'dark' ? 'Ganti ke mode terang' : 'Ganti ke mode gelap');
                    var lbl = btn.querySelector('[data-theme-label]');
                    if (lbl) lbl.textContent = next === 'dark' ? 'Mode Terang' : 'Mode Gelap';
                }
            };

            if (document.startViewTransition && !window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
                var transition = document.startViewTransition(function() {
                    applyTheme();
                });
                transition.ready.then(function() {
                    var clipPath = [
                        'circle(0px at ' + x + 'px ' + y + 'px)',
                        'circle(' + endRadius + 'px at ' + x + 'px ' + y + 'px)'
                    ];
                    document.documentElement.animate(
                        {
                            clipPath: clipPath,
                            filter: ['brightness(1.15) contrast(1.04) saturate(1.1)', 'brightness(1) contrast(1) saturate(1)']
                        },
                        {
                            duration: 700,
                            easing: 'cubic-bezier(0.25, 1, 0.35, 1)',
                            pseudoElement: '::view-transition-new(root)'
                        }
                    );
                });
            } else {
                applyTheme();
            }
        };
    </script>
    <title>@yield('title', $settings['name'] ?? 'Portal Berita')</title>
    <meta name="description" content="@yield('description', $settings['description'] ?? 'Berita terkini dan perspektif jernih.')">
    <link rel="canonical" href="@yield('canonical', request()->url().(request()->integer('page')>1?'?page='.request()->integer('page'):''))">
    <meta property="og:title" content="@yield('title', $settings['name'] ?? 'Portal Berita')">
    <meta property="og:type" content="@yield('ogtype', 'website')">
    <meta property="og:url" content="@yield('canonical', request()->url())">
    <meta property="og:description" content="@yield('description', $settings['description'] ?? 'Berita terkini')">
    @if(request()->is('cari','admin/*') || isset($preview))<meta name="robots" content="noindex">@endif
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
    <link rel="shortcut icon" href="/favicon.ico">
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
    @if(!empty($settings['favicon_media_id']))
        <link rel="icon" href="/media/{{ $settings['favicon_media_id'] }}/480">
    @endif
    @vite(['resources/css/sage.css', 'resources/css/editorial.css'])
    @stack('head')
</head>
<body class="reader">
{{-- Full Page Semi-3D Interactive Preloader --}}
<div class="kr-page-loader" id="krPageLoader" aria-hidden="true">
    <div class="kr-3d-scene">
        <div class="kr-3d-cube">
            <div class="kr-cube-face kr-cube-front"></div>
            <div class="kr-cube-face kr-cube-back"></div>
            <div class="kr-cube-face kr-cube-right"></div>
            <div class="kr-cube-face kr-cube-left"></div>
            <div class="kr-cube-face kr-cube-top"></div>
            <div class="kr-cube-face kr-cube-bottom"></div>
        </div>
        <div class="kr-3d-shadow"></div>
    </div>
    <div class="kr-loader-status">
        <div class="kr-loader-bar-track">
            <div class="kr-loader-bar-fill"></div>
        </div>
        <div class="kr-loader-text">Memuat...</div>
    </div>
</div>
<script>
(function() {
    // Hide page preloader when page finishes loading
    window.addEventListener('load', function() {
        var loader = document.getElementById('krPageLoader');
        if (loader) {
            loader.classList.add('is-loaded');
            setTimeout(function() {
                loader.remove();
            }, 600);
        }
    });
    // Fallback: maximum 2.5s to ensure content is never blocked
    setTimeout(function() {
        var loader = document.getElementById('krPageLoader');
        if (loader && !loader.classList.contains('is-loaded')) {
            loader.classList.add('is-loaded');
            setTimeout(function() { loader.remove(); }, 600);
        }
    }, 2500);
})();
</script>
<a class="skip" href="#content">Lewati ke isi</a>
<div class="edition-bar">
    <div class="wrap edition-inner">
        <div class="edition-time">
            <span>{{ now()->locale('id')->timezone('Asia/Jakarta')->translatedFormat('l, d F Y') }}</span>
            <span class="edition-separator">·</span>
            <span class="edition-clock" id="krLiveClock">{{ now()->timezone('Asia/Jakarta')->format('H:i') }}</span>
            <span class="edition-zone">WIB</span>
        </div>
        <div class="edition-social" aria-label="Media sosial redaksi">
            <span class="edition-social-label">Ikuti Kami:</span>
            <a href="https://facebook.com" target="_blank" rel="noopener noreferrer" class="edition-social-link" title="Facebook Redaksi Kapuas Raya" aria-label="Facebook Redaksi">
                <svg viewBox="0 0 24 24" width="13" height="13" fill="currentColor"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
            </a>
            <a href="https://instagram.com" target="_blank" rel="noopener noreferrer" class="edition-social-link" title="Instagram Redaksi Kapuas Raya" aria-label="Instagram Redaksi">
                <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="2" width="20" height="20" rx="5" ry="5"></rect><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"></path><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"></line></svg>
            </a>
            <a href="https://twitter.com" target="_blank" rel="noopener noreferrer" class="edition-social-link" title="X (Twitter) Redaksi Kapuas Raya" aria-label="X Redaksi">
                <svg viewBox="0 0 24 24" width="12" height="12" fill="currentColor"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
            </a>
            <a href="https://youtube.com" target="_blank" rel="noopener noreferrer" class="edition-social-link" title="YouTube Redaksi Kapuas Raya" aria-label="YouTube Redaksi">
                <svg viewBox="0 0 24 24" width="13" height="13" fill="currentColor"><path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>
            </a>
            <a href="https://tiktok.com" target="_blank" rel="noopener noreferrer" class="edition-social-link" title="TikTok Redaksi Kapuas Raya" aria-label="TikTok Redaksi">
                <svg viewBox="0 0 24 24" width="13" height="13" fill="currentColor"><path d="M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-.93-.01 2.92.01 5.84-.02 8.75-.08 1.4-.54 2.79-1.35 3.94-1.31 1.92-3.58 3.17-5.91 3.21-1.43.08-2.86-.31-4.08-1.03-2.02-1.19-3.44-3.37-3.65-5.71-.02-.5-.03-1-.01-1.49.18-1.9 1.12-3.72 2.58-4.96 1.66-1.44 3.98-2.13 6.15-1.72.02 1.48-.04 2.96-.04 4.44-.99-.32-2.15-.23-3.02.37-.63.41-1.11 1.04-1.36 1.75-.21.51-.24 1.07-.14 1.61.24 1.64 1.82 3.02 3.5 2.87 1.12-.01 2.19-.66 2.77-1.61.19-.33.4-.67.41-1.06.1-1.79.06-3.57.07-5.36.01-4.03-.01-8.05.02-12.07z"/></svg>
            </a>
        </div>
    </div>
</div>
<div class="sticky-header-group">
@if($tickerArticles->isNotEmpty())
<aside class="latest-ticker" aria-label="10 berita terbaru" data-latest-ticker><div class="wrap ticker-inner"><strong class="ticker-label"><span class="fire-flame-wrap" aria-hidden="true"><i class="ember ember-1"></i><i class="ember ember-2"></i><i class="ember ember-3"></i></span>TERBARU</strong><div class="ticker-viewport"><div class="ticker-track"><ul class="ticker-items">@foreach($tickerArticles as $tickerArticle)<li><a href="{{ $tickerArticle->url }}">{{ $tickerArticle->title }}</a></li>@endforeach</ul><ul class="ticker-items ticker-copy" aria-hidden="true" inert>@foreach($tickerArticles as $tickerArticle)<li><a href="{{ $tickerArticle->url }}" tabindex="-1">{{ $tickerArticle->title }}</a></li>@endforeach</ul></div></div></div></aside>
@endif
<header class="reader-header">
    <div class="wrap reader-masthead">
        <a class="reader-brand" href="/" aria-label="{{ $settings['name'] ?? 'KAPUAS RAYA' }} — Beranda">
            <img src="/images/logo-kapuasraya.png" width="200" height="46" alt="{{ $settings['name'] ?? 'KAPUAS RAYA' }}" style="height: 44px; width: auto; max-width: 260px; object-fit: contain;">
        </a>
        <div class="reader-search" role="button" tabindex="0" id="krSearchTrigger" aria-label="Buka pencarian berita" title="Cari berita (Klik untuk membuka)">
            <span class="reader-search-placeholder">Cari berita, tokoh, atau topik...</span>
            <button type="button" aria-label="Cari berita" tabindex="-1"><svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><circle cx="10.5" cy="10.5" r="6.5"/><path d="m16 16 5 5"/></svg></button>
        </div>
    </div>
</header>
<div class="nav-band"><div class="wrap nav-inner">
    <nav class="reader-nav" aria-label="Navigasi utama">
        <a href="/" @if(request()->is('/')) aria-current="page" @endif>Beranda</a>
        <a href="/terbaru" @if(request()->is('terbaru')) aria-current="page" @endif>Index</a>
        @if($navigation->isNotEmpty())
        <details class="category-dropdown" data-category-dropdown>
            <summary>Topik <svg width="14" height="14" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path d="m5 7 5 5 5-5"/></svg></summary>
            <div class="category-popover"><strong>Semua topik</strong><div class="category-options">@foreach($navigation as $channel)<a href="/kanal/{{ $channel->slug }}" @if(request()->is('kanal/'.$channel->slug)) aria-current="page" @endif>{{ $channel->name }}</a>@endforeach</div></div>
        </details>
        @endif
        <a href="/halaman/tentang" @if(request()->is('halaman/tentang')) aria-current="page" @endif>Tentang Kami</a>
    </nav>
    <div class="nav-theme-toggle">
        <button type="button" class="theme-toggle-btn" id="theme-toggle-btn" onclick="toggleThemeMode(event)" aria-label="Ganti mode tema gelap atau terang" title="Ganti mode (Gelap / Terang)">
            <span class="theme-toggle-icon-wrap" aria-hidden="true">
                <svg class="theme-icon theme-icon-sun" viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="4"/>
                    <path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M6.34 17.66l-1.41 1.41M19.07 4.93l-1.41 1.41"/>
                </svg>
                <svg class="theme-icon theme-icon-moon" viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 3a6 6 0 0 0 9 9 9 0 1 1-9-9Z"/>
                </svg>
            </span>
            <span class="theme-toggle-text" data-theme-label>Mode Gelap</span>
        </button>
    </div>
</div></div>
</div>
@if($breaking)
<div class="reader-breaking"><div class="wrap"><strong><span aria-hidden="true"></span> BREAKING NEWS</strong>@if($breaking->url)<a href="{{ $breaking->url }}">{{ $breaking->text }}</a>@else<span>{{ $breaking->text }}</span>@endif</div></div>
@endif
<main id="content" class="wrap reader-main">@yield('content')</main>
@include('public.index-section')
<footer class="reader-footer footer-compact">
    <div class="wrap footer-signoff">
        <a class="reader-brand" href="/" aria-label="KAPUAS RAYA">
            <span style="display: inline-flex; align-items: center; background: #ffffff; padding: 6px 12px; border-radius: 6px;">
                <img src="/images/logo-kapuasraya.png" width="180" height="42" alt="KAPUAS RAYA" style="height: 34px; width: auto; object-fit: contain;">
            </span>
        </a>
        <a class="footer-contact" href="/halaman/kontak">Hubungi redaksi</a>
    </div>
    <div class="wrap footer-info">
        <nav aria-label="Informasi media">@foreach(['tentang'=>'Tentang kami','pedoman-redaksi'=>'Pedoman redaksi','koreksi'=>'Koreksi berita','privasi'=>'Privasi'] as $slug=>$label)<a href="/halaman/{{ $slug }}">{{ $label }}</a>@endforeach</nav>
        <div class="footer-social">@if(!empty($settings['whatsapp']))<a href="https://wa.me/{{ $settings['whatsapp'] }}">WhatsApp</a>@endif @foreach(['facebook'=>'Facebook','x'=>'X'] as $key=>$label)@if(!empty($settings[$key]))<a href="{{ $settings[$key] }}" rel="noopener noreferrer">{{ $label }}</a>@endif @endforeach</div>
    </div>
    <div class="wrap footer-legal"><span>© {{ date('Y') }} {{ $settings['name'] ?? 'Portal Berita' }}. Seluruh hak cipta dilindungi.</span><a href="/login">Ruang redaksi</a></div>
</footer>
@include('public.search-modal')
<button type="button" class="kr-scroll-top" id="krScrollTopBtn" aria-label="Kembali ke atas" title="Kembali ke atas">
    <span class="kr-rocket-flame" aria-hidden="true"></span>
    <svg class="kr-rocket-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
        <path d="M4.5 16.5c-1.5 1.26-2 5-2 5s3.74-.5 5-2c.71-.84.7-2.13-.09-2.91a2.18 2.18 0 0 0-2.91-.09z"/>
        <path d="m12 15-3-3a22 22 0 0 1 2-3.95A12.88 12.88 0 0 1 22 2c0 2.72-.78 7.5-6 11a22.35 22.35 0 0 1-4 2z"/>
        <path d="M9 12H4s.55-3.03 2-4c1.62-1.08 5 0 5 0"/>
        <path d="M12 15v5s3.03-.55 4-2c1.08-1.62 0-5 0-5"/>
    </svg>
</button>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const scrollBtn = document.getElementById('krScrollTopBtn');

    // Scroll-to-Top Button Detection
    window.addEventListener('scroll', function() {
        if (scrollBtn && !scrollBtn.classList.contains('is-launching')) {
            scrollBtn.classList.toggle('is-visible', window.scrollY > 280);
        }
    }, { passive: true });

    // Rocket Launch Animation on Click
    if (scrollBtn) {
        scrollBtn.addEventListener('click', function() {
            if (scrollBtn.classList.contains('is-launching')) return;
            
            scrollBtn.classList.add('is-launching');

            // Smooth scroll to top
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });

            // Reset roket setelah mendarat di atas
            setTimeout(function() {
                scrollBtn.classList.remove('is-launching');
                scrollBtn.classList.remove('is-visible');
            }, 950);
        });
    }

    // Image Skeleton Loader Transition
    function initImageSkeletons() {
        const images = document.querySelectorAll('.reader img:not(.kr-loader-logo):not([data-no-skeleton])');
        images.forEach(function(img) {
            if (img.complete && img.naturalHeight !== 0) {
                img.classList.add('is-img-loaded');
            } else {
                img.classList.add('is-img-loading');
                img.addEventListener('load', function() {
                    img.classList.remove('is-img-loading');
                    img.classList.add('is-img-loaded');
                });
                img.addEventListener('error', function() {
                    img.classList.remove('is-img-loading');
                    img.classList.add('is-img-error');
                });
            }
        });
    }
    initImageSkeletons();

    // Anti-Repost: Otomatis tambahkan atribusi tautan asli saat artikel disalin (Copy-Paste)
    document.addEventListener('copy', function(e) {
        const selection = window.getSelection();
        if (!selection || selection.rangeCount === 0 || selection.toString().length < 80) return;

        const articleEl = document.querySelector('article.reading');
        if (!articleEl) return;

        const articleTitle = document.querySelector('h1')?.innerText || document.title;
        const pageUrl = window.location.href;
        const attribution = `\n\nArtikel ini telah tayang di ${document.title} dengan judul "${articleTitle}".\nBaca selengkapnya di: ${pageUrl}\n`;

        const originalText = selection.toString();
        const copyContent = originalText + attribution;

        if (e.clipboardData) {
            e.clipboardData.setData('text/plain', copyContent);
            e.preventDefault();
        }
    });

    // ==========================================
    // Fullscreen Search Modal Interaction
    // ==========================================
    const searchModal = document.getElementById('krSearchModal');
    const searchTrigger = document.getElementById('krSearchTrigger');
    const searchCloseBtn = document.getElementById('krSearchCloseBtn');
    const searchBackdrop = document.getElementById('krSearchBackdrop');
    const searchInput = document.getElementById('krSearchModalInput');
    const searchClearBtn = document.getElementById('krSearchClearBtn');
    const searchForm = document.getElementById('krSearchDialogForm');

    const searchDefaultPanel = document.getElementById('krSearchDefaultPanel');
    const searchLiveResults = document.getElementById('krSearchLiveResults');
    const searchLiveGrid = document.getElementById('krSearchLiveGrid');
    const searchLiveCount = document.getElementById('krSearchLiveCount');
    const searchTopicSuggestions = document.getElementById('krSearchTopicSuggestions');
    const searchTopicsList = document.getElementById('krSearchTopicsList');

    const searchHistoryCol = document.getElementById('krSearchHistoryCol');
    const searchHistoryChips = document.getElementById('krSearchHistoryChips');
    const searchHistoryClear = document.getElementById('krSearchHistoryClear');

    const SEARCH_HISTORY_KEY = 'portal_search_history';

    function getSearchHistory() {
        try {
            return JSON.parse(localStorage.getItem(SEARCH_HISTORY_KEY)) || [];
        } catch(e) {
            return [];
        }
    }

    function saveSearchHistory(query) {
        if (!query || query.trim().length < 2) return;
        query = query.trim();
        let history = getSearchHistory();
        history = history.filter(q => q.toLowerCase() !== query.toLowerCase());
        history.unshift(query);
        if (history.length > 8) history = history.slice(0, 8);
        try {
            localStorage.setItem(SEARCH_HISTORY_KEY, JSON.stringify(history));
        } catch(e) {}
        renderSearchHistory();
    }

    function renderSearchHistory() {
        if (!searchHistoryCol || !searchHistoryChips) return;
        const history = getSearchHistory();
        if (history.length === 0) {
            searchHistoryCol.style.display = 'none';
            searchHistoryChips.innerHTML = '';
            return;
        }

        searchHistoryCol.style.display = 'block';
        searchHistoryChips.innerHTML = history.map(item => `
            <div class="kr-search-history-chip">
                <a href="/cari?q=${encodeURIComponent(item)}" class="kr-search-history-link">
                    <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 14 16"/></svg>
                    <span>${item}</span>
                </a>
                <button type="button" class="kr-search-history-delete" data-del="${item}" aria-label="Hapus">&times;</button>
            </div>
        `).join('');

        searchHistoryChips.querySelectorAll('.kr-search-history-delete').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.stopPropagation();
                const toDel = this.getAttribute('data-del');
                let h = getSearchHistory().filter(q => q !== toDel);
                try { localStorage.setItem(SEARCH_HISTORY_KEY, JSON.stringify(h)); } catch(err) {}
                renderSearchHistory();
            });
        });
    }

    if (searchHistoryClear) {
        searchHistoryClear.addEventListener('click', function() {
            try { localStorage.removeItem(SEARCH_HISTORY_KEY); } catch(e) {}
            renderSearchHistory();
        });
    }

    function openSearchModal() {
        if (!searchModal) return;
        searchModal.classList.add('is-open');
        searchModal.setAttribute('aria-hidden', 'false');
        document.body.classList.add('kr-modal-opened');
        renderSearchHistory();
        setTimeout(function() {
            if (searchInput) searchInput.focus();
        }, 80);
    }

    function closeSearchModal() {
        if (!searchModal) return;
        searchModal.classList.remove('is-open');
        searchModal.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('kr-modal-opened');
    }

    if (searchTrigger) {
        searchTrigger.addEventListener('click', openSearchModal);
        searchTrigger.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                openSearchModal();
            }
        });
    }

    if (searchCloseBtn) searchCloseBtn.addEventListener('click', closeSearchModal);
    if (searchBackdrop) searchBackdrop.addEventListener('click', closeSearchModal);

    window.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && searchModal && searchModal.classList.contains('is-open')) {
            closeSearchModal();
        }
        // Shortcut keyboard '/' atau 'Ctrl + K' untuk membuka search modal
        if ((e.key === '/' || (e.ctrlKey && e.key === 'k') || (e.metaKey && e.key === 'k')) && 
            !['INPUT', 'TEXTAREA'].includes(document.activeElement.tagName) && 
            searchModal && !searchModal.classList.contains('is-open')) {
            e.preventDefault();
            openSearchModal();
        }
    });

    // Handle Form Submit
    if (searchForm) {
        searchForm.addEventListener('submit', function() {
            if (searchInput && searchInput.value) {
                saveSearchHistory(searchInput.value);
            }
        });
    }

    // Live search suggest dengan debounce
    let liveSearchDebounce = null;

    function resetSearchToDefault() {
        if (searchClearBtn) searchClearBtn.style.display = 'none';
        if (searchLiveResults) {
            searchLiveResults.style.display = 'none';
        }
        if (searchDefaultPanel) {
            searchDefaultPanel.style.display = 'block';
        }
        if (searchLiveGrid) searchLiveGrid.innerHTML = '';
        if (searchTopicSuggestions) searchTopicSuggestions.style.display = 'none';
        if (searchTopicsList) searchTopicsList.innerHTML = '';
    }

    if (searchInput) {
        ['input', 'keyup', 'search'].forEach(function(evtName) {
            searchInput.addEventListener(evtName, function() {
                const val = this.value.trim();
                clearTimeout(liveSearchDebounce);

                // Jika input kosong atau hanya spasi, langsung kembalikan ke tampilan default
                if (val.length === 0) {
                    resetSearchToDefault();
                    return;
                }

                if (searchClearBtn) searchClearBtn.style.display = 'inline-flex';

                if (val.length < 2) {
                    resetSearchToDefault();
                    if (searchClearBtn) searchClearBtn.style.display = 'inline-flex';
                    return;
                }

                liveSearchDebounce = setTimeout(function() {
                    fetch(`/api/search/suggest?q=${encodeURIComponent(val)}`)
                        .then(res => res.json())
                        .then(data => {
                            // Cek ulang apakah input masih berisi teks
                            if (!searchInput.value.trim()) {
                                resetSearchToDefault();
                                return;
                            }

                            if (!searchLiveResults || !searchDefaultPanel) return;

                            searchDefaultPanel.style.display = 'none';
                            searchLiveResults.style.display = 'block';

                            const articles = data.articles || [];
                            const topics = data.topics || [];

                            if (searchLiveCount) {
                                searchLiveCount.textContent = `${articles.length} Ditemukan`;
                            }

                            // Render topic suggestions
                            if (searchTopicSuggestions && searchTopicsList) {
                                if (topics.length > 0) {
                                    searchTopicSuggestions.style.display = 'flex';
                                    searchTopicsList.innerHTML = topics.map(t => `
                                        <a href="/kanal/${t.slug}" class="kr-search-topic-chip">
                                            <span class="kr-search-topic-hash">#</span>${t.name}
                                        </a>
                                    `).join('');
                                } else {
                                    searchTopicSuggestions.style.display = 'none';
                                    searchTopicsList.innerHTML = '';
                                }
                            }

                            // Render articles
                            if (searchLiveGrid) {
                                if (articles.length === 0) {
                                    searchLiveGrid.innerHTML = `
                                        <div class="kr-search-empty-state">
                                            <p>Tidak ditemukan artikel untuk kata kunci "<strong>${val}</strong>".</p>
                                            <a href="/cari?q=${encodeURIComponent(val)}" class="button">Lihat pencarian lengkap &rarr;</a>
                                        </div>
                                    `;
                                } else {
                                    searchLiveGrid.innerHTML = articles.map(item => `
                                        <article class="kr-search-live-card">
                                            ${item.image ? `
                                                <a href="${item.url}" class="kr-search-live-card-thumb" tabindex="-1">
                                                    <img src="${item.image}" width="120" height="80" alt="" loading="lazy">
                                                </a>
                                            ` : ''}
                                            <div class="kr-search-live-card-body">
                                                ${item.category ? `<span class="kr-search-live-card-cat">${item.category}</span>` : ''}
                                                <h4 class="kr-search-live-card-title">
                                                    <a href="${item.url}">${item.title}</a>
                                                </h4>
                                                <span class="kr-search-live-card-time">${item.published || ''}</span>
                                            </div>
                                        </article>
                                    `).join('') + `
                                        <div class="kr-search-view-all-box">
                                            <a href="/cari?q=${encodeURIComponent(val)}" class="kr-search-view-all-link">
                                                Buka Semua Hasil Pencarian untuk "${val}" &rarr;
                                            </a>
                                        </div>
                                    `;
                                }
                            }
                        })
                        .catch(() => {});
                }, 180);
            });
        });
    }

    if (searchClearBtn) {
        searchClearBtn.addEventListener('click', function() {
            if (searchInput) {
                searchInput.value = '';
                searchInput.focus();
                resetSearchToDefault();
            }
        });
    }

    // Live clock update in WIB
    (function initLiveClock() {
        var clockEl = document.getElementById('krLiveClock');
        if (!clockEl) return;
        function updateClock() {
            try {
                var now = new Date();
                var timeStr = now.toLocaleTimeString('id-ID', {
                    timeZone: 'Asia/Jakarta',
                    hour: '2-digit',
                    minute: '2-digit',
                    hour12: false
                }).replace('.', ':');
                clockEl.textContent = timeStr;
            } catch (e) {}
        }
        setInterval(updateClock, 10000);
    })();
});
</script>
@vite(['resources/js/public.js'])
</body></html>
