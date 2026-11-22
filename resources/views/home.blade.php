<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Fondasi portal berita dengan desain hijau sage dan pengalaman membaca yang nyaman.">
    <meta name="robots" content="noindex,nofollow">
    <title>{{ config('app.name') }} — Fondasi Portal Berita</title>
    @viteReactRefresh
    @vite(['resources/css/sage.css', 'resources/js/app.jsx'])
</head>
<body>
    <div class="top"><div class="wrap">RUANG INFORMASI · PERSPEKTIF BARU</div></div>
    <header class="wrap">
        <div class="masthead"><div><a class="brand" href="/">portal<span>berita.</span></a><div class="tagline">Dekat dengan kabar. Jernih dalam membaca.</div></div><span class="badge">Pratinjau fondasi</span></div>
        <nav aria-label="Navigasi halaman"><a href="#utama">Beranda</a><a href="#fondasi">Fondasi proyek</a><a href="#desain">Palet desain</a></nav>
    </header>
    <main class="wrap" id="utama">
        <section class="hero"><div><div class="eyebrow">Portal berita · dalam pengembangan</div><h1>Berita yang dekat.<br>Sudut pandang yang jernih.</h1><p>Fondasi untuk ruang berita modern, dengan warna hijau sage yang tenang dan tipografi yang mengutamakan kenyamanan membaca.</p><span class="badge">Laravel + komponen React</span></div><div class="art" role="img" aria-label="Ilustrasi tipografi di atas latar hijau sage"><strong>Perspektif.</strong></div></section>
        <section class="grid" id="fondasi" aria-label="Status fondasi"><article class="card"><span class="number">01 / ARSITEKTUR</span><h2>Konten dari server</h2><p>Blade menyiapkan HTML utama, React menangani komponen interaktif. Ini adalah halaman fondasi, belum berisi berita redaksi.</p></article><article class="card"><span class="number">02 / INFRASTRUKTUR</span><h2>Untuk shared hosting</h2><p>Aset dibangun sebelum diunggah. Konfigurasi cache dan sesi menggunakan berkas untuk tahap awal.</p></article><article class="card"><span class="number">03 / TAHAP BERIKUTNYA</span><h2>Ruang kerja redaksi</h2><p>Topik, artikel, media privat, dan alur review akan dikembangkan mengikuti PRD. Login redaksi belum tersedia.</p></article></section>
        <div id="desain"><div id="palette-preview"></div><noscript><p>Palet desain: Sage #A3B18A, Forest #344E41, Ivory #FAF9F6, Terracotta #BC6C4A.</p></noscript></div>
    </main>
    <footer><div class="wrap">{{ date('Y') }} · {{ config('app.name') }} · Pratinjau desain dan fondasi teknis.</div></footer>
</body>
</html>
