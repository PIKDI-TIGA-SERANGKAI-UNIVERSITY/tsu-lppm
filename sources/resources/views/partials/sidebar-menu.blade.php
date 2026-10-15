{{--
    Menu sidebar statis (dipakai bila pikdi/tsu-foundation tidak terpasang).
    Dengan tsu-foundation, menu dikelola dinamis lewat System Management > Menus Management.
    Tambahkan menu modul Anda di sini: route => [label, ikon].
--}}
@php
    $staticMenus = [
        'admin.dashboard' => ['Dashboard', 'fas fa-tachometer-alt'],
        'panduan'         => ['Buku Panduan', 'fas fa-book-open'],
    ];
@endphp

@foreach ($staticMenus as $route => [$label, $icon])
    @if (Route::has($route))
        <li class="nav-item">
            <a href="{{ route($route) }}" class="nav-link {{ request()->routeIs($route . '*') ? 'active' : '' }}">
                <i class="nav-icon {{ $icon }}"></i>
                <p>{{ $label }}</p>
            </a>
        </li>
    @endif
@endforeach
