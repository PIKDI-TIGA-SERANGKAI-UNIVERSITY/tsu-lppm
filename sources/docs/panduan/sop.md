### Standar Operasional Prosedur (SOP) Koordinasi PIKDI <!-- id: sop-koordinasi-pikdi; icon: fas fa-clipboard-check; short: SOP Koordinasi Resmi PIKDI -->

* ✅ **Boleh Dikerjakan Mandiri:**
  - Membuat tabel migration, model, dan business logic baru.
  - Membuat controller baru di `Modules/Admin` atau modul domain baru.
  - Mendaftarkan menu sidebar di database.
  - Membuat permission Spatie untuk hak akses menu internal modul.
  - Mendesain antarmuka Blade, tabel, dan kartu statistik.
* ⚠️ **Wajib Koordinasi dengan PIKDI:**
  - Permintaan `TSU_SSO_CLIENT_ID` & Secret (client pengembangan untuk lokal, client production untuk rilis). Caranya: bab Autentikasi, "Meminta Akses SSO ke PIKDI".
  - Penerbitan kunci rahasia `PIKDI_EMERGENCY_SECRET` & `PIKDI_RESCUE_SECRET`.
  - Permintaan kunci `HRIS_SYNC_SECRET` untuk modul data karyawan.
  - Perubahan pada package inti `pikdi/tsu-core` / `pikdi/tsu-foundation` / `pikdi/tsu-karyawan`.
  - Penambahan channel WebSockets baru untuk broadcast lintas domain.

#### Kontak Helpdesk PIKDI

| Saluran | Alamat |
|---|---|
| WhatsApp (langsung membuka chat) | [**+62 851-1141-5175**](https://wa.me/6285111415175?text=Halo%20Helpdesk%20PIKDI%20TSU%2C%20saya%20membutuhkan%20bantuan%20teknis%20terkait%20aplikasi%20di%20lingkungan%20TSU.) |
| Email | [**pikdi@tsu.ac.id**](mailto:pikdi@tsu.ac.id) |
| Jam layanan | Senin–Jumat 08.00–16.30 WIB, Sabtu 08.00–13.00 WIB, Minggu dan libur nasional tutup |

Sebutkan nama aplikasi, apa yang sedang dikerjakan, dan pesan error lengkap (kode `[TSU_...]` bila ada). Di aplikasi, tombol **Hubungi Helpdesk PIKDI** pada pesan error dan halaman error server sudah membuka chat WhatsApp ini.
