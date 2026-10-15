<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class PublicUrlTest extends TestCase
{
    public function test_homebase_url_follows_config_and_is_used_on_login_page(): void
    {
        $this->requirePackages('foundation'); // halaman login TSU
        config(['tsu-core.homebase_url' => 'https://homebase.lokal.test/']);

        $this->assertSame('https://homebase.lokal.test', tsu_homebase_url());

        $this->get('/login')
            ->assertOk()
            ->assertSee('https://homebase.lokal.test/forgot-password', false)
            ->assertDontSee('https://homebase.tsu.ac.id/forgot-password', false);
    }

    public function test_public_disk_url_points_to_public_storage_junction(): void
    {
        $url = Storage::disk('public')->url('dokumen/contoh.pdf');

        $this->assertStringEndsWith('/public/storage/dokumen/contoh.pdf', $url);
        $this->assertStringNotContainsString('//public', $url);
    }

    public function test_uploaded_profile_photo_url_is_reachable_path(): void
    {
        $this->requirePackages('foundation'); // profile_photo_url dari trait TsuUser
        $folder = 'zz-test-' . Str::random(8);
        Storage::disk('public')->put($folder . '/foto.png', 'png');

        try {
            $user = new \App\Models\User(['name' => 'Uji Foto', 'avatar_url' => $folder . '/foto.png']);

            $this->assertStringEndsWith('/public/storage/' . $folder . '/foto.png', $user->profile_photo_url);
        } finally {
            Storage::disk('public')->deleteDirectory($folder);
        }
    }
}
