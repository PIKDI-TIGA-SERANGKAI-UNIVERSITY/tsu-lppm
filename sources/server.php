<?php

/**
 * Router server bawaan PHP untuk `php artisan serve` pada susunan tsu-project-template.
 *
 * Server dijalankan dari AKAR REPO (lihat App\Console\Commands\ServeCommand), karena index.php berada di sana
 * dan aset berada di public/ dengan alamat berawalan /public/. Server bawaan PHP tidak membaca .htaccess,
 * jadi aturan keamanan .htaccess akar repo ditirukan di sini:
 *
 *   1. Dotfile/dotfolder (.env, .git, ...) kecuali .well-known  -> 403
 *   2. Folder (selain /)                                       -> 403 (tanpa daftar isi folder)
 *   3. Skrip PHP di dalam public/                              -> 403 (public/ hanya untuk aset)
 *   4. Berkas di dalam public/                                 -> dilayani apa adanya
 *   5. Berkas lain (sources/, README.md, composer.json, ...)   -> 403, kecuali /index.php
 *   6. Selain itu                                              -> diteruskan ke index.php (Laravel)
 *
 * Bila aturan keamanan di .htaccess akar repo diubah, sesuaikan juga berkas ini (dijaga ArtisanServeTest).
 * Hanya untuk pengembangan, bukan untuk server staging/production.
 */

$root = dirname(__DIR__);
$uri = rawurldecode((string) parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH));

$deny = static function (): void {
    http_response_code(403);
    header('Content-Type: text/plain; charset=UTF-8');
    echo '403 Forbidden';
};

// 1. Dotfile & dotfolder
if (preg_match('~(^|/)\.(?!well-known/)~', $uri)) {
    $deny();

    return true;
}

if ($uri !== '/' && $uri !== '') {
    $path = realpath($root . $uri);

    // Hanya jalur di dalam akar repo yang diperiksa (mencegah ../ keluar folder)
    if ($path !== false && str_starts_with(str_replace('\\', '/', $path), str_replace('\\', '/', $root))) {
        // 2. Folder
        if (is_dir($path)) {
            $deny();

            return true;
        }

        // 3. Skrip PHP di public/
        if (preg_match('~^/public/.*\.(php\d?|phtml|phar)$~i', $uri)) {
            $deny();

            return true;
        }

        // 4. Aset statis di public/
        if (str_starts_with($uri, '/public/')) {
            return false;
        }

        // 5. Berkas lain di luar public/
        if ($uri !== '/index.php') {
            $deny();

            return true;
        }
    }
}

// 6. Laravel
require $root . '/index.php';
