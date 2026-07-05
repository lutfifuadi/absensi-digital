# Product Requirements Document (PRD)

## Fitur Sinkronisasi Google Sheets untuk Data Guru

| Field      | Detail                                                |
| ---------- | ----------------------------------------------------- |
| PRD ID     | PRD-005-google-sheets-sync-guru                      |
| Versi      | 1.0                                                   |
| Status     | Draft                                                 |
| Penulis    | Antigravity (PRD Specialist)                          |
| Tanggal    | 2026-07-05                                            |
| Prioritas  | Medium-High                                           |
| RICE Score | 66.6 (Reach: 2, Impact: 3, Confidence: 80%, Effort: 7.2) |

---

## 1. Ringkasan

Dokumen ini menjelaskan kebutuhan untuk penambahan fitur sinkronisasi data guru secara massal melalui Google Sheets di halaman Daftar Guru (`/admin/guru`). Fitur ini akan memanfaatkan infrastruktur dasar yang serupa dengan fitur sinkronisasi siswa (PRD-004), namun dimodifikasi khusus untuk mengenali entitas guru. Fitur ini mencakup **auto-detect mapping** yang cerdas berbasis kecocokan nama header kolom guru, penambahan kolom tipe (`type`) di tabel pengaturan Google Sheets untuk memisahkan konfigurasi siswa dan guru, serta integrasi tombol eksekusi antrian lokal (queue) semi-otomatis demi mendukung kenyamanan pengembangan di lingkungan lokal (*local development*).

---

## 2. Latar Belakang & Tujuan

### 2.1 Latar Belakang

Manajemen data guru yang dinamis memerlukan cara pemutakhiran massal yang cepat dan bebas hambatan. Memperbarui satu per satu data guru melalui formulir web sangat tidak efisien bagi admin sekolah yang mengelola puluhan hingga ratusan guru. Di sisi lain, memelihara berkas Excel lokal secara manual rentan terhadap hilangnya data dan sulit disinkronkan secara kolaboratif. 

Dengan mengintegrasikan Google Sheets, pihak sekolah dapat berkolaborasi mengisi data guru secara online di awan (cloud), kemudian melakukan sinkronisasi satu klik untuk memasukkan atau memperbarui data guru tersebut langsung ke database sistem presensi.

### 2.2 Dampak jika tidak diselesaikan

1. **Efisiensi Rendah:** Admin harus memasukkan data guru secara manual satu per satu atau bergantung pada tim IT untuk melakukan import database manual.
2. **Ketergantungan Format yang Kaku:** Jika menggunakan import CSV biasa tanpa auto-detect mapping, admin harus mencocokkan urutan kolom secara tepat, yang sering kali memicu error impor karena salah posisi kolom.
3. **Kesulitan Uji Coba Lokal:** Tanpa pemicu antrian lokal yang terintegrasi di halaman web, tim pengembang harus terus menjalankan terminal queue worker secara manual yang memakan resource dan memicu hambatan proses pengujian lokal.

### 2.3 Solusi yang Diusulkan

1. **Pemisahan Konfigurasi Siswa & Guru:** Menambahkan kolom `type` pada tabel `google_sheet_settings` untuk membedakan record pengaturan Google Sheets untuk tipe `siswa` dan `guru`.
2. **Auto-Detect Mapping Guru:** Menggunakan logika pencocokan header otomatis di backend untuk memetakan kolom di Google Sheets (seperti "Nomor Induk Guru" atau "NIP") secara cerdas ke kolom database `nip`, `nama_lengkap`, dan sebagainya tanpa konfigurasi JSON manual.
3. **Tombol Sinkronisasi & Pemicu Queue Lokal di Halaman Guru:** Menyediakan tombol pemicu sinkronisasi di halaman Daftar Guru (`/admin/guru`), lengkap dengan tombol "Proses Antrian" (semi-otomatis via AJAX `queue:work --once`) saat aplikasi berjalan di lingkungan lokal.

---

## 3. Tujuan & Metrik Keberhasilan

| Tujuan | Metrik | Target |
|---|---|---|
| Mempermudah impor massal data guru secara real-time | Waktu yang diperlukan admin untuk memperbarui seluruh data guru | < 3 menit dari persiapan spreadsheet hingga masuk ke database |
| Menghilangkan ketergantungan pada berkas CSV kaku | Tingkat keberhasilan pemetaan kolom dengan variasi penulisan header | > 95% kecocokan kolom standar guru secara otomatis |
| Memaksimalkan kenyamanan developer dalam pengujian | Aksesibilitas eksekusi antrian tanpa membuka terminal | Tersedia tombol "Proses Antrian" di UI admin guru ketika di lingkungan lokal |

---

## 4. Scope (Ruang Lingkup)

### 4.1 In Scope

- **Database:** Migrasi penambahan kolom `type` (string/enum) pada tabel `google_sheet_settings` dengan indeks pendukung.
- **Model:** Penyesuaian `GoogleSheetSetting.php` untuk mendukung pemisahan tipe (`siswa` vs `guru`).
- **Backend Service:** Pembuatan method `syncGuru` pada `GoogleSheetsService` (atau service terdedikasi) serta penyesuaian aturan auto-detect mapping di `MappingService` khusus untuk data guru.
- **Queue Job:** Pembuatan atau perluasan job antrian (misalnya `GoogleSheetsGuruSyncJob`) yang memproses baris data secara bertahap (chunking).
- **Controller:** Pembuatan controller baru (`GoogleSheetsGuruSettingController`) atau perluasan controller lama untuk menangani aksi konfigurasi, preview, dan pemicu antrian guru.
- **Frontend / UI:** 
  - Penambahan tombol "Google Sheets Sync" di halaman `/admin/guru` yang memicu modal pengaturan/sync.
  - Implementasi preview pemetaan kolom otomatis secara visual.
  - Penyediaan tombol pemicu antrian lokal (`queue:work --once --queue=syncs`) khusus di lingkungan pengembangan lokal.

### 4.2 Out of Scope

- Pembuatan fitur sinkronisasi data jadwal mengajar atau data absensi guru (PRD ini hanya fokus pada biodata/data master guru).
- Sinkronisasi dua arah (mengirimkan data guru dari database lokal kembali ke Google Sheets).

---

## 5. User Stories & Acceptance Criteria

### 5.1 User Story: Pengaturan Google Sheets Data Guru

> **Sebagai** Admin Sekolah,
> **Saya ingin** mengonfigurasi kredensial Google Sheets dan Spreadsheet ID khusus untuk Data Guru,
> **Sehingga** data guru tersinkronisasi dari lembar kerja yang benar tanpa mengganggu pengaturan data siswa.

- **Acceptance Criteria 1 (AC-01) - Penyimpanan Terpisah:**
  - **Given:** Admin berada di halaman pengaturan Google Sheets Guru.
  - **When:** Admin menyimpan kredensial Service Account JSON dan Spreadsheet ID.
  - **Then:** Sistem menyimpan konfigurasi tersebut di tabel `google_sheet_settings` dengan nilai `type = 'guru'`.
- **Acceptance Criteria 2 (AC-02) - Preview Mapping Guru:**
  - **Given:** Admin telah mengonfigurasi spreadsheet untuk guru.
  - **When:** Admin memicu tombol "Preview Mapping".
  - **Then:** Sistem mendeteksi header dari Google Sheets dan menampilkan tabel kecocokan kolom dengan field database guru secara akurat.

### 5.2 User Story: Sinkronisasi Massal Guru

> **Sebagai** Admin Sekolah,
> **Saya ingin** memicu sinkronisasi data guru dari Google Sheets secara massal,
> **Sehingga** data guru di database terbarui/bertambah secara otomatis sesuai perubahan di spreadsheet.

- **Acceptance Criteria 3 (AC-03) - Sinkronisasi Tanpa Gagal Kolom:**
  - **Given:** Admin telah mengisi data di Google Sheet menggunakan header yang bervariasi namun bermakna sama (misal: "NIP Guru", "Nama Lengkap").
  - **When:** Admin mengklik tombol "Mulai Sinkronisasi".
  - **Then:** Sistem memasukkan job sinkronisasi guru ke antrian database dan memproses data guru dengan sukses (menambah data baru atau mengupdate data yang sudah ada berdasarkan kunci unik NIP).

### 5.3 User Story: Eksekusi Antrian Lokal (Semi-Otomatis)

> **Sebagai** Developer / QA Tester,
> **Saya ingin** mengeksekusi antrian job sinkronisasi guru langsung dari browser di lingkungan lokal,
> **Sehingga** saya tidak perlu membiarkan terminal terus menjalankan command queue worker.

- **Acceptance Criteria 4 (AC-04) - Pemicu Antrian Lokal Guru:**
  - **Given:** Aplikasi berjalan di lingkungan lokal (`APP_ENV=local`) dan terdapat job sinkronisasi guru yang berstatus pending di antrian database.
  - **When:** Pengembang mengklik tombol "Proses Antrian" pada panel sync guru.
  - **Then:** Sistem mengeksekusi perintah Artisan `queue:work` dengan opsi `--once` dan `--queue=syncs` secara asinkron via AJAX, lalu memperbarui status halaman secara visual.

---

## 6. Alur Pengguna (User Flow)

```
[Mulai]
   │
   ▼
[Halaman Daftar Guru /admin/guru]
   │
   ▼
[Klik Tombol "Google Sheets Sync"]
   │
   ├─► Jika Pengaturan Belum Ada: Tampilkan form setup (Upload JSON & Spreadsheet ID)
   ├─► Jika Pengaturan Sudah Ada: Tampilkan modal opsi Sinkronisasi & Preview
   │
   ▼
[Konfigurasi Disimpan (type = 'guru')]
   │
   ▼
[Klik "Sinkronkan Sekarang"]
   │
   ├─► Sistem memasukkan tugas ke antrian (GoogleSheetsGuruSyncJob)
   │
   ▼
[Deteksi Environment]
   │
   ├─► Jika Live/Production: Diproses otomatis di latar belakang oleh antrian server.
   │
   └─► Jika Local Dev:
         │
         ▼
       Tampil tombol "Proses Antrian"
         │
         ▼
       [Klik "Proses Antrian"]
         │
         ▼
       Sistem mengeksekusi `queue:work --once` via AJAX
         │
         ▼
[Sinkronisasi Selesai & Data Guru Masuk/Terupdate di Database]
```

---

## 7. Spesifikasi Teknis & Dampak Kode

### 7.1 Struktur Database & Migrasi

Tabel `google_sheet_settings` memerlukan kolom baru untuk membedakan antara tipe pengaturan siswa dan tipe pengaturan guru.

**Rencana Migrasi:**
```php
Schema::table('google_sheet_settings', function (Blueprint $table) {
    // Tambahkan kolom type dengan nilai default 'siswa'
    $table->string('type')->default('siswa')->after('id');
    
    // Opsional: Buat index gabungan jika diperlukan untuk optimasi query
    $table->index(['type']);
});
```

### 7.2 Perubahan Model (`GoogleSheetSetting.php`)

```php
// Tambahkan properti type ke fillable
protected $fillable = [
    'type',
    'service_account_json',
    'spreadsheet_id',
    'sheet_name',
    'range',
    'column_mapping',
];
```

### 7.3 Logika Pemetaan Kolom Guru (`MappingService` / `GoogleSheetsService`)

Sistem harus memetakan variasi nama kolom dari Google Sheet secara otomatis ke field database guru. Berikut adalah daftar target kolom database dan sinonim kata kunci yang akan dicocokkan (case-insensitive):

* **`nip`** ➔ `'nip'`, `'no induk'`, `'nomor induk'`, `'nip guru'`, `'nomor induk guru'`
* **`nama_lengkap`** ➔ `'nama'`, `'nama lengkap'`, `'nama guru'`, `'fullname'`, `'full name'`
* **`username`** ➔ `'username'`, `'id'`, `'user name'`
* **`email`** ➔ `'email'`, `'e-mail'`, `'surel'`
* **`jenis_kelamin`** ➔ `'jk'`, `'jenis kelamin'`, `'gender'`, `'sex'`
* **`mata_pelajaran`** ➔ `'mapel'`, `'mata pelajaran'`, `'subjek'`, `'subject'`
* **`jabatan`** ➔ `'jabatan'`, `'posisi'`, `'role'`
* **`no_hp`** ➔ `'no hp'`, `'no telp'`, `'no handphone'`, `'no telepon'`, `'phone'`
* **`status`** ➔ `'status'`, `'keaktifan'`

### 7.4 Controller (`GoogleSheetsGuruSettingController` atau Extended `GoogleSheetsSettingController`)

Direkomendasikan menggunakan controller terpisah `GoogleSheetsGuruSettingController` untuk menjaga kerapian kode (Separation of Concerns) atau memperluas controller lama dengan parameter dinamis. Controller ini harus menangani:
- Penyimpanan pengaturan Google Sheet khusus data guru (`type = 'guru'`).
- Aksi `previewMapping()` yang memuat sheet data guru dan menampilkan deteksi pemetaan kolom sebelum dieksekusi.
- Aksi `syncGuru()` yang melakukan verifikasi data, memicu job `GoogleSheetsGuruSyncJob`, dan merespons status awal.
- Aksi `processQueue()` untuk eksekusi Artisan cmd `queue:work` lokal.

### 7.5 Penanganan Queue Job (`GoogleSheetsGuruSyncJob`)

- Memproses data guru secara berulang per *chunk* (misal 50 baris per batch).
- Mengabaikan baris yang tidak memiliki informasi kunci unik (`nip` kosong).
- Melakukan upsert (update or create) data guru berdasarkan field `nip` untuk menghindari data ganda.

---

## 8. Rencana Pengujian (Test Cases)

| ID Test | Skenario Uji | Langkah Pengujian | Hasil yang Diharapkan |
|---|---|---|---|
| **TC-01** | Setup Konfigurasi Guru | 1. Buka form konfigurasi Google Sheets Guru.<br>2. Unggah file Service Account JSON, masukkan Spreadsheet ID.<br>3. Klik "Simpan". | Data berhasil disimpan di tabel `google_sheet_settings` dengan nilai `type` bernilai `'guru'`. |
| **TC-02** | Preview Pemetaan Kolom Guru | 1. Buka panel preview mapping.<br>2. Verifikasi daftar kolom database guru yang terpetakan otomatis. | Semua kolom standar guru terdeteksi dengan tepat sesuai sinonim header yang diisi di Google Sheets. |
| **TC-03** | Sinkronisasi Massal (Upsert) | 1. Buat data guru baru dan ubah nama guru lama di Google Sheets.<br>2. Jalankan tombol sinkronisasi.<br>3. Proses antrian. | Guru baru berhasil terdaftar di database, guru lama berhasil diperbarui namanya, tidak ada duplikasi data berdasarkan NIP. |
| **TC-04** | Handling Header Tidak Valid | 1. Masukkan header tidak standar (misal: "Alamat Rumah") di Google Sheets Guru.<br>2. Jalankan sinkronisasi. | Sistem melewati kolom tersebut tanpa menggagalkan proses sinkronisasi kolom-kolom utama lainnya. |
| **TC-05** | Pemicu Antrian Lokal via AJAX | 1. Atur `APP_ENV=local`.<br>2. Picu sinkronisasi guru.<br>3. Klik tombol "Proses Antrian". | Job dieksekusi secara asinkron, mengembalikan status sukses, dan halaman web melakukan reload/update status data guru tanpa perlu menjalankan queue worker di terminal manual. |

---

_Dokumen ini disusun untuk menjadi acuan resmi implementasi fitur Sinkronisasi Google Sheets untuk Data Guru._
