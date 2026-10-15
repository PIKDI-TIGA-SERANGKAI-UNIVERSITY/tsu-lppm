<?php

namespace Modules\Admin\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        if (Session::has('tmp')) {
            Session::forget('tmp');
        }

        // Feature Toggle: Jika mode dashboard demo dimatikan di .env, gunakan kanvas bersih
        $isDemoDashboard = filter_var(config('tsu-core.features.demo_dashboard', true), FILTER_VALIDATE_BOOLEAN);
        if (!$isDemoDashboard) {
            $manualBookUrl = config('tsu-core.manual_book_url') ?: route('panduan');
            return view('admin::dashboard.blank', [
                'title'         => 'Dashboard',
                'menu'          => 'dashboard',
                'menuIcon'      => 'fas fa-home',
                'manualBookUrl' => $manualBookUrl,
                'currentUser'   => Auth::user(),
            ]);
        }

        // Fitur paket opsional: pikdi/tsu-karyawan (data karyawan) & pikdi/tsu-foundation (menu dinamis)
        $karyawanService = 'Pikdi\\TsuKaryawan\\Services\\KaryawanSyncService';
        $isKaryawanEnabled = class_exists($karyawanService) && $karyawanService::isFeatureEnabled();
        $menuModel = 'Pikdi\\TsuFoundation\\Models\\MenuSidebar';

        // Metrik Statistik Sistem
        $stats = [
            'total_karyawan' => $isKaryawanEnabled ? $this->safeCount('Pikdi\\TsuKaryawan\\Models\\DataDosenTendik') : 0,
            'total_user'     => \App\Models\User::count(),
            'total_role'     => \Spatie\Permission\Models\Role::count(),
            'total_menu'     => class_exists($menuModel) ? $this->safeCount($menuModel) : 0,
        ];

        // Pemeriksaan Status Kesehatan Sistem (Health Check)
        $dbConnected = false;
        try {
            DB::connection()->getPdo();
            $dbConnected = true;
        } catch (\Throwable $e) {
            $dbConnected = false;
        }

        $ssoConfigured = !empty(config('tsu-foundation.sso.client_id')) && !empty(config('tsu-foundation.sso.client_secret'));
        $hrisConfigured = !empty(config('tsu-karyawan.hris.url')) && !empty(config('tsu-karyawan.hris.sync_secret'));

        $manualBookUrl = config('tsu-core.manual_book_url') ?: route('panduan');

        $data = [
            'title'              => 'Dashboard Utama',
            'menu'               => 'dashboard',
            'menuIcon'           => 'fas fa-tachometer-alt',
            'stats'              => $stats,
            'dbConnected'        => $dbConnected,
            'ssoConfigured'      => $ssoConfigured,
            'hrisConfigured'     => $hrisConfigured,
            'manualBookUrl'      => $manualBookUrl,
            'isKaryawanEnabled'  => $isKaryawanEnabled,
            'currentUser'        => Auth::user(),
        ];

        return view('admin::dashboard.dashboard', $data);
    }

    /**
     * Jumlah baris model paket opsional; 0 bila tabelnya belum dimigrasi.
     */
    protected function safeCount(string $model): int
    {
        try {
            return $model::count();
        } catch (\Throwable $e) {
            return 0;
        }
    }
}
