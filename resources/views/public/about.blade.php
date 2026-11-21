@extends('layouts.public')
@section('title', $page->title . ' — ' . ($settings['name'] ?? 'Kapuas Raya'))
@section('description', 'Mengenal Kapuas Raya: Visi, Misi, Standar Jurnalisme, dan Komitmen Kami dalam Menyajikan Berita Aktual dan Terpercaya.')

@section('content')
<div class="reader-breadcrumb">
    <a href="/">Beranda</a>
    <span aria-hidden="true">/</span>
    <span>{{ $page->title }}</span>
</div>

<article class="kr-about-wrapper">
    {{-- Header Banner --}}
    <header class="kr-about-hero">
        <span class="kr-about-badge">PROFIL REDAKSI</span>
        <h1 class="kr-about-title">{{ $page->title }}</h1>
        <p class="kr-about-lead">
            Menghadirkan jurnalisme yang jernih, independen, dan berakar kuat pada denyut nadi masyarakat Kalimantan Barat serta nasional.
        </p>
    </header>

    <div class="kr-about-content">
        {{-- Section: Siapa Kami --}}
        <section class="kr-about-section">
            <div class="kr-about-grid-2">
                <div class="kr-about-text">
                    <h2>Mengenal Kapuas Raya</h2>
                    <p>
                        <strong>Kapuas Raya</strong> lahir dari tekad untuk menyajikan informasi yang tidak sekadar cepat, namun juga akurat, berimbang, dan beretika. Di tengah derasnya arus disinformasi dan era digital yang serba instan, kami memilih berdiri kokoh di atas prinsip dasar jurnalistik: verifikasi fakta, kedalaman analisis, dan keberpihakan pada kepentingan publik.
                    </p>
                    <p>
                        Dengan cakupan peliputan mulai dari peristiwa lokal di pelosok Kalimantan Barat, dinamika pemerintahan daerah, isu lingkungan, hingga perkembangan nasional dan internasional, kami hadir sebagai jendela referensi utama bagi seluruh lapisan masyarakat.
                    </p>
                </div>
                <div class="kr-about-stats-card">
                    <div class="kr-stat-item">
                        <strong class="kr-stat-num">24/7</strong>
                        <span class="kr-stat-label">Pembaruan Berita Berkelanjutan</span>
                    </div>
                    <div class="kr-stat-item">
                        <strong class="kr-stat-num">100%</strong>
                        <span class="kr-stat-label">Komitmen Verifikasi Fakta</span>
                    </div>
                    <div class="kr-stat-item">
                        <strong class="kr-stat-num">Independen</strong>
                        <span class="kr-stat-label">Bebas Intervensi Kepentingan</span>
                    </div>
                </div>
            </div>
        </section>

        {{-- Section: Visi & Misi --}}
        <section class="kr-about-section">
            <div class="kr-cards-grid">
                <div class="kr-about-card">
                    <div class="kr-card-icon-wrap" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"/>
                            <polygon points="12 8 8 12 12 16 16 12 12 8"/>
                        </svg>
                    </div>
                    <h3>Visi Kami</h3>
                    <p>
                        Menjadi media massa digital terkemuka dan tepercaya yang menginspirasi, mencerdaskan kehidupan bangsa, serta menjadi pendorong kemajuan sosial dan ekonomi masyarakat.
                    </p>
                </div>

                <div class="kr-about-card">
                    <div class="kr-card-icon-wrap" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                            <polyline points="14 2 14 8 20 8"/>
                            <line x1="16" y1="13" x2="8" y2="13"/>
                            <line x1="16" y1="17" x2="8" y2="17"/>
                            <polyline points="10 9 9 9 8 9"/>
                        </svg>
                    </div>
                    <h3>Misi Kami</h3>
                    <ul>
                        <li>Menyajikan berita yang akurat, berimbang, dan tidak memihak sesuai Kode Etik Jurnalistik (KEJ).</li>
                        <li>Mengangkat potensi daerah dan menyuarakan aspirasi masyarakat yang jarang tersentuh.</li>
                        <li>Memanfaatkan inovasi teknologi modern untuk mempermudah akses informasi bagi pembaca.</li>
                    </ul>
                </div>
            </div>
        </section>

        {{-- Section: Nilai-Nilai Utama --}}
        <section class="kr-about-section">
            <h2>Nilai Jurnalistik Kami</h2>
            <div class="kr-values-grid">
                <div class="kr-value-box">
                    <div class="kr-value-number">01</div>
                    <h4>Integritas & Kejujuran</h4>
                    <p>Fakta adalah pondasi tertinggi kami. Setiap informasi melalui tahapan kurasi dan cek fakta yang ketat.</p>
                </div>
                <div class="kr-value-box">
                    <div class="kr-value-number">02</div>
                    <h4>Kecepatan & Ketepatan</h4>
                    <p>Kami berpacu dengan waktu tanpa pernah mengorbankan akurasi dan kredibilitas informasi.</p>
                </div>
                <div class="kr-value-box">
                    <div class="kr-value-number">03</div>
                    <h4>Relevansi Publik</h4>
                    <p>Fokus pada informasi yang memberikan dampak positif dan nilai guna bagi pembaca kami.</p>
                </div>
            </div>
        </section>

        {{-- Section: Redaksi & Kontak Singkat --}}
        <section class="kr-about-footer-card">
            <div class="kr-footer-card-inner">
                <div>
                    <h3>Punya Informasi atau Hak Jawab?</h3>
                    <p>Redaksi Kapuas Raya membuka ruang seluas-luasnya untuk kritik membangun, klarifikasi, maupun kerja sama liputan.</p>
                </div>
                <div class="kr-about-actions">
                    <a href="/halaman/pedoman-redaksi" class="kr-btn-outline">Pedoman Redaksi</a>
                    <a href="/halaman/kontak" class="kr-btn-primary">Hubungi Kami &rarr;</a>
                </div>
            </div>
        </section>
    </div>
</article>
@endsection
