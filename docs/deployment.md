# Deployment dan pemulihan

## Persiapan hosting

Periksa PHP >=8.2 dengan PDO MySQL, GD/WebP, mbstring, DOM, fileinfo, OpenSSL, ZIP; MySQL utf8mb4; HTTPS; cron tiap menit; akses CLI; kuota storage. Laravel 12 digunakan. Document root harus `public`. Jangan menyalin `.env.local`, akun demo, database uji atau backup ke direktori publik.

Instal dependensi berdasarkan lockfile: `composer install --no-dev --optimize-autoloader`, `npm ci`, `npm run build`. Build dilakukan lokal/CI. Unggah source, vendor dan public/build. Tidak memerlukan Node/Redis/worker permanen pada hosting.

Sediakan `.env` hosting terpisah: APP_ENV=production, APP_DEBUG=false, APP_URL domain HTTPS, DB_CONNECTION=mysql dan kredensial privat, SESSION_DRIVER=file, CACHE_STORE=file, SESSION_SECURE_COOKIE=true. Buat APP_KEY sekali untuk instalasi baru; pertahankan key instalasi aktif. Storage dan bootstrap/cache harus writable.

Backup sebelum `php artisan migrate --force`. Jangan jalankan migrate:fresh pada database hosting. Buat admin dengan `php artisan editorial:create-admin email@example.com "Nama Admin"`; password diminta secara privat. Jalankan `php artisan config:cache` dan `php artisan view:cache`.

Cron per menit: `* * * * * cd /path/app && /path/php artisan schedule:run >> /path/private/scheduler.log 2>&1`. Verifikasi `php artisan editorial:publish-due`, lalu uji jadwal sungguhan di staging. Pantau log dan ruang disk. SMTP belum diperlukan untuk login; pemulihan akun memakai CLI `editorial:reset-password email@example.com`, yang diaudit dan membatalkan sesi.

## Backup dan restore

Jalankan harian `php artisan editorial:backup /path/private/portal-YYYYMMDD.zip`. Arsip berisi snapshot tabel aplikasi dan media editorial privat; termasuk hash akun, sehingga wajib dibatasi aksesnya. Salin secara terenkripsi ke penyimpanan terpisah. Pertahankan tujuh harian dan empat mingguan sesuai kuota; jangan mengandalkan satu disk. Atur rotasi log teknis 30 hari dan retensi audit 12 bulan sesuai kebijakan pemilik. APP_KEY/env disimpan terpisah dengan aman, tidak termasuk arsip.

Uji pemulihan: buat database baru, konfigurasi lingkungan uji terpisah, jalankan migrasi, kemudian `php artisan editorial:restore /path/private/backup.zip`. Restore menolak production dan database berisi akun/artikel/media. Periksa artikel, relasi, gambar dan login; sesi lama dibatalkan. Uji otomatis sudah memeriksa pemulihan database/artikel/berkas privat. Untuk data besar, lakukan backup database native provider selain arsip aplikasi.

## Rollback

Simpan rilis sebelumnya dan backup pra-migrasi. Alihkan ke maintenance bila perlu. Rollback kode hanya bila skema kompatibel; jangan menjalankan migrasi down yang membuang berita baru. Untuk skema tidak kompatibel, pulihkan backup ke database terpisah, rekonsiliasi berita yang masuk sesudah backup, lalu alihkan konfigurasi setelah diverifikasi. Catat waktu insiden dan hasil restore.

## Gerbang peluncuran

Staging memerlukan pembatasan akses terpisah; noindex bukan autentikasi. Verifikasi HTTPS, rahasia tidak bisa diakses melalui URL, cron, restore, tampilan mobile, keyboard, isi kebijakan/kontak, dan identitas final. Ukur Lighthouse mobile dan beban sesuai izin provider. Pemilik menyetujui konten serta peluncuran. Implementasi lokal bukan bukti bahwa hosting produksi sudah lulus.
