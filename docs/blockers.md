# Hambatan dan verifikasi tertunda

- Target MySQL untuk pengujian terisolasi belum diverifikasi; database remote tidak boleh dipakai sebagai database test destruktif.
- PHP CLI memunculkan peringatan loader ionCube yang tidak ditemukan. CLI Laravel dan build tetap berhasil; konfigurasi PHP lokal perlu dibersihkan sebelum serah terima.
- Kelayakan hosting, cron, SMTP, backup/restore, domain dan konten final belum diverifikasi.
