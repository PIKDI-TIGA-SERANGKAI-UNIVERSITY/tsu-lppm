<!DOCTYPE html>
<html lang="id" data-lte-color-mode="off">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no"/>
    <link rel="icon" href="{{ tsu_asset('tsu/img/logotsu.png') }}" type="image/png"/>
    <title>TSU - {{ $title ?? 'Tiga Serangkai University' }}</title>

    <!-- Google Fonts: Inter & Outfit -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@500;600;700;800&display=swap" rel="stylesheet">

    <!-- Bootstrap CSS -->
    <!-- AdminLTE 4 (sudah termasuk Bootstrap 5.3) -->
    <link rel="stylesheet" href="{{ tsu_asset('adminlte/dist/css/adminlte.min.css') }}">
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="{{ tsu_asset('adminlte/plugins/fontawesome-free-7.1.0-web/css/all.min.css') }}">
    <!-- SweetAlert 2 -->
    <link rel="stylesheet" href="{{ tsu_asset('adminlte/plugins/sweetalert2/sweetalert2.min.css') }}">
    <script src="{{ tsu_asset('adminlte/plugins/sweetalert2/sweetalert2.all.min.js') }}"></script>

    <!-- TSU Unified Design System & Theme CSS -->
    <link rel="stylesheet" href="{{ tsu_asset('tsu/css/tsu-theme.css') }}">

    <!-- Anti-FOUC Theme Initializer -->
    <script>
        (function() {
            try {
                var pref = localStorage.getItem('tsu_theme');
                if (!pref) {
                    var matchPref = document.cookie.match(/(^|; )tsu_theme_pref=([^;]+)/);
                    if (matchPref) pref = matchPref[2];
                }
                if (!pref) pref = 'system';

                var isDark = false;
                if (pref === 'dark') {
                    isDark = true;
                } else if (pref === 'light') {
                    isDark = false;
                } else {
                    isDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
                }

                if (isDark) {
                    document.documentElement.classList.add('dark-mode'); document.documentElement.setAttribute('data-bs-theme', 'dark');
                    document.addEventListener('DOMContentLoaded', function() {
                        if (document.body) document.body.classList.add('dark-mode');
                    });
                } else {
                    document.documentElement.classList.remove('dark-mode'); document.documentElement.setAttribute('data-bs-theme', 'light');
                    document.addEventListener('DOMContentLoaded', function() {
                        if (document.body) document.body.classList.remove('dark-mode');
                    });
                }
            } catch(e) {}
        })();
    </script>

    @yield('link_href')
</head>

<body class="{{ request()->cookie('tsu_theme') === 'dark' ? 'dark-mode' : '' }}">
    <div class="tsu-landing-wrapper">
        @include('landing.layout.navbar')

        @yield('content')

        @include('landing.layout.footer')
    </div>

    <!-- REQUIRED SCRIPTS -->
    <script src="{{ tsu_asset('adminlte/plugins/jquery/jquery.min.js') }}"></script>
    <script src="{{ tsu_asset('adminlte/plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>

    <!-- Ambient Hero Parallax Script (Passive & Hardware Accelerated) -->
    <script>
        (function() {
            var ambient = document.getElementById('tsu-hero-ambient');
            if (!ambient || window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

            var orbs = ambient.querySelectorAll('.tsu-hero-orb');
            var targetX = 0, targetY = 0;
            var currentX = 0, currentY = 0;
            var ticking = false;

            window.addEventListener('mousemove', function(e) {
                var windowWidth = window.innerWidth;
                var windowHeight = window.innerHeight;
                targetX = (e.clientX - windowWidth / 2) / (windowWidth / 2);
                targetY = (e.clientY - windowHeight / 2) / (windowHeight / 2);

                if (!ticking) {
                    requestAnimationFrame(updateHeroOrbs);
                    ticking = true;
                }
            }, { passive: true });

            function updateHeroOrbs() {
                currentX += (targetX - currentX) * 0.04;
                currentY += (targetY - currentY) * 0.04;

                orbs.forEach(function(orb) {
                    var speed = parseFloat(orb.getAttribute('data-speed')) || 0.03;
                    var moveX = currentX * 100 * speed * 25;
                    var moveY = currentY * 100 * speed * 25;
                    orb.style.transform = 'translate3d(' + moveX.toFixed(2) + 'px, ' + moveY.toFixed(2) + 'px, 0)';
                });

                if (Math.abs(targetX - currentX) > 0.001 || Math.abs(targetY - currentY) > 0.001) {
                    requestAnimationFrame(updateHeroOrbs);
                } else {
                    ticking = false;
                }
            }
        })();
    </script>

    <!-- Theme Switcher Runtime Script -->
    <script>
        (function() {
            function applyTheme(pref, save) {
                var effectiveDark = false;
                if (pref === 'dark') {
                    effectiveDark = true;
                } else if (pref === 'light') {
                    effectiveDark = false;
                } else {
                    effectiveDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
                }

                if (effectiveDark) {
                    document.documentElement.classList.add('dark-mode'); document.documentElement.setAttribute('data-bs-theme', 'dark');
                    if (document.body) document.body.classList.add('dark-mode');
                } else {
                    document.documentElement.classList.remove('dark-mode'); document.documentElement.setAttribute('data-bs-theme', 'light');
                    if (document.body) document.body.classList.remove('dark-mode');
                }

                // Update checks & labels
                document.querySelectorAll('.tsu-theme-check').forEach(function(chk) {
                    if (chk.getAttribute('data-for') === pref) {
                        chk.style.display = 'inline-block';
                    } else {
                        chk.style.display = 'none';
                    }
                });

                // Update active item class
                document.querySelectorAll('.tsu-theme-item').forEach(function(item) {
                    if (item.getAttribute('data-theme') === pref) {
                        item.classList.add('active');
                    } else {
                        item.classList.remove('active');
                    }
                });

                var labelMap = { light: 'Terang', dark: 'Gelap', system: 'Otomatis' };
                var labelEl = document.getElementById('tsu-landing-theme-label');
                if (labelEl) {
                    labelEl.textContent = labelMap[pref] || 'Tema';
                }

                if (save) {
                    try {
                        localStorage.setItem('tsu_theme', pref);
                        var cookieTheme = effectiveDark ? 'dark' : 'light';
                        document.cookie = 'tsu_theme=' + cookieTheme + ';path=/;max-age=' + (60 * 60 * 24 * 365);
                        document.cookie = 'tsu_theme_pref=' + pref + ';path=/;max-age=' + (60 * 60 * 24 * 365);
                    } catch(e) {}
                }
            }

            var currentPref = localStorage.getItem('tsu_theme');
            if (!currentPref) {
                var match = document.cookie.match(/(^|; )tsu_theme_pref=([^;]+)/);
                if (match) currentPref = match[2];
            }
            if (!currentPref) {
                currentPref = 'system';
            }

            applyTheme(currentPref, false);

            document.querySelectorAll('.tsu-theme-item').forEach(function(item) {
                item.addEventListener('click', function(e) {
                    e.preventDefault();
                    var selectedTheme = this.getAttribute('data-theme');
                    applyTheme(selectedTheme, true);
                });
            });

            if (window.matchMedia) {
                window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', function() {
                    var pref = localStorage.getItem('tsu_theme') || 'system';
                    if (pref === 'system') {
                        applyTheme('system', false);
                    }
                });
            }
        })();
    </script>

    <!-- Sticky Navbar Elevation & Smooth Scroll Script -->
    <script>
        (function() {
            var navbar = document.querySelector('.tsu-landing-navbar');
            
            // 1. Sticky Navbar scroll effect (menambahkan bayangan & elevasi saat scroll)
            function updateNavbarScroll() {
                if (!navbar) return;
                if (window.pageYOffset > 15) {
                    navbar.classList.add('tsu-navbar-scrolled');
                } else {
                    navbar.classList.remove('tsu-navbar-scrolled');
                }
            }
            window.addEventListener('scroll', updateNavbarScroll, { passive: true });
            updateNavbarScroll();

            // 2. Smooth Scroll untuk seluruh link anchor internal (#hero, #fitur, #bantuan, dll.)
            document.querySelectorAll('a[href^="#"], a[href*="#"]').forEach(function(anchor) {
                anchor.addEventListener('click', function(e) {
                    var href = this.getAttribute('href');
                    if (!href) return;
                    
                    var hash = '';
                    if (href.startsWith('#')) {
                        hash = href;
                    } else if (href.indexOf('#') !== -1) {
                        var parts = href.split('#');
                        var path = parts[0];
                        hash = '#' + parts[1];
                        var currentPath = window.location.pathname;
                        if (path && path !== currentPath && path !== '' && path !== window.location.href.split('#')[0]) {
                            return;
                        }
                    }

                    if (hash === '#' || hash === '#0' || hash === '') return;

                    var targetEl = document.querySelector(hash);
                    if (targetEl) {
                        e.preventDefault();
                        var navHeight = navbar ? navbar.offsetHeight : 70;
                        var targetTop = targetEl.getBoundingClientRect().top + window.pageYOffset - (navHeight + 10);

                        window.scrollTo({
                            top: targetTop,
                            behavior: 'smooth'
                        });

                        if (history.pushState) {
                            history.pushState(null, null, hash);
                        }
                    }
                });
            });

            // 3. ScrollSpy: Update active nav link secara dinamis saat halaman di-scroll
            var sections = document.querySelectorAll('section[id], div[id].tsu-landing-hero');
            var navLinks = document.querySelectorAll('.tsu-landing-nav-link[href*="#"]');

            function updateScrollSpy() {
                var scrollPosition = window.pageYOffset;
                var navHeight = navbar ? navbar.offsetHeight : 70;

                sections.forEach(function(section) {
                    var sectionTop = section.offsetTop - (navHeight + 80);
                    var sectionHeight = section.offsetHeight;
                    var sectionId = section.getAttribute('id');

                    if (scrollPosition >= sectionTop && scrollPosition < sectionTop + sectionHeight) {
                        navLinks.forEach(function(link) {
                            var linkHref = link.getAttribute('href');
                            if (linkHref && linkHref.endsWith('#' + sectionId)) {
                                link.classList.add('active');
                            } else if (linkHref && linkHref.startsWith('#')) {
                                link.classList.remove('active');
                            }
                        });
                    }
                });
            }
            window.addEventListener('scroll', updateScrollSpy, { passive: true });
            updateScrollSpy();
        })();
    </script>

    @include('tsu-core::components.alert')
    @yield('script')
</body>
</html>
