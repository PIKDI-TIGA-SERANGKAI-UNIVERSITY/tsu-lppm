<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

/**
 * Template dipakai di luar laptop PIKDI: paket TSU harus terpasang dari GitHub PIKDI (repo privat),
 * bukan dari folder lokal. Pengembang paket PIKDI memakai path lokal lewat composer.local.json (tidak di-commit).
 */
class ComposerSetupTest extends TestCase
{
    private function composer(): array
    {
        return json_decode(file_get_contents(dirname(__DIR__, 2) . '/composer.json'), true, 512, JSON_THROW_ON_ERROR);
    }

    public function test_tsu_packages_come_from_pikdi_github_not_local_paths(): void
    {
        $composer = $this->composer();

        $this->assertSame([], array_values(array_filter($composer['repositories'], fn ($r) => ($r['type'] ?? null) === 'path')),
            'composer.json tidak boleh berisi repositori path (pakai composer.local.json untuk path lokal)');

        $urls = array_column($composer['repositories'], 'url');
        foreach (['tsu-core', 'tsu-foundation', 'tsu-karyawan'] as $package) {
            $this->assertContains("https://github.com/PIKDI-TIGA-SERANGKAI-UNIVERSITY/{$package}.git", $urls);
        }

        // Diambil lewat git (login GitHub tersimpan), tanpa wajib token API
        foreach ($composer['repositories'] as $repository) {
            $this->assertTrue($repository['no-api'] ?? false, $repository['url'] . ' harus memakai "no-api": true');
        }

        $this->assertSame('dev-main', $composer['require']['pikdi/tsu-core']);
        // tsu-foundation boleh dilepas (composer remove), tetapi bila ada harus dev-main
        foreach (['pikdi/tsu-foundation', 'pikdi/tsu-karyawan'] as $optional) {
            if (isset($composer['require'][$optional])) {
                $this->assertSame('dev-main', $composer['require'][$optional]);
            }
        }
    }

    public function test_local_path_override_is_optional_and_not_committed(): void
    {
        $root = dirname(__DIR__, 2);

        $this->assertContains('composer.local.json', $this->composer()['extra']['merge-plugin']['include']);
        $this->assertFileExists($root . '/composer.local.json.example');

        $gitignore = file_get_contents($root . '/.gitignore');
        $this->assertStringContainsString('/composer.local.json', $gitignore);
        // composer.lock di-commit pada proyek turunan, jadi tidak boleh diabaikan git
        $this->assertStringNotContainsString('/composer.lock', $gitignore);
    }
}
