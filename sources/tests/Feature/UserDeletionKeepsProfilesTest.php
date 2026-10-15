<?php

namespace Tests\Feature;

use App\Models\DataMahasiswa;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Hapus user di proyek satelit = mengeluarkan akun login saja ("kick").
 * Data mahasiswa & dosen/tendik tetap ada, hanya tautan user_id yang dilepas (NULL).
 */
class UserDeletionKeepsProfilesTest extends TestCase
{
    private const NEW_MIGRATION = 'database/migrations/2026_10_10_000001_keep_mahasiswa_profile_when_user_deleted.php';

    private function migrate(bool $withMahasiswa = true): void
    {
        $paths = [
            base_path('database/migrations/2014_10_12_000000_create_users_table.php'),
            base_path('database/migrations/2025_12_19_100641_create_permission_tables.php'),
        ];
        if ($withMahasiswa) {
            $paths[] = base_path('database/migrations/2026_01_11_180625_create_data_mahasiswas_table.php');
            $paths[] = base_path(self::NEW_MIGRATION);
        }
        if (static::packageInstalled('foundation')) {
            $paths[] = base_path('vendor/pikdi/tsu-foundation/database/migrations');
        }
        if (static::packageInstalled('karyawan')) {
            $paths[] = base_path('vendor/pikdi/tsu-karyawan/database/migrations');
        }

        $this->artisan('migrate', ['--realpath' => true, '--path' => $paths])->assertSuccessful();
    }

    private function makeUser(string $name): \App\Models\User
    {
        $user = new \App\Models\User(['id' => (string) Str::uuid(), 'name' => $name, 'email' => Str::slug($name) . '@tsu.test', 'username' => Str::slug($name)]);
        $user->save();

        return $user;
    }

    public function test_deleting_user_keeps_mahasiswa_profile(): void
    {
        $this->migrate();
        $user = $this->makeUser('Siti Mahasiswa');
        $profile = DataMahasiswa::query()->create(['nim' => '2026001', 'nama' => 'Siti Mahasiswa', 'user_id' => $user->id]);

        $user->delete();

        $profile = $profile->fresh();
        $this->assertNotNull($profile, 'Data mahasiswa tidak boleh ikut terhapus');
        $this->assertNull($profile->user_id, 'Tautan ke akun yang dihapus dilepas');
    }

    public function test_deleting_user_keeps_dosen_tendik_profile(): void
    {
        $this->requirePackages('karyawan');
        $this->migrate(false);
        $user = $this->makeUser('Dr Dosen');
        $profile = \Pikdi\TsuKaryawan\Models\DataDosenTendik::query()->create(['nik' => '19870001', 'nama_lengkap' => 'Dr. Dosen', 'user_id' => $user->id, 'is_active' => 1]);

        $user->delete();

        $profile = $profile->fresh();
        $this->assertNotNull($profile, 'Data dosen/tendik tidak boleh ikut terhapus');
        $this->assertNull($profile->user_id);
    }

    public function test_user_management_delete_only_kicks_the_account(): void
    {
        $this->requirePackages('foundation');
        $this->migrate();
        $admin = $this->makeUser('Admin Pikdi');
        $admin->assignRole(\Spatie\Permission\Models\Role::findOrCreate('pikdi', 'web'));
        $user = $this->makeUser('Mahasiswa Keluar');
        DataMahasiswa::query()->create(['nim' => '2026002', 'nama' => 'Mahasiswa Keluar', 'user_id' => $user->id]);

        $this->actingAs($admin)->delete(route('users.user.destroy', $user->id))
            ->assertSessionHas('success');

        $this->assertNull(\App\Models\User::query()->find($user->id), 'Akun login terhapus');
        $this->assertDatabaseHas(config('app.table.data_mahasiswas'), ['nim' => '2026002', 'user_id' => null]);
    }

    public function test_migration_converts_existing_cascade_foreign_key(): void
    {
        $this->migrate(false);
        $table = config('app.table.data_mahasiswas');

        // Tabel lama (sebelum perbaikan): ON DELETE CASCADE
        Schema::create($table, function (Blueprint $t) {
            $t->uuid('id')->primary();
            $t->foreignUuid('user_id')->nullable()->constrained(config('app.table.users'))->cascadeOnDelete();
            $t->string('nim')->unique();
        });

        (require base_path(self::NEW_MIGRATION))->up();

        $fk = collect(Schema::getForeignKeys($table))->first(fn ($f) => $f['columns'] === ['user_id']);
        $this->assertSame('set null', strtolower($fk['on_delete']));

        // Idempoten: aman dijalankan ulang
        (require base_path(self::NEW_MIGRATION))->up();
        $this->assertCount(1, Schema::getForeignKeys($table));
    }
}
