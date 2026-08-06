# PRD-009: Pelacakan & Autentikasi Individual Guru Piket (Public Scanner & Rekap Saya)

| Metadata | Detail |
|---|---|
| **PRD ID** | PRD-009 |
| **Fitur** | Autentikasi Individual Guru Piket & Pelangganan Presensi ("Rekap Piket Saya") |
| **Versi** | 1.0 |
| **Status** | In Review (Menunggu Persetujuan Pengguna) |
| **Prioritas** | High / Critical (Akuntabilitas Operasional Sekolah) |
| **Tanggal** | 6 Agustus 2026 |
| **Target Role** | Guru Piket, Admin Sekolah, Super Admin |

---

## 1. Latar Belakang & Tujuan

### 1.1 Masalah Saat Ini
1. **Password Global Tanpa Akuntabilitas**:
   Halaman scanner gerbang publik (`/scan-qr`) sebelumnya dilindungi oleh **satu Password Kiosk Global**. Siapa pun yang mengetahui password tersebut dapat membuka scanner tanpa adanya pencatatan siapa individu Guru Piket yang bertanggung jawab pada shift tersebut.
2. **Ketiadaan Data Pencatat**:
   Tabel `absensi_siswa` tidak menyimpan rincian `dicatat_oleh` (User ID Guru Piket) saat proses scan QR gerbang atau absensi cepat dilakukan. Akibatnya, pihak sekolah/admin tidak dapat memverifikasi atau mengaudit guru piket mana yang men-scan/menginput kehadiran siswa tertentu.
3. **Kebutuhan Laporan Shift Piket**:
   Guru Piket yang bertugas tidak memiliki halaman rekapitulasi khusus untuk melihat dan mencetak hasil kerja *shift* piket beliau sendiri (*"Rekap Piket Saya"*).

### 1.2 Tujuan Utama (Objectives)
1. **Penerapan *Strict Responsibility***:
   Menghapus penggunaan password global. Membuka halaman scanner gerbang **wajib** menggunakan password akun pribadi Guru Piket resmi yang terdaftar (role `piket`).
2. **Smart Password Authentication (Single Field)**:
   Mempertahankan UX halaman login scanner yang simpel dengan **1 kolom input Password saja** (tanpa input Username/NIP). Sistem secara otomatis mengidentifikasi akun Guru Piket pemilik password tersebut di latar belakang.
3. **Pencatatan Permanen (`dicatat_oleh`)**:
   Menyimpan `user_id` Guru Piket secara otomatis di setiap baris transaksi `absensi_siswa` yang dihasilkan selama sesi piket berlangsung.
4. **Halaman & Menu "Rekap Piket Saya"**:
   Menyediakan menu dan halaman laporan khusus bagi Guru Piket YBS di `/piket/rekap-saya` lengkap dengan ringkasan statistik shift, tabel detail presensi, dan fitur cetak/ekspor laporan PDF/Excel.

---

## 2. Kebutuhan Pengguna (User Stories)

| ID Story | Peran (User) | Keinginan (Action) | Manfaat (Benefit) |
|---|---|---|---|
| **US-01** | Guru Piket | Membuka scanner gerbang hanya dengan menginputkan password akun pribadinya di 1 kolom password. | Tidak perlu repot memilih nama/memasukkan NIP, namun identitas dirinya langsung dikenali sistem secara otomatis. |
| **US-02** | Guru Piket | Melihat salam pembuka *"Selamat bertugas, [Nama Guru]"* di layar scanner setelah login berhasil. | Memastikan bahwa sesi scanner gerbang yang aktif telah terhubung ke akun pribadinya. |
| **US-03** | Admin Sekolah | Melacak secara pasti siapa Guru Piket yang men-scan atau mengubah data absensi siswa tertentu. | Mencegah saling lempar tanggung jawab dan meningkatkan kedisiplinan piket harian. |
| **US-04** | Guru Piket | Membuka menu **"Rekap Piket Saya"** di portal piket. | Melihat statistik hasil scan shift pribadinya (Hadir, Terlambat, Pulang) dan mencetak bukti laporan kerja shift piket. |
| **US-05** | Sistem | Menolak akses login scanner jika password yang dimasukkan tidak cocok dengan akun Guru Piket aktif mana pun. | Mencegah akses tak berizin dan menjaga akuntabilitas sistem. |

---

## 3. Spesifikasi Teknis & Alur Data

### 3.1 Skema Database (Database Migration)
Menambahkan kolom pelacakan `dicatat_oleh` pada tabel `absensi_siswa`:
```sql
ALTER TABLE `absensi_siswa` 
ADD COLUMN `dicatat_oleh` BIGINT UNSIGNED NULL AFTER `guru_id`,
ADD CONSTRAINT `absensi_siswa_dicatat_oleh_foreign` 
FOREIGN KEY (`dicatat_oleh`) REFERENCES `users`(`id`) ON DELETE SET NULL;
```

### 3.2 Alur Autentikasi Smart Password (`PublicQrScanController@auth`)
```text
[Input Password]
       │
       ▼
[Ambil Seluruh User dengan Role 'piket' & Status Aktif]
       │
       ▼
[Looping & Check Hash::check($password, $user->password)]
       ├── (Cocok) ──► Set Session:
       │               - qr_scan_authenticated = true
       │               - piket_user_id = $user->id
       │               - piket_user_name = $user->name
       │               Log Activity: "Guru Piket [Nama] membuka sesi scanner"
       │               Redirect ke /scan-qr/scan
       │
       └── (Tidak Ada Yang Cocok) ──► Return Error:
                       "Password salah atau Anda tidak memiliki akses sebagai Guru Piket."
```

### 3.3 Alur Presensi & Saving Track (`PublicQrScanController@process` & `PiketScannerController@process`)
Saat proses scan QR siswa berhasil:
```php
AbsensiSiswa::updateOrCreate(
    ['siswa_id' => $siswa->id, 'tanggal' => $tanggal],
    [
        'kelas_id'     => $siswa->kelas_id,
        'jam_masuk'    => $jamMasuk,
        'status'       => $status,
        'metode'       => 'qr_piket',
        'dicatat_oleh' => session('piket_user_id') ?? auth()->id(),
    ]
);
```

---

## 4. Spesifikasi UI / UX & Menu Sidebar

### 4.1 Halaman Login Scanner (`/scan-qr`)
- Tetap menggunakan layout 1 kolom input password yang bersih dan responsif.
- Label input: `Password Akun Guru Piket`.
- Teks info footer: *"Masukkan password akun Guru Piket Anda untuk membuka scanner."*

### 4.2 Halaman Scanner Aktif (`/scan-qr/scan`)
- Menampilkan badge status di pojok atas:
  ```html
  <div class="piket-operator-badge">
    <i class="ti tabler-user-check text-success me-1"></i>
    Petugas Piket: <strong>Bpk. Ahmad, S.Pd.</strong>
  </div>
  ```

### 4.3 Sidebar Menu Piket (`resources/menu/vertical_piket.json`)
Struktur menu baru di bawah kelompok **Laporan & Rekapitulasi**:
```json
{
  "menuHeader": "Laporan & Rekapitulasi",
  "icon": "menu-icon ti tabler-report-analytics"
},
{
  "name": "Rekap Piket Saya",
  "icon": "menu-icon ti tabler-user-check",
  "slug": "piket.rekap-saya",
  "url": "/piket/rekap-saya"
},
{
  "name": "Rekap Harian Sekolah",
  "icon": "menu-icon ti tabler-report",
  "slug": "piket.rekap",
  "url": "/piket/rekap"
},
{
  "name": "Rekap Absensi Siswa",
  "icon": "menu-icon ti tabler-report-analytics",
  "slug": "piket.laporan.index",
  "url": "/piket/rekap-absensi"
}
```

### 4.4 Halaman "Rekap Piket Saya" (`/piket/rekap-saya`)
- **Stat Cards (Top Grid)**:
  - Total Scan Saya Hari Ini
  - Total Hadir Tepat Waktu (Discan Saya)
  - Total Terlambat (Discan Saya)
  - Total Izin / Pulang Cepat (Discan Saya)
- **Filter Panel**: Filter Tanggal / Rentang Waktu, Filter Status, dan Input Pencarian Nama Siswa.
- **Tabel Data**: Nomor, Waktu Scan, NISN/Siswa, Kelas, Status Presensi, Jenis (Scan Masuk / Scan Pulang), Keterangan.
- **Tombol Action Header**:
  - `[Cetak Laporan Shift PDF]`
  - `[Ekspor Excel]`

---

## 5. Rencana Pengujian (Verification Plan)

### 5.1 Pengujian Unit & Logika Autentikasi
1. Coba login scanner dengan password milik akun Guru Piket A -> **Harus Berhasil**, session `piket_user_id` mencatat ID Guru Piket A.
2. Coba login scanner dengan password umum/random -> **Harus Ditolak** dengan pesan error.
3. Coba login scanner dengan password akun Siswa / Ortu -> **Harus Ditolak** (karena bukan role `piket`).

### 5.2 Pengujian Data Tracking
1. Lakukan scan QR 3 siswa saat Guru Piket A login -> Cek tabel `absensi_siswa`, kolom `dicatat_oleh` harus terisi `ID_Guru_Piket_A`.
2. Ganti sesi login ke Guru Piket B dan scan 2 siswa -> Kolom `dicatat_oleh` pada 2 siswa tersebut terisi `ID_Guru_Piket_B`.

### 5.3 Pengujian Halaman Rekap Piket Saya
1. Buka `/piket/rekap-saya` saat terhubung sebagai Guru Piket A -> **Hanya menampilkan** 3 siswa yang di-scan oleh Guru Piket A.
2. Buka `/piket/rekap-saya` saat terhubung sebagai Guru Piket B -> **Hanya menampilkan** 2 siswa yang di-scan oleh Guru Piket B.
3. Uji coba cetak PDF laporan shift piket.

---

## 6. Catatan Keamanan (Security Notes)
- Password tidak pernah ditulis ke log teks. Seluruh verifikasi menggunakan `Hash::check()`.
- Sesi `piket_user_id` otomatis dibersihkan saat Guru Piket melakukan logout atau sesi berakhir (*session timeout*).
- Setiap aktivitas login scanner dicatat di `ActivityLog` dengan tag `piket_scan_login`.
