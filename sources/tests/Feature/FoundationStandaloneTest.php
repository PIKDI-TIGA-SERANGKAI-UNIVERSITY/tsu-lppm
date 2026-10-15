<?php

namespace Tests\Feature;

use Pikdi\TsuCore\Http\Controllers\TsuController;
use Pikdi\TsuFoundation\Services\TsuActivityLogService;
use Tests\TestCase;

/**
 * Memastikan tsu-foundation tidak lagi bergantung pada class milik template
 * (MiddlewareController, App\Exports, App\Http\Controllers\Controller).
 */
class FoundationStandaloneTest extends TestCase
{
    protected array $requiresPackages = ['foundation'];

    private function migrate(): void
    {
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

    public function test_foundation_controllers_do_not_depend_on_template_classes(): void
    {
        $crud = ['ActivityLogController', 'ErrorLogController', 'MenuController', 'PermissionController', 'RoleController', 'UserController'];
        foreach ($crud as $controller) {
            $this->assertSame(
                TsuController::class,
                get_parent_class('Pikdi\\TsuFoundation\\Http\\Controllers\\' . $controller),
                "{$controller} harus extends TsuController"
            );
        }

        foreach (['SsoController', 'UserProfileController'] as $controller) {
            $this->assertSame(
                \Illuminate\Routing\Controller::class,
                get_parent_class('Pikdi\\TsuFoundation\\Http\\Controllers\\' . $controller)
            );
        }

        // Base controller aplikasi tetap tersedia untuk controller developer
        $this->assertTrue(is_subclass_of(\App\Http\Controllers\MiddlewareController::class, TsuController::class));
        $this->assertFalse(class_exists('App\\Exports\\ActivityLogBackupExport'));
    }

    public function test_action_buttons_escape_row_data(): void
    {
        $controller = new class extends TsuController {
            public function buttons(object $row, array $options): string
            {
                return $this->getActionButtons($row, 'demo:item', $options);
            }
        };

        $html = $controller->buttons(
            (object) ['id' => '1"onmouseover="alert(1)', 'name' => 'Budi "><script>alert(1)</script>'],
            ['can_edit' => true, 'can_delete' => true, 'edit_url' => '/edit?a=1&b="x"', 'delete_url' => '/hapus/1']
        );

        $this->assertStringNotContainsString('<script>', $html);
        $this->assertStringNotContainsString('"onmouseover="', $html);
        $this->assertStringContainsString('&lt;script&gt;', $html);
        $this->assertStringContainsString('data-url="/edit?a=1&amp;b=&quot;x&quot;"', $html);
    }

    public function test_legacy_foundation_names_resolve_to_core_classes(): void
    {
        $moved = [
            'Pikdi\\TsuFoundation\\Http\\Controllers\\TsuController'       => \Pikdi\TsuCore\Http\Controllers\TsuController::class,
            'Pikdi\\TsuFoundation\\Services\\TsuResponseService'           => \Pikdi\TsuCore\Services\TsuResponseService::class,
            'Pikdi\\TsuFoundation\\Services\\TsuErrorHandlerService'       => \Pikdi\TsuCore\Services\TsuErrorHandlerService::class,
            'Pikdi\\TsuFoundation\\Services\\TsuFileUploadService'         => \Pikdi\TsuCore\Services\TsuFileUploadService::class,
            'Pikdi\\TsuFoundation\\Notifications\\TsuRealtimeNotification' => \Pikdi\TsuCore\Notifications\TsuRealtimeNotification::class,
        ];

        foreach ($moved as $old => $new) {
            $this->assertSame($new, (new \ReflectionClass($old))->getName(), "{$old} harus mengarah ke {$new}");
        }

        $movedUi = [
            'Pikdi\\TsuFoundation\\View\\Components\\TsuPageHeader'  => \Pikdi\TsuCore\View\Components\TsuPageHeader::class,
            'Pikdi\\TsuFoundation\\View\\Components\\TsuStatCard'    => \Pikdi\TsuCore\View\Components\TsuStatCard::class,
            'Pikdi\\TsuFoundation\\View\\Components\\TsuMasterGuide' => \Pikdi\TsuCore\View\Components\TsuMasterGuide::class,
            'Pikdi\\TsuFoundation\\Services\\BreadcrumbService'      => \Pikdi\TsuCore\Services\BreadcrumbService::class,
            'Modules\\System\\Services\\BreadcrumbService'           => \Pikdi\TsuCore\Services\BreadcrumbService::class,
        ];
        foreach ($movedUi as $old => $new) {
            $this->assertSame($new, (new \ReflectionClass($old))->getName(), "{$old} harus mengarah ke {$new}");
        }

        $this->assertTrue(trait_exists('Pikdi\\TsuFoundation\\Traits\\TsuExceptionHandlerTrait'));
        $this->assertSame(
            \Pikdi\TsuCore\Traits\TsuExceptionHandlerTrait::class,
            (new \ReflectionClass('Pikdi\\TsuFoundation\\Traits\\TsuExceptionHandlerTrait'))->getName()
        );
    }

    public function test_legacy_layout_view_names_resolve_to_core_views(): void
    {
        $coreViews = realpath(\Pikdi\TsuCore\TsuCoreServiceProvider::viewsPath());

        foreach (['system', 'tsu-foundation'] as $namespace) {
            foreach (['template.admin.header', 'template.layout.masterlogin', 'template.layout.email', 'components.alert'] as $view) {
                $path = realpath(view()->getFinder()->find("{$namespace}::{$view}"));
                $this->assertStringStartsWith($coreViews, $path, "{$namespace}::{$view} harus dilayani tsu-core");
            }
        }
    }

    public function test_foundation_views_only_use_assets_shipped_by_core(): void
    {
        $views = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(base_path('vendor/pikdi/tsu-foundation/resources/views')));
        foreach ($views as $file) {
            if (! str_ends_with($file, '.blade.php')) {
                continue;
            }
            $source = file_get_contents($file);
            $this->assertStringNotContainsString("asset('public/assets", $source, (string) $file);

            preg_match_all("~tsu_asset\('([^']+)'\)~", $source, $m);
            foreach ($m[1] as $asset) {
                $this->assertFileExists(\Pikdi\TsuCore\TsuCoreServiceProvider::assetsPath() . '/' . $asset, "{$asset} ({$file})");
            }
        }
    }

    public function test_core_upload_is_recorded_in_foundation_activity_log(): void
    {
        $this->migrate();
        \Illuminate\Support\Facades\Storage::fake('public');

        $path = \Pikdi\TsuCore\Services\TsuFileUploadService::upload(
            \Illuminate\Http\UploadedFile::fake()->create('Surat Tugas.pdf', 20, 'application/pdf'),
            'dokumen'
        );

        $this->assertDatabaseHas((new \Pikdi\TsuFoundation\Models\ActivityLog())->getTable(), [
            'module' => 'Media',
            'action' => 'UPLOAD',
        ]);
        $this->assertNotNull($path);
    }

    public function test_activity_log_backup_exports_excel_into_zip(): void
    {
        $this->migrate();
        TsuActivityLogService::record('Uji', 'CREATE', 'Log uji backup');

        $result = TsuActivityLogService::backupAndPurge(0);

        try {
            $this->assertSame('success', $result['status']);

            $zip = new \ZipArchive();
            $this->assertTrue($zip->open($result['zip_path']) === true);
            $entries = [];
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $entries[] = $zip->getNameIndex($i);
            }
            $zip->close();

            $this->assertNotEmpty(array_filter($entries, fn ($name) => str_ends_with($name, '.xlsx')));
        } finally {
            @unlink($result['zip_path'] ?? '');
        }
    }
}
