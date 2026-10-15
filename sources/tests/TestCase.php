<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    /**
     * Paket TSU opsional yang wajib terpasang untuk seluruh test di kelas ini ('foundation', 'karyawan').
     * Bila belum terpasang, test di-skip (template tetap bisa diuji tanpa paket tersebut).
     *
     * @var array<int, string>
     */
    protected array $requiresPackages = [];

    protected const PACKAGES = [
        'foundation' => 'Pikdi\\TsuFoundation\\TsuFoundationServiceProvider',
        'karyawan'   => 'Pikdi\\TsuKaryawan\\TsuKaryawanServiceProvider',
    ];

    protected function setUp(): void
    {
        $this->requirePackages(...$this->requiresPackages);

        parent::setUp();
    }

    public static function packageInstalled(string $package): bool
    {
        return class_exists(static::PACKAGES[$package]);
    }

    /**
     * Skip test bila salah satu paket belum terpasang (bisa dipanggil di dalam method test).
     */
    protected function requirePackages(string ...$packages): void
    {
        foreach ($packages as $package) {
            if (! static::packageInstalled($package)) {
                $this->markTestSkipped("Membutuhkan pikdi/tsu-{$package} (belum terpasang).");
            }
        }
    }
}
