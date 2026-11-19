# Portal Berita

Portal Laravel 12 + Blade + komponen React/Vite, database MySQL, dan desain Sage Editorial. HTML artikel dirender server; produksi tidak memerlukan Node.js permanen.

## Menjalankan lokal

1. `composer install` dan `npm ci`.
2. Siapkan `.env` privat dengan koneksi MySQL pengembangan. Jangan gunakan database produksi untuk demo/pengujian. Buat APP_KEY sekali dengan `php artisan key:generate` untuk instalasi baru.
3. `php artisan migrate`, `npm run build`.
4. `php artisan editorial:create-admin email@example.com "Nama Admin"` (password diminta privat).
5. `php artisan serve` dan buka http://127.0.0.1:8000.

Pada komputer pengembangan saat ini tersedia `.env.local` terpisah, MySQL lokal port 3307 dan database `portal_berita_local`. Jalankan `php artisan serve --env=local`. Akses demo privat ada di `storage/app/private/local-access.txt`; berkas ini tidak di-commit. Seeder `LocalDemoSeeder` hanya menerima lingkungan local/database berakhiran `_local`, dan tidak berjalan jika sudah ada pengguna. Seluruh artikel contoh diberi label DEMO.

## Fitur

- Portal publik: beranda, kanal, terbaru/filter, tag, penulis, pencarian, artikel, halaman informasi, metadata, NewsArticle, RSS/sitemap, redirect canonical.
- Dashboard: login terbatas, peran, CRUD artikel, editor React, autosave, konflik versi, preview privat, review/persetujuan, jadwal, penarikan dan riwayat revisi.
- Media privat: validasi gambar, varian WebP, metadata, referensi pemakaian, pencabutan akses dan perlindungan penghapusan.
- Admin: pengguna/admin terakhir, kanal/tag, identitas, halaman, banner terjadwal, breaking news, headline/pilihan, audit dan statistik perkiraan.
- Scheduler, backup/restore dan pemulihan password melalui CLI.

## Pengujian

`npm run build`, `composer validate --no-check-publish`, `php artisan test`.

Default test memakai SQLite in-memory. MySQL test wajib menggunakan database terisolasi berakhiran `_test`; guard test menolak database lain. Jangan mengarahkan RefreshDatabase ke database kerja/produksi. Pengujian MySQL lokal dilaporkan di `docs/test-report.md`.

## Dokumentasi

- `prd.md`: cakupan dan penerimaan.
- `docs/progress.md`: status tiap bagian.
- `docs/editor-guide.md`: operasi redaksi.
- `docs/deployment.md`: hosting, cron, backup/restore, rollback dan gerbang rilis.
- `docs/test-report.md`: bukti pengujian dan batas verifikasi.
- `docs/blockers.md`: kebutuhan eksternal yang belum terpenuhi.

Status: implementasi lokal tersedia dan diuji. Deployment produksi, identitas/konten final, verifikasi provider, audit Lighthouse dan uji beban staging merupakan gerbang peluncuran terpisah.
