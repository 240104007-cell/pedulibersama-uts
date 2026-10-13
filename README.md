# 🌿 PeduliBersama - Platform Penggalangan Dana & Donasi Terbuka

Platform penggalangan dana kemanusiaan berbasis **PHP Native** (tanpa framework) dengan backend **Supabase Cloud** (PostgreSQL) dan tampilan modern responsif menggunakan **Bootstrap 5** & **Custom CSS**.

Didesain khusus untuk menyalurkan bantuan bencana banjir, gempa bumi, pendidikan anak pedalaman, akses air bersih, dan santunan anak yatim di seluruh Indonesia secara transparan dan realtime.

---

## 🌟 Fitur Utama

- **Foto Asli & Desain Modern**: Menggunakan foto nyata korban bencana, relawan, air bersih, dan anak sekolah dengan tema hijau-putih yang ramah & terpercaya.
- **Autentikasi Akun (Supabase Auth)**: Registrasi, login, dan logout dengan session aman PHP.
- **CRUD Kampanye Lengkap**:
  - **Create**: Buat kampanye baru dengan judul, target dana, kategori, dan foto relevan.
  - **Read**: Telusuri semua kampanye aktif atau cari berdasarkan kata kunci & kategori.
  - **Update**: Edit data kampanye dan ubah status penggalangan dana.
  - **Delete**: Hapus kampanye beserta riwayat donasinya.
- **Donasi Cepat & Progress Bar**: Donasi online dengan tombol nominal instan, penghitungan capaian dana, dan animasi progress bar.
- **Live Realtime Update**: Integrasi Supabase JS Realtime (PostgreSQL Changes) yang otomatis memperbarui daftar donatur dan nominal masuk tanpa reload halaman.
- **Responsif Sepenuhnya**: Tampilan optimal untuk smartphone, tablet, maupun laptop/desktop.

---

## 📁 Struktur Direktori

```
donasi-platform/
├── assets/
│   ├── css/
│   │   └── style.css          # Styling custom tema hijau-putih & animasi
│   ├── js/
│   │   └── main.js            # Interaksi UI, scroll effect, nominal selector
│   └── img/
│       ├── hero.jpg           # Foto hero cover
│       ├── hero-relawan.jpg   # Foto relawan di hero
│       ├── banjir.jpg         # Foto bencana banjir bandang
│       ├── gempa.jpg          # Foto penanganan gempa bumi
│       ├── pendidikan.jpg     # Foto bantuan pendidikan anak
│       ├── air-bersih.jpg     # Foto akses air bersih
│       ├── kesehatan.jpg      # Foto bantuan medis balita
│       └── yatim.jpg          # Foto santunan anak yatim
├── config/
│   └── database.php           # Konfigurasi Supabase URL & API Keys
├── database/
│   └── schema.sql             # Skrip SQL tabel kampanye, donasi, triggers & RLS
├── includes/
│   ├── header.php             # Navbar responsif & flash alert
│   ├── footer.php             # Footer & scripts
│   └── functions.php          # Helper PHP, data resolver & formatter
├── index.php                  # Halaman utama (Hero, Statistik, Kategori, Unggulan)
├── kampanye.php               # Halaman katalog seluruh kampanye + filter
├── detail_kampanye.php        # Detail kampanye, form donasi, & donatur realtime
├── dashboard.php              # Dashboard kelola kampanye milik pengguna
├── tambah_kampanye.php        # Form buat kampanye baru + preset foto
├── edit_kampanye.php          # Form edit kampanye
├── hapus_kampanye.php         # Konfirmasi hapus kampanye
├── proses_donasi.php          # Handler pemrosesan donasi
├── login.php                  # Halaman login akun
├── register.php               # Halaman pendaftaran akun baru
└── logout.php                 # Halaman logout session
```

---

## 🚀 Cara Menjalankan Aplikasi

### 1. Setup Database di Supabase
1. Masuk ke [Supabase Dashboard](https://supabase.com/dashboard).
2. Pilih project Anda (`tvfutvuvwxgabanjynjv`).
3. Buka menu **SQL Editor** di sidebar kiri.
4. Buka file `database/schema.sql`, salin seluruh kodenya, lalu tempel dan klik **Run**.
5. Tabel `kampanye` dan `donasi` beserta RLS, trigger capaian dana, dan data awal akan otomatis dibuat.

### 2. Jalankan PHP Built-in Server
Buka terminal di folder proyek:
```bash
cd donasi-platform
php -S localhost:8000
```

### 3. Akses Melalui Browser
Buka browser dan kunjungi:
```
http://localhost:8000
```

---

## 🛠️ Pengujian Fitur
1. **Beranda & Foto**: Periksa hero section, kategori bencana, dan kartu donasi dengan gambar lokal & CDN.
2. **Lihat & Filter Kampanye**: Buka menu Kampanye, coba filter kategori Bencana Alam, Pendidikan, atau Air Bersih.
3. **Kirim Donasi**: Buka detail salah satu kampanye, pilih nominal (misal Rp 50.000), isi nama & email, lalu kirim donasi.
4. **Daftar & Login**: Daftar akun baru di halaman Registrasi, lalu masuk ke Dashboard untuk membuat kampanye baru.
