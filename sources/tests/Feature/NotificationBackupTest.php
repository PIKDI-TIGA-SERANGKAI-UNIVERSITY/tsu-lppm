<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Backup & Bersihkan notifikasi: file privat per user, hanya pemilik yang bisa mengunduh, wajib POST.
 */
class NotificationBackupTest extends TestCase
{
    protected array $requiresPackages = ['foundation'];

    protected function setUp(): void
    {
        parent::setUp();

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

    protected function tearDown(): void
    {
        // Test dilewati sebelum aplikasi dinyalakan (foundation tidak terpasang): tidak ada yang perlu dibersihkan
        if ($this->app) {
            Storage::disk('local')->deleteDirectory('backups/notifications');
        }

        parent::tearDown();
    }

    private function makeUserWithReadNotification(string $name): \App\Models\User
    {
        $user = new \App\Models\User([
            'id' => (string) Str::uuid(),
            'name' => $name,
            'email' => Str::slug($name) . '@tsu.ac.id',
            'username' => Str::slug($name),
        ]);
        $user->save();

        DB::table('notifications')->insert([
            'id' => (string) Str::uuid(),
            'type' => 'Tests\\Notif',
            'notifiable_type' => \App\Models\User::class,
            'notifiable_id' => $user->id,
            'data' => json_encode(['title' => 'Uji', 'message' => 'Pesan uji']),
            'read_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $user;
    }

    private function backupAs(\App\Models\User $user): string
    {
        $this->actingAs($user)->post(route('system.notifications.backupClear'))->assertRedirect();

        return (string) session('download_url');
    }

    public function test_backup_is_private_and_downloadable_by_owner(): void
    {
        $owner = $this->makeUserWithReadNotification('Operator / Uji');

        $downloadUrl = $this->backupAs($owner);
        $file = basename(parse_url($downloadUrl, PHP_URL_PATH));

        $this->assertStringContainsString('/system/notifications/backups/', $downloadUrl);
        $this->assertMatchesRegularExpression('/^\d{8}_\d{6}_[A-Za-z0-9]{32}\.xlsx$/', $file);
        $this->assertTrue(Storage::disk('local')->exists("backups/notifications/{$owner->id}/{$file}"));
        $this->assertEmpty(Storage::disk('public')->allFiles('exports'), 'Backup tidak boleh tersimpan di storage publik');
        $this->assertSame(0, $owner->readNotifications()->count());

        $response = $this->actingAs($owner)->get($downloadUrl);
        $response->assertOk();
        $this->assertStringContainsString('Backup_Notifikasi_operator_uji_', (string) $response->headers->get('content-disposition'));
    }

    public function test_other_user_and_guest_cannot_download(): void
    {
        $owner = $this->makeUserWithReadNotification('Pemilik');
        $downloadUrl = $this->backupAs($owner);

        $other = $this->makeUserWithReadNotification('Orang Lain');
        $this->actingAs($other)->get($downloadUrl)
            ->assertRedirect(route('system.notifications.index', ['filter' => 'read']))
            ->assertSessionHas('error');

        auth()->logout();
        $this->get($downloadUrl)->assertRedirect(route('login'));
    }

    public function test_backup_requires_post_and_rejects_path_traversal(): void
    {
        $user = $this->makeUserWithReadNotification('Uji Metode');

        $this->actingAs($user)->get('/system/notifications/backup-clear')->assertStatus(405);
        $this->assertSame(1, $user->readNotifications()->count(), 'GET tidak boleh menghapus notifikasi');

        $this->actingAs($user)->get('/system/notifications/backups/..%2F..%2F.env')->assertNotFound();
    }

    public function test_expired_backups_are_pruned_on_next_backup(): void
    {
        $user = $this->makeUserWithReadNotification('Uji Retensi');
        $disk = Storage::disk('local');
        $oldPath = "backups/notifications/{$user->id}/20200101_000000_lama.xlsx";
        $disk->put($oldPath, 'lama');
        touch($disk->path($oldPath), now()->subDays(8)->getTimestamp());

        $this->backupAs($user);

        $this->assertFalse($disk->exists($oldPath));
        $this->assertCount(1, $disk->allFiles("backups/notifications/{$user->id}"));
    }
}
