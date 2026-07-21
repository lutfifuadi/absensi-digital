# PRD: Kegiatan Multi-Day & Live Board Absensi Kegiatan + Scanner

| Field | Detail |
|-------|--------|
| PRD ID | PRD-002 |
| Versi | 1.0 |
| Status | Implemented |
| Penulis | Sophia (PM Manager) |
| Tanggal | 2026-07-21 |
| Prioritas | High |
| Target Release | Sprint Agustus 2026 (Minggu 1-2) |
| RICE Score | 2000 |

---

## 1. Ringkasan

PRD ini mendokumentasikan kebutuhan dan implementasi untuk dua fitur utama terkait pengelolaan kegiatan sekolah dan absensinya:

1. **Kegiatan Multi-Day** — Mendukung pencatatan kegiatan sekolah yang berlangsung lebih dari 1 hari dengan rentang tanggal pelaksanaan (`tanggal_pelaksanaan` s/d `tanggal_selesai`). Absensi siswa direkam secara harian (`tanggal_absen`) agar dapat memantau kehadiran hari demi hari pada kegiatan tersebut.
2. **Live Board Absensi Kegiatan + Hardware Scanner** — Menyediakan antarmuka layar penuh interaktif (ala *live-board* absensi harian) khusus untuk memantau scan absensi kegiatan secara real-time. Halaman ini dioptimalkan untuk memproses scan dari alat scanner kartu fisik USB/Bluetooth (HID keyboard) dengan loop auto-focus konstan dan feedback suara (*synthesized beep*) langsung lewat browser.

---

## 2. Latar Belakang & Masalah

### Masalah Saat Ini

- **Kegiatan Terbatas 1 Hari** — Sistem lama hanya memiliki kolom `tanggal_pelaksanaan` (tunggal). Akibatnya, kegiatan seperti LDKS (Latihan Dasar Kepemimpinan Siswa), Perkemahan Sabt-Minggu, atau Ujian yang berlangsung beberapa hari berturut-turut tidak dapat didata dengan baik.
- **Absensi Kegiatan Terbatas Sekali** — Karena absensi kegiatan tidak merekam informasi tanggal absen, satu siswa hanya bisa terdata hadir satu kali dalam satu kegiatan. Pada kegiatan multi-day, kehadiran hari ke-2 atau ke-3 tidak dapat direkam.
- **Tidak Ada Tampilan Layar Lebar Real-time** — Proses absensi kegiatan di aula atau gerbang tidak bisa ditampilkan secara megah di monitor/proyektor publik seperti halnya absensi harian sekolah.
- **Kurang Optimal untuk Barcode Scanner Fisik** — Petugas kesulitan melakukan absensi massal menggunakan alat *barcode scanner* USB karena harus mengklik input field secara manual setiap kali fokus hilang.

### Solusi yang Diusulkan

1. Menambahkan kolom `tanggal_selesai` di tabel `kegiatan` dan kolom `tanggal_absen` di tabel `absensi_kegiatan`.
2. Menghadirkan halaman **Live Board Kegiatan** (`/admin/absensi-kegiatan/live-board/{id}`) khusus yang memuat statistik kehadiran kegiatan, counter, detail profil siswa yang baru saja sukses scan, serta log scan terbaru.
3. Menanamkan *guard loop focus* (timer JavaScript 300ms) pada input scanner agar alat scanner HID keyboard selalu aktif mengirimkan data tanpa perlu klik manual.
4. Mengintegrasikan browser Web Audio API untuk memutar suara penanda sukses (high pitch beep) atau gagal (low pitch buzz).

---

## 3. Tujuan & Metrik Keberhasilan

| Tujuan | Metrik | Target |
|--------|--------|--------|
| Mendukung kegiatan multi-day | % kegiatan multi-day terdaftar dengan benar di database | 100% |
| Pencatatan absensi harian kegiatan | Jumlah data absensi per siswa per hari pada kegiatan multi-day | Sesuai jumlah hari kehadiran |
| Penggunaan alat scanner fisik lancar | Waktu henti (downtime/fokus hilang) scanner saat pemrosesan | 0 detik (selalu auto-refocus) |
| Feedback suara instan | Latensi suara penanda setelah kartu sukses di-tap | < 50ms (instan) |

---

## 4. Scope

### In Scope

- **Database Changes**:
  - Kolom `tanggal_selesai` (date, nullable) pada tabel `kegiatan`.
  - Kolom `tanggal_absen` (date, nullable) pada tabel `absensi_kegiatan`.
  - Script migrasi seeder untuk melengkapi data `tanggal_absen` pada absensi kegiatan lama.
- **Backend (Controller & Model)**:
  - Validasi range tanggal pelaksanaan kegiatan di `KegiatanController`.
  - Method `liveBoard()` di `AbsensiKegiatanController` untuk menyiapkan statistik target peserta, jumlah hadir, jumlah alpha, dan recent logs hari ini.
  - Method `liveBoardScan()` untuk memproses request scan QR via AJAX, memvalidasi range tanggal kegiatan (apakah hari ini masih aktif), mengecek hak target peserta, menyimpan absensi harian, dan mengembalikan data JSON ter-update.
- **Frontend (UI views)**:
  - Input `tanggal_selesai` pada form tambah & edit kegiatan.
  - Info range tanggal pelaksanaan pada tabel daftar kegiatan dan info detail kegiatan.
  - View `admin/kegiatan/live-board.blade.php` fullscreen dark-theme glassmorphism dengan auto-focus HID loop input & Web Audio API beep sound.
  - Tombol icon live-board desktop-analytics di halaman daftar absensi kegiatan.

### Out of Scope

- Notifikasi WhatsApp otomatis untuk absensi kegiatan.
- Pembatasan jam scan berdasarkan sesi waktu (waktu_mulai & waktu_selesai) — pengecekan hanya berdasarkan range tanggal pelaksanaan.

---

## 5. User Stories

| # | Sebagai | Saya ingin | Sehingga |
|---|---------|------------|----------|
| US-01 | Admin/Operator | Mengatur tanggal mulai dan tanggal selesai pada kegiatan sekolah | Kegiatan yang berlangsung lebih dari 1 hari terdokumentasi dengan benar |
| US-02 | Admin/Petugas | Menampilkan halaman Live Board Kegiatan di layar proyektor aula | Siswa dan panitia dapat melihat status kehadiran live saat memasuki ruangan |
| US-03 | Petugas | Menggunakan barcode scanner USB untuk men-scan kartu siswa tanpa menyentuh mouse/keyboard | Proses absensi berjalan sangat cepat dan hands-free |
| US-04 | Siswa | Mendengar bunyi beep yang jelas saat menempelkan kartu saya ke alat scanner | Saya yakin absensi saya telah berhasil terekam tanpa harus melihat ke layar proyektor |

---

## 6. Acceptance Criteria

| # | Given | When | Then |
|---|-------|------|------|
| **AC-01** | Admin membuat kegiatan baru dengan tanggal mulai 2026-07-21 dan selesai 2026-07-23 | Admin menyimpan form | Kegiatan terbuat dan tersimpan dengan range tanggal pelaksanaan yang benar |
| **AC-02** | Halaman Live Board Kegiatan dibuka untuk kegiatan multi-day | Siswa men-scan kartu QR pada tanggal 2026-07-21 | Sistem mencatat kehadiran siswa dengan `tanggal_absen = 2026-07-21` |
| **AC-03** | Siswa yang sama men-scan kartunya kembali pada keesokan harinya (2026-07-22) | Siswa men-scan kartu QR | Sistem mengizinkan dan mencatat kehadiran siswa dengan `tanggal_absen = 2026-07-22` |
| **AC-04** | Siswa men-scan kartunya pada tanggal yang sama untuk kedua kalinya | Siswa men-scan kartu QR | Sistem menolak dengan pesan "Siswa sudah melakukan absensi kegiatan hari ini" |
| **AC-05** | Tanggal saat ini berada di luar rentang tanggal kegiatan (misal kegiatan selesai 2026-07-23, hari ini 2026-07-24) | Siswa mencoba men-scan kartu | Sistem menolak scan dan mengembalikan status error 422 "Kegiatan sudah selesai" |
| **AC-06** | Fokus mouse dipindahkan ke tombol lain atau ke luar browser | Timer 300ms berjalan | Fokus secara otomatis dikembalikan ke elemen `#hw-scanner-input` agar siap menerima scan selanjutnya |
| **AC-07** | Scan absensi berhasil diproses | AJAX mengembalikan sukses | Browser memutar bunyi bip frekuensi tinggi (chime) |
| **AC-08** | Scan gagal diproses (duplikat / salah target / tidak dikenal) | AJAX mengembalikan error | Browser memutar bunyi bip frekuensi rendah (buzz) |

---

## 7. Estimasi & Timeline

| Task | Estimasi | Assigned To |
|------|----------|-------------|
| Migration: `tanggal_selesai` (kegiatan) & `tanggal_absen` (absensi_kegiatan) | 1.5 jam | Kang Encep |
| Seeder: Backfill data `tanggal_absen` pada absensi kegiatan lama | 1 jam | Kang Encep |
| Backend: Integrasi tanggal_selesai pada KegiatanController store/update | 1.5 jam | Kang Bayu |
| Frontend: Input form create/edit & info index kegiatan | 2 jam | Teh Ayu |
| Backend: Logika liveBoard & liveBoardScan di AbsensiKegiatanController | 3 jam | Kang Bayu |
| Frontend: View live-board.blade.php (fullscreen CSS, Web Audio, HID loop) | 5 jam | Teh Ayu |
| Frontend: Tombol entry live board di view absensi.blade.php | 0.5 jam | Teh Ayu |
| Testing: KegiatanMultiDayLiveBoardTest | 2 jam | Kang Asep |
| **Total** | **~16.5 jam** | |

---

## Changelog

| Versi | Tanggal | Perubahan | Oleh |
|-------|---------|-----------|------|
| 1.0 | 2026-07-21 | Initial draft & full implementation | Sophia |

## Approval

| Role | Nama | Status | Tanggal |
|------|------|--------|---------|
| Product Owner | Mas Lutfi | Approved | 2026-07-21 |
| Tech Lead | — | Approved | 2026-07-21 |
