@extends('landing.layout.master')
@section('title', 'Beranda - Tiga Serangkai University')

@section('content')
    @php
        $moduleFullName = $moduleFullName ?? tsu_module_title();
        $homebaseUrl = tsu_homebase_url();
    @endphp

    <!-- HERO SECTION (MINIMALIST & INFORMATIVE WITH PARALLAX) -->
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
                    <span>MODUL RESMI EKOSISTEM TSU</span>
                </div>

                <h1 class="tsu-hero-heading">
                    Portal Layanan Digital Terpadu <br>
                    <span class="highlight">{{ $moduleFullName }}</span>
                </h1>

                <p class="tsu-hero-desc">
                    Platform komprehensif sivitas akademika Tiga Serangkai University. Akses seluruh fitur dan tata kelola sistem melalui satu pintu autentikasi yang aman, cepat, dan terintegrasi.
                </p>

                <div class="tsu-hero-actions">
                    <a href="{{ route('login') }}" class="tsu-hero-btn-primary">
                        <i class="fas fa-fingerprint"></i>
                        <span>Masuk Portal SSO</span>
                        <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                    <a href="#fitur" class="tsu-hero-btn-secondary">
                        <i class="fas fa-layer-group"></i>
                        <span>Pelajari Fitur</span>
                    </a>
                    @if(filter_var(config('tsu-core.features.manual_book', true), FILTER_VALIDATE_BOOLEAN))
                        <a href="{{ config('tsu-core.manual_book_url') ?: route('panduan') }}" class="tsu-hero-btn-secondary" style="border-color: rgba(9, 75, 84, 0.35); color: #094b54;">
                            <i class="fas fa-book-reader"></i>
                            <span>Buku Panduan</span>
                        </a>
                    @endif
                </div>

                <!-- Status Ribbon Bar -->
                <div class="tsu-hero-ribbon">
                    <div class="tsu-ribbon-item">
                        <div class="tsu-ribbon-icon">
                            <i class="fas fa-server"></i>
                        </div>
                        <div class="tsu-ribbon-text">
                            <span class="tsu-ribbon-label">Status Server</span>
                            <span class="tsu-ribbon-val text-success">
                                <i class="fas fa-check-circle fa-xs me-1"></i> Operasional Normal
                            </span>
                        </div>
                    </div>

                    <div class="tsu-ribbon-item">
                        <div class="tsu-ribbon-icon">
                            <i class="fas fa-key"></i>
                        </div>
                        <div class="tsu-ribbon-text">
                            <span class="tsu-ribbon-label">Autentikasi SSO</span>
                            <span class="tsu-ribbon-val">
                                <i class="fas fa-link fa-xs me-1 text-info"></i> TSU Homebase
                            </span>
                        </div>
                    </div>

                    <div class="tsu-ribbon-item">
                        <div class="tsu-ribbon-icon">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <div class="tsu-ribbon-text">
                            <span class="tsu-ribbon-label">Protokol Keamanan</span>
                            <span class="tsu-ribbon-val">
                                <i class="fas fa-lock fa-xs me-1 text-warning"></i> TLS 1.3 & Rate Limit
                            </span>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- SECTION FITUR & LAYANAN UTAMA -->
    <section class="tsu-landing-section" id="fitur">
        <div class="container">
            
            <div class="tsu-section-head">
                <span class="tsu-section-badge">INTEGRASI & LAYANAN</span>
                <h2 class="tsu-section-title">Dirancang untuk Kemudahan & Efisiensi</h2>
                <p class="tsu-section-desc">
                    Mendukung kegiatan sivitas akademika dengan standar performa mutakhir, antarmuka adaptif, dan proteksi kredensial berlapis.
                </p>
            </div>

            <div class="row">
                
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="tsu-feature-card">
                        <div class="tsu-feature-card-icon">
                            <i class="fas fa-fingerprint"></i>
                        </div>
                        <h3 class="tsu-feature-card-title">Single Sign-On (SSO)</h3>
                        <p class="tsu-feature-card-text">
                            Cukup gunakan satu akun terpadu TSU Homebase untuk login tanpa kerepotan menghafal banyak kata sandi di setiap modul.
                        </p>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="tsu-feature-card">
                        <div class="tsu-feature-card-icon">
                            <i class="fas fa-user-shield"></i>
                        </div>
                        <h3 class="tsu-feature-card-title">Hak Akses Granular</h3>
                        <p class="tsu-feature-card-text">
                            Pengaturan peran dan otorisasi berbasis Spatie Permission yang ketat, memisahkan hak akses Dosen, Tendik, dan Mahasiswa.
                        </p>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="tsu-feature-card">
                        <div class="tsu-feature-card-icon">
                            <i class="fas fa-bolt"></i>
                        </div>
                        <h3 class="tsu-feature-card-title">Real-Time Sync</h3>
                        <p class="tsu-feature-card-text">
                            Dukungan WebSocket Laravel Reverb untuk notifikasi instan dan pembaruan status sistem tanpa perlu memuat ulang halaman.
                        </p>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="tsu-feature-card">
                        <div class="tsu-feature-card-icon">
                            <i class="fas fa-moon"></i>
                        </div>
                        <h3 class="tsu-feature-card-title">Adaptive Theme</h3>
                        <p class="tsu-feature-card-text">
                            Tampilan otomatis beradaptasi dengan preferensi Mode Terang atau Gelap perangkat pengguna tanpa kedip layar (Zero-FOUC).
                        </p>
                    </div>
                </div>

            </div>

        </div>
    </section>

    <!-- SECTION BANTUAN AKUN & FORGOT PASSWORD (COVERED BY TSU HOMEBASE) -->
    <section class="tsu-landing-section pt-0" id="bantuan">
        <div class="container">
            
            <div class="tsu-landing-helpbox">
                <div class="tsu-helpbox-content">
                    
                    <div class="tsu-helpbox-info">
                        <h3 class="tsu-helpbox-title">
                            <i class="fas fa-question-circle"></i>
                            Pusat Pengelolaan Akun & Pemulihan Kata Sandi
                        </h3>
                        <p class="tsu-helpbox-desc">
                            Seluruh manajemen akun, reset kata sandi (<i>forgot password</i>), dan pembaruan data profil sivitas akademika dikelola secara terpusat oleh <b>TSU Homebase</b>. Jika Anda lupa kata sandi, silakan gunakan fasilitas pemulihan resmi Homebase (jika sesi Anda di Homebase masih aktif, Anda dapat langsung mengubah sandi di menu Dashboard/Profil).
                        </p>
                    </div>

                    <div class="tsu-helpbox-action">
                        <a href="{{ $homebaseUrl }}/forgot-password" target="_blank" rel="noopener noreferrer" class="btn btn-primary px-4 py-2 fw-bold" style="background: #094b54; border-color: #094b54; border-radius: 8px;" title="Buka Formulir Pemulihan Kata Sandi di TSU Homebase (Bagi akun yang sedang login di Homebase, Anda akan diarahkan ke Dashboard/Profil)">
                            <span>Reset Sandi di Homebase</span>
                            <i class="fas fa-external-link-alt ms-1 fa-xs"></i>
                        </a>
                        <a href="{{ tsu_pikdi_whatsapp_url() }}" target="_blank" rel="noopener" class="btn btn-outline-secondary px-3 py-2 fw-bold" style="border-radius: 8px;" title="Helpdesk PIKDI: WhatsApp {{ tsu_pikdi_contact()['whatsapp_display'] }} · {{ tsu_pikdi_contact()['email'] }}">
                            <i class="fab fa-whatsapp me-1"></i> Bantuan PIKDI
                        </a>
                    </div>

                </div>
            </div>

        </div>
    </section>
@endsection
