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

`tsu-core` wajib dan tidak bergantung paket lain. `tsu-foundation` terpasang default. `tsu-karyawan` opsional. Daftar kombinasi yang didukung dan cara memasangnya: bab **Mulai dari Sini**, bagian **Kombinasi Paket yang Didukung**, di `/panduan`.

Modul bisnis baru dibuat dengan pola `nwidart/laravel-modules` (`php artisan module:make NamaModul`) agar setiap domain terisolasi dengan baik.

## 🛠️ Spesifikasi Teknis (Tech Stack)

- **Framework**: Laravel 13 (PHP 8.3+). Paket `pikdi/tsu-*` mendukung Laravel 11, 12, dan 13.
- **Arsitektur**: Modular Monolith
- **Database**: Eloquent ORM, `yajra/laravel-datatables-oracle` (MySQL & Oracle)
- **Autentikasi & Otorisasi**: SSO Homebase TSU, login lokal untuk pengembangan, serta jalur darurat (`pikdi/tsu-foundation`); `spatie/laravel-permission` untuk role & permission
- **Real-time**: Laravel Reverb (WebSocket) — notifikasi Kotak Masuk, lonceng navbar, toast
- **Frontend**: Blade, AdminLTE 4 + Bootstrap 5, jQuery, DataTables, Select2, SweetAlert2, FontAwesome 7 — disediakan `pikdi/tsu-core`
- **Dokumentasi interaktif**: `/panduan`, disusun otomatis dari bab tiap paket yang terpasang (pencarian `Ctrl + K`, checklist evaluasi kesiapan rilis)

Detail tiap layanan backend (activity log & backup, upload berkas anti-malware, error handler terpusat, dsb): bab **Service & Tools** di `/panduan`.

## 🛣️ Roadmap Pengembangan

Proyek ini dikembangkan dengan peta jalan (roadmap) teknis sebagai berikut:

1. Phase 1 (Current): Struktur Modular Monolith berbasis paket (`tsu-core`, `tsu-foundation`, `tsu-karyawan`), Login SSO Homebase, Koneksi Database Langsung.
2. Phase 2: Refactoring Service Layer untuk persiapan abstraksi data.
3. Phase 3: Transisi ke Arsitektur berbasis API (Headless Readiness).

---

## 🚀 Mulai Cepat

```bash
git clone <url-repo-proyek-anda>
cd <nama-proyek>/sources
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
```

Lalu buka aplikasi lewat virtual host `.test`, `http://localhost/nama-proyek`, atau `php artisan serve` dari folder `sources` — **document root harus folder repo, bukan `public/`**, karena `index.php` berada di akar repo. Kalau aplikasi dibuka sebelum langkah di atas selesai, halaman **"Instalasi belum selesai"** akan memandu.

**Panduan lengkap, langkah demi langkah, ada di dalam aplikasi itu sendiri** — bukan di README ini, supaya selalu sinkron dengan versi paket yang terpasang. Buka `/panduan` setelah server jalan (misal `http://nama-proyek.test/panduan`), mulai dari bab **SOP Koordinasi PIKDI** lalu **Mulai dari Sini**. Di sana ada semua detail: akses paket privat, konfigurasi `.env` lengkap (database, SSO Homebase, HRIS, kunci darurat), tiga cara menjalankan aplikasi beserta solusi masalah umum, jalur autentikasi darurat, dan checklist kesiapan rilis.

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

## 📦 Deploy ke Server Production

Deploy dikerjakan oleh **tim PIKDI**, bukan pengembang/mahasiswa. Menjelang rilis, repositori dipindahkan (*transfer ownership*) ke organisasi resmi `pikdi-tsu` setelah disetujui Tech Lead/Koordinator Sistem PIKDI (lihat tabel "Aturan Wajib" pada bab **Arsitektur Zona** di `/panduan`), lalu tim infrastruktur PIKDI mengonfigurasi skrip auto-deploy CI/CD, kredensial server produksi, dan checklist keamanan deploy secara terpusat.

## 📝 Catatan Pengembang

- **Namespace**: Semua logika inti berada di bawah namespace `App\` (untuk `sources/app`) dan `Modules\` (untuk `sources/Modules`).
- **Assets**: Aset UI TSU dipanggil dengan `tsu_asset('adminlte/...')` / `tsu_asset('tsu/...')` (berkas di `public/vendor/tsu-core`, dikelola package). Aset milik modul Anda simpan di `public/assets/...` (CSS modul: `public/assets/css/modules/`) dan panggil dengan `tsu_public_url('assets/...')`.

---

<div align="center">
  <strong>Pusat Informasi, Komunikasi dan Digital (PIKDI)</strong><br>
  Tiga Serangkai University
</div>
