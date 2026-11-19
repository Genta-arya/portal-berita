# Setup dan deployment

## Lokal

1. `composer install` (tanpa lockfile: `composer update` sekali lalu commit composer.lock).
2. Salin `.env.example` ke `.env` jika belum tersedia; isi kredensial MySQL secara privat.
3. `php artisan key:generate` untuk instalasi baru saja.
4. `npm install`, lalu commit package-lock.json; sesudahnya gunakan `npm ci`.
5. `npm run build` dan `php artisan serve`.
6. Jalankan `php artisan test` dan `composer validate`.

## Produksi

- Periksa PHP >= 8.2, ekstensi Laravel/PDO MySQL, HTTPS, kuota dan cron.
- Bangun aset di lokal/CI; unggah public/build dan dependensi produksi dari `composer install --no-dev --optimize-autoloader`.
- Document root harus menunjuk ke public; .env, storage privat, log dan vendor tidak boleh terekspos web.
- Atur APP_ENV=production, APP_DEBUG=false, APP_URL ke domain final, SESSION_SECURE_COOKIE=true.
- Jangan mengganti APP_KEY instalasi aktif. Backup sebelum `php artisan migrate --force`; jangan memakai migrate:fresh pada hosting.
- Pastikan storage dan bootstrap/cache dapat ditulis. Jalankan `php artisan config:cache` dan `php artisan view:cache`.
- Saat scheduler dibutuhkan, aktifkan cron per menit untuk `php artisan schedule:run`.
- Uji HTTPS, halaman, database dan restore backup sebelum dibuka publik.

MySQL yang diminta memakai server.hostdata.id:3306. Remote MySQL mungkin memerlukan
allowlist alamat klien; koneksi belum dianggap berhasil sebelum diuji.

## Batas setup awal

Tersedia skeleton Laravel 12, Blade, entry React/Vite, halaman fondasi dan palet sage.
CMS, akun redaksi, artikel dan fitur PRD lainnya belum diimplementasikan.
Halaman fondasi memakai noindex. Jangan menjalankan seeder akun contoh di produksi.
Media editorial harus privat saat modul media dibuat.

Unduhan dependensi pada sesi setup terhambat DNS. Lockfile, build dan pengujian
runtime harus diselesaikan pada lingkungan berjaringan sebelum deployment.
Evaluasi versi Laravel/PHP yang didukung hosting sebelum peluncuran.
