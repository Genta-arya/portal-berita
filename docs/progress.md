# Progres implementasi — 19 November 2026

Sumber: PRD 1.0. Checkout utama `D:\Project\portal-berita`. Perubahan batch saat ini dikumpulkan sesuai arahan pengguna: tidak push per perubahan.

## Implementasi lokal

- [x] Laravel/React/Vite, MySQL, migrasi dan lockfile.
- [x] Login/logout, throttle, peran server, sesi akun nonaktif/berubah, admin terakhir, akun CLI.
- [x] Artikel, editor visual React, autosave draft, konflik versi, sanitasi HTML, tag/kanal.
- [x] Upload/media privat, varian WebP, pemilihan ulang, metadata dan referensi revisi.
- [x] Review/revisi/persetujuan/jadwal/publikasi/penarikan, audit dan restore revisi.
- [x] Preview privat, batas 404/410, canonical/redirect slug dan pencabutan media.
- [x] Beranda, kanal, terbaru/filter, tag, profil, pencarian, artikel/terkait, halaman informasi.
- [x] Headline/pilihan, breaking, banner rentang aktif/anti-overlap, statistik/deduplikasi.
- [x] SEO/NewsArticle/RSS/sitemap, sharing, pengaturan, halaman error.
- [x] Backup/restore, scheduler, pemulihan password CLI, panduan operasi/deployment.
- [x] MySQL 8.4 terisolasi, migrasi, pengujian editorial/security/restore dan dataset 10.000 artikel.
- [x] Pemeriksaan browser beranda/artikel pada 360 dan 1440 px tanpa overflow horizontal.

## Gerbang peluncuran yang belum dapat dinyatakan selesai

- [ ] Staging provider, HTTPS, document root, cron nyata dan pencabutan akses melalui proxy hosting.
- [ ] Lighthouse mobile >=80 dan uji 20 pembaca bersamaan selama 5 menit di staging berizin.
- [ ] Pemeriksaan keyboard menyeluruh dan interaksi editor/autosave dalam browser oleh pengguna.
- [ ] Restore backup sungguhan dari storage terpisah pada hosting target.
- [ ] Nama/logo/domain, kontak, kebijakan, konten final dan persetujuan pemilik.

Ini adalah implementasi lokal; jangan menyatakan situs produksi siap rilis sampai gerbang di atas terbukti. Hasil rinci ada di test-report.md. P1/P2 tidak diimplementasikan.

## Pengiriman repository
Pengguna meminta push ke GitHub pada tindak lanjut terakhir. Implementasi lokal dikirim sebagai satu batch; gerbang peluncuran yang tercatat di atas tetap belum terverifikasi.

## Redesign pembaca dan arsip Kapuas Raya

- Hierarki baru: header identitas, nav forest, jelajah kanal, headline bertingkat, feed terbaru, sidebar, blok kanal dan footer.
- Beranda dan indeks diperiksa di browser pada 360/1440 px tanpa overflow halaman; navigasi indeks berfungsi. CSS pembaca terpisah dari dashboard.
- Arsip publik Kapuas Raya: 76 artikel, 4 halaman, 74 kategori, 3 tag, 86 entri media, 91 gambar; raw archive + ZIP disimpan privat.
- Verifikasi database lokal: 76 artikel impor berstatus draft, 4 halaman nonaktif, 91 gambar dipetakan. Akun WordPress tidak diambil (403).
- Lihat docs/kapuasraya-migration.md. Konten belum dipublikasikan ulang; sumber/kredit/atribusi dan redirect produksi memerlukan review migrasi.
- Suite diperbarui: 18 test, 138 assertion lulus di MySQL terisolasi; build Vite lulus.

## Editor jurnalis dan ticker terbaru

Editor admin kini menggunakan Tiptap (React/ProseMirror), menggantikan document.execCommand. Tersedia format paragraf/H2/H3, tebal/miring, daftar, kutipan, tautan, gambar internal beserta caption, YouTube, undo/redo, hapus format, jumlah kata, estimasi baca dan shortcut. Penyimpanan tetap melalui service editorial, validasi versi dan sanitasi server. Video keluaran editor yang memakai query parameter YouTube diizinkan hanya pada host/id embed yang dibatasi.

Ticker tepat di bawah tanggal mengambil maksimal 10 artikel terbit terbaru dengan urutan tanggal/id stabil. Posisi sticky, animasi berjalan, tombol jeda, pause saat hover/fokus, dan dukungan reduced-motion. Duplikasi visual untuk loop disembunyikan dari pembaca layar dan navigasi keyboard. Browser memverifikasi 10 tautan asli, tombol jeda, dan posisi ticker top=0 saat digulir. Editor diperiksa pada tab terpisah tanpa menyunting draft pengguna; Tiptap terpasang dan tidak ada error konsol.

## Toolbar library Quill

Atas permintaan pengguna untuk toolbar siap pakai, editor Tiptap/custom toolbar diganti dengan Quill Snow. Quill menangani editing, dropdown heading, format, list, link, image/video dan undo/redo. Integrasi media privat, caption, autosave dan konflik versi tetap memakai backend yang sama. Quill dipin 2.0.2; 2.0.3 ditolak karena advisory GHSA-v3m3-f69x-jf25 pada HTML export. Sanitasi HTML server tetap wajib. `npm audit --omit=dev` tidak melaporkan kerentanan pada dependency yang dipilih. Toolbar dan naskah lama diperiksa di browser pada tab terpisah tanpa menyimpan perubahan pada draft pengguna; tidak ada error konsol.
