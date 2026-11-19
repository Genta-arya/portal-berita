# Portal Berita

Fondasi Laravel 12 + Blade + komponen React/Vite untuk shared hosting.

- Kebutuhan produk: [prd.md](prd.md)
- Setup dan deployment: [docs/shared-hosting.md](docs/shared-hosting.md)
- Palet sage: [docs/design-system.md](docs/design-system.md)

Jalankan composer install, salin .env.example bila .env belum ada, isi kredensial
secara privat, lalu php artisan key:generate untuk instalasi baru.
Jalankan npm install, npm run build, lalu php artisan serve.

Sesi/cache memakai file dan antrean sync. Halaman fondasi tidak memerlukan tabel
MySQL. Jalankan php artisan migrate hanya setelah memastikan target database benar.
Jangan commit .env, password, vendor atau node_modules.

Status: halaman fondasi dan komponen pratinjau palet tersedia; CMS dan fitur berita
belum dibuat. Instalasi dependensi, build, lockfile serta tes runtime belum berhasil
divalidasi akibat gangguan DNS pada lingkungan setup. Selesaikan sebelum deployment.
