<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\KapuasRayaImporter;
use Illuminate\Console\Command;

class ImportKapuasRaya extends Command
{
    protected $signature = 'editorial:import-kapuasraya {--user= : ID pengguna penanggung jawab impor}';

    protected $description = 'Impor arsip Kapuas Raya sebagai draft lokal tanpa publikasi otomatis';

    public function handle(KapuasRayaImporter $importer): int
    {
        if (! app()->environment(['local', 'testing'])) {
            $this->error('Impor ini dibatasi ke local/testing.');

            return self::FAILURE;
        }
        $user = User::findOrFail($this->option('user'));
        if (! $user->active || ! in_array($user->role, ['developer', 'admin', 'editor'])) {
            $this->error('Pilih admin/editor aktif.');

            return self::FAILURE;
        }
        $root = storage_path('app/private/imports/kapuasraya');
        $result = $importer->run($root, $user);
        file_put_contents($root.'/import-report.json', json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        $this->line(json_encode($result['created']));
        $this->info($result['status']);
        $this->line('Peringatan: '.count($result['warnings']));

        return self::SUCCESS;
    }
}
