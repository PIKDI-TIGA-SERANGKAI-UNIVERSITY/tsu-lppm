@extends('landing.layout.master')
@section('title', 'Beranda - ' . $moduleFullName)

@section('content')
    @php
        $moduleFullName = $moduleFullName ?? tsu_module_title();
        $homebaseUrl = tsu_homebase_url();
        $isManualBookEnabled = filter_var(config('tsu-core.features.manual_book', true), FILTER_VALIDATE_BOOLEAN);
        $manualBookUrl = config('tsu-core.manual_book_url') ?: route('panduan');
    @endphp

    <!-- HERO SECTION (BLANK STARTER CANVAS) -->
    <section class="tsu-landing-hero" id="hero">
        
        <!-- Ambient Parallax Orbs -->
        <div class="tsu-landing-ambient" id="tsu-hero-ambient" aria-hidden="true">
            <div class="tsu-hero-orb tsu-hero-orb-1" data-speed="0.04"></div>
            <div class="tsu-hero-orb tsu-hero-orb-2" data-speed="-0.03"></div>
            <div class="tsu-hero-orb tsu-hero-orb-3" data-speed="0.02"></div>
        </div>

        <div class="container">
            <div class="tsu-hero-content">
                
                <div class="tsu-landing-pill">
                    <span class="tsu-pulse-dot"></span>
                    <span>KANVAS AWAL LANDING PAGE</span>
                </div>

                <h1 class="tsu-hero-heading">
                    Selamat Datang di Portal <br>
                    <span class="highlight">{{ $moduleFullName }}</span>
                </h1>

                <p class="tsu-hero-desc">
                    Halaman ini adalah kanvas kerja awal yang bersih untuk modul <b>{{ $moduleFullName }}</b>. Anda dapat langsung merancang antarmuka publik, katalog layanan, pengumuman, atau formulir sesuai kebutuhan domain aplikasi Anda.
                </p>

                <div class="tsu-hero-actions">
                    <a href="{{ route('login') }}" class="tsu-hero-btn-primary">
                        <i class="fas fa-fingerprint"></i>
                        <span>Masuk Portal SSO</span>
                        <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                    @if($isManualBookEnabled)
                        <a href="{{ $manualBookUrl }}" class="tsu-hero-btn-secondary">
                            <i class="fas fa-book-reader"></i>
                            <span>Buku Panduan TSU</span>
                        </a>
                    @endif
                </div>

                <!-- DEVELOPER CANVAS GUIDE BOX -->
                <div class="mt-4 pt-3 mx-auto" style="max-width: 680px; width: 100%;">
                    <div class="tsu-landing-dev-box text-start">
                        
                        <div class="d-flex align-items-center mb-2">
                            <i class="fas fa-code me-2" style="font-size: 1.1rem; color: #1d7a87;"></i>
                            <span class="fw-bold" style="font-size: 0.88rem; color: var(--tsu-primary-dark, #094b54);">
                                File Tampilan Landing Page Ini:
                            </span>
                        </div>
                        
                        <code class="tsu-landing-dev-code mb-3">
                            sources/resources/views/landing/blank.blade.php
                        </code>

                        <div class="small text-muted mb-2" style="font-size: 0.8rem; line-height: 1.5;">
                            <i class="fas fa-lightbulb text-warning me-1"></i>
                            <b>Tips Landing Page Demo:</b> Untuk kembali melihat referensi demo lengkap (fitur, showcase, bantuan akun), atur <code>FEATURE_DEMO_LANDING_PAGE=true</code> di file <code>.env</code>.
                        </div>

                        <div class="small text-muted" style="font-size: 0.8rem; line-height: 1.5;">
                            <i class="fas fa-toggle-on text-success me-1"></i>
                            <b>Tips Buku Panduan:</b> Untuk menyembunyikan tombol panduan saat aplikasi siap rilis, atur <code>FEATURE_MANUAL_BOOK=false</code> di file <code>.env</code>.
                        </div>

                        <hr class="my-3" style="opacity: 0.15;">

                        <div class="d-flex flex-wrap align-items-center justify-content-between" style="gap: 8px;">
                            <span class="text-muted small" style="font-size: 0.76rem;">
                                <i class="fas fa-shield-alt me-1 text-teal"></i> Ekosistem Tiga Serangkai University
                            </span>
                            <div>
                                <a href="{{ route('login') }}" class="btn btn-sm btn-primary px-3 py-1 fw-bold" style="font-size: 0.78rem; background: #094b54; border-color: #094b54; border-radius: 6px;">
                                    <i class="fas fa-sign-in-alt me-1"></i> Login Pengguna
                                </a>
                            </div>
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </section>
@endsection