<?php

namespace Tests\Feature;

use Database\Seeders\DatabaseSeeder;
use Database\Seeders\DevAdminSeeder;
use Tests\TestCase;

class DevAdminSeederTest extends TestCase
{
    protected array $requiresPackages = ['foundation'];

    protected function setUp(): void
    {
        parent::setUp();

        // Hanya migrasi yang relevan (data_mahasiswas memakai collation khusus MySQL, tidak jalan di SQLite)
        $this->artisan('migrate', [
            '--realpath' => true,
            '--path' => [
                base_path('database/migrations/2014_10_12_000000_create_users_table.php'),
                base_path('database/migrations/2025_12_19_100641_create_permission_tables.php'),
                base_path('vendor/pikdi/tsu-foundation/database/migrations'),
                base_path('vendor/pikdi/tsu-karyawan/database/migrations'),
            ],
        ])->assertSuccessful();
    }

    public function test_database_seeder_does_not_create_dev_account_automatically(): void
    {
        $this->artisan('db:seed', ['--class' => DatabaseSeeder::class])
            ->expectsOutputToContain('php artisan db:seed --class=DevAdminSeeder')
            ->assertSuccessful();

        $this->assertDatabaseMissing(config('app.table.users'), ['email' => config('tsu-foundation.dev_admin.email')]);
    }

    public function test_dev_admin_seeder_runs_when_called_manually_in_local(): void
    {
        $this->app['env'] = 'local';

        $this->seed(DevAdminSeeder::class);

        $this->assertDatabaseHas(config('app.table.users'), ['email' => config('tsu-foundation.dev_admin.email')]);
    }

    /**
     * Akun bersama "devadmin" hanya untuk laptop pengembang: di server bersama (staging, production, dll.)
     * setiap aksi harus tercatat atas nama akun SSO pribadi.
     */
    public function test_dev_admin_seeder_refuses_to_run_outside_local(): void
    {
        foreach (['production', 'staging', 'testing'] as $env) {
            $this->app['env'] = $env;

            $this->artisan('db:seed', ['--class' => DevAdminSeeder::class, '--force' => true])
                ->expectsOutputToContain('DevAdminSeeder dilewati')
                ->assertSuccessful();

            $this->assertDatabaseMissing(config('app.table.users'), ['email' => config('tsu-foundation.dev_admin.email')]);
        }
    }
}
