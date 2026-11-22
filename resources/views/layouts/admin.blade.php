<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}"><meta name="robots" content="noindex">
    <link rel="icon" href="/images/icon-kapuasraya.png">
    <title>@yield('title', 'Ruang Redaksi') — Kapuas Raya</title>
    @vite(['resources/css/sage.css', 'resources/css/admin.css']) @stack('head')
</head>
<body class="admin">
@php
    $role = auth()->user()->role;
    $links = [['/admin', 'Ringkasan', 'grid'], ['/admin/articles', 'Artikel', 'article'], ['/admin/media', 'Media', 'image'], ['/admin/profile', 'Profil saya', 'users']];
    if (in_array($role, ['developer', 'editor', 'admin'])) {
        $links[] = ['/admin/manage/featured', 'Headline & pilihan', 'star'];
        $links[] = ['/admin/manage/trending', 'Trending Topik', 'categories'];
        $links[] = ['/admin/manage/breaking', 'Breaking news', 'bolt'];
    }
    $managementLinks = auth()->user()->isAdministrator() ? ['users'=>'Pengguna','categories'=>'Topik','tags'=>'Tag','banners'=>'Iklan','pages'=>'Halaman','settings'=>'Pengaturan','audit'=>'Audit aktivitas'] : [];
@endphp
<aside class="workspace-sidebar" id="workspace-sidebar" aria-label="Navigasi redaksi">
    <a class="workspace-logo" href="/admin"><img src="/images/logo-kapuasraya.png" alt="Kapuas Raya" width="174" height="44"></a>
    <div class="workspace-edition"><span></span> RUANG REDAKSI</div>
    <nav>
        <p class="nav-heading">RUANG KERJA</p>
        @foreach($links as [$url, $label, $icon])
            <a href="{{ $url }}" @class(['workspace-nav-link', 'is-active' => $url === '/admin' ? request()->is('admin') : request()->is(ltrim($url, '/').'*')]) @if($url === '/admin' ? request()->is('admin') : request()->is(ltrim($url, '/').'*')) aria-current="page" @endif>
                @include('admin.icon', ['name' => $icon])<span>{{ $label }}</span>
            </a>
        @endforeach
        @if($managementLinks)
            <p class="nav-heading nav-heading-management">PENGELOLAAN</p>
            @foreach($managementLinks as $key => $label)
                <a href="/admin/manage/{{ $key }}" @class(['workspace-nav-link', 'is-active'=>request()->is('admin/manage/'.$key)]) @if(request()->is('admin/manage/'.$key)) aria-current="page" @endif>
                    @include('admin.icon', ['name' => $key])<span>{{ $label }}</span>
                </a>
            @endforeach
        @endif
    </nav>
    <div class="workspace-user"><a class="user-avatar" href="/admin/profile" aria-label="Ubah foto profil">@if(auth()->user()->avatar_path)<img src="/admin/users/{{ auth()->id() }}/avatar" alt="">@else{{ mb_strtoupper(mb_substr(auth()->user()->name,0,1)) }}@endif</a><div><strong>{{ auth()->user()->name }}</strong><small>{{ ucfirst($role) }}</small></div><form action="/logout" method="post">@csrf<button aria-label="Keluar dari akun" title="Keluar">@include('admin.icon', ['name'=>'logout'])</button></form></div>
</aside>
<div class="workspace-body">
    <header class="workspace-topbar">
        <button class="workspace-menu" type="button" aria-controls="workspace-sidebar" aria-expanded="false" aria-label="Buka navigasi">@include('admin.icon', ['name'=>'menu'])</button>
        <div class="workspace-breadcrumb">Redaksi <span>/</span> <strong>@yield('title', 'Ruang kerja')</strong></div>
        <div class="workspace-topbar-actions"><span class="workspace-date">{{ now()->timezone('Asia/Jakarta')->locale('id')->translatedFormat('d F Y') }}</span><a href="/" target="_blank" rel="noopener">Lihat situs @include('admin.icon', ['name'=>'external'])</a></div>
    </header>
    <main class="admin-main" id="main-content">
        @if(session('success'))<p role="status" class="notice">{{ session('success') }}</p>@endif
        @if($errors->any())<div role="alert" class="error"><strong>Periksa kembali isian:</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
        @yield('content')
    </main>
    <footer class="workspace-footer"><span>Kapuas Raya <span class="footer-dot">/</span> Ruang Redaksi</span><span>Kelola kabar. Hadirkan makna.</span></footer>
</div>
<script>
const menu = document.querySelector('.workspace-menu');
menu.addEventListener('click', () => {
    const open = document.body.classList.toggle('nav-open');
    menu.setAttribute('aria-expanded', String(open));
    menu.setAttribute('aria-label', open ? 'Tutup navigasi' : 'Buka navigasi');
});
document.addEventListener('keydown', event => { if (event.key === 'Escape') { document.body.classList.remove('nav-open'); menu.setAttribute('aria-expanded', 'false'); } });
</script>
@stack('scripts')
</body>
</html>
