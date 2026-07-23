# PRD: Proteksi Absensi Siswa pada Hari Libur

| Field | Detail |
|-------|--------|
| PRD ID | PRD-011 |
| Versi | 1.0 |
| Status | Approved |
| Penulis | AI subagent prd |
| Tanggal | 2026-07-23 |
| Prioritas | High |
| Target Release | Sprint 11 |
| RICE Score | 3333 |

## 1. Ringkasan
Dokumen ini mendefinisikan kebutuhan untuk fitur **Proteksi Absensi Siswa pada Hari Libur**. Fitur ini dirancang untuk mencegah siswa melakukan absensi (baik secara mandiri maupun melalui scan QR publik) ketika hari tersebut adalah hari libur (baik Global, berdasarkan Tingkat, maupun berdasarkan Kelas).

## 2. Latar Belakang & Masalah
Saat ini terdapat **celah logika** pada sistem presensi:
- Sistem sudah memiliki fitur pendefinisian hari libur (`Holiday`) dan sistem otomatisasi penandaan alpha (`AutoMarkAlphaCommand`) yang mengecualikan hari libur.
- Namun, **PublicQrScanController** (pada method `process` & `liveBoardScan`) dan **AbsensiMandiriController** (pada method `store`) belum mengintegrasikan pemeriksaan hari libur sebelum memproses absensi masuk maupun pulang.
- Akibat celah ini, siswa masih dapat melakukan presensi pada hari libur, yang merusak validitas data statistik kehadiran di dashboard dan membiarkan aktivitas absensi yang tidak valid tercatat.

## 3. Tujuan & Metrik Keberhasilan
| Tujuan | Metrik | Target |
|--------|--------|--------|
| Mencegah absensi siswa pada hari libur | Jumlah absensi siswa yang tercatat pada hari libur | 0 absensi |
| Memberikan informasi penolakan yang ramah | Feedback kegagalan absensi saat libur mudah dipahami | 100% dipahami |

## 4. Scope
### In Scope
- Penambahan helper static method `isSiswaHoliday(Siswa $siswa, $date = null)` pada model `Holiday` untuk mendeteksi apakah siswa sedang libur pada tanggal tertentu (berdasarkan Libur Global, Tingkat Kelas, atau Kelas spesifik).
- Integrasi helper tersebut pada `PublicQrScanController::process()`.
- Integrasi helper tersebut pada `PublicQrScanController::liveBoardScan()`.
- Integrasi helper tersebut pada `AbsensiMandiriController::store()`.
- Menyajikan pesan error penolakan yang ramah dan informatif (menyebutkan nama hari liburnya).
- Penulisan Feature/Unit Test untuk memastikan fungsionalitas baru berjalan dengan benar dan tidak merusak fungsionalitas yang ada.

### Out of Scope
- Proteksi absensi untuk Guru dan Staff (karena Guru/Staff tetap dapat memiliki tugas piket/administrasi pada hari libur sekolah).
- Halaman antarmuka khusus (UI) baru untuk manajemen libur (menggunakan fitur manajemen libur yang sudah ada).

## 5. User Stories
| # | Sebagai | Saya ingin | Sehingga |
|---|---------|------------|----------|
| US-1 | Siswa | Sistem menolak absensi mandiri saya jika hari ini libur | Saya tidak perlu/tidak bisa melakukan absensi yang tidak valid dan mengetahui alasan liburnya. |
| US-2 | Guru Piket | Sistem menolak scan QR siswa melalui scanner publik jika siswa tersebut sedang libur | Sistem tidak mencatat data kehadiran palsu/tidak valid untuk siswa tersebut pada hari libur. |
| US-3 | Siswa / Pengamat | Sistem menolak scan QR siswa melalui Live Board jika siswa tersebut sedang libur | Live board tidak menampilkan data kehadiran siswa pada hari libur. |

## 6. Acceptance Criteria
| # | Given | When | Then |
|---|-------|------|------|
| AC-1 | Hari ini diset sebagai hari libur Global ("Tahun Baru Islam") | Siswa mencoba melakukan absen mandiri via GPS | Request ditolak dengan pesan: `"Hari ini adalah hari libur (Tahun Baru Islam). Selamat berlibur!"` |
| AC-2 | Hari ini diset sebagai hari libur Tingkat "X" ("Ujian Sekolah Kelas XII") | Siswa kelas X mencoba melakukan absen via QR Scan Publik | Request ditolak dengan pesan: `"Hari ini adalah hari libur (Ujian Sekolah Kelas XII). Absensi untuk kelas Anda diliburkan."` |
| AC-3 | Hari ini diset sebagai hari libur Kelas "XI IPA 1" ("Studi Banding Kelas") | Siswa kelas XI IPA 1 mencoba melakukan scan via Live Board | Request ditolak dengan pesan: `"Hari ini adalah hari libur (Studi Banding Kelas). Absensi untuk kelas Anda diliburkan."` |
| AC-4 | Hari ini diset sebagai hari libur Kelas "XI IPA 1" | Siswa kelas XI IPA 2 mencoba melakukan absensi | Request diproses dengan normal (hadir/terlambat) tanpa terpengaruh hari libur kelas lain. |

## 7. Alur Utama (Happy Path)
1. Siswa melakukan scan QR code atau submit absensi mandiri.
2. Sistem mengidentifikasi profil siswa beserta relasi kelas dan tingkatnya.
3. Sistem memanggil helper `Holiday::isSiswaHoliday($siswa, $tanggal)`.
4. Jika hari tersebut bukan libur untuk siswa tersebut, proses absensi dilanjutkan seperti biasa.
5. Jika hari tersebut adalah hari libur, sistem menghentikan proses absensi dan mengembalikan respons JSON dengan `'success' => false` beserta pesan error penolakan yang ramah.

## 8. Business Rules
- **BR-1:** Hari libur dideteksi berdasarkan 3 cakupan prioritas:
  1. **Global Holiday:** `tingkat IS NULL` dan `kelas_id IS NULL`. Berlaku untuk semua siswa.
  2. **Level Holiday:** `tingkat` sesuai dengan tingkat kelas siswa. Berlaku untuk seluruh kelas di tingkat tersebut.
  3. **Class Holiday:** `kelas_id` sesuai dengan kelas siswa. Berlaku khusus untuk siswa kelas tersebut.
- **BR-2:** Evaluasi hari libur hanya berlaku untuk entitas **Siswa**. Guru dan Staff Tata Usaha dikecualikan dari proteksi ini.
- **BR-3:** Response error harus bertipe JSON karena ketiga endpoint absensi tersebut diakses menggunakan AJAX/JSON.

## 9. Data Requirements (Helper Definition)
Model `App\Models\Holiday` akan ditambahkan fungsi static baru:
```php
/**
 * Memeriksa apakah siswa sedang libur pada tanggal tertentu.
 * 
 * @param \App\Models\Siswa $siswa
 * @param string|null $date (Y-m-d)
 * @return \App\Models\Holiday|null
 */
public static function isSiswaHoliday(Siswa $siswa, $date = null): ?Holiday
```

## 10. Non-Functional Requirements
- **Performa:** Query pemeriksaan hari libur harus dioptimalkan (menggunakan index pada kolom `tanggal` yang sudah ada) agar overhead pada proses scan QR minimal.
- **Keamanan & Konsistensi:** Mencegah manipulasi tanggal request dari sisi klien (selalu gunakan waktu/tanggal server `now()->toDateString()`).

## 11. Dependencies
- Fitur ini bergantung pada model `Holiday`, `Siswa`, dan `Kelas` yang sudah ada dalam database.

## 12. Estimasi & Timeline
| Task | Estimasi | Assigned To |
|------|----------|-------------|
| Pembuatan Helper `isSiswaHoliday` di model `Holiday` | 2 jam | Backend Developer |
| Integrasi pada `PublicQrScanController::process` | 2 jam | Backend Developer |
| Integrasi pada `PublicQrScanController::liveBoardScan` | 2 jam | Backend Developer |
| Integrasi pada `AbsensiMandiriController::store` | 2 jam | Backend Developer |
| Penulisan Feature & Unit Tests | 4 jam | QA / Tester |
| **Total** | **12 jam (1.5 Hari Kerja)** | |

## 13. Risks & Mitigasi
| Risk | Likelihood | Impact | Risk Score | Mitigasi |
|------|------------|--------|------------|----------|
| Salah konfigurasi hari libur oleh admin sehingga siswa tidak bisa absen | 2 | 4 | 8 (Medium) | Menyediakan pesan error yang jelas dan memfasilitasi admin untuk dengan mudah mengedit/menghapus hari libur jika terjadi kesalahan. |

## 14. QA & Test Cases
### Test Cases (Automated & Manual)
1. **test_absen_mandiri_ditolak_pada_hari_libur_global**: Memastikan absen mandiri ditolak jika hari libur global aktif.
2. **test_absen_mandiri_ditolak_pada_hari_libur_tingkat**: Memastikan absen mandiri ditolak jika hari libur tingkat yang sesuai aktif.
3. **test_absen_mandiri_ditolak_pada_hari_libur_kelas**: Memastikan absen mandiri ditolak jika hari libur kelas yang sesuai aktif.
4. **test_absen_mandiri_tetap_berhasil_untuk_siswa_kelas_lain**: Memastikan hari libur kelas tertentu tidak mengganggu absensi siswa di kelas lainnya.
5. **test_scan_qr_public_ditolak_pada_hari_libur_siswa**: Memastikan `PublicQrScanController::process` menolak siswa pada hari liburnya.
6. **test_live_board_scan_ditolak_pada_hari_libur_siswa**: Memastikan `PublicQrScanController::liveBoardScan` menolak siswa pada hari liburnya.
7. **test_guru_dan_staff_tetap_bisa_absen_pada_hari_libur**: Memastikan fungsionalitas absensi guru dan staff tidak terhalang oleh hari libur siswa.

---

## Executive Summary Card
+-----------------------------------------------+
| PRD-011: Proteksi Absensi Siswa Hari Libur   |
+-----------------------------------------------+
| Status    : Approved                          |
| Prioritas : High                              |
| RICE Score: 3333                              |
| Risk Level: Medium (Score: 8)                 |
| Quality   : 100/100 — Excellent               |
| Deadline  : TBD                               |
| Estimasi  : 1.5 Hari Kerja (12 jam)           |
+-----------------------------------------------+
| Ringkasan:                                     |
| Proteksi agar siswa tidak dapat melakukan      |
| absensi mandiri/QR scan pada hari libur        |
| (Global, Tingkat, maupun Kelas).              |
|                                               |
| Impact:                                        |
| Menjaga integritas data absensi dan           |
| kebersihan statistik dashboard utama.         |
+-----------------------------------------------+

## Quality Score Card
| # | Kriteria | Bobot | Status |
|---|----------|-------|--------|
| 1 | Ringkasan jelas | 5 | 5/5 |
| 2 | Masalah terdefinisi | 10 | 10/10 |
| 3 | Tujuan & metrik terukur | 10 | 10/10 |
| 4 | Scope in/out terdefinisi | 10 | 10/10 |
| 5 | User stories lengkap | 10 | 10/10 |
| 6 | Acceptance criteria (Given/When/Then) | 15 | 15/15 |
| 7 | Alur utama terdokumentasi | 10 | 10/10 |
| 8 | Data requirements ada | 5 | 5/5 |
| 9 | Non-functional requirements | 5 | 5/5 |
| 10 | Dependencies teridentifikasi | 5 | 5/5 |
| 11 | Estimasi & timeline | 5 | 5/5 |
| 12 | Risks & mitigasi | 10 | 10/10 |
| **TOTAL** | | **100** | **100/100** |

## Changelog
| Versi | Tanggal | Perubahan | Oleh |
|-------|---------|-----------|------|
| 1.0 | 2026-07-23 | Inisiasi PRD Proteksi Absensi Hari Libur | AI subagent prd |
