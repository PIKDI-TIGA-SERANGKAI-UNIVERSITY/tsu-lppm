<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class KaryawanIntegrationTest extends TestCase
{
    protected array $requiresPackages = ['foundation', 'karyawan'];

    /**
     * Route Data Karyawan hanya terdaftar bila fitur aktif saat aplikasi boot.
     */
    protected const KARYAWAN_ENV = ['FEATURE_DATA_KARYAWAN' => 'true', 'HRIS_SYNC_SECRET' => 'secret', 'HRIS_URL' => 'https://hris.tsu.ac.id'];

    public function createApplication()
    {
        // Buang cache nilai .env milik test sebelumnya agar variabel di bawah (bukan isi .env) yang dipakai
        \Illuminate\Support\Env::enablePutenv();

        foreach (self::KARYAWAN_ENV as $key => $value) {
            putenv("{$key}={$value}");
            $_ENV[$key] = $_SERVER[$key] = $value;
        }

        return parent::createApplication();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        foreach (array_keys(self::KARYAWAN_ENV) as $key) {
            putenv($key);
            unset($_ENV[$key], $_SERVER[$key]);
        }
    }

    public function test_package_classes_resolve_without_legacy_app_aliases(): void
    {
        $this->assertTrue(class_exists(\Pikdi\TsuKaryawan\Services\KaryawanSyncService::class));
        $this->assertTrue(class_exists(\Pikdi\TsuKaryawan\Models\DataDosenTendik::class));
        $this->assertTrue(enum_exists(\Pikdi\TsuKaryawan\Enums\StatusKaryawanEnum::class));
        $this->assertTrue(class_exists(\Pikdi\TsuKaryawan\Notifications\HrisSyncCompletedNotification::class));

        $this->assertFalse(class_exists('App\Services\KaryawanSyncService'));
        $this->assertFalse(class_exists('App\Models\DataDosenTendik'));
        $this->assertFalse(class_exists('App\Notifications\HrisSyncCompletedNotification'));
    }

    public function test_guest_is_redirected_from_data_karyawan_route(): void
    {
        $response = $this->get(route('admin.data-karyawan.index'));
        $this->assertTrue(
            in_array($response->getStatusCode(), [302, 403]),
            "Expected 302 or 403 for guest, got {$response->getStatusCode()}"
        );
    }

    public function test_artisan_sync_command_is_registered(): void
    {
        $commands = Artisan::all();
        $this->assertArrayHasKey('tsu:sync-karyawan', $commands);
    }

    public function test_foundation_helper_follows_dual_condition_gate(): void
    {
        config(['tsu-karyawan.enabled' => false, 'tsu-karyawan.hris.sync_secret' => 'secret']);
        $this->assertFalse(tsu_karyawan_enabled());

        config(['tsu-karyawan.enabled' => true, 'tsu-karyawan.hris.sync_secret' => null]);
        $this->assertFalse(tsu_karyawan_enabled());

        config(['tsu-karyawan.enabled' => true, 'tsu-karyawan.hris.sync_secret' => 'secret']);
        $this->assertTrue(tsu_karyawan_enabled());
    }

    public function test_foundation_helper_detects_migrated_karyawan_table(): void
    {
        $this->assertFalse(tsu_karyawan_available());

        $this->migrateKaryawanTable();

        $this->assertTrue(tsu_karyawan_available());
    }

    public function test_realtime_notification_base_moved_to_core(): void
    {
        $this->assertSame(
            \Pikdi\TsuCore\Notifications\TsuRealtimeNotification::class,
            (new \ReflectionClass('App\Notifications\TsuRealtimeNotification'))->getName()
        );
    }

    public function test_uses_tsu_core_layout_and_detects_foundation(): void
    {
        $this->assertTrue(\Pikdi\TsuKaryawan\Support\Integration::foundationInstalled());
        $this->assertSame('tsu-core::template.admin.header', \Pikdi\TsuKaryawan\Support\Integration::layout());
        $this->assertStringContainsString('#karyawan-hris-sync', \Pikdi\TsuKaryawan\Support\Integration::guideUrl());
    }

    public function test_dashboard_reads_hris_status_from_package_config(): void
    {
        $this->migrateKaryawanTable();
        $this->enableKaryawan();
        config(['app.features.demo_dashboard' => true]);

        $this->actingAs($this->makeUser('pikdi'))
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertViewHas('hrisConfigured', true)
            ->assertViewHas('isKaryawanEnabled', true);
    }

    public function test_user_without_permission_cannot_view_or_sync_karyawan(): void
    {
        $this->migrateKaryawanTable();
        $this->seed(\Database\Seeders\PermissionSeeder::class);
        $this->seed(\Pikdi\TsuKaryawan\Database\Seeders\TsuKaryawanSeeder::class);
        $this->enableKaryawan();
        Http::fake();

        $mahasiswa = $this->makeUser('mahasiswa');

        $this->actingAs($mahasiswa)->get(route('admin.data-karyawan.index'))->assertForbidden();
        $this->actingAs($mahasiswa)->getJson(route('admin.data-karyawan.json'))->assertForbidden();
        $this->actingAs($mahasiswa)->post(route('admin.data-karyawan.sync'))->assertForbidden();

        Http::assertNothingSent();
    }

    public function test_admin_with_seeded_permission_sees_foundation_ui(): void
    {
        $this->migrateKaryawanTable();
        $this->seed(\Database\Seeders\PermissionSeeder::class);
        $this->seed(\Pikdi\TsuKaryawan\Database\Seeders\TsuKaryawanSeeder::class);
        $this->enableKaryawan();

        $this->actingAs($this->makeUser('admin ' . config('tsu-core.module.name')))
            ->get(route('admin.data-karyawan.index'))
            ->assertOk()
            ->assertSee('tsu-page-header', false)
            ->assertSee('tsu-stat-card', false)
            ->assertSee(route('panduan') . '#karyawan-hris-sync', false)
            ->assertSee('Sync dari HRIS');
    }

    public function test_user_profile_relation_resolves_dosen_tendik_from_package(): void
    {
        $this->migrateKaryawanTable();
        $dosen = $this->makeUser('dosen');
        \Pikdi\TsuKaryawan\Models\DataDosenTendik::query()->create(['nik' => '777', 'nama_lengkap' => 'Dr. Dosen', 'user_id' => $dosen->id]);

        $this->assertSame('Dr. Dosen', $dosen->fresh()->dosenTendik->nama_lengkap);
        $this->assertSame('Dr. Dosen', $dosen->fresh()->profil->nama_lengkap);

        // Halaman edit user (foundation) memuat $user->dosenTendik untuk user non-mahasiswa
        $this->actingAs($this->makeUser('pikdi'))
            ->get(route('users.user.edit', $dosen->id))
            ->assertOk();
    }

    public function test_database_seeder_seeds_karyawan_menu_and_permissions_idempotently(): void
    {
        $this->migrateKaryawanTable();

        $this->seed(\Database\Seeders\DatabaseSeeder::class);
        $menuCount = \Pikdi\TsuFoundation\Models\MenuSidebar::query()->count();
        $this->seed(\Database\Seeders\DatabaseSeeder::class);

        $this->assertSame($menuCount, \Pikdi\TsuFoundation\Models\MenuSidebar::query()->count());
        $this->assertDatabaseHas(tsu_table_name('menu_sidebars'), [
            'route' => 'admin.data-karyawan.index',
            'permission_name' => 'admin:data-karyawan:view',
        ]);
        $this->assertDatabaseHas(tsu_table_name('menu_sidebars'), ['route' => 'system.menu.index']);

        $adminRole = Role::findByName('admin ' . config('tsu-core.module.name'), 'web');
        $this->assertTrue($adminRole->hasPermissionTo('admin:data-karyawan:view'));
        $this->assertTrue($adminRole->hasPermissionTo('admin:data-karyawan:create'));
    }

    private function enableKaryawan(): void
    {
        config([
            'tsu-karyawan.enabled' => true,
            'tsu-karyawan.hris.url' => 'https://hris.tsu.ac.id',
            'tsu-karyawan.hris.sync_secret' => 'secret',
        ]);
    }

    private function makeUser(string $role): \App\Models\User
    {
        $user = new \App\Models\User([
            'id' => (string) Str::uuid(),
            'name' => 'User ' . $role,
            'email' => Str::slug($role) . '@tsu.ac.id',
            'username' => Str::slug($role),
        ]);
        $user->save();
        $user->assignRole(Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']));

        return $user;
    }

    private function migrateKaryawanTable(): void
    {
        // Hanya migrasi yang relevan (data_mahasiswas memakai collation khusus MySQL, tidak jalan di SQLite)
        $this->artisan('migrate', [
            '--realpath' => true,
            '--path' => [
                base_path('database/migrations/2014_10_12_000000_create_users_table.php'),
                base_path('database/migrations/2025_12_19_100641_create_permission_tables.php'),
                base_path('database/migrations/2026_07_16_155057_create_notifications_table.php'),
                base_path('vendor/pikdi/tsu-foundation/database/migrations'),
                base_path('vendor/pikdi/tsu-karyawan/database/migrations'),
            ],
        ])->assertSuccessful();
    }
}
