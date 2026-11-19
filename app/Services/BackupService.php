<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class BackupService
{
    public const TABLES = ['users', 'categories', 'tags', 'media', 'articles', 'article_tag', 'article_revisions', 'editorial_notes', 'media_usages', 'featured_placements', 'breaking_news', 'ad_slots', 'ad_banners', 'pages', 'site_settings', 'article_view_daily', 'audit_logs', 'source_imports'];

    public function create(string $path): void
    {
        $zip = new \ZipArchive;
        if ($zip->open($path, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            throw new \RuntimeException('Arsip tidak dapat dibuat.');
        }
        try {
            DB::transaction(function () use ($zip) {
                $data = [];
                foreach (self::TABLES as $table) {
                    $data[$table] = DB::table($table)->get()->map(fn ($row) => (array) $row)->all();
                }
                $zip->addFromString('database.json', json_encode(['format' => 1, 'created_at' => now()->toIso8601String(), 'tables' => $data], JSON_THROW_ON_ERROR));
                foreach (Storage::disk('local')->allFiles('editorial') as $file) {
                    $zip->addFromString('media/'.$file, Storage::disk('local')->get($file));
                }
            });
        } finally {
            $zip->close();
        }
    }

    public function restore(string $path): void
    {
        if (app()->environment('production')) {
            throw new \RuntimeException('Restore langsung produksi ditolak. Gunakan database uji baru lalu prosedur pemulihan terkontrol.');
        }
        foreach (['users', 'articles', 'media'] as $table) {
            if (DB::table($table)->exists()) {
                throw new \RuntimeException('Restore memerlukan database hasil migrasi yang masih kosong.');
            }
        }
        $zip = new \ZipArchive;
        if ($zip->open($path) !== true) {
            throw new \RuntimeException('Arsip tidak valid.');
        }
        try {
            $data = json_decode($zip->getFromName('database.json'), true, 512, JSON_THROW_ON_ERROR);
            if (($data['format'] ?? null) !== 1) {
                throw new \RuntimeException('Format tidak didukung.');
            }
            DB::transaction(function () use ($zip, $data) {
                Schema::disableForeignKeyConstraints();
                try {
                    foreach (array_reverse(self::TABLES) as $table) {
                        DB::table($table)->delete();
                    }
                    foreach (self::TABLES as $table) {
                        foreach (array_chunk($data['tables'][$table] ?? [], 100) as $rows) {
                            DB::table($table)->insert($rows);
                        }
                    }
                    for ($i = 0; $i < $zip->numFiles; $i++) {
                        $name = $zip->getNameIndex($i);
                        if (str_starts_with($name, 'media/editorial/') && ! str_contains($name, '..') && ! str_contains($name, '\\')) {
                            Storage::disk('local')->put(substr($name, 6), $zip->getFromIndex($i));
                        }
                    }
                    DB::table('users')->increment('session_version');
                } finally {
                    Schema::enableForeignKeyConstraints();
                }
            });
        } finally {
            $zip->close();
        }
    }
}
