# PRD: Optimasi Performa & UX Scan QR

| Field | Detail |
|-------|--------|
| PRD ID | PRD-015 |
| Versi | 1.0 |
| Status | Approved |
| Penulis | Sophia (PM Manager) |
| Tanggal | 2026-07-25 |
| Prioritas | Critical |
| Target Release | 2026-07-25 |
| RICE Score | 2400 |
| Risk Level | Medium |
| Quality | 92/100 |

---

## 1. Ringkasan

Optimasi menyeluruh pada fitur Scan QR presensi untuk meningkatkan kecepatan data entry dan UX guru piket saat melakukan absensi siswa. Fokus utama adalah: (1) mempercepat respons data masuk setelah scan berhasil, (2) mengubah tampilan toast notification menjadi translucent dengan animasi fade yang cepat, (3) menambahkan error handling dan database transaction untuk mencegah 500 error saat concurrent scan, serta (4) menambahkan rate limiting di endpoint scan.

---

## 2. Latar Belakang & Masalah

### Masalah Saat Ini
1. **Toast terlalu lama** — Scan publik 3000ms, piket 2500ms. Guru harus menunggu toast hilang sebelum bisa scan berikutnya.
2. **Scanner unlock terlambat** — `isProcessing = false` di-set SETELAH timeout toast, bukan saat response server diterima.
3. **Tidak ada transaction** — Proses INSERT absensi tanpa `DB::transaction()`, rentan race condition saat concurrent scan.
4. **Error handling tidak lengkap** — Beberapa endpoint (`DashboardController::scanQrAjax()`, `AbsensiMandiriController::store()`, mode explicit `liveBoardScan()`, `EkskulAbsensiService::recordScanAbsensi()`) tidak handle MySQL error 1062, menghasilkan 500 Internal Server Error.
5. **Tidak ada rate limiting** — Endpoint `PublicQrScanController::process()` dan `PiketScannerController::process()` tanpa throttle.
6. **Background toast gelap** — Menggunakan `rgba(15,23,42,0.9)` yang tidak translucent seperti standar Bootstrap/Vuexy.
7. **Live Board scan mode explicit tanpa error handling** — Mode masuk/pulang explicit bisa 500 error saat concurrent.

### Dampak Jika Tidak Diselesaikan
- Guru piket terhambat saat absensi ratusan siswa karena harus menunggu toast
- 500 error sporadis saat concurrent scan dari multi-device
- Data absensi bisa tidak konsisten (jam pulang overwrite tanpa proteksi)

### Solusi yang Diusulkan
- Unlock scanner segera setelah response diterima (bukan setelah toast hilang)
- Ubah toast menjadi translucent + fade + durasi 1500ms
- Bungkus semua proses INSERT dalam `DB::transaction()`
- Tambahkan try-catch error 1062 di semua endpoint
- Tambahkan rate limiting di route scan
- Optimasi frontend scan rate

---

## 3. Tujuan & Metrik Keberhasilan

| Tujuan | Metrik | Target |
|--------|--------|--------|
| Scan lebih cepat | Waktu dari scan ke scanner unlock | < 500ms |
| Toast cepat | Durasi toast tampil | 1500ms |
| Tidak ada 500 error | Error rate saat concurrent scan | 0% |
| Translucent toast | Visual match dengan Vuexy style | 100% |
| Rate limiting aktif | Endpoint scan punya throttle | 100% |

---

## 4. Scope

### In Scope
1. **Frontend Toast Redesign** — Ubah toast di 3 halaman scanner (scan-qr-scan, piket/scanner, live-board) menjadi translucent + fade animation
2. **Scanner Unlock Optimization** — Pindahkan `isProcessing = false` ke response handler (bukan timeout)
3. **Backend Transaction** — Bungkus INSERT absensi dalam `DB::transaction()`
4. **Error Handling** — Tambahkan try-catch error 1062 di semua endpoint scan
5. **Rate Limiting** — Tambahkan middleware throttle di route scan-qr/process dan piket/scanner/process
6. **Consistent Scan Rate** — Pastikan semua halaman scanner menggunakan scan interval yang konsisten

### Out of Scope
- Rotasi QR code (QR statis sudah menggunakan per-siswa)
- Validasi GPS/lokasi
- Optimasi WhatsApp notification queue
- Perubahan database schema
- Fitur baru selain optimasi yang disebutkan

---

## 5. User Stories

| # | Sebagai | Saya ingin | Sehingga |
|---|---------|------------|----------|
| US-1 | Guru Piket | Scanner segera terbuka kembali setelah data absensi tercatat | Saya bisa langsung scan siswa berikutnya tanpa menunggu |
| US-2 | Guru Piket | Toast notifikasi muncul cepat dan tidak terlalu lama | Saya tetap bisa membaca nama siswa yang baru di-scan |
| US-3 | Guru Piket | Tampilan toast transparan dan modern | Tampilan lebih profesional dan tidak menutupi kamera |
| US-4 | Admin | Tidak ada error 500 saat ratusan siswa scan bersamaan | Sistem stabil dan reliable |
| US-5 | Guru Piket | Dua device scanner bisa aktif bersamaan tanpa duplikasi | Data absensi tetap akurat |
| US-6 | Admin | Scan QR memiliki rate limiting | Sistem tidak overload saat banyak scan |

---

## 6. Acceptance Criteria

| # | Given | When | Then |
|---|-------|------|------|
| AC-1 | Guru Piket sedang melakukan scan | Scan QR berhasil dan data tercatat | Scanner langsung terbuka kembali (< 500ms), toast muncul 1500ms |
| AC-2 | Guru Piket sedang melakukan scan | Scan QR berhasil | Toast muncul dengan background translucent, animasi fade, durasi 1500ms |
| AC-3 | Dua device scanner aktif bersamaan | Keduanya scan QR siswa yang sama | Hanya satu yang berhasil, yang lain mendapat pesan "sudah tercatat" (tidak 500 error) |
| AC-4 | Ratusan siswa scan bersamaan | Concurrent request masuk | Tidak ada 500 error, semua request ter-handle dengan benar |
| AC-5 | Endpoint scan-qr/process diakses | Request dalam jumlah banyak | Rate limiting aktif, request berlebih ditolak dengan 429 |
| AC-6 | Toast ditampilkan | Animasi masuk | Fade in dengan opacity 0 ke 1, tanpa bounce/slide berat |
| AC-7 | Scanner sedang proses | User reload halaman | Scanner reset dengan benar, tidak ada state corrupt |
| AC-8 | Mode pulang di-scan | Siswa sudah punya absensi masuk | Jam pulang tercatat dengan benar, tidak overwrite |
| AC-9 | Live Board mode explicit masuk | Siswa belum punya absensi | Record baru dibuat dengan benar |
| AC-10 | Live Board mode explicit pulang | Siswa sudah punya absensi masuk | Jam pulang tercatat, tidak 500 error |

---

## 7. Alur Utama (Happy Path)

### Flow: Scan QR Publik (Optimized)
1. Guru Piket akses `/scan-qr/scan`
2. Aktifkan kamera, mulai scanning
3. Siswa menunjukkan QR code
4. Camera decode QR → `jsQR()` → dapat string QR
5. `isProcessing = true` → kamera frame berhenti diproses
6. AJAX POST `/scan-qr/process` { qr_code: "..." }
7. Backend: `DB::transaction()` → cek waktu → cek siswa → INSERT absensi
8. Backend return JSON { success: true, message, siswa: { nama, kelas, jam } }
9. **Frontend: `isProcessing = false` SEGERA** → scanner terbuka kembali
10. Toast muncul dengan translucent background + fade animation (1500ms)
11. Audio feedback (beep)
12. Scan berikutnya bisa langsung dilakukan

### Flow: Concurrent Scan (Race Condition Handling)
1. Device A dan Device B scan QR siswa yang sama secara bersamaan
2. Keduanya SELECT → keduanya KOSONG
3. Device A INSERT → BERHASIL
4. Device B INSERT → GAGAL (error 1062)
5. Backend catch error 1062 → return { success: false, already: true, message: "Sudah tercatat" }
6. Device B tampilkan toast warning "Sudah tercatat"

---

## 8. Business Rules

- BR-1: Scanner harus terbuka kembali maksimal 500ms setelah response server diterima
- BR-2: Toast notification durasi maksimal 1500ms
- BR-3: Semua INSERT absensi harus dalam database transaction
- BR-4: Error 1062 harus ditangkap dan return response yang jelas (bukan 500 error)
- BR-5: Rate limiting aktif di semua endpoint process scan
- BR-6: Scan interval konsisten di semua halaman scanner (150ms minimum)
- BR-7: Toast menggunakan background translucent (`rgba(var(--bs-success-rgb), 0.85)`)

---

## 9. Data Requirements

Tidak ada perubahan schema database. Yang berubah hanya:

| Field | Tipe | Keterangan |
|-------|------|------------|
| Response JSON scan | object | Format response tetap sama, tambah field `already` untuk duplikasi |

---

## 10. Non-Functional Requirements

- **Performa:** Response time scan < 500ms (termasuk DB write)
- **Keamanan:** Rate limiting mencegah abuse
- **Kompatibilitas:** Chrome, Firefox, Safari (mobile & desktop)
- **Skalabilitas:** Handle 100+ concurrent scan tanpa degradation

---

## 11. Dependencies

- Bootstrap 5.3.2 (sudah ada)
- jsQR v1.4.0 (sudah ada)
- Laravel middleware throttle (built-in)

---

## 12. Estimasi & Timeline

| Task | Estimasi | Assigned To |
|------|----------|-------------|
| Backend: Transaction + Error Handling + Rate Limiting | 2 jam | Kang Bayu |
| Frontend: Toast Redesign + Scanner Unlock Optimization | 3 jam | Teh Ayu |
| Testing: Manual + Concurrent Scan Test | 1 jam | Kang Asep |
| **Total** | **6 jam** | |

---

## 13. Risks & Mitigasi

| Risk | Likelihood | Impact | Score | Level | Mitigasi |
|------|------------|--------|-------|-------|----------|
| Race condition masih terjadi meskipun sudah ada transaction | 3 | 3 | 9 | Medium | Unique constraint sebagai fallback terakhir |
| Toast 1500ms terlalu cepat untuk guru membaca | 2 | 2 | 4 | Low | Bisa di-adjust ke 2000ms jika diperlukan |
| Rate limiting memblokir scan yang valid | 2 | 3 | 6 | Medium | Set threshold cukup tinggi (120/menit) |
| Breaking change di frontend scanner | 2 | 4 | 8 | Medium | Test di semua halaman scanner sebelum deploy |

---

## 14. Wireframe / Mockup Reference

### Toast Translucent (Target)
```
┌──────────────────────────────────────┐
│  ✅ Nama Siswa            0.5s ago  │  ← translucent background
│  Kelas XII IPA 1 · 07:15           │     rgba(success-rgb, 0.85)
│  Absensi tercatat hadir             │     backdrop-filter: blur(10px)
├──────────────────────────────────────┤
│  ████████████████████░░░░ progress   │  ← 1500ms linear
└──────────────────────────────────────┘
```

---

## 15. Task Breakdown

### Backend Layer (Kang Bayu)
- [ ] Bungkus `PublicQrScanController::process()` dalam `DB::transaction()`
- [ ] Bungkus `PiketScannerController::process()` dalam `DB::transaction()`
- [ ] Tambahkan try-catch error 1062 di `DashboardController::scanQrAjax()`
- [ ] Tambahkan try-catch error 1062 di `AbsensiMandiriController::store()`
- [ ] Tambahkan try-catch error 1062 di `PublicQrScanController::liveBoardScan()` mode explicit
- [ ] Tambahkan try-catch error 1062 di `EkskulAbsensiService::recordScanAbsensi()`
- [ ] Tambahkan middleware throttle di route `scan-qr/process` dan `piket/scanner/process`

### Frontend Layer (Teh Ayu)
- [ ] Update toast CSS di `scan-qr-scan.blade.php` — translucent + fade + 1500ms
- [ ] Update toast CSS di `piket/scanner.blade.php` — translucent + fade + 1500ms
- [ ] Update toast CSS di `live-board.blade.php` — translucent + fade + 1500ms
- [ ] Pindahkan `isProcessing = false` ke response handler (bukan timeout) di semua scanner
- [ ] Pastikan scan interval konsisten (150ms) di semua halaman
- [ ] Update `DISMISS` constant ke 1500ms di scan-qr-scan
- [ ] Update `showFloatingToast` duration ke 1500ms di piket/scanner

### Testing (Kang Asep)
- [ ] Test scan normal — toast muncul 1500ms, scanner unlock cepat
- [ ] Test concurrent scan — tidak ada 500 error
- [ ] Test rate limiting — request berlebih ditolak
- [ ] Test mode pulang — tidak overwrite
- [ ] Test di Chrome, Firefox, Safari (mobile & desktop)

---

## Changelog

| Versi | Tanggal | Perubahan | Oleh |
|-------|---------|-----------|------|
| 1.0 | 2026-07-25 | Initial draft, approved by Mas Lutfi | Sophia |

---

## Approval

| Role | Nama | Status | Tanggal |
|------|------|--------|---------|
| Product Owner | Mas Lutfi | ✅ Approved | 2026-07-25 |
| Tech Lead | Sophia | ✅ Approved | 2026-07-25 |
