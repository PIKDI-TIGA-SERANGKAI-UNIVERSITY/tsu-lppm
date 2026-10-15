<?php

namespace Tests\Feature;

use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

/**
 * Skenario 3: tsu-project-template + tsu-karyawan TANPA tsu-foundation.
 * Menu Data Karyawan tampil lewat slot sidebar tsu-core, berdampingan dengan menu statis template.
 */
class KaryawanWithoutFoundationTest extends TestCase
{
    protected array $requiresPackages = ['karyawan'];

    protected const KARYAWAN_ENV = ['FEATURE_DATA_KARYAWAN' => 'true', 'HRIS_SYNC_SECRET' => 'secret'];

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

    protected function setUp(): void
    {
        parent::setUp();

        if (static::packageInstalled('foundation')) {
            $this->markTestSkipped('Skenario ini khusus tanpa pikdi/tsu-foundation.');
        }
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        foreach (array_keys(self::KARYAWAN_ENV) as $key) {
            putenv($key);
            unset($_ENV[$key], $_SERVER[$key]);
        }
    }

    public function test_data_karyawan_page_and_menu_without_foundation(): void
    {
        $this->artisan('migrate', [
            '--realpath' => true,
            '--path' => [
                base_path('database/migrations/2014_10_12_000000_create_users_table.php'),
                base_path('database/migrations/2025_12_19_100641_create_permission_tables.php'),
                base_path('vendor/pikdi/tsu-karyawan/database/migrations'),
            ],
        ])->assertSuccessful();
        $this->seed(\Database\Seeders\DatabaseSeeder::class);

        $user = new \App\Models\User(['id' => (string) Str::uuid(), 'name' => 'Operator HR', 'email' => 'hr@tsu.test', 'username' => 'hr']);
        $user->save();
        $user->givePermissionTo(Permission::findByName('admin:data-karyawan:view', 'web'));

        $html = $this->actingAs($user)->get(route('admin.data-karyawan.index'))->assertOk()->getContent();

        $this->assertStringContainsString('Total Dosen &amp; Tendik', $html);
        $this->assertStringContainsString('app-sidebar', $html, 'Layout admin tsu-core (AdminLTE 4)');
        $this->assertStringContainsString('<p>Data Karyawan</p>', $html, 'Menu karyawan dari slot tsu-core');
        $this->assertStringContainsString(route('panduan'), $html, 'Menu statis template tetap ada');
        $this->assertStringNotContainsString('/system/', $html, 'Tanpa fitur foundation');
    }
}
