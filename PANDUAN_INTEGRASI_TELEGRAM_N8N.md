# 🤖 Panduan Integrasi Telegram Bot & n8n — PeduliBersama (UAS)

Dokumentasi ini menjelaskan arsitektur, konfigurasi, dan pengujian integrasi **Telegram Bot** dengan website **PeduliBersama** (`https://donasi-platform.vercel.app`) menggunakan **n8n** sebagai orkestrator alur kerja (*workflow automation*).

---

## 🏛️ Arsitektur Integrasi & Keamanan Data

```
[ Pengguna Telegram ]
        ↕ (Pesan / Tombol Inline)
[ Telegram Bot API ]
        ↕ (Webhook Realtime)
[ n8n Workflow Automation ]
        ↕ (HTTP Request / HTTPS REST API)
[ Backend Website: bot_api.php ]
        ↕ (PostgREST / Service Role)
[ Database Supabase Cloud (PostgreSQL) ]
```

### 🔒 Prinsip Keamanan:
1. **Database Konsisten**: Website dan Telegram Bot membaca & menulis ke basis data **Supabase yang sama**.
2. **Kredensial Aman**: Token Bot Telegram dan Service Role Key database tidak diekspos ke frontend, melainkan disimpan di *Environment Variables* n8n & Vercel.
3. **Bridge API Terisolasi**: File `bot_api.php` bertindak sebagai perantara yang memvalidasi request, memformat pesan teks, progress bar visual (`🟩🟩⬜⬜`), serta menghasilkan link resmi donasi ke website.

---

## 📋 Fitur Integrasi Bot

| Perintah / Aksi | Respon Bot | Sumber Data |
|---|---|---|
| `/start`, `halo` | Menampilkan pesan sambutan resmi dan menu tombol inline | Template Bot API |
| `/program`, `Daftar Program` | Menampilkan seluruh program donasi aktif, kategori, persentase progress bar teks, dan link donasi web | Supabase: Tabel `kampanye` |
| `/detail <id>` | Menampilkan rincian lengkap program donasi, total donatur, deskripsi, dan tombol donasi | Supabase: Tabel `kampanye` & `donasi` |
| `/cari <kata kunci>` | Mencari kampanye berdasarkan judul atau deskripsi (contoh: `/cari banjir`) | Supabase: Fulltext/ilike |
| Pesan Tidak Dikenal | Respon ramah otomatis yang mengarahkan pengguna kembali ke daftar program atau link website | Fallback Bot API |

---

## 🚀 Langkah Konfigurasi Bertahap

### Langkah 1: Buat Telegram Bot via @BotFather
1. Buka aplikasi Telegram, cari akun resmi **`@BotFather`**.
2. Kirim perintah `/newbot`.
3. Beri nama bot (contoh: `PeduliBersama Bot`).
4. Beri username bot yang berakhiran `bot` (contoh: `PeduliBersamaDonasiBot`).
5. Simpan **HTTP API Token** yang diberikan oleh BotFather (format: `123456789:ABCdefGhIJKlmNoPQR...`).

---

### Langkah 2: Setup n8n
Anda dapat menggunakan n8n lokal di komputer atau n8n Cloud:
* **Menjalankan n8n Lokal**:
  Buka terminal / Command Prompt:
  ```bash
  n8n
  ```
  Buka browser di `http://localhost:5678`.

* **Hubungkan Kredensial Telegram di n8n**:
  1. Di dashboard n8n, klik menu **Credentials** > **Add Credential**.
  2. Pilih **Telegram API**.
  3. Masukkan **Access Token** yang didapat dari `@BotFather`.
  4. Klik **Save**.

---

### Langkah 3: Import Workflow n8n
File workflow siap pakai sudah disediakan di repositori:
📁 `n8n_workflow_pedulibersama.json`

1. Di n8n, buat workflow baru, lalu klik menu titik tiga di kanan atas > **Import from File**.
2. Pilih file `n8n_workflow_pedulibersama.json`.
3. Buka node **Telegram Trigger** dan node **Telegram (Send Message)**, pastikan kredensial Telegram yang Anda simpan di Langkah 2 sudah terpilih.
4. Klik tombol **Activate** (toggle di kanan atas) agar bot aktif merespon pesan 24/7.

---

## 🧪 Panduan Pengujian (Testing & Verifikasi)

1. **Uji Perintah `/start`**:
   * Kirim `/start` ke bot Telegram Anda.
   * Pastikan muncul pesan sambutan dengan tombol inline *"📋 Daftar Program Donasi"* dan *"🌐 Kunjungi Website"*.
2. **Uji Perintah `/program`**:
   * Klik tombol *"📋 Daftar Program Donasi"* atau ketik `/program`.
   * Periksa apakah daftar kampanye yang muncul sama persis dengan yang ada di website `https://donasi-platform.vercel.app`.
   * Perhatikan progress bar visual teks `🟩🟩⬜⬜` dan nominal dana terkumpul.
3. **Uji Pesan Fallback (Pertanyaan Acak)**:
   * Kirim pesan bebas seperti *"Mau tanya dong"*.
   * Pastikan bot membalas dengan sopan bahwa perintah belum dikenali dan memberikan tombol jalan pintas ke program donasi & website.
4. **Uji Alur Donasi**:
   * Klik link *"Donasi Sekarang"* pada program yang dikirimkan bot.
   * Anda akan diarahkan ke halaman detail kampanye di website untuk melakukan pembayaran donasi.
   * Setelah donasi berhasil di web, ketik kembali `/program` di bot; nominal `dana_terkumpul` di Telegram otomatis ikut terupdate secara konsisten.
