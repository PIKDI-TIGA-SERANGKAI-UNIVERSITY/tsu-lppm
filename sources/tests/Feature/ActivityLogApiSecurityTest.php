<?php

namespace Tests\Feature;

use Tests\TestCase;

class ActivityLogApiSecurityTest extends TestCase
{
    protected array $requiresPackages = ['foundation'];

    private const ENDPOINTS = [
        ['GET', '/v1/system/activity-logs'],
        ['POST', '/v1/system/activity-logs/ack'],
        ['POST', '/v1/system/activity-logs/purge'],
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('migrate', [
            '--realpath' => true,
            '--path' => [
                base_path('database/migrations/2014_10_12_000000_create_users_table.php'),
                base_path('vendor/pikdi/tsu-foundation/database/migrations'),
            ],
        ])->assertSuccessful();
    }

    private function callApi(string $method, string $uri, ?string $key)
    {
        $headers = $key === null ? [] : ['X-TSU-HOMEBASE-KEY' => $key];

        return $this->json($method, $uri, ['ids' => [1], 'days' => 30], $headers);
    }

    public function test_api_rejects_everything_when_secret_not_configured(): void
    {
        config(['tsu-foundation.homebase.secret_key' => null, 'services.homebase.secret_key' => null]);

        foreach (self::ENDPOINTS as [$method, $uri]) {
            // Termasuk kunci default lama yang dulu tertanam di kode
            $this->callApi($method, $uri, 'tsu-homebase-default-secret')
                ->assertStatus(503)
                ->assertJsonPath('code', '[TSU_AUTH_NOT_CONFIGURED]');
        }
    }

    public function test_legacy_default_secret_is_treated_as_not_configured(): void
    {
        config(['tsu-foundation.homebase.secret_key' => 'tsu-homebase-default-secret']);

        $this->callApi('GET', '/v1/system/activity-logs', 'tsu-homebase-default-secret')->assertStatus(503);
    }

    public function test_api_requires_matching_key_when_configured(): void
    {
        config(['tsu-foundation.homebase.secret_key' => 'kunci-rahasia-uji']);

        foreach (self::ENDPOINTS as [$method, $uri]) {
            $this->callApi($method, $uri, null)->assertStatus(401);
            $this->callApi($method, $uri, 'kunci-salah')->assertStatus(401);
            $this->callApi($method, $uri, 'tsu-homebase-default-secret')->assertStatus(401);
            $this->callApi($method, $uri, 'kunci-rahasia-uji')->assertOk();
        }
    }

    public function test_bearer_token_is_accepted(): void
    {
        config(['tsu-foundation.homebase.secret_key' => 'kunci-rahasia-uji']);

        $this->withToken('kunci-rahasia-uji')->getJson('/v1/system/activity-logs')->assertOk();
    }
}
