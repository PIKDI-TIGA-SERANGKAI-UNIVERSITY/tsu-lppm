### Sistem Trafik Tiga Zona (Rules of Engagement) <!-- id: zona-tiga-zona; icon: fas fa-traffic-light; short: Sistem 3 Zona (Merah/Kuning/Hijau) -->

Untuk menjaga agar template tetap stabil dan selalu bisa menerima pembaruan sistem dari kampus, kode proyek dibagi ke dalam 3 zona ketat:

* 🔴 **ZONA MERAH: `sources/vendor/pikdi/*` (FROZEN / DILARANG DIUBAH)**
  Berisi package inti PIKDI (peta lengkap di [Peta Paket TSU & Dokumentasi Lanjutan](#paket-peta-paket)): `pikdi/tsu-core` (**wajib**: layout & komponen UI TSU, aset AdminLTE + `tsu-theme.css`, halaman error, standar developer), `pikdi/tsu-foundation` (**default**: 4 jalur login TSU, RBAC, menu dinamis, manajemen user, activity & error log, notifikasi), dan `pikdi/tsu-karyawan` (**opsional**: direktori dosen & tendik + sinkronisasi HRIS). **Dilarang keras mengubah file di folder ini!** Perubahan akan hilang saat `composer update`. Untuk menyesuaikan tampilan, override view lewat `sources/resources/views/vendor/{tsu-core|tsu-foundation|tsu-karyawan}/...`, dan ajukan perbaikan inti ke PIKDI.
* 🟡 **ZONA KUNING: Konfigurasi & Data Inti (RESTRICTED / HATI-HATI)**
  `sources/config/`, `sources/app/` (model `User` milik aplikasi, middleware, helper, landing page), serta role & permission sistem (`system:*`, `users:*`). Anda boleh mengonfigurasi lewat `.env` atau UI web, namun hindari mengutak-atik alur autentikasi dan struktur tabel bawaan.
* 🟢 **ZONA HIJAU: `Modules/Admin` & Modul Baru (WORKSPACE BEBAS ANDA)**
  **Tempat utama pengembang bekerja!** Tambahkan controller, model, migration, dan view bisnis Anda di sini tanpa rasa takut merusak modul inti.

---

### Kapan Membuat Modul Baru vs Menggunakan `Modules/Admin`? <!-- id: zona-kapan-modul-baru; icon: fas fa-folder-plus; short: Modul Baru vs Modul Admin -->

* **Gunakan `Modules/Admin` jika:** Fitur Anda merupakan fungsi pelengkap dashboard admin atau modul utilitas tunggal (misal: Pengaturan Lab, Rekap Laporan).
* **Buat Modul Domain Baru jika:** Fitur Anda adalah domain bisnis besar yang memiliki alur bisnis mandiri (misal: Modul `Inventaris`, Modul `Perpustakaan`, Modul `Presensi`). Gunakan generator:
  ```bash
  php artisan module:make NamaModul
  ```

---

### Aturan Wajib (DO's & DON'Ts) <!-- id: zona-aturan-wajib; icon: fas fa-tasks; short: Aturan Wajib DO/DON'T -->

Aturan yang menjaga struktur proyek, kredensial, dan kode inti tetap aman:

| Kategori | Yang WAJIB Dilakukan (DO) | Yang DILARANG KERAS (DON'T) |
|---|---|---|
| **Struktur Kode** | Seluruh controller, model, migration, dan view fitur baru **wajib** diletakkan di dalam folder `Modules/NamaModul/` atau `Modules/Admin/`. | **DILARANG** menumpuk file bisnis aplikasi baru di root `app/Http/Controllers/` atau `resources/views/`. |
| **Kredensial (.env)** | Simpan secret API di `.env` dan gunakan hash SHA-256 untuk secret sinkronisasi HRIS. | **DILARANG KERAS** melakukan commit file `.env` atau hardcode API key ke Git repository. |
| **Modifikasi Core / Vendor** | Gunakan prinsip **OOP Extends (Inheritance)** atau **View Override** di dalam folder modul sendiri jika butuh mengubah perilaku service/view. Jika fitur dibutuhkan seluruh kampus, ajukan Feature Request / PR resmi ke PIKDI. | **DILARANG KERAS** mengedit langsung file di dalam folder `vendor/` atau modul core `System`. Seluruh editan di dalam `vendor/` **PASTI HILANG KETIMPA 100%** saat `composer update` atau saat auto-deploy di server PIKDI! |
| **Auto-Deploy & Git** | Gunakan fitur **Template Repository** secara mandiri. Ketika aplikasi disetujui (ACC) untuk tayang di server kampus, repo ditransfer ke organisasi `pikdi-tsu` agar script auto-deploy dan credentials server dipasang secara terpusat dan aman. | **DILARANG** menaruh SSH keys / kredensial server produksi di repositori personal dan **DILARANG** membuka Pull Request deployment ke template induk. |
