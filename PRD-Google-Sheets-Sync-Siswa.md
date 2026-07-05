# Product Requirements Document (PRD)

## Perbaikan Fitur Sinkronisasi Google Sheets (Data Siswa)

| Field      | Detail                                                |
| ---------- | ----------------------------------------------------- |
| PRD ID     | PRD-004-google-sheets-sync-siswa                      |
| Versi      | 1.0                                                   |
| Status     | Draft                                                 |
| Penulis    | Antigravity (PRD Specialist)                          |
| Tanggal    | 2026-07-05                                            |
| Prioritas  | High                                                  |
| RICE Score | 82 (Reach: 3, Impact: 3, Confidence: 80%, Effort: 11) |

---

## 1. Ringkasan

Dokumen ini menjelaskan kebutuhan perbaikan fitur sinkronisasi data siswa melalui Google Sheets di halaman Daftar Siswa (`/admin/siswa`). Peningkatan ini berfokus pada penyelarasan mekanisme sinkronisasi dengan pengaturan utama Google Sheets, penghapusan konfigurasi manual berbasis JSON (`column_mapping`), penerapan **auto-detect mapping** yang cerdas dan mandiri, serta penyederhanaan alur kerja antrian (queue) untuk kemudahan _local development_ menggunakan command semi-otomatis `queue:work --once`.

---

## 2. Latar Belakang & Tujuan

### 2.1 Latar Belakang

Saat ini, fitur sinkronisasi Google Sheets mewajibkan Admin untuk mengonfigurasi `column_mapping` secara manual menggunakan format JSON yang rumit dan rentan kesalahan (_human error_). Selain itu, sistem belum terintegrasi secara mulus antara konfigurasi utama dan aksi eksekusi sinkronisasi. Pada sisi pengembangan lokal (_local development_), memproses antrian job sinkronisasi (queue) secara _real-time_ terus-menerus membebani sumber daya server lokal, sehingga diperlukan mekanisme pemicu antrian yang lebih fleksibel dan terkontrol.

### 2.2 Dampak jika tidak diselesaikan

1. **User Experience Buruk:** Admin sekolah kesulitan melakukan setup karena harus memahami sintaksis JSON hanya untuk memetakan kolom (seperti `nis`, `nama_lengkap`, dll).
2. **Inkonsistensi Data:** Kesalahan penulisan JSON manual dapat mengakibatkan kegagalan sinkronisasi atau data masuk ke kolom yang salah di database.
3. **Beban Resource Lokal:** Pengembang lokal harus menjalankan queue worker terus-menerus (`queue:work` atau `queue:listen`), yang memakan RAM dan CPU secara tidak perlu di mesin lokal.

### 2.3 Solusi yang Diusulkan

1. **Auto-Detect Mapping:** Memanfaatkan `MappingService` yang sudah ada untuk mendeteksi pemetaan kolom secara otomatis berdasarkan kemiripan nama header di Google Sheets (misalnya, header "Nomor Induk Siswa" atau "No Induk" akan langsung dikenali sebagai `nis`).
2. **Kemandirian Pengaturan (Zero JSON Config):** Admin cukup menghubungkan Service Account JSON, membuat/menghubungkan Spreadsheet, dan sinkronisasi bisa langsung berjalan tanpa perlu melakukan pengisian JSON mapping manual.
3. **Opsi "Proses Antrian" (Semi-Otomatis) di Lokal:** Menambahkan tombol pemicu manual untuk memproses _queue job_ yang tertunda via AJAX menggunakan perintah `queue:work --once --queue=syncs` pada lingkungan _development_ lokal.

---

## 3. Tujuan & Metrik Keberhasilan

| Tujuan                                               | Metrik                                           | Target                                              |
| ---------------------------------------------------- | ------------------------------------------------ | --------------------------------------------------- |
| Mempermudah proses setup sinkronisasi Google Sheets  | Waktu konfigurasi pertama kali oleh Admin        | < 2 menit (sebelumnya > 10 menit karena setup JSON) |
| Menghilangkan kegagalan akibat kesalahan format JSON | Persentase error akibat _malformed JSON mapping_ | 0% (karena input JSON dihilangkan dari UI utama)    |
| Mempermudah _local development_                      | Aksesibilitas eksekusi antrian tanpa terminal    | Tersedia tombol "Proses Antrian" di UI lokal        |

---

## 4. Scope (Ruang Lingkup)

### 4.1 In Scope

- Penyelarasan controller, service, dan view agar menggunakan **auto-detect mapping** secara penuh sebagai opsi utama.
- Penghapusan input konfigurasi JSON manual (`column_mapping`) dari UI pengaturan/sinkronisasi siswa.
- Pembuatan/pembaruan template Google Sheets yang langsung selaras dengan daftar kolom standar (`MappingService`).
- Penambahan tombol visual/interaksi untuk memicu pemrosesan antrian lokal (`queue:work --once --queue=syncs`) melalui AJAX.
- Pembaruan unit testing/feature testing untuk memverifikasi fungsionalitas auto-detect mapping saat sinkronisasi berjalan tanpa konfigurasi manual.

### 4.2 Out of Scope

- Integrasi dengan penyedia spreadsheet pihak ketiga selain Google Sheets (seperti Microsoft Excel Online).
- Sinkronisasi data selain Data Siswa (misalnya data guru atau data absensi harian) menggunakan Google Sheets.

---

## 5. User Stories & Acceptance Criteria

### 5.1 User Story: Konfigurasi Instan & Mandiri

> **Sebagai** Admin Sekolah,
> **Saya ingin** dapat menghubungkan Google Sheets hanya dengan mengunggah Service Account JSON dan mengisi Spreadsheet ID,
> **Sehingga** saya tidak perlu menyusun kode JSON secara manual untuk memetakan kolom data.

- **Acceptance Criteria 1 (AC-01) - Validasi Tanpa Mapping:**
  - **Given:** Admin berada di halaman `/admin/pengaturan/google-sheets`.
  - **When:** Admin mengunggah file Service Account JSON, mengisi Spreadsheet ID, mengosongkan/mengabaikan konfigurasi kolom mapping, dan mengklik "Simpan".
  - **Then:** Sistem berhasil menyimpan konfigurasi tanpa memunculkan error validasi _column mapping_.
- **Acceptance Criteria 2 (AC-02) - Preview Pemetaan Otomatis:**
  - **Given:** Admin telah mengisi kredensial dan ID Google Sheets.
  - **When:** Admin membuka panel preview mapping.
  - **Then:** Sistem secara otomatis mendeteksi kecocokan header sheet dengan kolom database (misal: "Nama Siswa" cocok dengan `nama_lengkap`) dan menampilkan status pemetaannya secara visual di layar.

### 5.2 User Story: Eksekusi Sinkronisasi Otomatis

> **Sebagai** Admin Sekolah,
> **Saya ingin** dapat langsung melakukan sinkronisasi dengan sekali klik setelah template dibuat,
> **Sehingga** data siswa di database terbarui secara instan.

- **Acceptance Criteria 3 (AC-03) - Sinkronisasi Tanpa JSON Setup:**
  - **Given:** Admin telah menghubungkan Service Account dan membuat/mengisi spreadsheet dengan template standar.
  - **When:** Admin mengklik tombol "Sinkronkan Sekarang".
  - **Then:** Sistem memicu job sinkronisasi ke dalam antrian (`GoogleSheetsSyncJob`) dan menggunakan auto-detect mapping untuk memproses baris data siswa tanpa kegagalan.

### 5.3 User Story: Eksekusi Antrian Lokal (Semi-Otomatis)

> **Sebagai** Developer / QA Tester di lingkungan lokal,
> **Saya ingin** memproses antrian job sinkronisasi dengan menekan tombol "Proses Antrian" di halaman web,
> **Sehingga** saya tidak perlu membiarkan terminal terus menjalankan command `queue:work` di latar belakang.

- **Acceptance Criteria 4 (AC-04) - Pemicu Queue Lokal:**
  - **Given:** Aplikasi berjalan di _environment_ lokal (`APP_ENV=local` atau opsi visual diaktifkan) dan terdapat job sinkronisasi yang berstatus pending di antrian database.
  - **When:** Pengembang mengklik tombol "Proses Antrian".
  - **Then:** Sistem memanggil `Artisan::call('queue:work', ['--once' => true, '--queue' => 'syncs'])` melalui AJAX dan mengembalikan status kemajuan sinkronisasi terbaru di halaman web.

---

## 6. Alur Pengguna (User Flow)

```
[Mulai]
   │
   ▼
[Halaman Pengaturan /admin/pengaturan/google-sheets]
   │
   ├─► 1. Hubungkan Service Account (Upload JSON Kredensial)
   ├─► 2. Masukkan Spreadsheet ID & Range (misal: Sheet1!A:Z)
   │
   ▼
[Klik "Buat / Gunakan Template"]
   │
   ├─► Sistem menuliskan 11 header standar di Google Sheet target
   │
   ▼
[Isi Data Siswa di Google Sheet]
   │
   ▼
[Klik "Sinkronkan Sekarang"]
   │
   ├─► Sistem memasukkan tugas ke antrian (GoogleSheetsSyncJob)
   │
   ▼
[Deteksi Environment]
   │
   ├─► Jika Live/Production: Diproses otomatis oleh supervisor queue runner.
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
[Sinkronisasi Selesai & Data Siswa Masuk ke Database]
```

---

## 7. Spesifikasi Teknis & Dampak Kode

### 7.1 Struktur Model & Database

- Model `GoogleSheetSetting` tetap memiliki properti `column_mapping`, namun properti ini secara bawaan bernilai `null` atau array kosong jika tidak diisi manual.
- Proses sinkronisasi akan mengutamakan _auto-detect mapping_ melalui `MappingService`.

### 7.2 Logika Pemetaan Kolom (MappingService)

Sistem menggunakan deteksi string fleksibel untuk mencocokkan header Google Sheets dengan kolom database:

- `nis` ➔ 'nis', 'nipd', 'no induk', 'nomor induk', 'no.induk', 'nomorinduk', 'nis siswa', 'no induk siswa', 'nomor induk siswa'
- `nama_lengkap` ➔ 'nama', 'nama lengkap', 'nama siswa', 'namalengkap', 'nama_lengkap', 'fullname', 'full name', 'nama_siswa'
- `nisn` ➔ 'nisn', 'no nisn', 'nomor nisn', 'nisn siswa', 'no induk nasional'
- `kelas_nama` ➔ 'kelas', 'nama kelas', 'kelas_nama', 'kelasnama', 'class', 'class name', 'kelas saat ini'
- Dan kolom lainnya (`tempat_lahir`, `tanggal_lahir`, `jenis_kelamin`, `alamat`, `no_hp`, `no_hp_ortu`, `tahun_akademik_nama`).

### 7.3 Penyesuaian Backend & Controller

1. **GoogleSheetsSettingController:**
   - Menghapus validasi wajib untuk `column_mapping` pada aksi update pengaturan.
   - Mengintegrasikan `previewMapping()` agar secara dinamis menyajikan preview mapping otomatis bahkan ketika `column_mapping` di database bernilai `null` atau kosong.
   - Mempertahankan `processQueue()` yang menjalankan `Artisan::call('queue:work', ['--once' => true, '--queue' => 'syncs'])` dan mengembalikan output log ke frontend.
2. **GoogleSheetsService:**
   - Memperkuat metode `syncSiswa()`. Ketika array `column_mapping` dari database kosong, sistem langsung menjalankan `MappingService->detectMapping($headers)` untuk mendapatkan skema pemetaan secara dinamis.
3. **GoogleSheetsSyncJob:**
   - Memproses data per _chunk_ (ukuran default: 50 baris) dan secara rekursif memicu _job_ berikutnya jika masih terdapat baris data yang belum diproses.

---

## 8. Rencana Pengujian (Test Cases)

| ID Test | Skenario Uji                         | Langkah Pengujian                                                                                     | Hasil yang Diharapkan                                                                                         |
| ------- | ------------------------------------ | ----------------------------------------------------------------------------------------------------- | ------------------------------------------------------------------------------------------------------------- |
| TC-01   | Setup Awal Tanpa JSON Mapping        | 1. Isi Kredensial JSON dan Spreadsheet ID.<br>2. Kosongkan input manual JSON.<br>3. Klik Simpan.      | Pengaturan berhasil disimpan tanpa error validasi.                                                            |
| TC-02   | Preview Auto-Detect Mapping          | 1. Buka halaman pengaturan.<br>2. Klik tombol "Preview Mapping".                                      | Menampilkan tabel kecocokan kolom database berdasarkan header Google Sheets yang terdeteksi secara dinamis.   |
| TC-03   | Eksekusi Sync dengan Auto-Detect     | 1. Jalankan sinkronisasi dengan status `column_mapping` kosong.<br>2. Proses antrian.                 | Data siswa tersinkronisasi dengan sukses ke database berdasarkan pencocokan header otomatis.                  |
| TC-04   | Penanganan Header Tidak Dikenal      | 1. Masukkan data di Google Sheet dengan header asing (misal: "Kode Pos").<br>2. Lakukan sinkronisasi. | Sistem mengabaikan kolom tidak dikenal tersebut tanpa menghentikan proses sinkronisasi kolom standar lainnya. |
| TC-05   | Pemicu Antrian Lokal (Semi-Otomatis) | 1. Di lingkungan lokal, picu sinkronisasi.<br>2. Klik tombol "Proses Antrian" di halaman pengaturan.  | Job antrian dieksekusi secara instan dan status sinkronisasi di UI berubah menjadi sukses/selesai.            |

---

_Dokumen ini dibuat secara otomatis dan disetujui untuk menjadi acuan pengembangan perbaikan fitur Sinkronisasi Google Sheets._
