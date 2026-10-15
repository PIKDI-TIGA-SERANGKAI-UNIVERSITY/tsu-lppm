<?php

namespace Tests\Feature;

use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Template berjalan dengan kombinasi paket apa pun:
 * tsu-core saja, + tsu-foundation (default), + tsu-karyawan.
 * Ekspektasi menyesuaikan paket yang terpasang.
 */
class TemplateStandaloneTest extends TestCase
{
    protected function makeUser(): \App\Models\User
    {
        $this->artisan('migrate', [
            '--realpath' => true,
            '--path' => [
                base_path('database/migrations/2014_10_12_000000_create_users_table.php'),
                base_path('database/migrations/2025_12_19_100641_create_permission_tables.php'),
            ],
        ])->assertSuccessful();

        if (static::packageInstalled('foundation')) {
            $this->artisan('migrate', ['--realpath' => true, '--path' => base_path('vendor/pikdi/tsu-foundation/database/migrations')])
                ->assertSuccessful();
        }

        $user = new \App\Models\User([
            'id'       => (string) Str::uuid(),
            'name'     => 'Pengembang Uji',
            'email'    => 'dev' . Str::random(5) . '@tsu.test',
            'username' => 'dev' . Str::random(5),
        ]);
        $user->save();

        return $user;
    }

    public function test_public_pages_are_served_by_the_template(): void
    {
        $this->get('/')->assertOk();
        $this->get('/panduan')->assertOk();
        $this->get('/panduan-pengembang')->assertRedirect('/panduan');

        $this->assertSame(\App\Http\Controllers\HomeController::class . '@index', app('router')->getRoutes()->getByName('indexing')->getActionName());
        $this->assertSame(\Pikdi\TsuCore\Http\Controllers\DocsController::class . '@index', app('router')->getRoutes()->getByName('panduan')->getActionName());
    }

    public function test_login_route_matches_installed_packages(): void
    {
        $response = $this->get('/login')->assertOk();

        if (static::packageInstalled('foundation')) {
            $response->assertDontSee('Login belum tersedia');
            $this->assertStringStartsWith('Pikdi\\TsuFoundation\\', app('router')->getRoutes()->getByName('login')->getActionName());
        } else {
            // D2a: tanpa tsu-foundation tidak ada login bawaan, hanya panduan memasangnya
            $response->assertSee('Login belum tersedia')->assertSee('composer require pikdi/tsu-foundation');
        }

        $this->get('/admin/dashboard')->assertRedirect(route('login'));
    }

    public function test_dashboard_renders_for_logged_in_user(): void
    {
        $html = $this->actingAs($this->makeUser())->get('/admin/dashboard')->assertOk()->getContent();

        $this->assertStringContainsString('Pengembang Uji', $html);

        if (static::packageInstalled('foundation')) {
            $this->assertStringContainsString('tsu-notif-dropdown', $html, 'Lonceng notifikasi dari tsu-foundation');
        } else {
            // Menu statis template & tanpa fitur foundation
            $this->assertStringContainsString(route('panduan'), $html);
            $this->assertStringNotContainsString('tsu-notif-dropdown', $html);
            $this->assertStringNotContainsString('/system/', $html);
        }
    }

    public function test_seeders_run_with_installed_packages(): void
    {
        $this->makeUser();

        $this->artisan('db:seed', ['--class' => \Database\Seeders\DatabaseSeeder::class])->assertSuccessful();
    }
}
