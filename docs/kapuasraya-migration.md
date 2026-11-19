# Arsip dan migrasi Kapuas Raya

Sumber publik: https://kapuasraya.com/ melalui WordPress REST API. Pengambilan 19 November 2026, atas permintaan pemilik situs. Situs sumber tidak diubah.

## Hasil pengambilan

| Jenis | Tersedia pada API | Diarsipkan |
| --- | ---: | ---: |
| Artikel | 76 | 76 |
| Halaman | 4 | 4 |
| Kategori | 74 | 74 |
| Tag | 3 | 3 |
| Entri media | 86 | 86 |
| URL gambar unik (media + isi) | 91 | 91 |

Arsip privat: `storage/app/private/imports/kapuasraya/`. ZIP: `storage/app/private/imports/kapuasraya-archive.zip`. Raw JSON menyimpan judul, isi HTML asli, tanggal, slug, URL, ID penulis, kategori dan tag. Homepage HTML, metadata situs, robots, sitemap, gambar, indeks SHA-256 dan manifest juga tersedia. Arsip tidak di-commit dan tidak disajikan ke publik.

Ini adalah arsip konten yang dapat diakses publik, bukan backup database WordPress/server. Endpoint users mengembalikan 403; akun, password, draft privat, komentar, konfigurasi plugin/theme, menu privat dan isi database internal tidak diambil. Tidak ada bypass autentikasi. Lima gambar inline pada CDN eksternal yang dirujuk artikel ikut diarsipkan; atribusi/hak pemakaiannya tetap perlu ditinjau sebelum terbit ulang.

## Impor ke CMS lokal

`php artisan editorial:import-kapuasraya --env=local --user=1`

- 76 artikel masuk sebagai draft, tidak dipublikasikan otomatis. Empat halaman masuk nonaktif. Konten demo lama tidak dihapus.
- Gambar didekode dan dibuat varian WebP privat; gambar inline diarahkan ke endpoint media baru. Sanitasi HTML berlaku pada hasil impor. Raw HTML asli tetap ada dalam arsip.
- Tabel `source_imports` memetakan ID/URL lama ke ID baru; pengulangan impor tidak menggandakan artikel. Tanggal publikasi asli disimpan sebagai metadata sumber, bukan menandai draft sebagai pernah terbit pada sistem baru.
- Penulis akun sementara adalah penanggung jawab impor; ID author WordPress tetap tersimpan untuk rekonsiliasi. Kredit gambar tidak dikarang; lengkapi kredit, sumber, atribusi, ringkasan dan kanal sebelum persetujuan publikasi. Dua artikel sumber tidak mempunyai featured media.
- URL lama disimpan untuk persiapan redirect saat migrasi domain; redirect situs produksi belum diaktifkan. Tautan dalam naskah yang masih menuju situs lama perlu ditinjau.

Laporan privat `import-report.json` mencatat jumlah dan peringatan. Pengujian otomatis memastikan status draft, halaman nonaktif, sanitasi, mapping kategori/tag, akses anonim ditolak dan idempotensi. Pengguna dapat meninjau draft melalui `/admin/articles?status=draft`.

Script pengambilan ulang: `scripts/archive-kapuasraya.ps1`. Gunakan lokasi arsip baru untuk snapshot baru apabila sumber berubah; jangan menghapus snapshot awal. Cadangkan arsip ke lokasi terpisah sebelum perpindahan hosting.
