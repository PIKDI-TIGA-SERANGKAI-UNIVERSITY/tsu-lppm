<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Handler aplikasi memakai TsuExceptionHandlerTrait milik tsu-core.
 */
class ErrorPageTest extends TestCase
{
    public function test_handler_uses_core_trait(): void
    {
        $this->assertContains(
            \Pikdi\TsuCore\Traits\TsuExceptionHandlerTrait::class,
            class_uses(\App\Exceptions\Handler::class)
        );
    }

    public function test_not_found_renders_tsu_page_and_json(): void
    {
        $this->get('/halaman-yang-tidak-ada-' . Str::random(6))
            ->assertNotFound()
            ->assertSee('Halaman Tidak Ditemukan');

        $this->getJson('/halaman-yang-tidak-ada-' . Str::random(6))
            ->assertNotFound()
            ->assertJsonPath('message', 'Halaman tidak ditemukan.');
    }

    public function test_spatie_permission_denial_returns_friendly_403(): void
    {
        $this->requirePackages('foundation'); // route /users/user & seeder permission sistem
        $this->artisan('migrate', [
            '--realpath' => true,
            '--path' => [
                base_path('database/migrations/2014_10_12_000000_create_users_table.php'),
                base_path('database/migrations/2025_12_19_100641_create_permission_tables.php'),
                base_path('vendor/pikdi/tsu-foundation/database/migrations'),
            ],
        ])->assertSuccessful();
        $this->seed(\Database\Seeders\PermissionSeeder::class);

        $user = new \App\Models\User(['id' => (string) Str::uuid(), 'name' => 'Mahasiswa', 'email' => 'mhs@tsu.ac.id', 'username' => 'mhs']);
        $user->save();

        $this->actingAs($user)->getJson('/users/user')
            ->assertForbidden()
            ->assertJsonPath('message', 'Akses Dibatasi! Anda tidak memiliki izin: View');
    }

    public function test_production_crash_hides_internal_message(): void
    {
        Route::get('/__tsu-test-crash', fn () => throw new \RuntimeException('detail-internal-rahasia'))->middleware('web');
        config(['app.debug' => false]);

        $this->get('/__tsu-test-crash')
            ->assertStatus(500)
            ->assertSee('Terjadi Kesalahan Server')
            ->assertDontSee('detail-internal-rahasia');

        $this->getJson('/__tsu-test-crash')
            ->assertStatus(500)
            ->assertJsonPath('error_code', 'SERVER_ERROR');
    }
}
