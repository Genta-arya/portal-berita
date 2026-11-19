# Progres implementasi

Sumber kebutuhan: `prd.md` versi 1.0. Checkout utama: `D:\Project\portal-berita`.

## Tahap 0 — pemeriksaan awal

- [x] Skeleton Laravel yang sudah ada ditemukan dan dipertahankan.
- [x] Dependensi Composer/npm dan kedua lockfile tersedia.
- [x] Laravel CLI berjalan (12.73.0).
- [x] `composer validate --no-check-publish` lulus.
- [x] `npm run build` lulus.
- [x] PRD terbaru disinkronkan ke checkout.

## Tahap 1 — fondasi data (berjalan)

- [x] Migrasi identitas redaksi dibuat: role, active, slug, bio, session_version.
- [ ] Migrasi model editorial lengkap.
- [ ] Verifikasi migrasi pada MySQL terisolasi.

## Tahap berikutnya

Login/Policy/akun CLI; editor dan media privat; alur editorial; halaman publik; modul pendukung; QA dan panduan operasi. Seluruh tahap tersebut belum selesai. Jangan mengklaim MVP siap rilis.

## Pembaruan login redaksi — 19 November 2026

- Login/logout sesi dan pembatasan login tersedia.
- Akun nonaktif dan versi sesi yang berubah kehilangan akses dashboard.
- Perintah CLI `php artisan editorial:create-admin email nama` meminta password privat, tanpa akun bawaan.
- Seeder tidak lagi membuat akun contoh.
- Pengujian PHPUnit database SQLite terisolasi lulus: 5 test, 16 assertion. Ini belum membuktikan kompatibilitas MySQL.
- Build Vite lulus. Hak akses per modul dan perlindungan admin terakhir masih perlu diimplementasikan.

## Fondasi artikel

Migrasi kanal, artikel, tag, revisi, catatan editorial dan audit tersedia. Policy artikel membatasi penulis ke kontennya sendiri dan melarang edit konten terbit. Scope publik mengecualikan draft, penarikan dan waktu terbit masa depan. PHPUnit terisolasi: 7 test, 22 assertion lulus. UI artikel, media dan workflow belum selesai.
