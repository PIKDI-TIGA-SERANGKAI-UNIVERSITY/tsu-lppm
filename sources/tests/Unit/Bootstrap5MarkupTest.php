<?php

namespace Tests\Unit;

use Pikdi\TsuCore\Testing\AssertsBootstrap5Markup;
use Tests\TestCase;

/**
 * Standar UI TSU: view, controller, dan modul template memakai Bootstrap 5 / AdminLTE 4.
 * Modul baru di Modules/ ikut diperiksa.
 */
class Bootstrap5MarkupTest extends TestCase
{
    use AssertsBootstrap5Markup;

    public function test_views_controllers_and_modules_use_bootstrap5_markup(): void
    {
        $this->assertBootstrap5Markup([resource_path('views'), app_path(), base_path('Modules')]);
    }

    public function test_module_stylesheets_target_bootstrap5_classes(): void
    {
        // CSS modul aplikasi (public/assets/css) dan CSS di dalam Modules/
        $this->assertBootstrap5Stylesheet([dirname(base_path()) . '/public/assets/css', base_path('Modules')]);
    }
}
