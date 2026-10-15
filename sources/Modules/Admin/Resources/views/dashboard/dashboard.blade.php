@extends('tsu-core::template.admin.header')
@section('title', $title ?? 'Dashboard Utama')

@section('link_href')
    <style>
        /* === Hero Welcome Banner === */
        .tsu-hero-banner {
            background: linear-gradient(135deg, #094b54 0%, #0c6170 100%);
            border-radius: var(--tsu-radius-lg, 12px);
            padding: 1.6rem 2rem;
            color: #ffffff;
            box-shadow: 0 6px 20px rgba(9, 75, 84, 0.15);
            margin-bottom: 1.5rem;
            position: relative;
            overflow: hidden;
        }
        .tsu-hero-banner::after {
            content: '';
            position: absolute;
            right: -30px;
            bottom: -30px;
            width: 180px;
            height: 180px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 50%;
            pointer-events: none;
        }
        .tsu-hero-banner::before {
            content: '';
            position: absolute;
            right: 120px;
            top: -40px;
            width: 120px;
            height: 120px;
            background: rgba(255, 255, 255, 0.04);
            border-radius: 50%;
            pointer-events: none;
        }

        /* === TSU Stat Cards Grid (Adaptive 3 or 4 Columns) === */
        .tsu-stat-grid-dash {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        .tsu-stat-grid-dash.grid-3 {
            grid-template-columns: repeat(3, 1fr);
        }
        @media (max-width: 992px) {
            .tsu-stat-grid-dash,
            .tsu-stat-grid-dash.grid-3 {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        @media (max-width: 576px) {
            .tsu-stat-grid-dash,
            .tsu-stat-grid-dash.grid-3 {
                grid-template-columns: 1fr;
            }
        }
        .tsu-stat-card {
            border-radius: var(--tsu-radius-lg, 12px);
            padding: 1.15rem 1.25rem;
            box-shadow: 0 4px 14px rgba(9, 75, 84, 0.08);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .tsu-stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(9, 75, 84, 0.15);
        }
        .tsu-stat-card__top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 0.65rem;
        }
        .tsu-stat-card__label {
            font-size: 0.78rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            opacity: 0.95;
            margin: 0;
        }
        .tsu-stat-card__icon-badge {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            flex-shrink: 0;
        }
        .tsu-stat-card__value {
            font-size: 1.85rem;
            font-weight: 800;
            line-height: 1.15;
            letter-spacing: -0.02em;
            margin-bottom: 0.25rem;
            display: flex;
            align-items: baseline;
            gap: 0.35rem;
        }
        .tsu-stat-card__unit {
            font-size: 0.9rem;
            font-weight: 600;
            opacity: 0.85;
        }
        .tsu-stat-card__subtext {
            font-size: 0.75rem;
            font-weight: 500;
            opacity: 0.85;
            line-height: 1.25;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* Card Color Schemes */
        .tsu-stat-card--total {
            background: linear-gradient(135deg, #094b54 0%, #0c6170 100%);
            color: #ffffff;
        }
        .tsu-stat-card--users {
            background: linear-gradient(135deg, #0284c7 0%, #0ea5e9 100%);
            color: #ffffff;
        }
        .tsu-stat-card--roles {
            background: linear-gradient(135deg, #b45309 0%, #d97706 100%);
            color: #ffffff;
        }
        .tsu-stat-card--menus {
            background: linear-gradient(135deg, #047857 0%, #10b981 100%);
            color: #ffffff;
        }

        /* === TSU Container Card === */
        .tsu-card {
            background: #ffffff;
            border-radius: var(--tsu-radius-lg, 12px);
            border: 1px solid rgba(0, 0, 0, 0.06);
            box-shadow: 0 4px 16px rgba(9, 75, 84, 0.06);
            overflow: hidden;
            margin-bottom: 1.5rem;
        }

        .tsu-quick-link-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 1.1rem;
            transition: all 0.2s ease;
            text-decoration: none !important;
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        .tsu-quick-link-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(9, 75, 84, 0.09);
            border-color: #094b54;
        }
        .tsu-quick-icon {
            width: 44px;
            height: 44px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            flex-shrink: 0;
        }

        .tsu-step-item {
            display: flex;
            align-items: flex-start;
            gap: 1rem;
            padding: 0.9rem 0;
            border-bottom: 1px solid #f1f5f9;
        }
        .tsu-step-item:last-child,
        body.dark-mode .tsu-step-item:last-child {
            border-bottom: none !important;
            padding-bottom: 0;
        }
        /* Perintah/kode inline tidak boleh patah di tengah; pindah utuh ke baris berikutnya */
        .tsu-step-item code {
            white-space: nowrap;
        }
        .tsu-step-num {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: rgba(9, 75, 84, 0.08);
            color: #094b54;
            font-weight: 800;
            font-size: 0.85rem;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .tsu-guardrail-box {
            background: #f8fbfb;
            border: 1px solid #e2e8f0;
            border-left: 4px solid #094b54 !important;
        }

        /* Dark Mode Local Overrides */
        body.dark-mode .tsu-card {
            background: #1e293b !important;
            border-color: #334155 !important;
            border-top: 3px solid var(--tsu-gold, #f8c12a) !important;
            box-shadow: 0 6px 24px rgba(0, 0, 0, 0.4), 0 -2px 14px rgba(248, 193, 42, 0.22) !important;
        }
        body.dark-mode .tsu-card .card-header i {
            color: var(--tsu-gold, #f8c12a) !important;
        }
        body.dark-mode .tsu-quick-link-card {
            background: #0f172a !important;
            border-color: #334155 !important;
        }
        body.dark-mode .tsu-quick-link-card:hover {
            background: #1a273a !important;
            border-color: var(--tsu-gold, #f8c12a) !important;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.45), 0 0 12px rgba(248, 193, 42, 0.25) !important;
        }
        body.dark-mode .tsu-quick-link-card h6 {
            color: #f8fafc !important;
        }
        body.dark-mode .tsu-quick-link-card p {
            color: #94a3b8 !important;
        }
        body.dark-mode .tsu-guardrail-box {
            background: #0f172a !important;
            border-color: #334155 !important;
            border-left-color: var(--tsu-gold, #f8c12a) !important;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.3), -2px 0 10px rgba(248, 193, 42, 0.15) !important;
        }
        body.dark-mode .tsu-guardrail-box h6 {
            color: var(--tsu-gold, #f8c12a) !important;
        }
    </style>
@endsection

@section('content')
    {{-- TSU Page Header --}}
    <x-tsu-page-header
        :title="$title ?? 'Dashboard Utama'"
        :icon="$menuIcon ?? 'fas fa-tachometer-alt'"
        :breadcrumb="true"
    >
        <x-slot name="actions">
            @if(filter_var(config('tsu-core.features.manual_book', true), FILTER_VALIDATE_BOOLEAN))
                <a href="{{ $manualBookUrl }}" class="btn btn-sm btn-outline-primary fw-bold shadow-sm me-2" style="border-radius: 8px;">
                    <i class="fas fa-book-open me-1"></i> Buku Panduan TSU
                </a>
            @endif
            @if(!empty($isKaryawanEnabled))
                @can('admin:data-karyawan:view')
                    <a href="{{ route('admin.data-karyawan.index') }}" class="btn btn-sm tsu-btn-primary-action" style="border-radius: 8px;">
                        <i class="fas fa-users me-1"></i> Data Pegawai
                    </a>
                @endcan
            @endif
        </x-slot>
    </x-tsu-page-header>

    {{-- Main Content --}}
    <section class="app-content">
        <div class="container-fluid">

            {{-- HERO WELCOME BANNER --}}
            <div class="tsu-hero-banner">
                <div class="row align-items-center">
                    <div class="col-lg-8 mb-3 mb-lg-0">
                        <div class="d-flex align-items-center mb-2">
                            <span class="badge text-bg-light text-dark fw-bold me-2 px-2 py-1" style="font-size: 0.76rem;">
                                <i class="fas fa-layer-group text-primary me-1"></i> MODUL: {{ strtoupper(config('tsu-core.module.name', 'template')) }}
                            </span>
                            <span class="badge text-white px-2 py-1" style="background: rgba(255,255,255,0.2); font-size: 0.76rem;">
                                <i class="fas fa-server me-1"></i> ENV: {{ strtoupper(app()->environment()) }}
                            </span>
                        </div>
                        <h4 class="fw-bold mb-1" style="font-size: 1.45rem; letter-spacing: -0.01em;">
                            Selamat Datang, {{ $currentUser->name ?? 'Pengembang TSU' }}!
                        </h4>
                        <p class="mb-0 text-white-50" style="font-size: 0.9rem; max-width: 650px;">
                            Template standar arsitektur ekosistem TSU. Gunakan starter kit ini untuk membangun fitur internal kampus dengan integrasi SSO Homebase (dan Master HRIS bila paket <code>tsu-karyawan</code> dipasang).
                        </p>
                    </div>
                    <div class="col-lg-4 text-lg-end">
                        <div class="d-inline-flex flex-column align-items-lg-end" style="gap: 0.5rem;">
                            @if(filter_var(config('tsu-core.features.manual_book', true), FILTER_VALIDATE_BOOLEAN))
                                <a href="{{ $manualBookUrl }}" class="btn btn-sm btn-light fw-bold px-3 py-2 shadow-sm" style="color: #094b54; border-radius: 8px;">
                                    <i class="fas fa-book-reader me-1 text-primary"></i> Baca Panduan &amp; Standar
                                </a>
                            @endif
                            <span class="badge text-white-50" style="font-size: 0.78rem;">
                                Tekan <kbd style="background: rgba(0,0,0,0.3); color: #fff; padding: 2px 5px; border-radius: 4px;">Ctrl</kbd> + <kbd style="background: rgba(0,0,0,0.3); color: #fff; padding: 2px 5px; border-radius: 4px;">K</kbd> untuk cari menu
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- STAT CARDS GRID (Adaptif 3 atau 4 Kolom) --}}
            <div class="tsu-stat-grid-dash {{ empty($isKaryawanEnabled) ? 'grid-3' : '' }}">
                @if(!empty($isKaryawanEnabled))
                    {{-- Data Pegawai --}}
                    <div class="tsu-stat-card tsu-stat-card--total">
                        <div class="tsu-stat-card__top">
                            <span class="tsu-stat-card__label">Data Pegawai</span>
                            <div class="tsu-stat-card__icon-badge">
                                <i class="fas fa-id-card"></i>
                            </div>
                        </div>
                        <div class="tsu-stat-card__value">
                            {{ number_format($stats['total_karyawan'] ?? 0) }}
                            <span class="tsu-stat-card__unit">Orang</span>
                        </div>
                        <div class="tsu-stat-card__subtext">
                            Sinkronisasi Master TSU HRIS
                        </div>
                    </div>
                @endif

                {{-- Akun Pengguna --}}
                <div class="tsu-stat-card tsu-stat-card--users">
                    <div class="tsu-stat-card__top">
                        <span class="tsu-stat-card__label">Akun Pengguna</span>
                        <div class="tsu-stat-card__icon-badge">
                            <i class="fas fa-users"></i>
                        </div>
                    </div>
                    <div class="tsu-stat-card__value">
                        {{ number_format($stats['total_user'] ?? 0) }}
                        <span class="tsu-stat-card__unit">Akun</span>
                    </div>
                    <div class="tsu-stat-card__subtext">
                        User Terdaftar di Sistem
                    </div>
                </div>

                {{-- Hak Akses Role --}}
                <div class="tsu-stat-card tsu-stat-card--roles">
                    <div class="tsu-stat-card__top">
                        <span class="tsu-stat-card__label">Hak Akses Role</span>
                        <div class="tsu-stat-card__icon-badge">
                            <i class="fas fa-user-shield"></i>
                        </div>
                    </div>
                    <div class="tsu-stat-card__value">
                        {{ number_format($stats['total_role'] ?? 0) }}
                        <span class="tsu-stat-card__unit">Role</span>
                    </div>
                    <div class="tsu-stat-card__subtext">
                        Matriks Otorisasi RBAC
                    </div>
                </div>

                {{-- Menu Sidebar --}}
                <div class="tsu-stat-card tsu-stat-card--menus">
                    <div class="tsu-stat-card__top">
                        <span class="tsu-stat-card__label">Menu Navigasi</span>
                        <div class="tsu-stat-card__icon-badge">
                            <i class="fas fa-sitemap"></i>
                        </div>
                    </div>
                    <div class="tsu-stat-card__value">
                        {{ number_format($stats['total_menu'] ?? 0) }}
                        <span class="tsu-stat-card__unit">Item</span>
                    </div>
                    <div class="tsu-stat-card__subtext">
                        Struktur Sidebar Terdaftar
                    </div>
                </div>
            </div>

            {{-- 2 COLUMNS MAIN CONTENT SECTION --}}
            <div class="row">
                
                {{-- KOLOM KIRI (8 KOLOM): Launchpad & Onboarding Guide --}}
                <div class="col-lg-8 mb-4">
                    
                    {{-- WIDGET 1: PINTASAN FITUR UTAMA --}}
                    <div class="tsu-card mb-4">
                        <div class="card-header bg-white py-3 px-4 border-bottom d-flex align-items-center justify-content-between">
                            <h6 class="fw-bold mb-0" style="color: var(--tsu-primary-dark, #094b54); font-size: 0.95rem;">
                                <i class="fas fa-th-large me-2 text-primary"></i> Pintasan Fitur Inti Modul
                            </h6>
                            <span class="badge text-bg-light border text-muted px-2 py-1" style="font-size: 0.78rem;">
                                Launchpad
                            </span>
                        </div>
                        <div class="card-body p-4">
                            <div class="row">
                                @if(!empty($isKaryawanEnabled))
                                    <div class="col-md-6 mb-3">
                                        <a href="{{ route('admin.data-karyawan.index') }}" class="tsu-quick-link-card">
                                            <div class="tsu-quick-icon" style="background: rgba(9, 75, 84, 0.1); color: #094b54;">
                                                <i class="fas fa-id-badge"></i>
                                            </div>
                                            <div>
                                                <h6 class="fw-bold text-dark mb-1" style="font-size: 0.92rem;">Direktori Pegawai</h6>
                                                <p class="text-muted mb-0 small">Monitoring data dosen &amp; tendik sinkron dari HRIS.</p>
                                            </div>
                                        </a>
                                    </div>
                                @endif
                                @if(Route::has('users.user.index'))
                                <div class="col-md-6 mb-3">
                                    <a href="{{ route('users.user.index') }}" class="tsu-quick-link-card">
                                        <div class="tsu-quick-icon" style="background: rgba(2, 132, 199, 0.1); color: #0284c7;">
                                            <i class="fas fa-users-cog"></i>
                                        </div>
                                        <div>
                                            <h6 class="fw-bold text-dark mb-1" style="font-size: 0.92rem;">Manajemen Pengguna</h6>
                                            <p class="text-muted mb-0 small">Atur akun modul dan pasang role lokal pengguna.</p>
                                        </div>
                                    </a>
                                </div>
                                @endif
                                @if(Route::has('system.role.index'))
                                <div class="col-md-6 mb-3">
                                    <a href="{{ route('system.role.index') }}" class="tsu-quick-link-card">
                                        <div class="tsu-quick-icon" style="background: rgba(180, 83, 9, 0.1); color: #b45309;">
                                            <i class="fas fa-shield-alt"></i>
                                        </div>
                                        <div>
                                            <h6 class="fw-bold text-dark mb-1" style="font-size: 0.92rem;">Hak Akses &amp; Role</h6>
                                            <p class="text-muted mb-0 small">Matriks otorisasi permission sistem &amp; modul.</p>
                                        </div>
                                    </a>
                                </div>
                                @endif
                                @if(Route::has('system.menu.index'))
                                <div class="col-md-6 mb-3">
                                    <a href="{{ route('system.menu.index') }}" class="tsu-quick-link-card">
                                        <div class="tsu-quick-icon" style="background: rgba(4, 120, 87, 0.1); color: #047857;">
                                            <i class="fas fa-bars"></i>
                                        </div>
                                        <div>
                                            <h6 class="fw-bold text-dark mb-1" style="font-size: 0.92rem;">Hierarki Menu</h6>
                                            <p class="text-muted mb-0 small">Kelola susunan pohon navigasi sidebar aplikasi.</p>
                                        </div>
                                    </a>
                                </div>
                                @endif
                                @can('system:activity-log:view')
                                    <div class="col-md-6 mb-3">
                                        <a href="{{ route('system.activity-logs.index') }}" class="tsu-quick-link-card">
                                            <div class="tsu-quick-icon" style="background: rgba(13, 148, 136, 0.1); color: #0d9488;">
                                                <i class="fas fa-history"></i>
                                            </div>
                                            <div>
                                                <h6 class="fw-bold text-dark mb-1" style="font-size: 0.92rem;">Audit Aktivitas</h6>
                                                <p class="text-muted mb-0 small">Riwayat aksi, IP pengguna &amp; audit trail sistem.</p>
                                            </div>
                                        </a>
                                    </div>
                                @endcan
                                @can('system:error-log:view')
                                    <div class="col-md-6 mb-3">
                                        <a href="{{ route('system.error-logs.index') }}" class="tsu-quick-link-card">
                                            <div class="tsu-quick-icon" style="background: rgba(225, 29, 72, 0.1); color: #e11d48;">
                                                <i class="fas fa-exclamation-triangle"></i>
                                            </div>
                                            <div>
                                                <h6 class="fw-bold text-dark mb-1" style="font-size: 0.92rem;">Diagnostik Log Error</h6>
                                                <p class="text-muted mb-0 small">Inspeksi error stack trace &amp; kode insiden TSU.</p>
                                            </div>
                                        </a>
                                    </div>
                                @endcan
                            </div>
                        </div>
                    </div>

                    {{-- WIDGET 2: ALUR 5 LANGKAH MEMBUAT FITUR BARU --}}
                    <div class="tsu-card mb-4">
                        <div class="card-header bg-white py-3 px-4 border-bottom d-flex align-items-center justify-content-between">
                            <h6 class="fw-bold mb-0" style="color: var(--tsu-primary-dark, #094b54); font-size: 0.95rem;">
                                <i class="fas fa-route me-2 text-warning"></i> Alur 5 Langkah Pembuatan Fitur Baru
                            </h6>
                            <a href="{{ $manualBookUrl }}#crud" class="small text-primary fw-bold">
                                Baca Detail <i class="fas fa-arrow-right ms-1"></i>
                            </a>
                        </div>
                        <div class="card-body p-4">
                            <div class="tsu-step-item">
                                <div class="tsu-step-num">1</div>
                                <div>
                                    <b class="text-dark d-block fw-semibold">Generate Modul Baru</b>
                                    <small class="text-muted">Jalankan <code>php artisan module:make NamaModul</code> untuk membuat folder terisolasi di <code>Modules/</code>, lalu aktifkan dengan <code>php artisan module:enable NamaModul</code>.</small>
                                </div>
                            </div>
                            <div class="tsu-step-item">
                                <div class="tsu-step-num">2</div>
                                <div>
                                    <b class="text-dark d-block fw-semibold">Buat Model &amp; Migrasi Modul</b>
                                    <small class="text-muted">Buat dengan <code>php artisan module:make-migration</code> dan <code>module:make-model</code>, lalu jalankan <code>php artisan migrate</code>.</small>
                                </div>
                            </div>
                            <div class="tsu-step-item">
                                <div class="tsu-step-num">3</div>
                                <div>
                                    <b class="text-dark d-block fw-semibold">Daftarkan Route &amp; Pasang Proteksi Permission</b>
                                    <small class="text-muted">Daftarkan route di <code>Routes/web.php</code>. Pada controller yang <code>extends MiddlewareController</code>, panggil <code>registerPermissions('modul:fitur')</code>.</small>
                                </div>
                            </div>
                            <div class="tsu-step-item">
                                <div class="tsu-step-num">4</div>
                                <div>
                                    <b class="text-dark d-block fw-semibold">Daftarkan Menu Navigasi di Sidebar</b>
                                    <small class="text-muted">Buka <b>System Management &gt; Menus Management</b> (butuh role super admin) untuk menambah entri route &amp; ikon, dan daftarkan juga di <code>MenuSeeder</code> agar ikut terpasang di server.</small>
                                </div>
                            </div>
                            <div class="tsu-step-item">
                                <div class="tsu-step-num">5</div>
                                <div>
                                    <b class="text-dark d-block fw-semibold">Daftarkan Permission &amp; Assign ke Role</b>
                                    <small class="text-muted">Buat izin di <b>Role Permissions</b> lalu centang pada <b>Roles</b>, dan daftarkan juga di <code>$appPermissions</code> pada <code>PermissionSeeder</code> agar ikut terpasang di server.</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- WIDGET 3: BATASAN WAJIB / GUARDRAILS --}}
                    <div class="p-3 rounded shadow-none tsu-guardrail-box">
                        <h6 class="fw-bold mb-2" style="font-size: 0.92rem;">
                            <i class="fas fa-shield-alt me-2 text-danger"></i> Ketentuan &amp; Batasan Pengembang Ekosistem TSU:
                        </h6>
                        <ul class="text-muted small mb-0 ps-3 leading-relaxed">
                            <li><b>Data Pegawai:</b> Tabel <code>data_dosen_tendiks</code> (package <code>pikdi/tsu-karyawan</code>) bersifat <i>Consumer (Read-only)</i>, dilarang membuat master tabel pegawai baru.</li>
                            <li><b>Format Otorisasi:</b> Wajib menggunakan format Spatie <code>modul:fitur:action</code>, hindari hardcoded role check.</li>
                            <li><b>Keamanan Kredensial:</b> Dilarang keras menaruh secret key atau API token di dalam kode yang di-commit ke Git.</li>
                        </ul>
                    </div>

                </div>

                {{-- KOLOM KANAN (4 KOLOM): Status Lingkungan & Pusat Dokumentasi --}}
                <div class="col-lg-4 mb-4">

                    @if(filter_var(config('tsu-core.features.manual_book', true), FILTER_VALIDATE_BOOLEAN))
                        {{-- WIDGET 4: PUSAT BUKU PANDUAN --}}
                        <div class="tsu-card mb-4">
                            <div class="card-header bg-white py-3 px-4 border-bottom">
                                <h6 class="fw-bold mb-0" style="color: var(--tsu-primary-dark, #094b54); font-size: 0.95rem;">
                                    <i class="fas fa-book me-2 text-primary"></i> Pusat Buku Panduan TSU
                                </h6>
                            </div>
                            <div class="card-body p-4">
                                <p class="text-muted small mb-3">
                                    Pelajari standar arsitektur, batasan sistem, dan integrasi API resmi sebelum memulai pengkodean:
                                </p>
                                <div class="mb-3 pb-3 border-bottom">
                                    <a href="{{ $manualBookUrl }}" class="d-flex align-items-center text-dark fw-semibold small">
                                        <i class="fas fa-file-alt text-primary me-2 fa-lg"></i>
                                        <span>Buku Panduan Pengembang TSU</span>
                                    </a>
                                    <small class="text-muted d-block mt-1">Instalasi, tutorial pemula, standar CRUD, dan SOP koordinasi.</small>
                                </div>
                                <div class="mb-3 pb-3 border-bottom">
                                    <a href="{{ $manualBookUrl }}#auth" class="d-flex align-items-center text-dark fw-semibold small">
                                        <i class="fas fa-id-card-alt text-info me-2 fa-lg"></i>
                                        <span>Autentikasi, Hak Akses &amp; Menu</span>
                                    </a>
                                    <small class="text-muted d-block mt-1">Login SSO Homebase, role, permission, dan menu sidebar.</small>
                                </div>
                                <div class="mb-3">
                                    <a href="{{ $manualBookUrl }}#ui" class="d-flex align-items-center text-dark fw-semibold small">
                                        <i class="fas fa-palette text-warning me-2 fa-lg"></i>
                                        <span>TSU Global Design System</span>
                                    </a>
                                    <small class="text-muted d-block mt-1">Palet warna, komponen Blade, &amp; gaya tabel.</small>
                                </div>

                                <a href="{{ $manualBookUrl }}" class="btn w-100 tsu-btn-primary-action btn-sm mt-2">
                                    <i class="fas fa-book-open me-1"></i> Buka Buku Panduan Lengkap
                                </a>
                            </div>
                        </div>
                    @endif

                    {{-- WIDGET 5: STATUS KESEHATAN SISTEM (HEALTH CHECK) --}}
                    <div class="tsu-card">
                        <div class="card-header bg-white py-3 px-4 border-bottom">
                            <h6 class="fw-bold mb-0" style="color: var(--tsu-primary-dark, #094b54); font-size: 0.95rem;">
                                <i class="fas fa-heartbeat me-2 text-danger"></i> Status Lingkungan &amp; Server
                            </h6>
                        </div>
                        <div class="card-body p-4">
                            {{-- Database --}}
                            <div class="d-flex align-items-center justify-content-between py-2 border-bottom">
                                <span class="text-muted small fw-semibold">Koneksi Database</span>
                                @if($dbConnected)
                                    <span class="badge text-bg-success px-2 py-1" style="font-size: 0.72rem;"><i class="fas fa-check-circle me-1"></i> Terhubung</span>
                                @else
                                    <span class="badge text-bg-danger px-2 py-1" style="font-size: 0.72rem;"><i class="fas fa-times-circle me-1"></i> Gagal</span>
                                @endif
                            </div>

                            {{-- SSO Homebase --}}
                            <div class="d-flex align-items-center justify-content-between py-2 border-bottom">
                                <span class="text-muted small fw-semibold">SSO OAuth Homebase</span>
                                @if($ssoConfigured)
                                    <span class="badge text-bg-success px-2 py-1" style="font-size: 0.72rem;"><i class="fas fa-check-circle me-1"></i> Terkonfigurasi</span>
                                @else
                                    <span class="badge text-bg-warning px-2 py-1" style="font-size: 0.72rem;"><i class="fas fa-exclamation-circle me-1"></i> Rescue Mode</span>
                                @endif
                            </div>

                            {{-- API Sync HRIS --}}
                            <div class="d-flex align-items-center justify-content-between py-2 border-bottom">
                                <span class="text-muted small fw-semibold">HRIS Data Sync</span>
                                @if($hrisConfigured)
                                    <span class="badge text-bg-info px-2 py-1" style="font-size: 0.72rem;"><i class="fas fa-sync-alt me-1"></i> API Ready</span>
                                @else
                                    <span class="badge text-bg-secondary px-2 py-1" style="font-size: 0.72rem;">Belum Diatur</span>
                                @endif
                            </div>

                            {{-- Environment & Framework --}}
                            <div class="d-flex align-items-center justify-content-between py-2 border-bottom">
                                <span class="text-muted small fw-semibold">Framework</span>
                                <span class="text-dark small fw-bold">Laravel {{ app()->version() }}</span>
                            </div>
                            <div class="d-flex align-items-center justify-content-between pt-2">
                                <span class="text-muted small fw-semibold">PHP Environment</span>
                                <span class="text-dark small fw-bold">PHP {{ PHP_VERSION }}</span>
                            </div>
                        </div>
                    </div>

                </div>

            </div>

        </div>
    </section>
@endsection
