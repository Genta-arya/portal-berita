# Project Context: Portal Berita (Kapuas Raya)

## Tech Stack
- **Framework**: Laravel 12.x (PHP 8.5+)
- **Frontend**: Blade Templates, Vanilla CSS (`editorial.css`), Vite 7.x
- **Database**: MySQL VPS Remote (`51.79.152.173:3306`, database `db_kapuasraya`)
- **Storage**: Local private disk (`storage/app/private/editorial`) with `/media/{id}/{variant}` serving logic

## Arsitektur Media & Gambar
- Gambar media disimpan secara fisik di `storage/app/private/editorial/import-{uuid}/` (format original, 480.webp, 1280.webp).
- `.gitignore` di `storage/app/private/.gitignore` dikonfigurasi untuk menyertakan `!editorial/` dan `!editorial/**`, sehingga seluruh file gambar ikut ter-commit ke git repository dan tidak hilang saat ganti perangkat.
- Tersedia command `php artisan editorial:fetch-images` untuk sinkronisasi/unduh ulang gambar dari `kapuasraya.com` jika diperlukan.

## Fitur & Komponen Baru
1. **Split Berita Terbaru (`/terbaru`)**:
   - Kolom kiri: Variasi desain kartu berita (Lead Hero Card berukuran besar dengan badge Live, Accent Card beraksen merah/gold, dan Feed Card standar).
   - Kolom kanan: Sidebar widget Berita Trending (1–5) dan Kabar Menarik Lainnya.
2. **Arsip Berita (Komponen di atas Footer)**:
   - Date picker langsung submit otomatis saat tanggal dipilih (`onchange="if(this.value) this.form.submit();"`).
   - Kartu arsip bulanan dinamis (November 2026, Oktober 2026, September 2026, dst.).
   - Routing `/arsip` dengan filter tanggal (`?tanggal=`), bulan (`?bulan=`), dan tahun (`?tahun=`).
3. **Navigasi Utama**:
   - Beranda (`/`), Index (`/terbaru`), Kategori (Dropdown), Tentang Kami (`/halaman/tentang`).
