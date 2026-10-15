<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

/**
 * Gate::before super admin (pikdi, super admin, super admin {modul}) didaftarkan oleh pikdi/tsu-foundation,
 * Blade directive (@rupiah, @tglIndo, @hari, @bulanRomawi) oleh pikdi/tsu-core.
 * Daftarkan service khusus aplikasi di sini.
 */
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bab Buku Panduan (/panduan) khusus starter kit ini. Bab tentang paket (tsu-core, tsu-foundation,
     * tsu-karyawan) dibawa paketnya masing-masing. Tambahkan bab milik proyek Anda dengan cara yang sama:
     * tsu_docs()->chapter('kunci', ['title' => ..., 'file' => base_path('docs/panduan/....md'), 'order' => ...]).
     */
    protected function registerDocs(): void
    {
        $dir = base_path('docs/panduan/') ;

        tsu_docs()
            ->chapter('mulai', [
                'title'    => 'Mulai dari Sini (Quick Start)',
                'file'     => $dir . 'mulai.md',
                'order'    => 10,
                'icon'     => 'fas fa-rocket',
                'badge'    => ['label' => '0 to 1', 'variant' => 'light border'],
                'shortcut' => ['label' => 'Mulai Instalasi', 'icon' => 'fas fa-rocket', 'variant' => 'primary', 'anchor' => 'mulai-instalasi'],
                'source'   => 'template',
            ])
            ->chapter('modul-pertama', [
                'title'    => 'Tutorial Pemula: Membuat Modul Pertama Anda',
                'file'     => $dir . 'modul-pertama.md',
                'order'    => 20,
                'icon'     => 'fas fa-graduation-cap',
                'badge'    => ['label' => 'Pemula', 'variant' => 'success'],
                'shortcut' => ['label' => 'Tutorial Pemula', 'icon' => 'fas fa-graduation-cap', 'variant' => 'success', 'anchor' => 'modul-pertama-peta'],
                'source'   => 'template',
            ])
            ->chapter('zona', [
                'title'    => 'Arsitektur Zona Modul & Aturan Struktur',
                'file'     => $dir . 'zona.md',
                'order'    => 30,
                'icon'     => 'fas fa-traffic-light',
                'badge'    => ['label' => 'Wajib', 'variant' => 'danger'],
                'shortcut' => ['label' => '3 Zona Arsitektur', 'icon' => 'fas fa-traffic-light', 'variant' => 'outline-danger', 'anchor' => 'zona-tiga-zona'],
                'source'   => 'template',
            ])
            ->chapter('sop', [
                'title'  => 'SOP Koordinasi dengan PIKDI',
                'file'   => $dir . 'sop.md',
                'order'    => 5, // paling atas: SOP wajib dibaca sebelum mulai mengembangkan
                'icon'     => 'fas fa-clipboard-check',
                'badge'    => ['label' => 'SOP Wajib', 'variant' => 'danger'],
                'shortcut' => ['label' => 'SOP Koordinasi PIKDI', 'icon' => 'fas fa-clipboard-check', 'variant' => 'outline-primary', 'anchor' => 'sop-koordinasi-pikdi'],
                'source'   => 'template',
            ])
            ->checklist('template-awal', [
                '**Bebas Pelanggaran Zona Merah:** Tidak ada file di dalam direktori `sources/vendor/pikdi/` yang diubah atau dihapus sembarangan.',
            ], order: 10, source: 'template')
            ->checklist('template-rilis', [
                '**Kesiapan Rilis:** Saat siap ke production, pastikan `FEATURE_DEMO_DASHBOARD=false`, `FEATURE_DEMO_LANDING_PAGE=false`, dan `FEATURE_MANUAL_BOOK=false` di `.env`.',
            ], order: 900, source: 'template');
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // Label breadcrumb halaman milik aplikasi
        \Pikdi\TsuCore\Services\BreadcrumbService::labels([
            'panduan' => 'Buku Panduan',
        ]);

        $this->registerDocs();

        if (class_exists(\Pikdi\TsuFoundation\TsuFoundationServiceProvider::class)) {
            // Profil mahasiswa (tabel data_mahasiswas) untuk login & sinkronisasi SSO tsu-foundation
            $this->callAfterResolving(
                \Pikdi\TsuFoundation\Profiles\TsuProfiles::class,
                fn ($profiles) => $profiles->register(new \App\Support\Profiles\MahasiswaProfileProvider(), 20)
            );
        } else {
            // Tanpa tsu-foundation (tanpa menu dinamis): menu sidebar statis milik aplikasi
            tsu_ui()->slot('sidebar.menu', 'partials.sidebar-menu');
        }
    }
}
