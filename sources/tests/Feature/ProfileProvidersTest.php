<?php

namespace Tests\Feature;

use App\Support\Profiles\MahasiswaProfileProvider;
use Pikdi\TsuFoundation\Profiles\TsuProfiles;
use Tests\TestCase;

/**
 * Template mendaftarkan profil mahasiswa ke tsu-foundation; foundation tidak mengenal model template.
 */
class ProfileProvidersTest extends TestCase
{
    protected array $requiresPackages = ['foundation'];

    public function test_mahasiswa_profile_is_registered_by_the_template(): void
    {
        $provider = (new \ReflectionProperty(TsuProfiles::class, 'providers'));
        $registered = array_map(fn ($p) => get_class($p['provider']), $provider->getValue(app(TsuProfiles::class)));

        $this->assertContains(MahasiswaProfileProvider::class, $registered);
        $this->assertTrue((new MahasiswaProfileProvider())->serves('mahasiswa'));
    }

    public function test_foundation_source_does_not_reference_template_models(): void
    {
        $root = base_path('vendor/pikdi/tsu-foundation/src');
        foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($root, \FilesystemIterator::SKIP_DOTS)) as $file) {
            $source = file_get_contents($file);
            $this->assertStringNotContainsString('App\\Models\\', $source, (string) $file);
            $this->assertStringNotContainsString('Foundation\Auth\ThrottlesLogins', $source, (string) $file); // milik laravel/ui
        }
    }

    public function test_template_user_uses_tsu_user_trait(): void
    {
        $this->assertContains(\Pikdi\TsuFoundation\Models\Concerns\TsuUser::class, class_uses_recursive(\App\Models\User::class));
        $this->assertSame('uuid', tsu_user_key_type());
    }
}
