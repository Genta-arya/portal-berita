<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class LocalDemoSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment('local') || ! str_ends_with(config('database.connections.mysql.database', ''), '_local')) {
            throw new \RuntimeException('Seeder hanya untuk database lokal _local.');
        }
        if (User::exists()) {
            return;
        }
        $password = Str::password(20);
        $admin = User::factory()->create(['name' => 'Admin Lokal', 'email' => 'admin@portal.test', 'password' => $password, 'role' => 'admin', 'slug' => 'admin-lokal']);
        Storage::disk('local')->put('local-access.txt', "Akses lokal saja\nEmail: admin@portal.test\nPassword: ".$password."\n");
        DB::table('site_settings')->insert(['key' => 'name', 'value' => 'Portal Berita', 'created_at' => now(), 'updated_at' => now()]);
        $titles = ['Nasional' => ['Ruang publik yang nyaman dimulai dari keterlibatan warga', 'Komunitas lokal menggerakkan kebiasaan membaca setiap pekan'], 'Ekonomi' => ['Pasar kreatif memberi ruang baru untuk usaha kecil', 'Pelaku usaha merancang langkah untuk tumbuh bersama'], 'Teknologi' => ['Literasi digital membantu keluarga mengenali informasi tepercaya', 'Inovasi sederhana membuat layanan warga lebih mudah'], 'Olahraga' => ['Lapangan lingkungan kembali menjadi ruang pertemuan warga', 'Komunitas lari mengajak pemula bergerak dengan aman']];
        $n = 0;
        foreach ($titles as $channel => $items) {
            $c = Category::create(['name' => $channel, 'slug' => Str::slug($channel), 'position' => ++$n]);
            foreach ($items as $title) {
                $dir = 'editorial/demo-'.Str::uuid();
                $img = imagecreatetruecolor(1280, 800);
                $bg = imagecolorallocate($img, 52 + $n * 15, 78 + $n * 12, 65 + $n * 8);
                imagefill($img, 0, 0, $bg);
                $accent = imagecolorallocate($img, 163, 177, 138);
                imagefilledellipse($img, 1050, 180, 600, 600, $accent);
                $ink = imagecolorallocate($img, 250, 249, 246);
                imagestring($img, 5, 70, 620, strtoupper($channel).' / ILUSTRASI DEMO LOKAL', $ink);
                ob_start();
                imagewebp($img, null, 85);
                $bytes = ob_get_clean();
                imagedestroy($img);
                Storage::disk('local')->put($dir.'/1280.webp', $bytes);
                Storage::disk('local')->put($dir.'/480.webp', $bytes);
                Storage::disk('local')->put($dir.'/original', $bytes);
                $variants = json_encode(['480' => ['path' => $dir.'/480.webp', 'width' => 1280, 'height' => 800], '1280' => ['path' => $dir.'/1280.webp', 'width' => 1280, 'height' => 800]]);
                $mid = DB::table('media')->insertGetId(['user_id' => $admin->id, 'name' => 'Ilustrasi demo '.$channel, 'path' => $dir.'/original', 'variants' => $variants, 'mime' => 'image/webp', 'size' => strlen($bytes), 'width' => 1280, 'height' => 800, 'alt' => 'Ilustrasi geometris kanal '.$channel, 'credit' => 'Ilustrasi internal demo', 'created_at' => now(), 'updated_at' => now()]);
                $a = new Article(['title' => '[DEMO] '.$title, 'slug' => Str::slug($title), 'summary' => 'Konten contoh untuk meninjau tampilan portal. Artikel ini adalah simulasi pengembangan, bukan laporan peristiwa nyata.', 'body' => '<p><strong>Konten demo lokal.</strong> Naskah ini dibuat untuk menguji pengalaman membaca, navigasi kanal, dan alur publikasi. Seluruh contoh di halaman ini merupakan simulasi dan tidak dimaksudkan sebagai laporan berita nyata.</p><h2>Informasi yang mudah dipahami</h2><p>Portal ini menempatkan konteks, sumber, dan kenyamanan membaca sebagai bagian penting dari penyajian informasi. Tim redaksi dapat mengganti naskah contoh ini dengan artikel yang telah diverifikasi.</p><blockquote>Setiap berita perlu melalui pemeriksaan sebelum diterbitkan.</blockquote><p>Gunakan dashboard redaksi untuk menulis, meminta tinjauan, menambahkan media, serta mengatur jadwal publikasi.</p>', 'category_id' => $c->id, 'hero_media_id' => $mid, 'hero_alt' => 'Ilustrasi geometris kanal '.$channel, 'hero_credit' => 'Ilustrasi internal demo']);
                $a->author_id = $admin->id;
                $a->editor_id = $admin->id;
                $a->status = 'published';
                $a->published_at = now()->subMinutes($n * 10);
                $a->approved_version = 1;
                $a->save();
                DB::table('media_usages')->insert(['media_id' => $mid, 'context_type' => 'article', 'context_id' => $a->id, 'kind' => 'hero']);
            }
        }
        foreach (['tentang' => 'Tentang kami', 'kontak' => 'Kontak redaksi', 'privasi' => 'Kebijakan privasi', 'pedoman-redaksi' => 'Pedoman redaksi', 'koreksi' => 'Mekanisme koreksi'] as $slug => $title) {
            DB::table('pages')->insert(['title' => $title, 'slug' => $slug, 'body' => '<p>Halaman contoh lokal. Pemilik media perlu melengkapi dan menyetujui informasi ini sebelum situs dibuka untuk publik.</p>', 'active' => true, 'created_at' => now(), 'updated_at' => now()]);
        }
    }
}
