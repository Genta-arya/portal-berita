# Panduan redaksi

## Masuk dan membuat artikel

Masuk melalui `/login`. Tidak ada registrasi pembaca. Admin membuat akun pengguna; password awal tidak disediakan sebagai bawaan. Dashboard merangkum pekerjaan sesuai izin.

Buka Artikel, isi judul draft lalu Buat artikel. Lengkapi ringkasan, kanal, isi visual, tag, dan gambar utama. Upload gambar melalui Media; gunakan pemilih gambar untuk isi dan ID media untuk gambar utama. Alt menjelaskan gambar, kredit menyebut sumber, dan caption memberi konteks. Gambar draft hanya dapat dibuka oleh redaksi yang berwenang.

Format isi: paragraf, H2/H3, tebal/miring, daftar, kutipan, tautan HTTP/HTTPS, gambar dan video YouTube. Caption dapat ditulis sebagai paragraf setelah gambar. HTML berbahaya disanitasi server; gambar eksternal dan iframe selain YouTube yang diizinkan dihapus.

## Simpan dan review

Simpan artikel secara manual. Draft/Needs Revision melakukan autosave setiap 30 detik saat berubah. Perhatikan pesan penyimpanan. Bila gagal, salin naskah dan coba kembali; jangan menutup halaman. Jika muncul konflik versi, simpan salinan perubahan Anda lalu muat ulang; sistem tidak menimpa versi server.

Ajukan review setelah selesai. Penulis tidak dapat menyunting naskah In Review sebelum editor meminta revisi. Editor dapat meminta revisi dengan catatan, menyetujui, kemudian menerbitkan atau menjadwalkan. Jadwal dimasukkan dalam WIB. Semua aksi memeriksa versi yang sama.

Mengubah artikel Approved/Scheduled membatalkan persetujuan/jadwal dan mengembalikannya ke Draft. Artikel Published hanya dapat diubah melalui simpan eksplisit editor/admin. Autosave tidak mengubah artikel terbit. Riwayat revisi menyediakan pemulihan sebagai versi baru.

## Koreksi dan penarikan

Editor memperbaiki artikel terbit dan menyimpan secara eksplisit; versi sebelumnya tetap ada dan waktu publikasi pertama dipertahankan. Jika artikel harus ditarik, pilih Tarik artikel dan isi alasan. URL menjadi 410 dan media eksklusifnya tidak lagi bisa dibuka anonim. Kembalikan ke Draft untuk menyiapkan ulang; URL tetap 410 sampai terbit kembali.

Draft yang belum pernah terbit dapat diarsipkan. Artikel pernah terbit tidak dihapus melalui dashboard. Media yang dirujuk artikel, revisi atau banner tidak bisa dihapus. Jangan menghapus berkas langsung dari storage.

## Operasional admin

Pengelolaan menyediakan pengguna, kanal/tag, banner, halaman informasi dan pengaturan identitas. Klik Edit pada data tersimpan untuk mengisi formulir. Minimal satu admin aktif dipertahankan. Perubahan peran/status/password membatalkan sesi lama.

Headline/Pilihan memilih ID artikel terbit dan urutan. Banner menggunakan rentang waktu WIB dan slot; jadwal aktif bertumpuk ditolak. Breaking news hilang otomatis saat akhir rentangnya. Statistik adalah perkiraan tampilan, bukan pembaca unik atau dasar penagihan.

Editor visual menggunakan Jodit Community (MIT) dengan toolbar bawaan dan undo/redo, shortcut Ctrl+B/Ctrl+I/Ctrl+Z, penghitung kata dan estimasi baca. Caption/kredit dapat disisipkan sesudah gambar. Seleksi dan riwayat undo dikelola editor. Berkas Word/HTML yang ditempel tetap melalui sanitasi ketika disimpan. HTML yang tidak didukung tidak diterbitkan sebagai kode bebas.

Tersedia tabel, garis bawah/coret, pencarian, fullscreen, preview, dan mode source HTML. Mode source tetap mengikuti sanitasi server; kode script, iframe bebas dan gambar di luar media internal tidak diterbitkan. Library self-hosted tanpa API key atau langganan; notice lisensi tersedia di public/vendor/jodit/LICENSE.txt.
