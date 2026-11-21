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
    @if(!empty($settings['favicon_media_id']))<link rel="icon" href="/media/{{ $settings['favicon_media_id'] }}/480">@endif
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
<div class="edition-bar"><div class="wrap edition-inner">{{ now()->locale('id')->timezone('Asia/Jakarta')->translatedFormat('l, d F Y') }} <span class="edition-zone">WIB</span></div></div>
@if($tickerArticles->isNotEmpty())
<aside class="latest-ticker" aria-label="10 berita terbaru" data-latest-ticker><div class="wrap ticker-inner"><strong class="ticker-label"><span class="fire-flame-wrap" aria-hidden="true"><i class="ember ember-1"></i><i class="ember ember-2"></i><i class="ember ember-3"></i></span>TERBARU</strong><div class="ticker-viewport"><div class="ticker-track"><ul class="ticker-items">@foreach($tickerArticles as $tickerArticle)<li><a href="{{ $tickerArticle->url }}">{{ $tickerArticle->title }}</a></li>@endforeach</ul><ul class="ticker-items ticker-copy" aria-hidden="true" inert>@foreach($tickerArticles as $tickerArticle)<li><a href="{{ $tickerArticle->url }}" tabindex="-1">{{ $tickerArticle->title }}</a></li>@endforeach</ul></div></div></div></aside>
@endif
<header class="reader-header">
    <div class="wrap reader-masthead">
        <a class="reader-brand" href="/" aria-label="{{ $settings['name'] ?? 'KAPUAS RAYA' }} — Beranda">
            <img src="/images/logo-kapuasraya.png" width="200" height="46" alt="{{ $settings['name'] ?? 'KAPUAS RAYA' }}" style="height: 44px; width: auto; max-width: 260px; object-fit: contain;">
        </a>
        <form action="/cari" class="reader-search" role="search">
            <label class="sr-only" for="search">Cari berita</label>
            <input id="search" name="q" placeholder="Cari berita atau topik" value="{{ is_string(request('q')) ? request('q') : '' }}" maxlength="100">
            <button aria-label="Cari berita" title="Cari berita"><svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><circle cx="10.5" cy="10.5" r="6.5"/><path d="m16 16 5 5"/></svg></button>
        </form>
    </div>
    <div class="nav-band"><div class="wrap nav-inner">
        <nav class="reader-nav" aria-label="Navigasi utama">
            <a href="/" @if(request()->is('/')) aria-current="page" @endif>Beranda</a>
            <a href="/terbaru" @if(request()->is('terbaru')) aria-current="page" @endif>Index</a>
            @if($navigation->isNotEmpty())
            <details class="category-dropdown" data-category-dropdown>
                <summary>Kategori <svg width="14" height="14" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path d="m5 7 5 5 5-5"/></svg></summary>
                <div class="category-popover"><strong>Semua kategori</strong><div class="category-options">@foreach($navigation as $channel)<a href="/kanal/{{ $channel->slug }}" @if(request()->is('kanal/'.$channel->slug)) aria-current="page" @endif>{{ $channel->name }}</a>@endforeach</div></div>
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
                        <path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z"/>
                    </svg>
                </span>
                <span class="theme-toggle-text" data-theme-label>Mode Gelap</span>
            </button>
        </div>
    </div></div>
</header>
@if($breaking)
<div class="reader-breaking"><div class="wrap"><strong><span aria-hidden="true"></span> BREAKING NEWS</strong>@if($breaking->url)<a href="{{ $breaking->url }}">{{ $breaking->text }}</a>@else<span>{{ $breaking->text }}</span>@endif</div></div>
@endif
<main id="content" class="wrap reader-main">@yield('content')</main>
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
    const navBand = document.querySelector('.reader .nav-band');
    const scrollBtn = document.getElementById('krScrollTopBtn');

    // Sticky Navbar Shadow & Scroll-to-Top Button Detection
    window.addEventListener('scroll', function() {
        const scrolled = window.scrollY > 40;
        if (navBand) {
            navBand.classList.toggle('is-scrolled', window.scrollY > 10);
        }
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
});
</script>
@vite(['resources/js/public.js'])
</body></html>
