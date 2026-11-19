# Kebutuhan eksternal dan batas verifikasi

Implementasi lokal dan database MySQL terisolasi telah tersedia. Database remote tidak dimigrasikan dan deployment produksi belum dilakukan.

Gerbang yang memerlukan lingkungan/keputusan pemilik:
- Domain/staging, akses deployment dan spesifikasi shared hosting (PHP/GD/WebP/ZIP/cron/document root/HTTPS/kuota).
- Identitas/logo, kontak redaksi dan isi kebijakan final yang disetujui pemilik.
- Backup terenkripsi pada storage terpisah serta uji pemulihan di hosting.
- Pengukuran Lighthouse dan beban staging sesuai izin provider.
- Peninjauan keyboard lengkap dan penggunaan editor di browser.

CLI PHP lokal menampilkan loader ionCube DLL tidak ditemukan. Laravel dan PHPUnit tetap berjalan; benahi konfigurasi PHP lokal sebelum serah terima lingkungan.
