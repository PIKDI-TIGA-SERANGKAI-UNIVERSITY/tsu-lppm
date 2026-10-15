<?php

namespace Tests\Feature;

use Pikdi\TsuFoundation\TsuFoundationServiceProvider;
use Tests\TestCase;

class FoundationConfigTest extends TestCase
{
    protected array $requiresPackages = ['foundation'];

    public function test_legacy_app_config_keys_are_gone(): void
    {
        foreach (['module', 'tsu_homebase', 'roles', 'oauth', 'pikdi', 'dev_admin', 'features', 'manual_book_url'] as $key) {
            $this->assertNull(config("app.{$key}"), "config('app.{$key}') seharusnya sudah dipindah ke tsu-foundation");
        }
        $this->assertArrayNotHasKey(0, config('app'), 'Elemen liar dari $tableName di config/app.php masih ada');
    }

    public function test_foundation_config_is_complete_without_published_copy(): void
    {
        $this->assertFileDoesNotExist(config_path('tsu-foundation.php'));
        $this->assertFileDoesNotExist(config_path('tsu-core.php'));

        // Login, Homebase server-to-server, secret PIKDI & dev admin: milik tsu-foundation
        foreach (['sso.client_id', 'homebase.secret_key', 'pikdi.email', 'emergency.command_secret', 'dev_admin.password', 'allowed_roles'] as $key) {
            $this->assertTrue(\Illuminate\Support\Arr::has(config('tsu-foundation'), $key), "tsu-foundation.{$key} tidak ada");
        }

        // Identitas modul & tampilan: milik tsu-core (tidak lagi diduplikasi di tsu-foundation)
        foreach (['module.name', 'module.table_name', 'homebase_url', 'home_route', 'features.manual_book', 'manual_book_url'] as $key) {
            $this->assertTrue(\Illuminate\Support\Arr::has(config('tsu-core'), $key), "tsu-core.{$key} tidak ada");
            $this->assertFalse(\Illuminate\Support\Arr::has(config('tsu-foundation'), $key), "tsu-foundation.{$key} masih terduplikasi");
        }

        $this->assertSame('pikdi@tsu.ac.id', config('tsu-foundation.pikdi.email'));
    }

    public function test_published_override_wins_and_new_package_keys_still_flow(): void
    {
        // Simulasi config hasil publish lama yang hanya berisi sebagian key
        config()->set('tsu-foundation', ['homebase' => ['client_id' => 'client-dari-aplikasi']]);
        config()->set('tsu-core', ['module' => ['name' => 'siakad']]);

        foreach ([
            [new TsuFoundationServiceProvider($this->app), 'vendor/pikdi/tsu-foundation/config/tsu-foundation.php', 'tsu-foundation'],
            [new \Pikdi\TsuCore\TsuCoreServiceProvider($this->app), 'vendor/pikdi/tsu-core/config/tsu-core.php', 'tsu-core'],
        ] as [$provider, $path, $key]) {
            (new \ReflectionMethod($provider, 'mergeConfigRecursiveFrom'))->invoke($provider, base_path($path), $key);
        }

        $this->assertSame('client-dari-aplikasi', config('tsu-foundation.homebase.client_id'));
        $this->assertSame('siakad', config('tsu-core.module.name'));
        // Key baru di blok yang sama tetap ikut dari package
        $this->assertTrue(\Illuminate\Support\Arr::has(config('tsu-foundation'), 'homebase.secret_key'));
        $this->assertNotNull(config('tsu-core.module.acronyms'));
        $this->assertSame('pikdi@tsu.ac.id', config('tsu-foundation.pikdi.email'));
    }

    public function test_merge_replaces_lists_entirely(): void
    {
        $merged = TsuFoundationServiceProvider::mergeConfigArrays(
            ['roles' => ['dosen', 'tendik', 'mahasiswa'], 'opt' => ['a' => 1, 'b' => 2]],
            ['roles' => ['dosen'], 'opt' => ['b' => 3]]
        );

        $this->assertSame(['dosen'], $merged['roles']);
        $this->assertSame(['a' => 1, 'b' => 3], $merged['opt']);
    }

    public function test_allowed_roles_and_table_name_helpers(): void
    {
        config(['tsu-foundation.allowed_roles' => ' dosen, ,tendik ']);
        $this->assertSame(['dosen', 'tendik'], tsu_allowed_roles());

        config(['tsu-foundation.allowed_roles' => '']);
        $this->assertSame([], tsu_allowed_roles());

        config(['tsu-core.module.table_name' => 'siakad']);
        $this->assertSame('siakad_menu_sidebars', tsu_table_name('menu_sidebars'));
        $this->assertSame('siakad_menu_sidebars', (new \Pikdi\TsuFoundation\Models\MenuSidebar())->getTable());

        config(['tsu-core.module.table_name' => '']);
        $this->assertSame('menu_sidebars', tsu_table_name('menu_sidebars'));
    }

    public function test_dashboard_sso_badge_reads_sso_credentials(): void
    {
        config(['tsu-foundation.sso.client_id' => 'sso-id', 'tsu-foundation.sso.client_secret' => 'sso-secret']);
        config(['tsu-foundation.homebase.client_id' => null, 'tsu-foundation.homebase.client_secret' => null]);

        $this->artisan('migrate', [
            '--realpath' => true,
            '--path' => [
                base_path('database/migrations/2014_10_12_000000_create_users_table.php'),
                base_path('database/migrations/2025_12_19_100641_create_permission_tables.php'),
                base_path('database/migrations/2026_07_16_155057_create_notifications_table.php'),
                base_path('vendor/pikdi/tsu-foundation/database/migrations'),
            ],
        ])->assertSuccessful();

        $user = new \App\Models\User(['id' => (string) \Illuminate\Support\Str::uuid(), 'name' => 'PIKDI', 'email' => 'p@tsu.ac.id', 'username' => 'p']);
        $user->save();
        $user->assignRole(\Spatie\Permission\Models\Role::firstOrCreate(['name' => 'pikdi', 'guard_name' => 'web']));
        config(['tsu-core.features.demo_dashboard' => true]);

        $this->actingAs($user)->get(route('admin.dashboard'))->assertOk()->assertViewHas('ssoConfigured', true);
    }
}
