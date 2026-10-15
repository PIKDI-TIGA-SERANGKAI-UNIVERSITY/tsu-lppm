<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\TsuActivityLogService;
use Modules\System\Models\ActivityLog;

class ActivityLogAuthTest extends TestCase
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

        if (!\Illuminate\Support\Facades\Schema::hasTable('system_activity_logs')) {
            $activityMigration = require base_path('vendor/pikdi/tsu-foundation/database/migrations/2026_09_25_100000_create_system_activity_logs_table.php');
            $activityMigration->up();
        }
    }

    public function test_auth_login_activity_log_recorded(): void
    {
        $log = TsuActivityLogService::record(
            'Auth',
            'LOGIN',
            'Test SSO Login Event',
            ['auth_method' => 'SSO', 'identity' => 'TEST_IDENTITY']
        );

        $this->assertNotNull($log);
        $this->assertEquals('Auth', $log->module);
        $this->assertEquals('LOGIN', $log->action);
        $this->assertEquals('Test SSO Login Event', $log->description);
        $this->assertEquals('SSO', $log->properties['auth_method']);
    }

    public function test_auth_login_with_uuid_user_id_recorded(): void
    {
        $uuid = 'a2457c31-d8d7-4f57-89ea-54a89da07dd5';
        $log = TsuActivityLogService::record(
            'Auth',
            'LOGIN',
            'Test SSO Login with UUID',
            ['auth_method' => 'SSO'],
            $uuid
        );

        $this->assertNotNull($log);
        $this->assertEquals($uuid, $log->user_id);
    }
}
