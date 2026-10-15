@extends('tsu-core::template.admin.header')
@section('title', 'Dashboard')

@section('content')
    {{-- Header Halaman Terpadu TSU --}}
    <x-tsu-page-header 
        title="Dashboard {{ tsu_module_title() }}" 
        icon="fas fa-home" 
        :breadcrumb="true"
    >
        <x-slot name="actions">
            @if(filter_var(config('tsu-core.features.manual_book', true), FILTER_VALIDATE_BOOLEAN))
                <a href="{{ $manualBookUrl ?? (config('tsu-core.manual_book_url') ?: route('panduan')) }}" 
                   class="btn btn-sm btn-light fw-bold shadow-sm me-2" 
                   style="color: var(--tsu-primary-dark, #094b54); border: 1px solid rgba(9, 75, 84, 0.2); border-radius: 8px;">
                    <i class="fas fa-book-reader me-1 text-primary"></i> Buku Panduan TSU
                </a>
            @endif
            <button type="button" class="btn btn-sm tsu-btn-reload" onclick="window.location.reload();" title="Muat Ulang Halaman">
                <i class="fas fa-sync-alt me-1"></i> Refresh
            </button>
        </x-slot>
    </x-tsu-page-header>

    {{-- Main Content Canvas --}}
    <section class="app-content">
        <div class="container-fluid">

            {{-- STARTER BLANK HERO CARD --}}
            <div class="tsu-card border-0 mb-4" style="border-top: 3px solid var(--tsu-gold, #f8c12a) !important;">
                <div class="card-body p-4 p-md-5 text-center">
                    <div class="mb-3">
                        <span class="d-inline-flex align-items-center justify-content-center" 
                              style="width: 72px; height: 72px; border-radius: 18px; background: rgba(9, 75, 84, 0.1); color: var(--tsu-primary, #094b54); font-size: 2rem;">
                            <i class="fas fa-layer-group"></i>
                        </span>
                    </div>

                    <h4 class="fw-bold mb-2" style="letter-spacing: -0.01em;">
                        Kanvas Dashboard Siap Digunakan
                    </h4>
                    
                    <p class="text-muted mx-auto mb-4" style="max-width: 620px; font-size: 0.95rem; line-height: 1.6;">
                        Selamat datang di modul <b>{{ tsu_module_title() }}</b>! 
                        Halaman ini adalah kanvas kerja awal yang bersih. Anda dapat langsung menambahkan kartu statistik (*stat cards*), grafik analitik, tabel monitoring, atau pintasan aksi sesuai kebutuhan domain bisnis modul Anda.
                    </p>

                    <div class="d-flex flex-wrap justify-content-center align-items-center gap-2 mb-4" style="gap: 10px;">
                        @if(filter_var(config('tsu-core.features.manual_book', true), FILTER_VALIDATE_BOOLEAN))
                            <a href="{{ $manualBookUrl ?? (config('tsu-core.manual_book_url') ?: route('panduan')) }}" class="btn btn-sm btn-outline-info px-3 py-2" style="border-radius: 6px;">
                                <i class="fas fa-book-open me-1"></i> Baca Buku Panduan TSU
                            </a>
                        @endif
                        @if(Route::has('system.menu.index'))
                        <a href="{{ route('system.menu.index') }}" class="btn btn-sm tsu-btn-primary-action px-3 py-2">
                            <i class="fas fa-bars me-1"></i> Kelola Menu Navigasi
                        </a>
                        @endif
                        @if(Route::has('system.role.index'))
                        <a href="{{ route('system.role.index') }}" class="btn btn-sm btn-outline-warning px-3 py-2" style="border-radius: 6px;">
                            <i class="fas fa-shield-alt me-1"></i> Atur Hak Akses (Role)
                        </a>
                        @endif
                        @if(Route::has('users.user.index'))
                        <a href="{{ route('users.user.index') }}" class="btn btn-sm btn-outline-secondary px-3 py-2" style="border-radius: 6px;">
                            <i class="fas fa-users me-1"></i> Manajemen Pengguna
                        </a>
                        @endif
                    </div>

                    {{-- Panduan Lokasi File & Tips Toggle untuk Developer --}}
                    <div class="d-inline-block text-start p-3 rounded" 
                         style="background: rgba(0, 0, 0, 0.03); border: 1px dashed rgba(0, 0, 0, 0.12); max-width: 600px; width: 100%;">
                        <div class="small text-muted d-flex align-items-center mb-1">
                            <i class="fas fa-code text-info me-2"></i>
                            <span class="fw-bold">File Tampilan Dashboard Ini:</span>
                        </div>
                        <code class="small text-break" style="font-family: SFMono-Regular, Consolas, monospace; color: var(--tsu-primary-dark, #094b54);">
                            sources/Modules/Admin/Resources/views/dashboard/blank.blade.php
                        </code>
                        <hr class="my-2" style="opacity: 0.15;">
                        <small class="text-muted d-block mb-1" style="font-size: 0.78rem;">
                            <i class="fas fa-lightbulb text-warning me-1"></i>
                            <b>Tips Dashboard Demo:</b> Untuk kembali melihat referensi demo lengkap, atur <code>FEATURE_DEMO_DASHBOARD=true</code> di file <code>.env</code>.
                        </small>
                        <small class="text-muted d-block" style="font-size: 0.78rem;">
                            <i class="fas fa-toggle-on text-success me-1"></i>
                            <b>Tips Buku Panduan:</b> Untuk menyembunyikan tombol buku panduan &amp; ikon navbar saat aplikasi siap rilis, atur <code>FEATURE_MANUAL_BOOK=false</code> di file <code>.env</code>.
                        </small>
                    </div>

                </div>
            </div>

        </div>
    </section>
@endsection
