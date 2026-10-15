<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Peserta pengembang eksternal (magang, PKL, mitra) yang masa tugasnya berakhir harus dikeluarkan
 * dari aplikasi turunan template (pengaman kedua; pembatasan utama di TSU Homebase).
 */
class AssignmentEnforcementTest extends TestCase
{
    use RefreshDatabase;

    protected array $requiresPackages = ['foundation'];

    private function makeUser(string $identityType, ?string $endsAt): \App\Models\User
    {
        $user = new \App\Models\User();
        $user->forceFill([
            'id'                 => (string) Str::uuid(),
            'name'               => 'Peserta Uji',
            'email'              => Str::random(6) . '@uji.test',
            'username'           => (string) random_int(9900000000, 9999999999),
            'identity_type'      => $identityType,
            'assignment_ends_at' => $endsAt,
        ])->save();

        return $user;
    }

    public function test_assignment_columns_exist_in_the_template_database(): void
    {
        foreach (['identity_type', 'assignment_starts_at', 'assignment_ends_at', 'assignment_supervisor_name', 'assignment_supervisor_email', 'assignment_origin'] as $column) {
            $this->assertTrue(\Illuminate\Support\Facades\Schema::hasColumn((new \App\Models\User())->getTable(), $column), "Kolom {$column} harus ada");
        }
    }

    public function test_session_of_external_participant_with_ended_assignment_is_cut(): void
    {
        $user = $this->makeUser('eksternal', now()->subDay()->toDateString());

        $this->actingAs($user)->get(route('admin.dashboard'))->assertRedirect(route('login'));

        $this->assertGuest();
    }

    public function test_external_participant_with_active_assignment_keeps_access(): void
    {
        $user = $this->makeUser('eksternal', now()->addWeek()->toDateString());

        $this->actingAs($user)->get(route('admin.dashboard'))->assertOk();
    }

    public function test_university_students_are_not_cut_by_an_ended_assignment(): void
    {
        $user = $this->makeUser('mahasiswa', now()->subDay()->toDateString());

        $this->actingAs($user)->get(route('admin.dashboard'))->assertOk();
    }
}
