<?php

namespace App\Console\Commands;

use App\Models\Setting;
use App\Support\FaviconGenerator;
use Illuminate\Console\Command;

class GenerateFavicon extends Command
{
    protected $signature = 'favicon:generate {path? : Optional logo path relative to the public disk}';

    protected $description = 'Generate favicon.png and favicon.ico from the configured logo';

    public function handle(): int
    {
        $logoPath = $this->argument('path') ?: Setting::getValue('logo');

        if (!$logoPath) {
            $this->error('Logo belum diatur di pengaturan.');
            return self::FAILURE;
        }

        if (!FaviconGenerator::generateFromLogo($logoPath)) {
            $this->error('Gagal membuat favicon dari logo. Pastikan format logo JPG/PNG valid.');
            return self::FAILURE;
        }

        $this->info('Favicon berhasil dibuat dari logo: ' . $logoPath);
        return self::SUCCESS;
    }
}
