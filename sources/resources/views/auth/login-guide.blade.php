@extends('tsu-core::template.layout.masterlogin')
@section('title', $title)

@section('link_href')
    <style>
        .tsu-login-guide {
            max-width: 720px;
            margin: 0 auto;
            border-radius: 16px;
            border: 0;
        }
        .tsu-login-guide pre {
            background: #0b1120;
            color: #e2e8f0;
            border-radius: 8px;
            padding: 0.75rem 1rem;
            font-size: 0.82rem;
            white-space: pre-wrap;
        }
        .tsu-login-guide .tsu-login-guide__option {
            border-left: 4px solid var(--tsu-primary, #094b54);
            padding-left: 1rem;
            margin-bottom: 1.5rem;
        }
        body.dark-mode .tsu-login-guide {
            background: var(--tsu-card-dark, #131d31);
            color: #e2e8f0;
        }
    </style>
@endsection

@section('content')
    <div class="card shadow-lg tsu-login-guide">
        <div class="card-body p-4 p-md-5">
            <div class="d-flex align-items-center mb-3">
                <img src="{{ tsu_asset('tsu/img/logotsu.png') }}" alt="Logo TSU" style="height: 40px;" class="me-3">
                <div>
                    <h4 class="fw-bold mb-0">Login belum tersedia</h4>
                    <small class="text-muted">{{ tsu_module_title() }}</small>
                </div>
            </div>

            <p class="mb-4">
                Proyek ini memakai <code>tsu-project-template</code> tanpa paket autentikasi.
                Pilih salah satu cara berikut untuk menyediakan halaman login:
            </p>

            <div class="tsu-login-guide__option">
                <h6 class="fw-bold"><i class="fas fa-university me-1"></i> 1. Login TSU (disarankan untuk lingkungan TSU)</h6>
                <p class="small mb-2">
                    Pasang <code>pikdi/tsu-foundation</code>: SSO Homebase, login lokal, Emergency &amp; Rescue Login,
                    manajemen pengguna, role, menu, log aktivitas, dan notifikasi.
                </p>
<pre>composer require pikdi/tsu-foundation
php artisan tsu:install --migrate --seed</pre>
            </div>

            <div class="tsu-login-guide__option">
                <h6 class="fw-bold"><i class="fas fa-code me-1"></i> 2. Autentikasi sendiri</h6>
                <p class="small mb-0">
                    Gunakan paket autentikasi Laravel pilihan Anda (mis. Laravel Breeze atau Fortify) yang menyediakan
                    route bernama <code>login</code>, lalu hapus blok <code>LoginGuideController</code> di
                    <code>routes/web.php</code>.
                </p>
            </div>

            <div class="d-flex flex-wrap" style="gap: 10px;">
                <a href="{{ route('indexing') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Kembali ke Beranda
                </a>
                @if(Route::has('panduan') && filter_var(config('tsu-core.features.manual_book', true), FILTER_VALIDATE_BOOLEAN))
                    <a href="{{ route('panduan') }}" class="btn btn-sm tsu-btn-primary-action">
                        <i class="fas fa-book-open me-1"></i> Buka Buku Panduan
                    </a>
                @endif
            </div>
        </div>
    </div>
@endsection
