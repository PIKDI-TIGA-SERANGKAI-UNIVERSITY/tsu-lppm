<?php

namespace Tests\Feature;

use Pikdi\TsuCore\Services\BreadcrumbService;
use Tests\TestCase;

/**
 * tsu-foundation mengisi slot layout tsu-core (menu, lonceng notifikasi, profil, pencarian cepat).
 */
class FoundationUiSlotsTest extends TestCase
{
    protected array $requiresPackages = ['foundation'];

    public function test_foundation_fills_core_layout_slots(): void
    {
        foreach (['sidebar.menu', 'navbar.right', 'navbar.user-menu', 'footer.scripts'] as $slot) {
            $this->assertTrue(tsu_ui()->hasSlot($slot), "Slot {$slot} harus diisi tsu-foundation");
        }
    }

    public function test_foundation_search_items_require_login(): void
    {
        $this->assertSame([], tsu_ui()->collectSearchItems(null));

        $user = new \App\Models\User(['name' => 'Uji']);
        $titles = array_column(tsu_ui()->collectSearchItems($user), 'title');
        $this->assertContains('Profil Saya / Akun', $titles);
    }

    public function test_foundation_breadcrumb_labels_are_registered(): void
    {
        $this->get('/'); // memastikan request & route tersedia
        $this->app['request']->setRouteResolver(fn () => \Illuminate\Support\Facades\Route::getRoutes()->getByName('system.activity-logs.index'));

        $labels = array_column(BreadcrumbService::generate(), 'label');
        $this->assertSame(['System Management', 'Log Aktivitas'], $labels);
    }

    public function test_realtime_inbox_listener_escapes_payload(): void
    {
        $script = view('tsu-foundation::slots.notification-listener')->render();

        $this->assertStringContainsString("addEventListener('tsu:notification'", $script);
        $this->assertStringContainsString('${esc(notification.title', $script);
        $this->assertStringContainsString('${esc(notification.message', $script);
        $this->assertStringContainsString('${esc(icon)}', $script);
        $this->assertStringContainsString('safeUrl(notification.action_url', $script);
        $this->assertStringNotContainsString('${notification.title', $script);
    }
}
