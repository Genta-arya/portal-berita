# Keputusan implementasi

- 19 November 2026: checkout utama berada di `D:\Project\portal-berita`; folder OneDrive hanya berisi PRD yang baru disusun. Implementasi meneruskan skeleton yang ada.
- PRD 1.0 disinkronkan dari folder kerja aktif ke checkout utama.
- Laravel 12, Blade, React, Vite dan MySQL dipertahankan. Pengujian cepat memakai database SQLite terisolasi; verifikasi MySQL tetap menjadi gerbang terpisah.
- Tidak menjalankan migrasi ke database `.env` sebelum memastikan target dan kondisi datanya.

- MySQL lokal terisolasi berjalan port 3307; database `_test` untuk test dan `_local` untuk demo. `.env` remote dipertahankan; `.env.local` privat digunakan oleh server lokal dengan `--env=local`.
- HTMLPurifier digunakan untuk sanitasi. Iframe dibatasi YouTube nocookie; video publik memakai tombol muat agar tidak langsung bergantung penyedia eksternal.
- Halaman informasi memakai konten teks/HTML sederhana tanpa gambar; logo/favicon/banner memakai referensi media yang terotorisasi. Tidak ada HTML/script iklan bebas.
- Konten artikel/media publik dicek langsung pada setiap request untuk pencabutan segera. Cache identitas/navigasi maksimal 60 detik dan dihapus saat pengaturan diubah.
- Reset email tidak diaktifkan sebelum SMTP tersedia; pemulihan CLI diaudit.
