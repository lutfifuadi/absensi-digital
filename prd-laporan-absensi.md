# Product Requirements Document (PRD): Paginasi Laporan Absensi Admin & Sinkronisasi Siswa

| Field | Detail |
|---|---|
| PRD ID | PRD-001 |
| Versi | 1.0 |
| Status | Approved |
| Penulis | Sophia (Asisten Project Manager) / PRD Specialist |
| Tanggal | 2026-07-15 |
| Prioritas | High |
| RICE Score | Reach: 80%, Impact: High (3), Confidence: High (90%), Effort: Low (1) -> RICE: 216 |

---

## 1. Ringkasan
Dokumen ini mendokumentasikan spesifikasi kebutuhan untuk implementasi fitur **Paginasi** pada halaman **Laporan Absensi Admin** serta **Sinkronisasinya dengan Halaman Siswa**. 
Saat ini, halaman Laporan Absensi Admin menampilkan daftar siswa dengan batas statis `limit(100)`. Ketika jumlah siswa dalam satu kelas mendekati batas atau untuk meningkatkan performa loading halaman (terutama loading data pivot absensi harian), diperlukan sistem paginasi dinamis sebesar **10 data per halaman** tanpa mengganggu fungsionalitas ekspor (Excel dan PDF) yang harus tetap mengekspor seluruh data siswa di kelas tersebut.

---

## 2. Latar Belakang & Masalah
- **Masalah Saat Ini**:
  1. Halaman rekap absensi menggunakan pemanggilan `$siswaList` dengan batasan statis `.limit(100)->get()`. Jika ada kelas dengan jumlah siswa lebih besar atau terjadi penumpukan data, ini tidak efisien dan tidak scalable.
  2. Loading seluruh data absensi pivot (30/31 hari untuk semua siswa sekaligus) dalam satu request membebani query database dan memperlambat render waktu respons UI di browser.
  3. Tidak adanya paginasi membuat antarmuka tabel rekap menjadi sangat panjang secara vertikal jika jumlah siswa banyak.
- **Dampak jika tidak diselesaikan**: Penurunan performa server dan browser seiring bertambahnya data siswa dan riwayat absensi, serta buruknya pengalaman pengguna (UX) saat bernavigasi.
- **Solusi yang diusulkan**: Menerapkan sistem paginasi Laravel (`paginate(10)`) pada pencarian siswa di Laporan Absensi Admin, dengan mempertahankan query strings (`kelas_id`, `bulan`, `tahun`) saat navigasi halaman, dan menyesuaikan query data pivot agar hanya mengambil data kehadiran siswa yang tampil di halaman aktif.

---

## 3. Tujuan & Metrik Keberhasilan
| Tujuan | Metrik | Target |
|---|---|---|
| Meningkatkan performa render halaman | Load time halaman Laporan Absensi | < 1.5 detik untuk kelas dengan banyak siswa |
| Mempermudah navigasi data siswa | Keberadaan komponen Pagination Link | Muncul di bawah tabel dengan style UI yang konsisten |
| Memastikan data ekspor tetap utuh | Kelengkapan data hasil ekspor Excel & PDF | 100% siswa dalam kelas tereksport (tidak terbatas 10 data) |

---

## 4. Scope

### In Scope
- Implementasi paginasi dinamis (10 data per halaman) pada `$siswaList` di `LaporanController@index`.
- Penambahan navigasi paginasi Tailwind/Bootstrap di `admin.laporan.index.blade.php`.
- Sinkronisasi logika keaktifan siswa (misal: menyaring siswa yang bukan 'alumni') antara Laporan Absensi dan halaman Siswa.
- Pemeliharaan query parameter (`kelas_id`, `bulan`, `tahun`) menggunakan `withQueryString()` pada link paginasi.
- Pengujian dan penyesuaian fungsionalitas ekspor Excel & PDF agar tetap mengambil seluruh data siswa tanpa terpengaruh filter paginasi 10 data.

### Out of Scope
- Perubahan alur penginputan data absensi harian.
- Fitur paginasi untuk rekap harian guru/staff (karena memiliki struktur yang berbeda).

---

## 5. User Stories
| # | Sebagai | Saya ingin | Sehingga |
|---|---|---|---|
| US-1 | Administrator Sekolah | Melihat data rekap absensi siswa dibatasi 10 data per halaman | Halaman termuat dengan cepat dan tampilan tabel lebih rapi serta mudah dibaca |
| US-2 | Administrator Sekolah | Berpindah antar halaman tabel (next/previous/page number) | Saya dapat melihat data siswa lainnya di kelas yang sama tanpa kehilangan filter bulan dan tahun yang sedang saya lihat |
| US-3 | Administrator Sekolah | Mengunduh berkas laporan dalam format Excel atau PDF | Berkas yang diunduh tetap berisi seluruh daftar siswa dalam kelas tersebut (bukan hanya 10 siswa dari halaman yang sedang aktif) |
| US-4 | Administrator Sekolah | Melakukan manajemen siswa di halaman Siswa | Status aktif/non-aktif siswa sinkron dengan data yang muncul di Laporan Absensi |

---

## 6. Acceptance Criteria

### AC-1: Tampilan Tabel Rekapitulasi Terpaginasi
- **Given**: Admin telah memilih Kelas, Bulan, dan Tahun pada filter Laporan Absensi.
- **When**: Admin menekan tombol "Terapkan".
- **Then**: 
  - Sistem menampilkan maksimal 10 baris data siswa beserta pivot tanggal kehadirannya pada halaman pertama.
  - Sistem menampilkan komponen navigasi halaman (Pagination Links) di bawah tabel.
  - Logika pivot absensi (`$absensiPivot`) hanya memproses data absensi dari 10 siswa yang tampil di halaman aktif tersebut (menghemat memori dan waktu query).

### AC-2: Navigasi Halaman Mempertahankan Parameter Filter
- **Given**: Admin berada di Halaman 1 dari Laporan Absensi dengan filter Kelas = "X-A", Bulan = "Juli", Tahun = "2026".
- **When**: Admin mengklik tautan "Halaman 2" atau tombol "Berikutnya" (Next).
- **Then**: 
  - Sistem mengarahkan ke halaman kedua.
  - URL yang terbentuk menyertakan parameter query filter: `?kelas_id=X&bulan=7&tahun=2026&page=2`.
  - Filter pencarian tetap aktif dan menampilkan 10 siswa berikutnya dari kelas "X-A".

### AC-3: Sinkronisasi dengan Halaman Siswa (Konsistensi Status Alumni)
- **Given**: Siswa bernama "Budi" memiliki status `'alumni'` di database/halaman manajemen siswa.
- **When**: Admin melihat Laporan Absensi untuk kelas asal Budi.
- **Then**: 
  - Sistem secara otomatis mengecualikan data Budi dari Laporan Absensi (sinkron dengan filter pencarian di `SiswaController` yang mengecualikan status `'alumni'`).

### AC-4: Integritas Ekspor Excel dan PDF (Seluruh Data)
- **Given**: Admin sedang melihat Laporan Absensi kelas "X-A" (total 35 siswa) yang terbagi menjadi 4 halaman paginasi.
- **When**: Admin mengklik tombol "EXCEL" atau "PDF".
- **Then**:
  - Sistem mengunduh berkas Excel/PDF yang berisi **35 siswa lengkap** beserta data absensi mereka.
  - Logika ekspor tidak terpengaruh oleh limitasi `paginate(10)` dari tampilan layar (index).

---

## 7. Alur Utama (Happy Path)
1. Admin masuk ke menu **Laporan Absensi**.
2. Admin memilih **Kelas** (misal: XI-IPA), **Bulan** (Juli), **Tahun** (2026), lalu menekan **Terapkan**.
3. Sistem memproses permintaan:
   - Mengambil total siswa di kelas tersebut (misal: 25 siswa).
   - Membagi ke dalam paginasi dengan batas 10 data per halaman (Halaman 1: 10 siswa, Halaman 2: 10 siswa, Halaman 3: 5 siswa).
   - Menarik data absensi bulanan hanya untuk 10 siswa di Halaman 1.
4. Tampilan memuat tabel rekap 10 siswa pertama, statistik ringkasan kelas, dan navigasi halaman di bagian bawah.
5. Admin mengklik tombol **Halaman 2** untuk melihat sisa siswa.
6. Admin mengklik **Excel / PDF** jika ingin mengunduh rekap lengkap (25 siswa).

---

## 8. Business Rules
- **BR-1**: Paginasi Laporan Absensi harus bernilai default 10 data per halaman (`$perPage = 10`).
- **BR-2**: Siswa dengan status `alumni` tidak boleh dimasukkan ke dalam daftar laporan absensi aktif.
- **BR-3**: Ringkasan statistik di atas tabel (Total Hadir, Sakit, Izin, Alpha, Terlambat) harus menggambarkan akumulasi dari **seluruh siswa di kelas** tersebut selama bulan berjalan, bukan hanya 10 siswa yang tampil di halaman aktif.

---

## 9. Data Requirements (Query Parameter & Database)
| Field | Tipe | Required | Validasi | Keterangan |
|---|---|---|---|---|
| `kelas_id` | Integer | Tidak | Harus ada di tabel `kelas` | Jika kosong, tampilkan pesan peringatan untuk memilih kelas terlebih dahulu |
| `bulan` | Integer | Ya | Rentang 1 s.d. 12 | Default: Bulan berjalan saat ini |
| `tahun` | Integer | Ya | Angka 4 digit | Default: Tahun berjalan saat ini |
| `page` | Integer | Tidak | Angka positif | Dihasilkan secara otomatis oleh paginator Laravel |

---

## 10. Non-Functional Requirements
- **Performa**: Query pencarian siswa untuk rekapitulasi tidak boleh melakukan *N+1 query problem*.
- **Desain Responsif**: Komponen navigasi paginasi harus ramah pengguna saat diakses melalui perangkat seluler (mobile view).
- **Keamanan**: Parameter input `kelas_id`, `bulan`, dan `tahun` wajib divalidasi/dibersihkan dari potensi SQL Injection.

---

## 11. Dependencies
- Model `Siswa` dan model `AbsensiSiswa`.
- Controller `LaporanController` dan View `admin/laporan/index.blade.php`.
- Sinkronisasi penanganan status keaktifan yang diatur di `SiswaController`.

---

## 12. Estimasi & Timeline
| Task | Estimasi | Assigned To |
|---|---|---|
| Backend (Penyesuaian query & logic pagination di LaporanController) | 3 jam | Backend Developer (Bayu) |
| Frontend (Integrasi pagination links & styling UI Tailwind/Bootstrap) | 2 jam | Frontend Developer (Ayu) |
| QA & Testing (Verifikasi fungsionalitas, edge-cases, dan fungsionalitas ekspor) | 2 jam | Tester / QA (Farhan) |
| **Total** | **7 jam** | |

---

## 13. Risks & Mitigasi
| Risk | Likelihood | Impact | Risk Score (L x I) | Mitigasi |
|---|---|---|---|---|
| Halaman Ekspor Excel/PDF ikut terpotong menjadi hanya 10 data | 4 | 5 | 20 | Pastikan route & controller ekspor memanggil query yang menggunakan `.get()` secara terpisah, bukan dari instance paginator `$siswaList` yang terpaginasi. |
| Kehilangan parameter filter saat berpindah halaman paginasi | 3 | 4 | 12 | Wajib memanggil `.withQueryString()` di backend saat inisialisasi query `$siswaList->paginate(10)->withQueryString()`. |
| Perbedaan hitungan ringkasan statistik (Summary Stats) | 2 | 4 | 8 | Pastikan query `$summary` dihitung menggunakan aggregate langsung dari DB berdasarkan `kelas_id`, bukan dari koleksi hasil paginasi. |

---

## Changelog
| Versi | Tanggal | Perubahan | Oleh |
|---|---|---|---|
| 1.0 | 2026-07-15 | Inisiasi dokumen spesifikasi kebutuhan paginasi laporan absensi | Sophia (Asisten PM) |

---

## Approval
| Role | Nama | Status | Tanggal |
|---|---|---|---|
| Product Owner | Mas Lutfi | Approved | 2026-07-15 |
| Tech Lead | Rian | Approved | 2026-07-15 |
