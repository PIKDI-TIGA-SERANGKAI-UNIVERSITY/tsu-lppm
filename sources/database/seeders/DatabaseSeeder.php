<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // 1. Seeder Sistem Baku (Wajib untuk semua environment)
        $this->call([
            PermissionSeeder::class,
            MenuSeeder::class,
        ]);

        // 1.1 Seeder Package Data Karyawan (permission + menu), hanya jika package pikdi/tsu-karyawan terpasang
        $karyawanSeeder = 'Pikdi\\TsuKaryawan\\Database\\Seeders\\TsuKaryawanSeeder';
        if (class_exists($karyawanSeeder)) {
            $this->call($karyawanSeeder);
        }

        // 2. Akun Pengembang Lokal TIDAK dibuat otomatis: wajib dijalankan manual atas kesadaran pengembang
        if (!app()->isProduction() && $this->command) {
            $this->command->info(' [INFO] Akun developer lokal tidak dibuat otomatis. Jika diperlukan, jalankan: php artisan db:seed --class=DevAdminSeeder');
        }
    }
}
