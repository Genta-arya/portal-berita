# Sage Editorial

| Warna | Hex | Pemakaian |
| --- | --- | --- |
| Sage | #A3B18A | Bidang dekoratif dan latar sorotan |
| Forest | #344E41 | Header, tombol, teks pada sage |
| Ivory | #FAF9F6 | Latar halaman |
| Ink | #25332B | Teks utama |
| Muted | #536354 | Teks sekunder |
| Line | #DCE2D6 | Garis dan batas kartu |
| Terracotta | #BC6C4A | Aksen kecil dan indikator fokus |

Gunakan forest/ink untuk teks; sage bukan warna teks kecil di atas putih.
Headline Georgia dan isi Arial/system sans tidak memerlukan font eksternal.
Uji kontras, keyboard, dan layout mobile saat komponen final dibuat.

## Edisi pembaca — redesign

Referensi hierarki portal: navigasi kanal mendatar, headline bertingkat, feed berita dan sidebar populer. Identitas tetap milik sendiri: simbol tiga bidang perspektif, wordmark Georgia, forest gelap `#263F33`, aksen terracotta `#B86142`, kertas putih `#FFFFFF` dan latar sage pucat `#F4F6F2`.

Stylesheet pembaca terpisah `resources/css/editorial.css`, dibatasi `.reader` agar dashboard tidak berubah. Beranda memakai headline utama dan dua pendamping, sidebar bernomor, feed ringkas, pilihan redaksi dan kelompok kanal. Nomor pada fallback Terbaru merupakan urutan daftar, bukan klaim popularitas. Navigasi dan jelajah kanal dapat digeser pada mobile tanpa memerlukan JavaScript.

Daftar/kanal/pencarian memakai kartu horizontal dan filter yang tetap berfungsi melalui GET. Halaman artikel memakai area baca sempit dengan judul serif, isi 17–18 px dan jarak baris panjang. Fokus keyboard dan prefers-reduced-motion disediakan. Tidak menyalin aset, logo, warna merek atau konten Kompas.
