# Analisis Fitur Scan QR - Aplikasi Presensi
**Tanggal:** 25 Juli 2026
**Status:** Research Complete

---

## RINGKASAN ARSITEKTUR

Aplikasi ini memiliki **5 mode Scan QR** yang berbeda:

| No | Mode | Siapa yang Scan | Target | Endpoint |
|----|------|-----------------|--------|----------|
| 1 | **Scan QR Publik** | Guru Piket (tanpa login, pakai password) | Siswa, Guru, Staff TU | `/scan-qr/process` |
| 2 | **Scan QR Piket Internal** | Guru Piket (login) | Siswa, Guru | `/piket/scanner/process` |
| 3 | **Live Board Publik** | Siapa saja (tanpa login) | Siswa, Guru, Staff TU | `/live-board/scan` |
| 4 | **Scan QR Kegiatan** | Admin/Siswa | Siswa (kegiatan) | `/admin/absensi-kegiatan/store` |
| 5 | **Scan QR Ekskul** | Siswa (scan QR dari Pembina) | Siswa | `/api/ekskul/absensi/scan/{token}` |

## LIBRARY

- **jsQR v1.4.0** — QR decoding client-side (semua halaman)
- **Endroid QR Code** — Generate QR image (ekskul)
- **Bootstrap 5.3.2** — UI framework

---

## POTENSI MASALAH PERFORMA

1. **Scan Rate tanpa limit pada ekskul**: `scan-ekskul.blade.php` memanggil `jsQR()` di setiap frame tanpa throttle (beda dengan scan-qr-scan yang punya 150ms interval).
2. **Cache settings inkonsisten**: PublicQrScanController cache 1 hari, PiketScannerController cache 10 menit.
3. **Query N+1 pada Live Board**: `getLeaderboardData()` memuat relasi dalam loop.
4. **Tidak ada rate limiting per-user**: Satu device bisa scan ratusan QR tanpa batasan.

## POTENSI MASALAH UX

1. **Tidak ada validasi GPS/lokasi** — Scanner bisa diakses dari mana saja.
2. **QR Code statis (tidak rotating)** untuk absensi utama — Risiko abuse.
3. **Dashboard scanQrAjax() tidak ada logika waktu** — Siswa bisa absen kapan saja.
4. **Live Board mode masuk bisa update record** — Jam masuk bisa berubah.
5. **Error message copy-paste bug** — "Sesi scan masuk guru sudah ditutup" untuk siswa.
6. **Offline sync CSRF token expired** — Sync bisa gagal dengan 419 error.

## POTENSI MASALAH KEAMANAN

1. **QR Code statis = tidak aman** — String permanen bisa disalin/digunakan orang lain.
2. **Password scan QR tidak di-rotate**.
3. **Device UUID hanya berbasis cookie** — Bisa dibuat custom.
4. **Live Board throttle 300/menit** — Terlalu tinggi, hampir tidak ada efek.

---

## FILE RELEVAN

### Controllers
- `app/Http/Controllers/PublicQrScanController.php`
- `app/Http/Controllers/PiketScannerController.php`
- `app/Http/Controllers/DashboardController.php`
- `app/Http/Controllers/Admin/AbsensiKegiatanController.php`
- `app/Http/Controllers/Admin/EkskulAbsensiController.php`
- `app/Http/Controllers/Api/EkskulAbsensiScanController.php`

### Views
- `resources/views/public/scan-qr-login.blade.php`
- `resources/views/public/scan-qr-scan.blade.php`
- `resources/views/public/live-board.blade.php`
- `resources/views/piket/scanner.blade.php`
- `resources/views/scan-ekskul.blade.php`

### Models
- `app/Models/Siswa.php`, `app/Models/Guru.php`, `app/Models/StaffTataUsaha.php`
- `app/Models/AbsensiSiswa.php`, `app/Models/AbsensiGuru.php`, `app/Models/AbsensiStaff.php`
- `app/Models/EkskulAbsensi.php`, `app/Models/Kegiatan.php`

### Services & Support
- `app/Services/EkskulAbsensiService.php`
- `app/Support/QrScanLogger.php`
- `app/Observers/AbsensiSiswaObserver.php`

### Routes
- `routes/web.php`, `routes/api.php`
