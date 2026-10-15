# <p align="center"> TSU Project Template v2.0 <br> (Modular Monolith Enterprise Edition) </p>

<p align="center">
  <img src="https://img.shields.io/badge/TSU_Framework-v2.0_Enterprise-gold?style=for-the-badge&logo=laravel" alt="TSU Framework v2.0">
  <img src="https://img.shields.io/badge/Architecture-Modular_Monolith-blue?style=for-the-badge" alt="Modular Monolith">
  <img src="https://img.shields.io/badge/Documentation-SSOT_Interactive-success?style=for-the-badge" alt="SSOT Documentation">
</p>


## 📢 Description

Template aplikasi berbasis Laravel yang dikustomisasi dengan pendekatan arsitektur *Modular Monolith*. Proyek ini dirancang untuk mengakomodasi kompleksitas sistem informasi akademik dengan memisahkan logika bisnis berdasarkan domain (Modul), bukan hanya berdasarkan lapisan teknis.

## 📋 Project Overview

Proyek ini adalah *boilerplate* yang memindahkan struktur standar Laravel (`app/`) ke dalam direktori kustom `sources/` untuk mendukung skalabilitas jangka panjang.
Aplikasi berjalan dengan **koneksi database langsung (Direct DB)**. Login resmi memakai SSO Homebase TSU lewat paket `pikdi/tsu-foundation` (tersedia juga login lokal untuk pengembangan). Arsitektur kode (Service Layer) telah disiapkan untuk transisi menuju implementasi berbasis API (*API-Driven*) di masa mendatang tanpa perlu merombak struktur utama.

## 🏗️ Struktur Direktori & Arsitektur

Perbedaan mendasar pada template ini adalah lokasi *core logic*. Direktori `sources/` berisi aplikasi Laravel, sedangkan *entry point* `index.php` berada di root repositori (document root web server = root repositori). Fitur inti PIKDI tidak lagi berada di dalam template, melainkan dipasang sebagai package Composer:

```text
root/
├── index.php                 # Entry point aplikasi
├── public/                   # Satu-satunya folder yang boleh diakses langsung dari web
│   ├── vendor/tsu-core/      # Aset UI TSU (AdminLTE, plugin, tsu-theme.css, logo) — otomatis dari pikdi/tsu-core, jangan diedit
│   ├── assets/css/modules/   # CSS khusus modul Anda (lihat custom-example.css)
│   └── storage/              # Junction ke sources/storage/app/public (file upload publik)
└── sources/                  # Aplikasi Laravel (diblokir dari akses web langsung)
    ├── app/                  # Logika global (Model User & Mahasiswa, landing page)
    ├── resources/views/      # Landing page, menu statis
    ├── Modules/
    │   └── Admin/            # Workspace pengembang: Dashboard, modul bisnis Anda
    ├── docs/panduan/         # Bab Buku Panduan milik starter kit ini (dirender di /panduan oleh pikdi/tsu-core)
    └── vendor/pikdi/
        ├── tsu-core/         # Wajib: UI TSU (layout, komponen, aset), halaman error, standar developer
        ├── tsu-foundation/   # Default: 4 login TSU (SSO, lokal, Emergency, Rescue), user, role, menu, log, notifikasi
        └── tsu-karyawan/     # Opsional (tidak terpasang default): direktori dosen & tendik + sinkronisasi HRIS
```

### Kombinasi Paket

| Kombinasi | Cara | Hasil |
|---|---|---|
| **Default** (tsu-core + tsu-foundation) | `composer install` | Login TSU, manajemen user/role/menu, log, notifikasi |
| + Data Karyawan | `composer require pikdi/tsu-karyawan:dev-main`, lalu `php artisan migrate --seed` (menu tampil bila `FEATURE_DATA_KARYAWAN=true` dan `HRIS_SYNC_SECRET` sah) | Menu Data Karyawan & sinkronisasi HRIS |
| + Data Karyawan, tanpa foundation | `composer remove pikdi/tsu-foundation`, lalu `composer require pikdi/tsu-karyawan:dev-main` dan `php artisan migrate` (menu tampil bila `FEATURE_DATA_KARYAWAN=true` dan `HRIS_SYNC_SECRET` sah) | Menu Data Karyawan lewat slot sidebar tsu-core |
| Tanpa login TSU (tsu-core saja) | `composer remove pikdi/tsu-foundation` | Landing page, panduan, dashboard & layout TSU tetap jalan; `/login` menampilkan panduan memasang autentikasi; sidebar memakai menu statis (`resources/views/partials/sidebar-menu.blade.php`) |

Test otomatis menyesuaikan paket yang terpasang (test fitur paket yang tidak terpasang di-skip).

Modul bisnis baru dibuat dengan pola `nwidart/laravel-modules` (`php artisan module:make NamaModul`) agar setiap domain terisolasi dengan baik.

## 🛠️ Spesifikasi Teknis (Tech Stack)

- **Framework Core**: Laravel 13 (PHP 8.3+). Paket `pikdi/tsu-*` mendukung Laravel 11, 12, dan 13.
- **Architecture Pattern**: Modular Monolith
- **Database Interface**: Eloquent ORM & `yajra/laravel-datatables-oracle` (Support MySQL & Oracle)
- **Authentication & Authorization**:
  - Login SSO Homebase TSU, login lokal untuk pengembangan, serta jalur darurat, disediakan paket `pikdi/tsu-foundation` (session-based, dengan pemisahan logika user internal/eksternal).
  - `spatie/laravel-permission` untuk Role & Permission Management (dengan custom dynamic table prefix).
- **Core Enterprise Services & Utilities**:
  - `TsuActivityLogService`: Perekaman jejak audit otomatis (User, Role, IP, User-Agent, Properties) terhubung ke Homebase API Hub (`/api/v1/system/activity-logs`). Menyediakan fitur *Backup & Purge (Darurat)* ke arsip ZIP Dual-Format (Excel + SQL) yang tersimpan secara terkelola di `storage/app/backups/activity-logs/`, terintegrasi dengan modal **Kelola Arsip Backup** (pantau kapasitas disk, unduh ulang berkas kapan saja dari modal maupun notifikasi, serta hapus arsip satuan/semua), dan notifikasi real-time ke akun operator.
  - `TsuFileUploadService`: Pengelolaan unggah berkas bisnis modul aman (perlindungan anti-malware, slug nama unik, dan auto-delete berkas lama saat edit/update). Khusus foto profil pengguna dikelola terpusat dari Homebase/HRIS (*Single Source of Truth*) dengan normalisasi URL otomatis dan mode *Claim Profile & Fill if Present* (anti-wipe foto).
  - `TsuResponseService` & `TsuErrorHandlerService`: Standarisasi respon JSON/Flash message dan mask error sensitif (`[TSU_XXX]`).
  - **Error Log & Diagnostik Sistem (`/system/error-logs`)**: Panel inspeksi visual log Laravel terpadu dengan pembacaan hemat memori (*Memory-Safe Chunking*). Dilengkapi filter multi-level, penandaan otomatis kode insiden `[TSU_XXX_FAIL]` dengan badge emas, collapsible stack trace bergaya terminal gelap, unduh log format Mentah (*Raw*) maupun Saringan (*Filtered*), serta pembersihan log aman (*truncate*) yang dijaga oleh Spatie RBAC (`system:error-log:delete`) dengan notifikasi real-time otomatis.
  - **TSU Karyawan Directory & HRIS Sync (`pikdi/tsu-karyawan`)**: Paket modular resmi penyedia direktori Dosen & Tendik, sinkronisasi aman via token rahasia HMAC dengan TSU HRIS pusat, notifikasi real-time rincian pembaruan pegawai di Kotak Masuk, proteksi *Dual-Condition Gate* (`FEATURE_DATA_KARYAWAN=true` + `HRIS_SYNC_SECRET`), dan perintah CLI `php artisan tsu:sync-karyawan`.
  - **Laravel Reverb (Real-Time WebSockets)**: Kanal notifikasi dan pembaruan data real-time berbasis event broadcast terintegrasi ke Kotak Masuk, Lonceng Navbar, dan Pop-up Toast.
  - **Database & Infra Exception Handler**: Penanganan darurat terpusat saat server database mati atau belum termigrasi dengan tampilan ramah pengguna dan proteksi kebocoran kredensial SQL.
  - **Core Helper & Blade Directives**:
    - `tsu_module_title()`: Pemformat judul modul otomatis bersih tanpa simbol dan Title Case dinamis.
    - `tsu_allowed_roles_text()`: Label dinamis kelompok izin sivitas kampus pada portal autentikasi.
    - Custom Blade Directives: Format instan `@rupiah()`, `@tglIndo()`, `@hari()`, dan `@bulanRomawi()`.
  - **Portal Dokumentasi Interaktif (`/panduan`)**: Mesin dokumentasi milik `pikdi/tsu-core`. Setiap paket (`tsu-core`, `tsu-foundation`, `tsu-karyawan`) dan starter kit ini mendaftarkan bab-babnya sendiri, sehingga panduan selalu sama dengan versi paket yang terpasang. Dilengkapi pencarian instan *Command Palette* (`Ctrl + K`), daftar isi yang mengikuti posisi baca, dan checklist evaluasi kesiapan rilis (*Release Readiness Checklist*).
- **Frontend Stack & UI System**:
  - Blade Templating Engine
  - AdminLTE 4 & Bootstrap 5 Ecosystem
  - AdminLTE Assets & Custom Components
  - **TSU Global Design System (`pikdi/tsu-core`)**:
    - **Master Design System Terpusat**: Token CSS resmi (`tsu-theme.css`), layout admin, dan aset AdminLTE dikelola di package `pikdi/tsu-core`. Aset otomatis di-publish ke `public/vendor/tsu-core` setiap `composer install/update` (manual: `php artisan vendor:publish --tag=tsu-core-assets --force`).
    - **Adaptive Theme System**: Mendukung 3 mode tampilan (Terang/*Light*, Gelap/*Dark*, dan **Sistem/Device Default**) yang otomatis beradaptasi dengan preferensi OS pengguna secara *real-time*, didukung mekanisme Anti-FOUC via Cookie (`tsu_theme`) & `localStorage`.
    - **High-Contrast Notification Inbox**: Penyorotan kontras tinggi untuk baris notifikasi belum dibaca (*unread*) pada Mode Terang (soft-teal `#edf8fa` dengan border pekat) dan Mode Gelap (translucent dark-teal `rgba(14, 165, 180, 0.14)` dengan border neon cyan `#22d3ee` anti-silau).
    - **Arsitektur Loading Modern**: Navigasi instan (0ms latency) dengan *Top Slim Progress Bar* (Gold/Teal) ala GitHub/YouTube yang dilengkapi *download-link awareness* & *watchdog auto-dismiss timer*, *Welcome Transition Splash Screen* 1x pasca-login dengan animasi logo berdenyut, dan utilitas interaksi tombol `TsuLoading.btn()` untuk mencegah submit ganda.
    - **Konsistensi UI DataTables & Alerts**: Standarisasi tombol aksi seragam (*outline buttons* untuk Edit, Hapus, dan Lock), modal adaptif, kartu beraksen emas TSU (*Golden Glow*), proteksi pewarisan warna ikon tombol, dan notifikasi SweetAlert2 Toast dengan bilah hitung mundur (*countdown progress bar*) neon emas yang kontras di Dark Mode.
  - Libraries yang disediakan `pikdi/tsu-core`: jQuery, DataTables (Bootstrap 5), Select2, SweetAlert2, FontAwesome 7. Pustaka lain (misalnya editor teks atau grafik) ditambahkan per proyek.

## 🛣️ Roadmap Pengembangan

Proyek ini dikembangkan dengan peta jalan (roadmap) teknis sebagai berikut:

1. Phase 1 (Current): Struktur Modular Monolith berbasis paket (`tsu-core`, `tsu-foundation`, `tsu-karyawan`), Login SSO Homebase, Koneksi Database Langsung.
2. Phase 2: Refactoring Service Layer untuk persiapan abstraksi data.
3. Phase 3: Transisi ke Arsitektur berbasis API (Headless Readiness).

---

## 🚀 Panduan Memulai Proyek Baru (Khusus Tech Lead / Inisiator)

Untuk membuat aplikasi baru menggunakan template ini, ikuti alur dan SOP baku berikut:

1. **Wajib Gunakan "Use this template" (BUKAN "Fork")**
   - Klik tombol hijau **"Use this template"** -> **"Create a new repository"** di GitHub. Beri nama repository sesuai proyek baru Anda (misal: `tsu-pendaftaran` atau `tsu-perpustakaan`).
   - ⚠️ **Mengapa BUKAN Fork?** Menggunakan template menghasilkan repositori independen dengan riwayat commit (*Git History*) yang bersih dari awal (1 initial commit), mencegah tercampurnya issue/PR template induk, serta mengamankan kredensial rahasia (*environment secrets*) agar tidak bocor ke upstream.
   - Cukup branch **`main`**: jangan centang *Include all branches*. Repo proyek boleh publik karena tidak memuat rahasia (`.env` dan `auth.json` tidak di-commit).
   - **Akses paket:** repo `tsu-core`, `tsu-foundation` (dan `tsu-karyawan` bila dipakai) privat. Akun GitHub setiap pengembang harus diundang PIKDI dengan peran **Read** pada repo paket, selama masa penugasan. Server memakai token baca akun mesin PIKDI.
   - **`composer.lock`:** setelah `composer install` pertama, commit `sources/composer.lock` agar seluruh tim dan server memakai versi paket yang sama.
   - **Nama proyek:** isi `MODULE_NAME` dan `MODULE_FULL_NAME` di `.env` **sebelum** `php artisan migrate --seed`, karena nama role, izin, dan menu dibuat dari nilai itu.

2. **Aturan Baku Modifikasi Core / Vendor (Golden Rule)**
   - 🔴 **DILARANG KERAS mengedit file langsung di dalam folder `vendor/`, termasuk package core PIKDI (`vendor/pikdi/tsu-foundation`, `vendor/pikdi/tsu-karyawan`)**.
   - Setiap perubahan yang dilakukan langsung di dalam `vendor/` **pasti terhapus 100%** saat menjalankan `composer update` atau saat proses auto-deployment di server PIKDI!
   - **Solusi Standar (OOP Extends & View Override)**:
     - Jika butuh memodifikasi logika Controller/Service bawaan, buat class turunan (*extends*) di dalam modul Anda sendiri (`Modules/NamaModul/`) lalu override method yang diperlukan.
     - Jika butuh memodifikasi view bawaan paket, salin file view ke `sources/resources/views/vendor/{nama-paket}/` dengan path yang sama (contoh layout admin: `.../vendor/tsu-core/template/admin/navbar.blade.php`; view foundation: `.../vendor/tsu-foundation/...`), Laravel otomatis memakai salinan tersebut. Landing page & panduan sudah milik template (`sources/resources/views/landing`, `.../panduan`), ubah langsung.
     - Jika fungsionalitas tersebut memang dibutuhkan oleh seluruh aplikasi kampus, ajukan *Feature Request* atau PR resmi ke repositori template/package core PIKDI.

3. **Alur Serah Terima & Auto-Deploy (Transfer Organisasi)**
   - Kembangkan proyek di repositori tim/mahasiswa hingga seluruh fitur selesai dan lolos evaluasi kepatuhan rilis.
   - Setelah disetujui (**ACC**) oleh Tech Lead / Koordinator Sistem PIKDI, repositori dipindahkan (*transfer ownership*) ke organisasi resmi **`pikdi-tsu`**.
   - Tim infrastruktur PIKDI akan mengonfigurasi skrip auto-deploy CI/CD dan kredensial server produksi secara terpusat, aman, dan terisolasi.

---

## ⚙️ Panduan Instalasi & Setup Standar (Untuk Tim Developer)

Ikuti langkah berikut untuk mengatur lingkungan pengembangan lokal Anda:

1. **Clone & Install Dependencies**

    > **Akses paket TSU:** paket `pikdi/tsu-*` berada di repo GitHub **privat** PIKDI dan diunduh Composer lewat `git`. Minta akses ke Helpdesk PIKDI (WhatsApp **+62 851-1141-5175** atau **pikdi@tsu.ac.id**, sebutkan akun GitHub Anda), terima undangan GitHub yang dikirim ke email Anda, pastikan `git ls-remote https://github.com/PIKDI-TIGA-SERANGKAI-UNIVERSITY/tsu-core.git` berhasil, dan di Windows jalankan `git config --global core.longpaths true`. Detail: bab "Mulai dari Sini" di /panduan, Langkah 0.

    Karena struktur *core* Laravel berada di dalam folder `sources/`, semua perintah instalasi dan `artisan` **wajib** dijalankan di dalam folder tersebut.
   ```bash
   git clone <repository_url_proyek> tsu-project-template
   cd tsu-project-template/sources
   composer install
   ```
   Template tidak menyertakan `composer.lock`; setelah `composer install` pertama pada repo proyek Anda, commit `sources/composer.lock` yang terbentuk.

2. **Penyesuaian Environment (Wajib Diperhatikan!)**
   
    Pastikan Anda masih berada di dalam direktori `sources/`. Salin `.env.example` menjadi `.env` dengan menjalankan perintah berikut:
    ```bash
    cp .env.example .env
    ```
    Setelah itu, buka file `.env` dan **wajib** sesuaikan kelompok variabel krusial berikut agar aplikasi dan fitur SSO berjalan lancar di *local* Anda:

   **🔹 Core & Database**
   - `APP_NAME`: Nama proyek baru (Contoh: "TSU Template").
   - `APP_URL`: URL lokal proyek baru (Contoh: "http://tsu-template.test").
   - `SESSION_COOKIE`: Ubah spesifik per proyek (Contoh: "template_session"). *Penting agar sesi login tidak bentrok dengan aplikasi TSU lain di browser.*
   - `DB_*`: Masukkan kredensial koneksi dan nama database lokal. 

   **🔹 Arsitektur Modular (Otomatisasi Prefix & Judul Modul)**
   - `MODULE_FULL_NAME`: Nama lengkap proyek (Contoh: "tsu_template"). Ditampilkan di seluruh antarmuka secara dinamis dalam format Title Case bersih tanpa simbol.
   - `MODULE_NAME`: Prefix teknis untuk tabel Spatie dan identitas sistem, huruf kecil (Contoh: "template"). Isi **sebelum** menjalankan `migrate --seed`.
   - `MODULE_TITLE_ACRONYMS`: Daftar singkatan yang otomatis dijadikan **UPPERCASE** pada judul modul di antarmuka (Default: `"TSU,SSO,API,HRIS,PIKDI,SIAKAD,SIM"`). Bebas ditambah (misal: `PMB,LPM,BPM`) dipisahkan koma.
   - `TABLE_NAME`: Kosongkan jadi `""` jika ingin menggunakan nama tabel bawaan, atau isi eksplisit.

   **🔹 Feature Flags Starter Kit (Reset Demo Tanpa Merusak Sistem)**
   - `FEATURE_DEMO_DASHBOARD`: Setel `false` saat Anda siap membangun modul baru untuk langsung beralih ke kanvas dashboard kosong bersih (`blank.blade.php`). (Default: `true`).
   - `FEATURE_DEMO_LANDING_PAGE`: Setel `false` untuk langsung beralih ke kanvas landing page publik bersih (`blank.blade.php`). (Default: `true`).
   - `FEATURE_MANUAL_BOOK`: Setel `false` untuk menyembunyikan seluruh tautan buku panduan dan ikon navbar saat aplikasi siap rilis/digunakan. (Default: `true`).
    - `FEATURE_WELCOME_SPLASH`: Menampilkan layar transisi sambutan elegan (*Welcome Splash*) 1x pasca-login dengan animasi logo TSU berdenyut dan adaptif terhadap koneksi internet lambat. (Default: `true`).
    - `FEATURE_TOP_PROGRESS_BAR`: Menampilkan indikator garis progress tipis 3px ala GitHub/YouTube di puncak layar saat navigasi & AJAX. (Default: `true`).

   **🔹 Integrasi SSO & API (Homebase TSU)**
   - `TSU_SSO_CLIENT_ID` & `SECRET`: Kredensial SSO dari TSU Homebase, **diminta ke Helpdesk PIKDI**.
   - `TSU_SSO_REDIRECT_URI`: URL callback yang **sama persis** dengan alamat yang Anda buka, termasuk `http`/`https` dan subfolder (contoh: `http://localhost/nama-proyek/login/sso/callback`).
   - `HOMEBASE_CLIENT_ID` & `SECRET`: Kredensial untuk jalur komunikasi API antar layanan (sinkronisasi user & role), diminta ke PIKDI bila dipakai.

   > **Belum punya kredensial SSO?** Biarkan dulu teks contohnya. Saat tombol login SSO diklik, aplikasi menampilkan halaman **"Login SSO Belum Dikonfigurasi"** berisi URL callback Anda dan teks permintaan yang bisa langsung dikirim ke Helpdesk PIKDI lewat WhatsApp atau email. Sementara itu, gunakan login lokal (`DevAdminSeeder`). Detail: bab Autentikasi, "Meminta Akses SSO ke PIKDI" di `/panduan`.
   >
   > **Helpdesk PIKDI:** WhatsApp **+62 851-1141-5175** · **pikdi@tsu.ac.id** · Senin–Jumat 08.00–16.30 WIB, Sabtu 08.00–13.00 WIB, Minggu dan libur nasional tutup.

   **🔹 Integrasi TSU HRIS (Sinkronisasi Karyawan, opsional)**
   - Paket `pikdi/tsu-karyawan` tidak terpasang secara default. Pasang bila proyek membutuhkan data dosen & tendik: `composer require pikdi/tsu-karyawan:dev-main`, lalu `php artisan migrate --seed` (wajib diulang agar menu Data Karyawan langsung tampil, aman dijalankan berulang).
   - `FEATURE_DATA_KARYAWAN`: Feature flag modul Data Karyawan (default `false`). Hanya aktif jika disetel `true` **DAN** `HRIS_SYNC_SECRET` terisi sah dari PIKDI.
   - `HRIS_URL`: URL server TSU HRIS (Contoh lokal: "http://tsu_hris.test", production: "https://hris.tsu.ac.id").
   - `HRIS_SYNC_SECRET`: **Hash SHA-256** dari master secret HRIS, **diminta ke PIKDI** (admin TSU HRIS yang membuatnya lewat `php artisan hris:hash-secret` di server HRIS, bukan di proyek ini). Jangan pernah diisi secret aslinya (plaintext).
   - Menu Data Karyawan otomatis: lewat menu sidebar dinamis bila tsu-foundation terpasang, lewat slot sidebar tsu-core bila tidak. Fitur nonaktif = menu tersembunyi & URL 404.
   - 📖 *Baca panduan lengkap di:* [Manual Book Sinkronisasi Karyawan](sources/vendor/pikdi/tsu-karyawan/docs/MANUAL_BOOK_SINKRONISASI_KARYAWAN.md) (dokumen milik package `pikdi/tsu-karyawan`, tersedia setelah `composer install`).

   **🔹 Keamanan & Hak Akses Khusus**
   - `PIKDI_EMERGENCY_SECRET` & `RESCUE_SECRET`: Kunci rahasia untuk otorisasi *bypass/login* darurat tim PIKDI.
   - `PIKDI_COMMAND_SECRET`: Master passphrase otorisasi CLI inject admin (`php artisan tsu:create-admin`).
   - `APP_ALLOWED_ROLES`: Batasi role yang boleh mengakses aplikasi (Contoh: `"dosen,tendik"` atau `"dosen,tendik,magang"`). Tampilan informasi dan badge di halaman login otomatis menyesuaikan secara dinamis via helper `tsu_allowed_roles_text()`. Kosongkan jika semua sivitas akademika diizinkan masuk.
   - `MAIL_MAILER`: Di lingkungan lokal, setel `MAIL_MAILER=log` agar isi email atau OTP otorisasi langsung tertulis di `storage/logs/laravel.log` (atau gunakan Mail Catcher bawaan Laragon).
   - `GMAPS_KEY`: Isi jika modul Anda menggunakan fitur pemetaan/lokasi.


3. **Generate Key & Clear Cache**
    *(Pastikan masih di dalam direktori `sources/`)*
   ```bash
   php artisan key:generate
   php artisan config:clear
   ```

4. **Setup Database & Modules**
   Pastikan Anda sudah membuat database kosong. Jalankan migrasi beserta seeder-nya (`--seed` mengisi role, izin, dan menu sidebar; modul `Admin` sudah aktif sejak awal). *(Pastikan masih di dalam direktori `sources/`)*
   ```bash
   php artisan migrate --seed
   ```
   *(Opsional, dijalankan manual)* Buat akun developer lokal untuk login offline tanpa SSO:
   ```bash
   php artisan db:seed --class=DevAdminSeeder
   ```

   > **Tidak perlu menghafal urutan ini.** Bila aplikasi dibuka sebelum langkah selesai, halaman **"Instalasi belum selesai"** (dependensi, `.env`, `APP_KEY`) lalu halaman **error database** (contoh isian `.env`, cara membuat database, dan `php artisan migrate --seed`, semuanya dengan tombol Salin) akan memandu. Halaman error database lengkap dengan perintah hanya tampil saat `APP_DEBUG=true`.

5. **Menjalankan Aplikasi**

   Karena *entry point* (`index.php`) berada di luar direktori `sources/`, **document root harus folder repo**, bukan `public/`. Tiga cara didukung: virtual host `.test` di Laragon (disarankan di Windows), `http://localhost/[nama_proyek]`, atau `php artisan serve` dari folder `sources` (tanpa Laragon, cocok untuk Linux dan macOS; buka `http://127.0.0.1:8000`).

   > [!WARNING]
   > Laragon membuat virtual host otomatis dengan document root `public/`, sehingga browser menampilkan **"Index of /"** (`assets/`, `favicon.ico`, `robots.txt`, `web.config`). Perbaiki sekali per proyek: buka `laragon\etc\apache2\sites-enabled\auto.[nama_proyek].test.conf`, ubah `define ROOT "D:/laragon/www/[nama_proyek]/public"` menjadi `define ROOT "D:/laragon/www/[nama_proyek]"`, lalu Reload/Restart Apache.

   Untuk `http://localhost/[nama_proyek]`: butuh `mod_rewrite` aktif dan `AllowOverride All`, isi `APP_URL=http://localhost/[nama_proyek]` dan **`SESSION_COOKIE` yang unik per proyek** (semua proyek di `localhost` berbagi cookie browser yang sama). Login SSO butuh callback yang terdaftar di Homebase untuk alamat itu, atau pakai akun developer lokal.

   Untuk `php artisan serve`: template memakai perintah `serve` dan router `sources/server.php` khusus (karena `index.php` ada di akar repo) dengan aturan keamanan yang sama seperti `.htaccess`. Hanya untuk pengembangan; isi `APP_URL=http://127.0.0.1:8000` dan `SESSION_COOKIE` yang unik.

   Langkah lengkap, cara memeriksa, dan daftar masalah umum ada di bab **Mulai dari Sini**, bagian *Menjalankan Aplikasi di Komputer Lokal* (`/panduan`).

6. **Autentikasi & Akses Sistem**
   - **Mode Pengembang Lokal (Offline / Mahasiswa):**
     Akun developer lokal **tidak dibuat otomatis** oleh `migrate --seed`. Jalankan `php artisan db:seed --class=DevAdminSeeder` secara manual, lalu login menggunakan:
     - **Username/Email**: `devadmin` atau `dev.admin@tsu.ac.id`
     - **Password**: `password@123` (atau disesuaikan di `DEV_ADMIN_PASSWORD` pada `.env`)
     - *Role*: Otomatis menjadi `super admin [modul]` dengan akses penuh.
   - **Mode Server Produksi (SSO Kampus):**
     Di server produksi (`APP_ENV=production`), `DevAdminSeeder` menolak berjalan demi keamanan walaupun dipanggil manual, dan login wajib melalui SSO Homebase TSU.
   - **Injeksi Akun Super Admin di Server Produksi (CLI):**
     Sysadmin kampus atau tim internal PIKDI dapat menginjeksi akun admin di server produksi langsung via terminal:
     ```bash
     php artisan tsu:create-admin
     ```
     Command ini dilindungi otorisasi ganda (OTP email resmi PIKDI + *fallback* Master Passphrase `PIKDI_COMMAND_SECRET`) dan audit log keamanan.
   - **Akses Darurat di Web Produksi:**
     Jika SSO kampus mengalami gangguan, klik **5 kali** pada judul logo halaman login untuk membuka form login darurat.
   - **Audit Trail Masuk/Keluar Otomatis:**
     Seluruh aktivitas login dan logout (baik via SSO Homebase, login lokal pengembang, maupun mode darurat Rescue) otomatis tercatat ke dalam sistem audit trail `TsuActivityLogService` lengkap dengan metadata IP, browser, dan timestamp.

---

## 🔐 Checklist Keamanan Deploy (Wajib Sebelum Go-Live)

Karena *entry point* `index.php` berada di root repositori, **document root web server = root repositori**. Hanya `index.php` dan isi folder `public/` yang boleh diakses langsung dari browser.

1. **Apache**: sudah ditangani otomatis oleh `.htaccess` di root (whitelist `index.php` + `public/`, blokir dotfile seperti `.git`/`.env`, larang eksekusi PHP di `public/`) dan `sources/.htaccess` (`Require all denied`). Pastikan `AllowOverride All` aktif.
2. **Nginx**: `.htaccess` tidak berlaku. Tambahkan aturan setara pada blok `server`:
   ```nginx
   location ~ /\.(?!well-known/) { deny all; }
   location ^~ /sources/ { deny all; }
   location ^~ /docs/ { deny all; }
   location ~ ^/public/.*\.(php\d?|phtml|phar)$ { deny all; }
   ```
3. **Verifikasi** dari luar server, semua harus **403/404**, bukan 200:
   ```bash
   curl -I https://[domain]/sources/.env
   curl -I https://[domain]/.git/config
   curl -I https://[domain]/sources/storage/logs/laravel.log
   ```
   Jika sebelumnya pernah **200**, anggap seluruh isi `.env` bocor: ganti `APP_KEY`, password database, `HRIS_SYNC_SECRET`, seluruh `PIKDI_*_SECRET`, kredensial SSO/mail, lalu periksa access log server.
4. **`TSU_HOMEBASE_SECRET_KEY`** wajib diisi (`php -r "echo bin2hex(random_bytes(32));"`) dan **disamakan dengan Homebase** sebelum deploy. Jika kosong, endpoint `/v1/system/activity-logs` menolak semua request (503) sehingga penarikan log oleh Homebase berhenti.
5. Set `APP_ENV=production` dan `APP_DEBUG=false`, lalu jalankan `php artisan config:cache`.

---

## 📖 Dokumentasi Lengkap (Buku Panduan Pengembang)

**Mulai dari sini bila Anda baru pertama kali:** jalankan aplikasi, lalu buka `/panduan` di browser (misal: `http://tsu-project-template.test/panduan`) dan baca bab **Tutorial Pemula: Membuat Modul Pertama Anda**.

Buku Panduan disusun dari bab-bab milik tiap paket, jadi isinya selalu mengikuti versi paket yang terpasang (`composer update`):

| Bab | Pemilik | Isi |
|---|---|---|
| SOP Koordinasi PIKDI (bab pertama), Mulai dari Sini, Tutorial Pemula, Arsitektur Zona | Starter kit ini (`sources/docs/panduan/`) | Instalasi, tutorial untuk pemula, aturan zona, alur koordinasi dengan PIKDI |
| Ekosistem Paket, Tutorial CRUD Standar TSU, Service & Tools, Standar Desain UI | `pikdi/tsu-core` | Standar developer dan desain UI |
| Autentikasi, Hak Akses & Menu; Log, Audit & Notifikasi | `pikdi/tsu-foundation` | Login, RBAC, menu, log |
| Data Karyawan (Sinkronisasi HRIS) | `pikdi/tsu-karyawan` (opsional) | Sinkronisasi data dosen dan tendik |

Fitur halaman panduan: pencarian cepat `Ctrl + K`, daftar isi yang mengikuti posisi baca, dan checklist evaluasi yang bisa dicentang langsung. Untuk menyembunyikannya saat aplikasi siap rilis, setel `FEATURE_MANUAL_BOOK=false`.

**Menambah bab milik proyek Anda:** letakkan berkas markdown di `sources/docs/panduan/`, lalu daftarkan di `App\Providers\AppServiceProvider::registerDocs()` dengan `tsu_docs()->chapter(...)`. Aturan penulisan bab ada di komentar kelas `Pikdi\TsuCore\Docs\DocsRenderer`.

**Dokumentasi paket** (tersedia setelah `composer install`):
- `pikdi/tsu-core`: aset, layout, komponen, slot UI → [sources/vendor/pikdi/tsu-core/README.md](sources/vendor/pikdi/tsu-core/README.md)
- `pikdi/tsu-foundation`: integrasi ke proyek Laravel lain → [sources/vendor/pikdi/tsu-foundation/docs/PANDUAN_INTEGRASI.md](sources/vendor/pikdi/tsu-foundation/docs/PANDUAN_INTEGRASI.md)
- `pikdi/tsu-karyawan` (opsional): sinkronisasi HRIS → [sources/vendor/pikdi/tsu-karyawan/docs/MANUAL_BOOK_SINKRONISASI_KARYAWAN.md](sources/vendor/pikdi/tsu-karyawan/docs/MANUAL_BOOK_SINKRONISASI_KARYAWAN.md)

## 📝 Catatan Pengembang

- **Namespace**: Semua logika inti berada di bawah namespace `App\` (untuk `sources/app`) dan `Modules\` (untuk `sources/Modules`).
- **Assets**: Aset UI TSU dipanggil dengan `tsu_asset('adminlte/...')` / `tsu_asset('tsu/...')` (berkas di `public/vendor/tsu-core`, dikelola package). Aset milik modul Anda simpan di `public/assets/...` (CSS modul: `public/assets/css/modules/`) dan panggil dengan `tsu_public_url('assets/...')`.

---

<div align="center">
  <strong>Pusat Informasi, Komunikasi dan Digital (PIKDI)</strong><br>
  Tiga Serangkai University
</div>
