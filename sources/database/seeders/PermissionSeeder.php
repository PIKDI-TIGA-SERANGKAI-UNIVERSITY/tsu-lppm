<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

/**
 * Permission aplikasi.
 *
 * Permission & role fitur sistem (users, role, permission, menu, log) milik pikdi/tsu-foundation
 * di-seed oleh TsuFoundationSeeder (dipanggil di sini agar seeder ini tetap lengkap bila dijalankan sendiri).
 * Tambahkan permission modul Anda pada $appPermissions, mis. 'admin:inventaris:view'.
 */
class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $foundationSeeder = 'Pikdi\TsuFoundation\Database\Seeders\TsuFoundationSeeder';
        if (class_exists($foundationSeeder)) {
            $this->call($foundationSeeder);
        }

        $appPermissions = [
            // 'admin:inventaris:view',
        ];

        foreach ($appPermissions as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
