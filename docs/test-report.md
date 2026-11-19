# Laporan pengujian — 19 November 2026

Lingkungan: Windows, PHP 8.3.16, Laravel 12.73, MySQL 8.4.3 di 127.0.0.1:3307. Database `portal_berita_test` terpisah dari database demo dan remote. Test guard menolak database selain SQLite in-memory atau MySQL `_test`.

## Bukti

- PHPUnit: 17 test, 127 assertion lulus pada MySQL, 23,727 detik termasuk memasukkan dataset kapasitas. Tidak ada risky test pada hasil akhir tersebut.
- Alur penulis/editor, revisi, jadwal idempoten/cancel, akses draft/media, penarikan 410, redirect 301, publikasi hanya pihak berwenang, konflik 409, autosave Published ditolak.
- Sanitasi script/event/URL berbahaya, upload SVG/oversize ditolak, preview privat, admin terakhir dan hak akses management.
- Banner overlap ditolak; banner/breaking berakhir tepat rentang; media banner kedaluwarsa kembali privat.
- Restore revisi mempertahankan histori; media referensi tidak dapat dihapus; backup/restore memulihkan akun/artikel/file privat dan membatalkan sesi lama.
- Dataset 10.000 artikel: dua request serial terbaru/pencarian 201,78 ms pada pengukuran lokal ini. Ini bukan p95, bukan uji konkurensi dan bukan bukti kapasitas provider.
- Browser: beranda dan artikel 360 px (scrollWidth 345 <=360); beranda 1440 px (scrollWidth 1425 <=1440), layout terlihat tanpa overflow.
- Build Vite berhasil. Aset publik kecil dan React editor hanya dimuat pada editor; pemeriksaan Composer dan kompilasi Blade dilakukan.

## Belum terverifikasi

Lighthouse/Core Web Vitals, staging shared hosting, cron aktual per menit, SMTP provider, beban 20 pembaca/5 menit, aksesibilitas keyboard lengkap dan interaksi autosave browser. Angka performa lokal tidak menggantikan pengujian ini. Konfigurasi PHP CLI lokal masih memunculkan peringatan ionCube DLL, namun test/build berjalan.

## Redesign dan import

Pemeriksaan browser redesign: 360 px scrollWidth 345, 1440 px scrollWidth 1425; beranda dan navigasi ke indeks berhasil. Suite termasuk impor privat/idempoten: 18 test, 138 assertion lulus di MySQL. Kapuas Raya diarsipkan dari endpoint publik dan diverifikasi terhadap total API (76 artikel, 4 halaman, 74 kategori, 3 tag, 86 media, 91 gambar). Database lokal memuat 76 draft dan 91 referensi gambar sumber; rincian serta batasnya ada di kapuasraya-migration.md. Arsip privat dan ZIP tidak masuk Git.
