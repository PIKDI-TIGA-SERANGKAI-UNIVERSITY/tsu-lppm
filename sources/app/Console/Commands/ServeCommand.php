<?php

namespace App\Console\Commands;

use Illuminate\Foundation\Console\ServeCommand as LaravelServeCommand;

/**
 * `php artisan serve` untuk susunan tsu-project-template.
 *
 * Template menaruh index.php di akar repo (satu tingkat di atas folder `sources`) dan aset di `public/`
 * yang sejajar dengan `sources`, sedangkan perintah bawaan Laravel menjalankan server bawaan PHP dari
 * `sources/public` (folder yang tidak ada). Kelas ini hanya memindahkan folder kerja server ke akar repo.
 * Perutean dan aturan keamanan (setara .htaccess akar repo) ada di `sources/server.php`.
 *
 * Hanya untuk pengembangan. Seluruh opsi bawaan (--host, --port, --tries, --env) tetap berlaku.
 */
class ServeCommand extends LaravelServeCommand
{
    public function handle()
    {
        // ServeCommand::startProcess() memakai public_path() sebagai folder kerja proses server.
        // Perubahan ini hanya hidup selama perintah berjalan.
        $this->laravel->usePublicPath(static::documentRoot());

        return parent::handle();
    }

    /**
     * Akar repo: tempat index.php dan folder public/ berada.
     */
    public static function documentRoot(): string
    {
        return dirname(base_path());
    }
}
