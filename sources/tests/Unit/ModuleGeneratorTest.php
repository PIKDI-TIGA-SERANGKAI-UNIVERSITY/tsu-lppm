<?php

namespace Tests\Unit;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;
use Tests\TestCase;

/**
 * Menjaga perintah `php artisan module:make` (dipakai tutorial "Modul Pertama" di /panduan) tetap
 * menghasilkan modul utuh dengan susunan folder yang sama dengan modul Admin.
 */
class ModuleGeneratorTest extends TestCase
{
    private string $name = 'ZzGeneratorUji';

    private ?string $statusesBackup = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->statusesBackup = @file_get_contents(base_path('modules_statuses.json')) ?: null;
    }

    protected function tearDown(): void
    {
        if ($this->app) {
            File::deleteDirectory(base_path('Modules/' . $this->name));

            if ($this->statusesBackup !== null) {
                file_put_contents(base_path('modules_statuses.json'), $this->statusesBackup);
            }
        }

        parent::tearDown();
    }

    public function test_module_make_generates_a_complete_module(): void
    {
        // module:make memanggil `composer dump-autoload` (batas 60 detik, lambat di mesin sibuk): tidak relevan untuk uji ini
        Process::fake();

        $this->assertSame(0, Artisan::call('module:make', ['name' => [$this->name]]));

        $root = base_path('Modules/' . $this->name);

        foreach ([
            'module.json',
            'Providers/' . $this->name . 'ServiceProvider.php',
            'Providers/RouteServiceProvider.php',
            'Http/Controllers/' . $this->name . 'Controller.php',
            'Routes/web.php',
            'Resources/views/index.blade.php',
        ] as $file) {
            $this->assertFileExists($root . '/' . $file);
        }

        // Tidak boleh ada placeholder stub yang belum terisi
        foreach (File::allFiles($root) as $file) {
            $this->assertStringNotContainsString('$MODULE_NAMESPACE$', $file->getContents(), $file->getRelativePathname());
            $this->assertStringNotContainsString('$CONTROLLER_NAMESPACE$', $file->getContents(), $file->getRelativePathname());
        }

        $this->assertSame(0, Artisan::call('module:make-model', ['model' => 'Uji', 'module' => $this->name]));
        $this->assertFileExists($root . '/Entities/Uji.php');

        $this->assertSame(0, Artisan::call('module:make-migration', ['name' => 'create_ujis_table', 'module' => $this->name]));
        $this->assertNotEmpty(glob($root . '/Database/Migrations/*_create_ujis_table.php'));
    }
}
