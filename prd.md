# PRD — Portal Berita

Versi: 1.0 · Tanggal: 19 November 2026 · Bahasa: Indonesia

## 1. Tujuan dan cara menggunakan dokumen

Bangun portal berita dengan Laravel, komponen React, dan MySQL. Detik.com, Kompas.com, dan CNN Indonesia menjadi referensi jenis fitur dan hierarki berita; jangan menyalin logo, artikel, foto, atau desain mereka. Daftar kebutuhan di bawah adalah spesifikasi proyek, bukan klaim tentang fitur terbaru situs referensi.

Dokumen ini adalah sumber kebenaran implementasi MVP. Kerjakan tahap secara berurutan. Jangan menambah fitur di luar P0 untuk mengejar kemiripan dengan referensi. Jangan menyebut proyek selesai hanya karena halaman depan sudah tampil.

Jika repository sudah berisi implementasi, periksa dahulu dan lanjutkan kode yang ada. Jangan membuat aplikasi kedua. Jangan mengganti stack atau menghapus pekerjaan pengguna. Catat progres dan bukti pengujian di `docs/progress.md`, keputusan teknis di `docs/decisions.md`, dan hambatan di `docs/blockers.md`. Setelah konteks percakapan terputus, baca file tersebut sebelum melanjutkan.

## 2. Keputusan produk

| Aspek | Keputusan |
| --- | --- |
| Nama sementara | Portal Berita; dapat diubah admin |
| Fokus | Berita umum Indonesia; kanal fleksibel |
| Pembaca | Tanpa akun dan tanpa paywall |
| Redaksi | Admin, editor, penulis |
| Bahasa dan waktu | Bahasa Indonesia; tampilan WIB; database menyimpan waktu UTC |
| Hosting | Shared hosting PHP; build aset dilakukan di lokal/CI |
| Database | MySQL; konfigurasi melalui `.env`, bukan kode |
| Konten awal | Data contoh hanya di lokal/testing; tidak dianggap berita nyata |

Nama final, logo, domain, kontak, isi kebijakan, akun awal, serta kredensial hosting diberikan pemilik sebelum peluncuran. Selama pengembangan gunakan placeholder yang jelas. Kekurangan identitas final tidak menghalangi implementasi.

## 3. Arsitektur wajib

- Satu aplikasi Laravel monolit. Pertahankan Laravel 12 jika skeleton tersebut sudah ada; cocokkan PHP dan ekstensi hosting sebelum deployment.
- Laravel menangani routing, sesi, autentikasi, validasi, otorisasi, transaksi, dan query database.
- Blade merender HTML publik termasuk isi artikel dan metadata SEO. Berita harus dapat dibaca tanpa JavaScript.
- React dipakai sebagai komponen interaktif: editor artikel, autosave, pemilih media, dan interaksi dashboard. Jangan mengubah seluruh aplikasi menjadi SPA atau membuat backend terpisah.
- Vite membangun CSS/JavaScript. Produksi tidak memerlukan proses Node.js, Redis, WebSocket, atau worker permanen.
- Gunakan migrasi Eloquent, MySQL `utf8mb4`, indeks query, eager loading, dan pagination. Simpan lockfile Composer dan npm.
- Sesi dan cache memakai driver yang didukung shared hosting. Cron Laravel berjalan setiap menit untuk publikasi terjadwal.
- Media editorial berada di storage privat dan diakses melalui controller berotorisasi, bukan symlink publik.
- Pisahkan controller tipis, Form Request, Policy, dan service alur editorial. Logika publikasi hanya mempunyai satu implementasi yang digunakan dashboard dan scheduler.
- Gunakan library sanitizer HTML yang terawat; jangan membuat sanitizer berbasis regex.

## 4. Ruang lingkup

### P0 — wajib sebelum MVP selesai

1. Beranda, terbaru, kanal, artikel, tag, profil penulis, pencarian, dan halaman informasi.
2. Login redaksi, peran, pengelolaan akun, dan dashboard.
3. CRUD artikel, editor visual, media privat, autosave, preview, review, revisi, jadwal, publikasi, penarikan.
4. Headline, pilihan redaksi, breaking news, dan daftar populer.
5. SEO, canonical, redirect slug, sitemap, RSS, dan berbagi tautan.
6. Banner iklan gambar, pengaturan situs, audit, panduan backup dan deployment.
7. Pengujian otomatis alur kritis dan pemeriksaan tampilan mobile.

### Tidak dikerjakan dalam MVP

Komentar, akun pembaca, bookmark tersinkronisasi, newsletter, notifikasi push, polling, live report, paywall, aplikasi mobile, multi-bahasa, AI penulis berita, scraping berita, marketplace iklan, pembayaran, dan hosting video sendiri. Embed YouTube tervalidasi boleh digunakan.

## 5. Hak akses

| Aksi | Penulis | Editor | Admin |
| --- | --- | --- | --- |
| Buat draft | Ya | Ya | Ya |
| Baca/edit draft | Milik sendiri | Semua | Semua |
| Upload media | Ya | Ya | Ya |
| Lihat media privat | Milik sendiri | Semua | Semua |
| Ajukan review | Milik sendiri | Ya | Ya |
| Minta revisi/setujui/jadwalkan/terbitkan/tarik | Tidak | Ya | Ya |
| Edit artikel terbit | Tidak | Ya | Ya |
| Atur headline dan breaking news | Tidak | Ya | Ya |
| Kelola pengguna, kanal, iklan, halaman, pengaturan | Tidak | Tidak | Ya |

Semua pemeriksaan berlaku di server, termasuk request langsung dan autosave. Media yang dipakai artikel terbit dapat dipilih ulang. Penulis boleh membuat tag. Admin terakhir yang aktif tidak boleh dinonaktifkan atau diturunkan perannya. Perubahan peran/penonaktifan membatalkan sesi akun. Atribusi artikel tetap ada setelah akun dinonaktifkan. Registrasi publik tidak tersedia.

## 6. Halaman publik

| Rute | Perilaku |
| --- | --- |
| `/` | Headline utama/pendamping, terbaru, pilihan redaksi, blok kanal, populer, banner aktif |
| `/terbaru` | Urut `published_at DESC, id DESC`; filter kanal; 20 item per halaman |
| `/kanal/{slug}` | Nama/deskripsi kanal dan artikel terbit dengan pagination |
| `/berita/{id}/{slug}` | Artikel lengkap, atribusi, tanggal, gambar, caption/kredit, tag, bagikan, terkait |
| `/tag/{slug}` | Artikel terbit dengan tag tersebut |
| `/penulis/{slug}` | Nama publik, bio, artikel terbit; jangan tampilkan email login |
| `/cari?q=...` | Cari judul/isi artikel terbit; GET, 2–100 karakter, pagination, urutan terbaru |
| `/halaman/{slug}` | Tentang, kontak, privasi, pedoman redaksi, mekanisme koreksi |
| `/sitemap.xml` | Indeks sitemap beserta partisi artikel terbit bila diperlukan |
| `/rss.xml` | 50 artikel terbit terbaru |

Header mempunyai logo/nama, navigasi kanal, pencarian, dan menu mobile yang dapat diakses keyboard. Footer memuat halaman informasi. Form pencarian dan navigasi dasar berfungsi tanpa JavaScript. Kueri kosong menampilkan petunjuk, hasil kosong menampilkan pesan. Parameter halaman tidak valid tidak boleh menghasilkan error 500.

Jika headline kosong, pakai berita terbaru. Jangan mengulang artikel headline pada blok terbaru beranda pertama. Sembunyikan blok kanal kosong. Artikel terkait memakai kanal/tag, maksimal enam, tidak menyertakan artikel saat ini. Artikel dibaca utuh dalam satu halaman.

Tombol berbagi: WhatsApp, Facebook, X, dan salin URL canonical. Perubahan slug mengarah 301 langsung ke slug terkini berdasarkan ID; jangan membuat rantai redirect. Artikel yang tidak pernah terbit mengembalikan 404 bagi anonim. Artikel yang pernah terbit tetapi kini tidak terbit mengembalikan 410, termasuk melalui slug lama.

## 7. Alur editorial dan aturan status

Status internal: `draft`, `in_review`, `needs_revision`, `approved`, `scheduled`, `published`, `withdrawn`.

| Dari | Aksi | Menjadi | Pelaku |
| --- | --- | --- | --- |
| draft / needs_revision | Ajukan | in_review | Pemilik atau editor/admin |
| in_review | Minta revisi dengan catatan | needs_revision | Editor/admin |
| in_review | Setujui versi | approved | Editor/admin |
| approved | Terbitkan sekarang | published | Editor/admin |
| approved | Jadwalkan waktu mendatang | scheduled | Editor/admin |
| scheduled | Waktu tiba dan versi tetap disetujui | published | Scheduler |
| scheduled | Batalkan jadwal | approved | Editor/admin |
| approved / scheduled | Ubah isi | draft; persetujuan/jadwal dihapus | Editor/admin |
| published | Tarik dengan alasan | withdrawn | Editor/admin |
| withdrawn | Kembalikan untuk diedit | draft | Editor/admin |

Transisi lain ditolak. Versi `in_review` tidak diedit penulis sampai diminta revisi. Editor dapat menulis dan menyetujui artikelnya sendiri. Artikel yang pernah terbit tetap mempunyai `published_at` pertama, walau ditarik dan diterbitkan ulang.

Syarat terbit/jadwal: judul, slug, ringkasan, isi bermakna, kanal aktif, penulis, gambar utama dengan alt dan kredit, serta persetujuan atas versi terkini. Validasi berjalan di server. Jadwal WIB dikonversi ke UTC.

Publikasi, penarikan, pembatalan jadwal, serta pemeriksaan versi menggunakan transaksi dan penguncian record. Scheduler idempoten; dua pemanggilan tidak menggandakan publikasi. Jadwal batal tidak boleh diterbitkan oleh proses yang terlambat.

### Editor dan revisi

- Field: judul, slug, ringkasan, kanal, tag, isi, gambar utama, alt/caption/kredit, atribusi tamu opsional, metadata SEO, label sponsor.
- Editor mendukung paragraf, heading H2/H3, daftar, kutipan, tautan HTTP/HTTPS, gambar internal, caption, dan YouTube tervalidasi. Tolak script, event handler, iframe bebas, dan URL berbahaya di server.
- Simpan manual; autosave hanya `draft`/`needs_revision` setiap 30 detik saat berubah. Tampilkan status penyimpanan dan peringatan sebelum meninggalkan perubahan belum tersimpan.
- Setiap request perubahan membawa nomor versi. Versi basi mendapat 409 dan tidak menimpa data; pertahankan isian pengguna untuk pemulihan.
- Artikel terbit hanya berubah melalui simpan eksplisit editor/admin. Autosave artikel terbit ditolak.
- Simpan snapshot revisi, pelaku, waktu, dan catatan. Pemulihan revisi membuat versi baru, bukan menghapus riwayat; konten nonterbit harus disetujui ulang.
- Preview memerlukan sesi dan Policy; header `private, no-store` dan `noindex`.
- Draft yang belum pernah terbit boleh soft delete; penulis hanya miliknya. Artikel pernah terbit ditarik, bukan dihapus melalui dashboard.

## 8. Media

Terima JPEG, PNG, WebP maksimal 5 MB dan maksimal 8.000 × 8.000 piksel. Periksa MIME dan decoding; tolak SVG, executable, dan berkas rusak. Nama disusun server. Buat varian lebar 480 dan 1280 piksel tanpa memperbesar gambar kecil; simpan dimensi untuk layout.

Original tetap privat. Anonim hanya bisa mengakses varian yang sedang dirujuk artikel terbit atau aset publik yang disetujui. Penarikan mencabut akses media eksklusif artikel tersebut; media bersama artikel terbit lain tetap tersedia. Gunakan respons yang tidak membolehkan cache publik bertahan setelah akses dicabut.

Catat pemakaian media untuk isi, gambar utama, revisi, banner, halaman, dan identitas. Tolak penghapusan media yang masih dirujuk. Alt/caption/kredit disimpan per pemakaian, sehingga edit satu artikel tidak mengubah artikel lain. Media library menyediakan pencarian, preview, metadata, serta informasi pemakaian sesuai izin.

## 9. Modul pendukung

- Dashboard: jumlah per status, antrean review, artikel terbaru, statistik; daftar dapat difilter status/kanal/penulis/tanggal/kata kunci sesuai izin.
- Kanal/tag: nama, slug unik, deskripsi, status; kanal mempunyai urutan. Kanal terpakai tidak dihapus tanpa pemindahan artikel.
- Headline/pilihan redaksi: editor memilih artikel terbit dan mengurutkannya. Penarikan otomatis mengeluarkan artikel dari semua permukaan publik.
- Breaking news: teks, URL HTTP/HTTPS opsional, mulai, akhir, aktif. Tampil hanya pada rentang `[mulai, akhir)`.
- Iklan: gambar, alt, URL HTTP/HTTPS, slot, mulai/akhir, aktif. Slot: atas beranda, sela daftar, sidebar desktop, akhir artikel. Tolak jadwal aktif bertumpuk pada slot sama secara atomik. Beri label Iklan, dimensi tetap, tanpa script atau pop-up.
- Populer: agregat view tujuh hari terakhir; deduplikasi artikel/sesi selama 30 menit; abaikan bot yang dikenali dan preview. Jangan menyimpan IP mentah. Statistik gagal tidak boleh menggagalkan pembacaan berita. Jika belum ada data, tampilkan terbaru dengan label Terbaru.
- Pengaturan: nama, logo/favicon, deskripsi SEO, kontak, tautan sosial. Halaman informasi dapat diedit admin dengan sanitasi.
- Login: sesi, CSRF, pembatasan percobaan, logout. Reset email hanya diaktifkan setelah SMTP dikonfigurasi; sediakan prosedur CLI pemulihan akun yang diaudit. Admin awal dibuat lewat CLI tanpa password bawaan.
- Audit: publikasi, penarikan, revisi, perubahan peran, jadwal, dan pengaturan; simpan pelaku/target/waktu tanpa password/token.

## 10. Model data minimum

| Entitas | Data penting |
| --- | --- |
| users | nama publik, slug, email unik, password hash, role, active, bio, session_version |
| categories | nama, slug unik, deskripsi, posisi, active |
| articles | judul, slug, ringkasan, isi, status, author_id, editor_id, category_id, version, approved_version, scheduled_at, published_at, withdrawn_at, alasan, metadata SEO, deleted_at |
| tags / article_tag | tag unik dan relasi artikel |
| media / media_usages | pemilik, path privat, varian, MIME, ukuran/dimensi; konteks pemakaian dan metadata |
| article_revisions | artikel, versi, snapshot, pelaku, catatan, waktu |
| editorial_notes | artikel, pelaku, catatan, waktu |
| featured_placements | artikel, lokasi, posisi |
| breaking_news / ad_banners | konten, rentang aktif, status; slot/banner/tujuan |
| pages / site_settings | halaman informasi dan identitas |
| article_view_daily | artikel, tanggal, jumlah; unik artikel/tanggal |
| audit_logs | pelaku, aksi, target, metadata aman, waktu |

Gunakan foreign key dan indeks status/waktu terbit, jadwal, kanal, penulis, serta tabel pivot. Relasi slug artikel menggunakan ID stabil; tabel redirect tambahan hanya diperlukan bagi perubahan URL entitas tanpa ID. Semua daftar publik menggunakan scope `published` bersama.

## 11. Tampilan, SEO, dan kualitas

Palet: forest `#344E41`, sage `#A3B18A`, ivory `#FAF9F6`, ink `#25332B`, muted `#536354`, border `#DCE2D6`, terracotta `#BC6C4A`. Headline Georgia; isi Arial/system sans. Gunakan forest/ink untuk teks, bukan sage untuk teks kecil di atas putih. Ikuti `docs/design-system.md` bila tersedia dan konsisten.

Tampilan editorial dengan hierarki judul jelas, ruang baca nyaman, foto relevan, dan navigasi kanal. Body minimal 16 px. Uji lebar 360, 768, dan 1440 px; tidak boleh ada scroll horizontal, tombol tertutup iklan, atau formulir tanpa label. Fokus keyboard terlihat. Gambar punya alt dan dimensi; gambar utama diprioritaskan, gambar di bawah layar lazy load.

Setiap halaman publik mempunyai title, description, canonical dan Open Graph. Artikel mempunyai NewsArticle JSON-LD yang sesuai konten. Canonical pagination menunjuk halaman masing-masing. Pencarian/admin/preview noindex; draft tetap harus dilindungi autentikasi. Sitemap/RSS tidak memuat draft atau artikel ditarik.

Cache daftar maksimal 60 detik dengan invalidasi setelah perubahan editorial dan batas kedaluwarsa sebelum transisi jadwal banner/breaking. Status akses artikel/media diperiksa sebelum melayani konten. Target Lighthouse mobile ≥80 pada staging; catat konfigurasi dan hasil, jangan mengklaim tanpa pengukuran.

## 12. Urutan implementasi untuk agen

| Tahap | Pekerjaan | Gerbang sebelum tahap berikutnya |
| --- | --- | --- |
| 0 | Periksa repository, instruksi lokal, runtime, `.env` tanpa mencetak rahasia; buat checklist P0 | Lokasi proyek dan kondisi awal tercatat |
| 1 | Dependensi, lockfile, MySQL lokal/testing, migrasi, model | Instalasi/build dan migrasi bersih berhasil |
| 2 | Login, peran, Policy, akun CLI, dashboard | Uji akses terlarang dan admin terakhir lulus |
| 3 | Artikel, editor React, media privat, revisi/autosave | Draft tersimpan; anonim tidak bisa melihat isi/media |
| 4 | Review, persetujuan, jadwal, publikasi, penarikan | Uji transisi, konflik, dan scheduler lulus |
| 5 | Semua halaman publik, SEO, RSS/sitemap | Konten terbit tersedia; draft/withdrawn tidak bocor |
| 6 | Headline, breaking, iklan, statistik, pengaturan | Batas waktu, konflik banner, fallback teruji |
| 7 | QA integrasi, mobile, keamanan, panduan operasi | Semua kriteria penerimaan dibuktikan |
| 8 | Staging, backup/restore, kesiapan hosting | Laporan rilis dan persetujuan pemilik sebelum produksi |

Jangan menandai tahap selesai jika pengujian belum berjalan. Jika terhambat layanan eksternal, lanjutkan pekerjaan lokal yang independen dan catat hambatan spesifik. Jangan memakai database produksi untuk test otomatis, `migrate:fresh`, atau seeder contoh. Jangan mencetak atau commit `.env` dan kredensial. Jangan melakukan deployment produksi hanya karena build berhasil.

## 13. Kriteria penerimaan wajib

- [ ] Penulis membuat draft, upload, preview, submit; editor meminta revisi, menyetujui, menerbitkan.
- [ ] Penulis gagal membuka/mengubah draft orang lain, artikel terbit, pengguna, dan pengaturan melalui request langsung.
- [ ] Anonim tidak bisa mengakses draft, preview, original, atau varian media privat.
- [ ] Sanitasi menolak payload script/URL berbahaya; upload palsu/rusak/terlalu besar ditolak.
- [ ] Autosave hanya status yang diperbolehkan; kegagalan mempertahankan isian; konflik versi menghasilkan 409 tanpa kehilangan data server.
- [ ] Revisi dan catatan tersimpan; restore membuat versi baru dan mengikuti aturan persetujuan.
- [ ] Scheduler menerbitkan paling lambat dua menit setelah jadwal pada cron normal; pemanggilan ganda idempoten; pembatalan tidak bocor.
- [ ] Artikel terbit muncul pada daftar, kanal, tag, penulis, pencarian, RSS/sitemap sesuai batas pagination.
- [ ] Penarikan menghasilkan 410 dan menghilangkan semua akses publik termasuk media eksklusif; draft baru 404.
- [ ] Slug lama 301 langsung ke canonical; koreksi mempertahankan waktu terbit pertama.
- [ ] Breaking/banner hanya muncul dalam rentang aktif; bentrok banner ditolak; cache tidak menunda transisi lebih dari 60 detik.
- [ ] Headline/kanal/statistik kosong mempunyai fallback yang benar.
- [ ] Akun nonaktif kehilangan sesi; admin aktif terakhir terlindungi.
- [ ] Halaman artikel dan pencarian dasar bekerja tanpa JS; keyboard/mobile lulus pemeriksaan.
- [ ] Build produksi, test otomatis, dan validasi Composer lulus; hasil dicatat dengan perintah dan waktu.
- [ ] Backup database/media berhasil dipulihkan pada lingkungan uji.
- [ ] Hosting menjalankan PHP/MySQL dan cron yang diperlukan tanpa Node permanen; `.env`/vendor/storage privat tidak terekspos.

## 14. Operasional dan definisi selesai

Sediakan `README.md`, `docs/progress.md`, `docs/decisions.md`, `docs/blockers.md`, `docs/editor-guide.md`, `docs/deployment.md`, dan `docs/test-report.md`. Panduan memuat instalasi, pembuatan admin, alur redaksi, cron, konfigurasi SMTP, build, backup/restore, dan rollback.

Document root produksi wajib `public`. Gunakan HTTPS, `APP_DEBUG=false`, secure session cookie, serta APP_KEY tetap untuk instalasi aktif. Backup sebelum migrasi produksi. Backup harian database/media ke lokasi terpisah; sasaran retensi 7 harian dan 4 mingguan disesuaikan kuota. Catat hasil restore, bukan sekadar keberadaan backup.

MVP selesai ketika seluruh P0 diimplementasikan dan kriteria penerimaan lulus. Staging dan peluncuran produksi adalah status terpisah. Konten final, kebijakan, kontak, domain, dan persetujuan peluncuran harus datang dari pemilik. Fitur yang tertunda, test yang belum dijalankan, dan kebutuhan akses eksternal wajib disebutkan jujur dalam laporan akhir.
