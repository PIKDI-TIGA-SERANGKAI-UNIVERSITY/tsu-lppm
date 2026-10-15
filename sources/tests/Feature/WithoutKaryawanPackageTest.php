<?php

namespace Tests\Feature;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Foundation\PackageManifest;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tests\TestCase;

/**
 * Memastikan template & tsu-foundation tetap berjalan normal TANPA package pikdi/tsu-karyawan.
 *
 * Package disimulasikan tidak terpasang dengan memutus autoload Pikdi\TsuKaryawan\ dan
 * mengeluarkan provider-nya dari package discovery. Wajib berjalan di proses terpisah agar
 * modifikasi autoloader tidak memengaruhi test lain.
 */
#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
class WithoutKaryawanPackageTest extends TestCase
{
    protected array $requiresPackages = ['foundation'];

    public function createApplication()
    {
        $basePath = dirname(__DIR__, 2);

        // 1. Putus autoload Pikdi\TsuKaryawan\ (PSR-4 + classmap)
        $loader = require $basePath . '/vendor/autoload.php';
        $loader->setPsr4('Pikdi\\TsuKaryawan\\', []);
        $loader->setPsr4('Pikdi\\TsuKaryawan\\Database\\Seeders\\', []);
        $classMap = new \ReflectionProperty($loader, 'classMap');
        $classMap->setValue($loader, array_filter(
            $classMap->getValue($loader),
            fn ($class) => !str_starts_with($class, 'Pikdi\\TsuKaryawan\\'),
            ARRAY_FILTER_USE_KEY
        ));

        $app = require $basePath . '/bootstrap/app.php';

        // 2. Keluarkan provider karyawan dari package discovery
        $app->instance(PackageManifest::class, new class(new Filesystem(), $app->basePath(), $app->getCachedPackagesPath()) extends PackageManifest {
            public function providers()
            {
                return array_values(array_filter(parent::providers(), fn ($provider) => !str_contains($provider, 'TsuKaryawan')));
            }
        });

        $app->make(Kernel::class)->bootstrap();

        return $app;
    }

    public function test_package_is_really_absent(): void
    {
        $this->assertFalse(class_exists('Pikdi\\TsuKaryawan\\Models\\DataDosenTendik'));
        $this->assertFalse(Route::has('admin.data-karyawan.index'));
        // Helper tsu_karyawan_* milik pikdi/tsu-karyawan: tidak ada saat paket tidak terpasang
        $this->assertFalse(function_exists('tsu_karyawan_available') && tsu_karyawan_available());
        $this->assertFalse(function_exists('tsu_karyawan_enabled') && tsu_karyawan_enabled());
    }

    public function test_dashboard_sidebar_and_quick_search_render(): void
    {
        $this->migrate();
        \Pikdi\TsuFoundation\Models\MenuSidebar::query()->create([
            'name' => 'Data Karyawan', 'route' => 'admin.data-karyawan.index', 'icon' => 'fas fa-users',
            'order' => 1, 'isactive' => 1,
        ]);
        config(['app.features.demo_dashboard' => true]);

        $this->actingAs($this->makeUser('pikdi'))
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertViewHas('isKaryawanEnabled', false)
            ->assertViewHas('hrisConfigured', false)
            ->assertDontSee('Direktori Pegawai');
    }

    public function test_sso_sync_for_dosen_does_not_crash(): void
    {
        $this->migrate();

        $result = app(\Pikdi\TsuFoundation\Services\UserSyncService::class)->handle([
            'id' => 9999,
            'name' => 'Dosen Uji',
            'email' => 'dosen.uji@tsu.ac.id',
            'username' => 'dosenuji',
            'nik' => '1234567',
            'roles' => [['name' => 'dosen', 'is_identity' => true]],
        ], null);

        // Role dosen harus ter-assign agar jalur syncDosenTendik() benar-benar teruji
        $this->assertTrue($result['user']->hasRole('dosen'));
    }

    public function test_user_profile_for_dosen_is_null_without_package(): void
    {
        $this->migrate();
        $dosen = $this->makeUser('dosen');

        $this->assertNull($dosen->dosenTendik);
        $this->assertNull($dosen->profil);
    }

    public function test_database_seeder_runs_without_karyawan(): void
    {
        $this->migrate();
        $this->seed(\Database\Seeders\DatabaseSeeder::class);

        $this->assertDatabaseHas(tsu_table_name('menu_sidebars'), ['route' => 'system.menu.index']);
        $this->assertDatabaseMissing(tsu_table_name('menu_sidebars'), ['route' => 'admin.data-karyawan.index']);
        $this->assertDatabaseMissing('permissions', ['name' => 'admin:data-karyawan:view']);
    }

    private function migrate(): void
    {
        $this->artisan('migrate', [
            '--realpath' => true,
            '--path' => [
                base_path('database/migrations/2014_10_12_000000_create_users_table.php'),
                base_path('database/migrations/2025_12_19_100641_create_permission_tables.php'),
                base_path('database/migrations/2026_07_16_155057_create_notifications_table.php'),
                base_path('vendor/pikdi/tsu-foundation/database/migrations'),
            ],
        ])->assertSuccessful();
    }

    private function makeUser(string $role): \App\Models\User
    {
        $user = new \App\Models\User([
            'id' => (string) Str::uuid(),
            'name' => 'User ' . $role,
            'email' => $role . '@tsu.ac.id',
            'username' => $role,
            'isactive' => 1,
        ]);
        $user->save();
        $user->assignRole(\Spatie\Permission\Models\Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']));

        return $user;
    }
}
