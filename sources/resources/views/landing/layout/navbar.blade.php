<!-- Modern TSU Landing Navbar -->
<nav class="tsu-landing-navbar">
    {{-- Tiga kolom: logo | tautan | aksi. Logo dan aksi sama-sama flex 1 1 0 (lebar sama) sehingga tautan di tengah
         benar-benar rata tengah halaman, bukan hanya di tengah sisa ruang antara logo dan aksi. --}}
    <div class="container d-flex align-items-center">
        
        <!-- Brand Identity -->
        <div class="d-flex align-items-center justify-content-start" style="flex: 1 1 0;">
            <a href="{{ route('indexing') }}" class="tsu-landing-brand">
                <img src="{{ tsu_asset('tsu/img/Logo_TSU_Transparan.png') }}" alt="Logo TSU">
            </a>
        </div>

        @php
            $isDemoLandingNav = filter_var(config('tsu-core.features.demo_landing_page', true), FILTER_VALIDATE_BOOLEAN);
            $isManualBookNav = filter_var(config('tsu-core.features.manual_book', true), FILTER_VALIDATE_BOOLEAN);
        @endphp
        <!-- Navigation Links (Centered / Minimalist) -->
        <ul class="tsu-landing-nav-links" style="flex: 0 0 auto;">
            <li>
                <a href="#hero" class="tsu-landing-nav-link active">Beranda</a>
            </li>
            @if($isDemoLandingNav)
                <li>
                    <a href="#fitur" class="tsu-landing-nav-link">Fitur & Layanan</a>
                </li>
                <li>
                    <a href="#bantuan" class="tsu-landing-nav-link">Bantuan Akun</a>
                </li>
            @endif
            @if($isManualBookNav)
                <li>
                    <a href="{{ config('tsu-core.manual_book_url') ?: route('panduan') }}" class="tsu-landing-nav-link" style="color: #0d8390; font-weight: 600;">
                        <i class="fas fa-book-reader me-1 fa-xs"></i> Panduan
                    </a>
                </li>
            @endif
        </ul>

        <!-- Right Side Actions: Theme & SSO Login CTA -->
        <div class="d-flex align-items-center justify-content-end" style="gap: 0.85rem; flex: 1 1 0;">
            
            <!-- Adaptive Theme Switcher -->
            <div class="dropdown">
                <button class="tsu-landing-theme-btn dropdown-toggle" type="button" id="landingThemeDropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" title="Ubah Tema Tampilan">
                    <i class="fas fa-palette me-1 text-teal"></i>
                    <span id="tsu-landing-theme-label" class="d-none d-sm-inline">Tema</span>
                </button>
                <div class="dropdown-menu dropdown-menu-end tsu-theme-menu border-0 shadow-lg" aria-labelledby="landingThemeDropdown">
                    <div class="dropdown-header small fw-bold text-uppercase px-3 py-1" style="font-size: 0.68rem; letter-spacing: 0.5px; opacity: 0.7;">
                        Mode Tampilan
                    </div>
                    <a class="dropdown-item tsu-theme-item" href="javascript:void(0)" data-theme="light">
                        <i class="fas fa-sun text-warning me-2 fa-fw"></i>
                        <span>Terang</span>
                        <i class="fas fa-check text-success ms-auto tsu-theme-check" data-for="light" style="display: none; font-size: 0.75rem;"></i>
                    </a>
                    <a class="dropdown-item tsu-theme-item" href="javascript:void(0)" data-theme="dark">
                        <i class="fas fa-moon text-info me-2 fa-fw"></i>
                        <span>Gelap</span>
                        <i class="fas fa-check text-success ms-auto tsu-theme-check" data-for="dark" style="display: none; font-size: 0.75rem;"></i>
                    </a>
                    <div class="dropdown-divider my-1"></div>
                    <a class="dropdown-item tsu-theme-item" href="javascript:void(0)" data-theme="system">
                        <i class="fas fa-desktop text-secondary me-2 fa-fw"></i>
                        <span>Otomatis</span>
                        <i class="fas fa-check text-success ms-auto tsu-theme-check" data-for="system" style="display: none; font-size: 0.75rem;"></i>
                    </a>
                </div>
            </div>

            <!-- SSO Login Button -->
            <a href="{{ route('login') }}" class="tsu-landing-btn-login">
                <i class="fas fa-fingerprint"></i>
                <span>Masuk SSO</span>
            </a>

        </div>

    </div>
</nav>
