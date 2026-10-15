<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Menu sidebar aplikasi. Menu "System Management" milik pikdi/tsu-foundation di-seed oleh
 * TsuFoundationSeeder; menu Data Karyawan oleh pikdi/tsu-karyawan.
 */
class MenuSeeder extends Seeder
{
    public function run(): void
    {
        $menuModel = 'Pikdi\TsuFoundation\Models\MenuSidebar';
        if (! class_exists($menuModel)) {
            return; // menu dinamis adalah fitur tsu-foundation
        }

        // Bersihkan menu dashboard usang jika ada
        $menuModel::query()->where('route', 'dashboard')->delete();

        // Dashboard (Menu Utama)
        $menuModel::query()->updateOrCreate(
            ['route' => 'admin.dashboard'],
            [
                'name'            => 'Dashboard',
                'icon'            => 'fas fa-tachometer-alt',
                'order'           => -99,
                'permission_name' => '',
                'parent_id'       => null,
                'isactive'        => 1,
            ]
        );
    }
}
