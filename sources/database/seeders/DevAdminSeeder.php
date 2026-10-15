<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/*
 * Akun developer super admin lokal untuk uji coba offline tanpa SSO Homebase.
 *
 * TIDAK dipanggil otomatis oleh DatabaseSeeder. Jalankan manual:
 *   php artisan db:seed --class=DevAdminSeeder
 *
 * Logika & konfigurasi (DEV_ADMIN_* di .env) disediakan oleh pikdi/tsu-foundation,
 * dan otomatis menolak berjalan saat APP_ENV=production. Tanpa tsu-foundation (tanpa sistem login
 * bawaan) seeder ini hanya menampilkan pemberitahuan.
 */
if (class_exists(\Pikdi\TsuFoundation\Database\Seeders\DevAdminSeeder::class)) {
    class DevAdminSeeder extends \Pikdi\TsuFoundation\Database\Seeders\DevAdminSeeder
    {
    }
} else {
    class DevAdminSeeder extends Seeder
    {
        public function run(): void
        {
            $this->command?->warn('DevAdminSeeder membutuhkan pikdi/tsu-foundation (login TSU). Lihat halaman /login untuk panduan.');
        }
    }
}
