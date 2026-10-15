<?php

namespace Tests\Feature;

use Tests\TestCase;

class FoundationIntegrationTest extends TestCase
{
    protected array $requiresPackages = ['foundation'];

    protected function setUp(): void
    {
        parent::setUp();

        if (!\Illuminate\Support\Facades\Schema::hasTable('users')) {
            if (!class_exists('CreateUsersTable')) {
                require_once base_path('database/migrations/2014_10_12_000000_create_users_table.php');
            }
            (new \CreateUsersTable)->up();
        }

        if (!\Illuminate\Support\Facades\Schema::hasTable('roles')) {
            $permMigration = require base_path('database/migrations/2025_12_19_100641_create_permission_tables.php');
            $permMigration->up();
        }
    }

    public function test_public_routes_accessible()
    {
        $this->get('/')->assertStatus(200);
        $this->get('/login')->assertStatus(200);
        $this->get('/panduan')->assertStatus(200);
    }

    public function test_guest_blocked_from_protected_system_routes()
    {
        $routes = [
            '/admin/dashboard',
            '/users/user',
            '/users/profile',
            '/system/activity-logs',
            '/system/error-logs',
        ];

        foreach ($routes as $route) {
            $response = $this->get($route);
            $this->assertTrue(
                in_array($response->getStatusCode(), [302, 403]),
                "Route {$route} unexpected status {$response->getStatusCode()}"
            );
        }
    }

    public function test_pikdi_role_bypasses_all_gates()
    {
        $role = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'pikdi', 'guard_name' => 'web'], ['is_identity' => true]);

        $user = new \App\Models\User([
            'id' => (string) \Illuminate\Support\Str::uuid(),
            'name' => 'Root PIKDI',
            'email' => 'pikdi@tsu.ac.id',
            'username' => 'pikdi',
            'status' => 'aktif',
        ]);
        $user->save();
        $user->assignRole($role);

        $this->actingAs($user);

        // PIKDI role must bypass any Gate ability via Gate::before
        $this->assertTrue(\Illuminate\Support\Facades\Gate::allows('system:role:delete'));
        $this->assertTrue(\Illuminate\Support\Facades\Gate::allows('non_existent_random_permission'));

        // Normal user without pikdi or super admin role cannot bypass
        $regularRole = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'operator', 'guard_name' => 'web']);
        $regularUser = new \App\Models\User([
            'id' => (string) \Illuminate\Support\Str::uuid(),
            'name' => 'Operator Test',
            'email' => 'operator@tsu.ac.id',
            'username' => 'operator',
            'status' => 'aktif',
        ]);
        $regularUser->save();
        $regularUser->assignRole($regularRole);

        $this->actingAs($regularUser);
        $this->assertFalse(\Illuminate\Support\Facades\Gate::allows('non_existent_random_permission'));
    }

    public function test_error_log_download_filtered_and_raw()
    {
        $role = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'pikdi', 'guard_name' => 'web'], ['is_identity' => true]);
        $user = \App\Models\User::firstOrCreate(
            ['email' => 'admin.test@tsu.ac.id'],
            [
                'id' => (string) \Illuminate\Support\Str::uuid(),
                'name' => 'Admin Tester',
                'username' => 'admintester',
                'status' => 'aktif',
            ]
        );
        $user->assignRole($role);
        $this->actingAs($user);

        // Pastikan ada file log untuk pengujian
        $logPath = storage_path('logs/test-download.log');
        file_put_contents($logPath, "[2026-10-02 10:00:00] local.ERROR: Sample Error Message [TSU_ERR_01]\n[2026-10-02 10:05:00] local.INFO: Sample Info Message\n");

        // 1. Download filtered
        $filteredResponse = $this->get('/system/error-logs/download?file=test-download.log&level=ERROR');
        $filteredResponse->assertStatus(200);
        $filteredResponse->assertHeader('content-type', 'text/plain; charset=UTF-8');
        $this->assertStringContainsString('FILTERED ERROR LOG EXPORT', $filteredResponse->getContent());
        $this->assertStringContainsString('Sample Error Message', $filteredResponse->getContent());
        $this->assertStringNotContainsString('Sample Info Message', $filteredResponse->getContent());

        // 2. Download raw
        $rawResponse = $this->get('/system/error-logs/download?file=test-download.log&raw=1');
        $rawResponse->assertStatus(200);

        // Cleanup
        if (file_exists($logPath)) {
            @unlink($logPath);
        }
    }

    public function test_activity_log_backup_management_endpoints()
    {
        $role = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'pikdi', 'guard_name' => 'web'], ['is_identity' => true]);
        $user = \App\Models\User::firstOrCreate(
            ['email' => 'admin.test@tsu.ac.id'],
            [
                'id' => (string) \Illuminate\Support\Str::uuid(),
                'name' => 'Admin Tester',
                'username' => 'admintester',
                'status' => 'aktif',
            ]
        );
        $user->assignRole($role);
        $this->actingAs($user);

        // Buat file zip dummy di backup directory
        $backupDir = storage_path('app/backups/activity-logs');
        if (!file_exists($backupDir)) {
            mkdir($backupDir, 0755, true);
        }
        $dummyFile = $backupDir . '/backup-activity-logs-test-feature.zip';
        file_put_contents($dummyFile, 'PK dummy zip content');

        // 1. Test List Backups
        $listResponse = $this->getJson(route('system.activity-logs.backups'));
        $listResponse->assertStatus(200)
            ->assertJsonPath('status', 'success');

        $this->assertNotEmpty($listResponse->json('data.files'));

        // 2. Test Delete Single Backup
        $deleteResponse = $this->deleteJson(route('system.activity-logs.delete-backup'), [
            'file' => 'backup-activity-logs-test-feature.zip',
        ]);
        $deleteResponse->assertStatus(200)
            ->assertJsonPath('status', 'success');

        $this->assertFileDoesNotExist($dummyFile);
    }
}
