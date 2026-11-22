# Hak akses ruang redaksi

- Developer: akses operasional penuh; membuat dan mengelola akun Admin, Editor, dan Penulis. Akun Developer disembunyikan dari daftar, pencarian, serta jumlah pengguna; peran Developer tidak dapat dibuat atau diubah melalui form pengguna.
- Admin: mengelola situs, semua artikel, dan akun Editor/Penulis. Tidak dapat membuat, menaikkan peran, mengubah password, atau menonaktifkan akun Admin/Developer.
- Editor: meninjau serta menerbitkan artikel dan mengelola headline, trending topik, breaking news, serta media.
- Penulis: membuat artikel atas namanya sendiri, melihat artikel miliknya, mengedit draft/revisi miliknya, dan mengajukannya untuk review. Tidak bisa mengedit artikel penulis lain, menerbitkan berita, memindahkan penulis, mengarsipkan berita, atau mengakses pengelolaan situs/pengguna.

Password wajib diisi dan dikonfirmasi saat membuat akun (minimal 8 karakter). Saat mengedit, kosongkan kedua kolom untuk mempertahankan password. Password disimpan sebagai hash; password, peran, status, atau email yang berubah akan membatalkan sesi akun tersebut.

Developer aktif terakhir dan Admin aktif terakhir dilindungi dari penonaktifan atau penurunan peran.

Pembuatan Admin melalui terminal memerlukan identitas Developer aktif: `php artisan editorial:create-admin email@example.com "Nama Admin" --developer=ID`. Password diminta melalui input privat. Pengelolaan melalui dashboard tetap direkomendasikan.
