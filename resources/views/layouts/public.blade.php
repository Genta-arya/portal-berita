<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
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
<a class="skip" href="#content">Lewati ke isi</a>
<div class="edition-bar"><div class="wrap edition-inner"><span>{{ now()->locale('id')->timezone('Asia/Jakarta')->translatedFormat('l, d F Y') }} <span class="edition-zone">/ WIB</span></span><a href="/halaman/tentang">Independen dalam perspektif</a></div></div>
<header class="reader-header">
    <div class="wrap reader-masthead">
        <a class="reader-brand" href="/" aria-label="{{ $settings['name'] ?? 'Portal Berita' }} — Beranda">
            @if(!empty($settings['logo_media_id']))
                <img src="/media/{{ $settings['logo_media_id'] }}/480" width="180" height="60" alt="{{ $settings['name'] ?? 'Portal Berita' }}">
            @else
                <span class="brand-symbol" aria-hidden="true"><i></i><i></i><i></i></span><span>{{ $settings['name'] ?? 'Portal Berita' }}<b>.</b></span>
            @endif
        </a>
        <p class="brand-promise">Dekat dengan kabar.<br><strong>Jernih dalam membaca.</strong></p>
        <form action="/cari" class="reader-search" role="search">
            <label class="sr-only" for="search">Cari berita</label>
            <input id="search" name="q" placeholder="Cari berita, temukan perspektif" value="{{ request('q') }}" maxlength="100">
            <button aria-label="Cari berita" title="Cari berita"><svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><circle cx="10.5" cy="10.5" r="6.5"/><path d="m16 16 5 5"/></svg></button>
        </form>
    </div>
    <div class="nav-band"><div class="wrap nav-inner">
        <nav class="reader-nav" aria-label="Navigasi utama">
            <a href="/" @if(request()->is('/')) aria-current="page" @endif>Beranda</a>
            <a href="/terbaru" @if(request()->is('terbaru')) aria-current="page" @endif>Terbaru</a>
            @if($navigation->isNotEmpty())
            <details class="category-dropdown" data-category-dropdown>
                <summary>Kategori <svg width="14" height="14" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path d="m5 7 5 5 5-5"/></svg></summary>
                <div class="category-popover"><div class="category-popover-heading"><strong>Jelajahi kategori</strong><span>Temukan kabar pilihan Anda</span></div><div class="category-options">@foreach($navigation as $channel)<a href="/kanal/{{ $channel->slug }}" @if(request()->is('kanal/'.$channel->slug)) aria-current="page" @endif>{{ $channel->name }}</a>@endforeach</div></div>
            </details>
            @endif
        </nav>
        <a class="nav-index" href="/terbaru">Indeks berita <span aria-hidden="true">☷</span></a>
    </div></div>
</header>
@if($breaking)
<div class="reader-breaking"><div class="wrap"><strong><span aria-hidden="true"></span> BREAKING NEWS</strong>@if($breaking->url)<a href="{{ $breaking->url }}">{{ $breaking->text }}</a>@else<span>{{ $breaking->text }}</span>@endif</div></div>
@endif
<main id="content" class="wrap reader-main">@yield('content')</main>
<footer class="reader-footer footer-compact">
    <div class="wrap footer-signoff">
        <div><a class="reader-brand" href="/">{{ $settings['name'] ?? 'Portal Berita' }}<b>.</b></a><p>Dekat dengan kabar. Jernih dalam membaca.</p></div>
        <a class="footer-contact" href="/halaman/kontak">Hubungi redaksi</a>
    </div>
    <div class="wrap footer-info">
        <nav aria-label="Informasi media">@foreach(['tentang'=>'Tentang kami','pedoman-redaksi'=>'Pedoman redaksi','koreksi'=>'Koreksi berita','privasi'=>'Privasi'] as $slug=>$label)<a href="/halaman/{{ $slug }}">{{ $label }}</a>@endforeach</nav>
        <div class="footer-social">@if(!empty($settings['whatsapp']))<a href="https://wa.me/{{ $settings['whatsapp'] }}">WhatsApp</a>@endif @foreach(['facebook'=>'Facebook','x'=>'X'] as $key=>$label)@if(!empty($settings[$key]))<a href="{{ $settings[$key] }}" rel="noopener noreferrer">{{ $label }}</a>@endif @endforeach</div>
    </div>
    <div class="wrap footer-legal"><span>© {{ date('Y') }} {{ $settings['name'] ?? 'Portal Berita' }}. Seluruh hak cipta dilindungi.</span><a href="/login">Ruang redaksi</a></div>
</footer>
@vite(['resources/js/public.js'])
</body></html>
