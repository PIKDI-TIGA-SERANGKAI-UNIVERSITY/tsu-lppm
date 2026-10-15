<!-- Modern TSU Landing Footer Area -->
<footer class="tsu-landing-footer">
    <div class="container">
        <div class="row">
            
            <!-- Kolom 1: Brand & Deskripsi Institusi -->
            <div class="col-lg-5 col-md-6 mb-4 mb-lg-0">
                <a href="{{ route('indexing') }}" class="tsu-landing-brand mb-2">
                    <img src="{{ tsu_asset('tsu/img/Logo_TSU_Transparan.png') }}" alt="Logo TSU" style="height: 42px;">
                    <div class="tsu-landing-brand-text">
                        <span class="tsu-landing-brand-title">Tiga Serangkai University</span>
                        <span class="tsu-landing-brand-sub">{{ tsu_module_title() }}</span>
                    </div>
                </a>
                <p class="tsu-footer-brand-desc">
                    Mewujudkan ekosistem digital kampus yang unggul, terintegrasi, dan aman untuk mendukung seluruh aktivitas sivitas akademika Tiga Serangkai University.
                </p>
            </div>

            <!-- Kolom 2: Navigasi Cepat -->
            @php
                $isDemoLandingFooter = filter_var(config('tsu-core.features.demo_landing_page', true), FILTER_VALIDATE_BOOLEAN);
                $isManualBookFooter = filter_var(config('tsu-core.features.manual_book', true), FILTER_VALIDATE_BOOLEAN);
            @endphp
            <div class="col-lg-3 col-md-6 mb-4 mb-lg-0">
                <h6 class="tsu-footer-heading">Navigasi Portal</h6>
                <ul class="tsu-footer-links">
                    <li><a href="{{ route('indexing') }}#hero"><i class="fas fa-angle-right me-1 fa-xs text-muted"></i> Beranda</a></li>
                    @if($isDemoLandingFooter)
                        <li><a href="#fitur"><i class="fas fa-angle-right me-1 fa-xs text-muted"></i> Fitur & Layanan</a></li>
                        <li><a href="#bantuan"><i class="fas fa-angle-right me-1 fa-xs text-muted"></i> Bantuan Akun & Sandi</a></li>
                    @endif
                    @if($isManualBookFooter)
                        <li><a href="{{ config('tsu-core.manual_book_url') ?: route('panduan') }}"><i class="fas fa-book-reader me-1 fa-xs text-teal"></i> Panduan Pengembang</a></li>
                    @endif
                    <li><a href="{{ route('login') }}"><i class="fas fa-angle-right me-1 fa-xs text-muted"></i> Masuk SSO Homebase</a></li>
                </ul>
            </div>

            <!-- Kolom 3: Layanan Ekosistem TSU -->
            <div class="col-lg-4 col-md-12">
                <h6 class="tsu-footer-heading">Ekosistem Kampus</h6>
                <ul class="tsu-footer-links">
                    <li>
                        <a href="{{ tsu_homebase_url() }}" target="_blank" rel="noopener noreferrer">
                            <i class="fas fa-external-link-alt me-1 fa-xs text-muted"></i> TSU Homebase Portal
                        </a>
                    </li>
                    <li>
                        <a href="{{ tsu_homebase_url() }}/forgot-password" target="_blank" rel="noopener noreferrer" title="Layanan Pemulihan Kata Sandi Resmi di TSU Homebase (Bagi akun yang sedang login di Homebase, Anda akan diarahkan ke Dashboard/Profil)">
                            <i class="fas fa-key me-1 fa-xs text-muted"></i> Layanan Pemulihan Kata Sandi
                        </a>
                    </li>
                    <li>
                        <a href="{{ tsu_pikdi_whatsapp_url() }}" target="_blank" rel="noopener">
                            <i class="fab fa-whatsapp me-1 fa-xs text-muted"></i> Helpdesk PIKDI {{ tsu_pikdi_contact()['whatsapp_display'] }}
                        </a>
                    </li>
                    <li>
                        <a href="{{ tsu_pikdi_mailto() }}">
                            <i class="fas fa-envelope me-1 fa-xs text-muted"></i> {{ tsu_pikdi_contact()['email'] }}
                        </a>
                    </li>
                </ul>
            </div>

        </div>

        <!-- Footer Bottom Bar -->
        <div class="tsu-footer-bottom">
            <span>&copy; {{ date('Y') }} <b>Tiga Serangkai University</b>. Hak Cipta Dilindungi.</span>
            <span><i class="fas fa-code me-1 text-teal"></i> Dikembangkan oleh PIKDI Developer Team</span>
        </div>
    </div>
</footer>

<script>
    @if (Session::has('alert'))
        Swal.fire('{{ session('alert')['title'] }}', '{{ session('alert')['message'] }}', '{{ session('alert')['status'] }}')
    @endif
</script>
@include('tsu-core::components.alert')
@yield('script')
