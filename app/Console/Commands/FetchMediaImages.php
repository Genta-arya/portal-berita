<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class FetchMediaImages extends Command
{
    protected $signature = 'editorial:fetch-images';
    protected $description = 'Unduh dan regenerasi file fisik gambar media (original, 480.webp, 1280.webp) dari kapuasraya.com';

    public function handle(): int
    {
        $sources = DB::table('source_imports')->where('type', 'image')->pluck('source_id', 'local_id');
        $mediaList = DB::table('media')->whereIn('id', $sources->keys())->get();

        $this->info("Menemukan {$mediaList->count()} data gambar di database.");

        $success = 0;
        $failed = 0;

        foreach ($mediaList as $item) {
            $sourceUrl = $sources[$item->id] ?? null;
            if (!$sourceUrl) {
                continue;
            }

            $variants = json_decode($item->variants, true) ?? [];
            $path480 = $variants['480']['path'] ?? null;
            $path1280 = $variants['1280']['path'] ?? null;
            $originalPath = $item->path;

            // Jika file fisik sudah ada lengkap, lewati
            if (
                $originalPath && Storage::disk('local')->exists($originalPath) &&
                $path480 && Storage::disk('local')->exists($path480) &&
                $path1280 && Storage::disk('local')->exists($path1280)
            ) {
                $success++;
                continue;
            }

            $this->line("Mengunduh [ID {$item->id}]: {$sourceUrl}...");

            try {
                $response = Http::withoutVerifying()
                    ->timeout(30)
                    ->withHeaders([
                        'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                        'Referer' => 'https://kapuasraya.com/'
                    ])
                    ->get($sourceUrl);

                if (!$response->successful()) {
                    $this->warn("Gagal mengunduh ({$response->status()}): {$sourceUrl}");
                    $failed++;
                    continue;
                }

                $bytes = $response->body();
                $img = @imagecreatefromstring($bytes);
                if (!$img) {
                    $this->warn("Gambar tidak dapat didekode oleh GD: {$sourceUrl}");
                    $failed++;
                    continue;
                }

                $origW = imagesx($img);
                $origH = imagesy($img);

                // Buat varian 480 dan 1280
                foreach ([480, 1280] as $targetWidth) {
                    $w = min($targetWidth, $origW);
                    $h = (int) round($origH * $w / $origW);

                    $scaled = imagecreatetruecolor($w, $h);
                    imagefill($scaled, 0, 0, imagecolorallocate($scaled, 255, 255, 255));
                    imagecopyresampled($scaled, $img, 0, 0, 0, 0, $w, $h, $origW, $origH);

                    ob_start();
                    imagewebp($scaled, null, 82);
                    $webpData = ob_get_clean();
                    imagedestroy($scaled);

                    $varPath = $variants[(string)$targetWidth]['path'] ?? dirname($originalPath) . "/{$targetWidth}.webp";
                    Storage::disk('local')->put($varPath, $webpData);
                    $variants[(string)$targetWidth] = ['path' => $varPath, 'width' => $w, 'height' => $h];
                }

                // Simpan original
                Storage::disk('local')->put($originalPath, $bytes);
                imagedestroy($img);

                // Update metadata media
                DB::table('media')->where('id', $item->id)->update([
                    'variants' => json_encode($variants),
                    'size' => strlen($bytes),
                    'width' => $origW,
                    'height' => $origH,
                    'updated_at' => now(),
                ]);

                $success++;
                $this->info("Berhasil disimpan [ID {$item->id}]: {$originalPath}");
            } catch (\Throwable $e) {
                $this->error("Error memproses [ID {$item->id}]: " . $e->getMessage());
                $failed++;
            }
        }

        $this->info("Selesai! Berhasil: {$success}, Gagal: {$failed}");
        return self::SUCCESS;
    }
}
