# PRD: Optimasi Guru Multi-Mapel & Fitur Wali Kelas

| Field | Detail |
|-------|--------|
| PRD ID | PRD-001 |
| Versi | 1.0 |
| Status | Implemented |
| Penulis | Sophia (PM Manager) |
| Tanggal | 2026-07-21 |
| Prioritas | High |
| Target Release | Sprint Agustus 2026 (Minggu 1-2) |
| RICE Score | 360 |

---

## 1. Ringkasan

PRD ini mencakup empat peningkatan terkait pada sistem Aplikasi Presensi:

1. **Guru Multi-Mapel** — Mengubah model data guru dari *single choice* mata pelajaran menjadi *multi choice* (relasi many-to-many dengan tabel mapels), karena kenyataan di lapangan seorang guru bisa mengajar lebih dari satu mata pelajaran.
2. **Sinkronisasi Mapel di Halaman Penugasan** — Mengubah input `mata_pelajaran` pada form penugasan guru dari *text field* menjadi *select dropdown* yang bersumber dari tabel mapels yang sudah terdaftar di sistem.
3. **Rekap Murid Belum Absensi** — Menambahkan menu baru di portal Wali Kelas untuk melihat daftar siswa yang **belum melakukan absensi** pada hari/tanggal tertentu.
4. **Absensi Manual per Murid** — Menambahkan fitur absensi manual di portal Wali Kelas dengan cara mengetik nama murid (search/autocomplete), memilih status absensi, dan menambahkan keterangan.

Keempat fitur ini saling terkait dalam konteks peningkatan pengalaman guru dan wali kelas dalam mengelola pembelajaran dan absensi.

---

## 2. Latar Belakang & Masalah

### Masalah Saat Ini

| # | Masalah | Detail |
|---|---------|--------|
| M1 | **Guru hanya bisa punya 1 mapel** | Field `mata_pelajaran` di tabel `guru` adalah string biasa. Guru yang mengajar Matematika & Fisika sekaligus tidak bisa direkam dengan benar. |
| M2 | **Input mapel assignment masih manual** | Form penugasan menggunakan `<input type="text">` untuk mata pelajaran, padahal sudah ada tabel `mapels` yang terisi data. Guru harus mengetik manual, rawan typo dan tidak konsisten. |
| M3 | **Wali kelas tidak punya rekap siswa belum absen** | Tidak ada tampilan khusus yang memberi tahu wali kelas siswa mana saja yang **belum** melakukan absensi di hari tertentu. |
| M4 | **Absensi manual per siswa belum ada** | Wali kelas tidak bisa dengan cepat mengabsen satu siswa dengan cara mencari namanya. Fitur bulk absensi ada tetapi tidak praktis untuk absensi individual. |

### Dampak Jika Tidak Diselesaikan

- **Data guru tidak akurat** — Report dan analytics mapel tidak bisa diandalkan
- **Inkonsistensi data** — Nama mapel bisa berbeda-beda antar assignment (misal: "Matematika" vs "Mtematika")
- **Wali kelas kurang informasi** — Tidak bisa proaktif mengecek siswa yang bolos/mangkir
- **Produktivitas wali kelas rendah** — Untuk absen satu siswa harus scroll bulk table

### Solusi yang Diusulkan

1. Relasi **many-to-many** antara `guru` ↔ `mapels` melalui tabel pivot `guru_mapel`
2. Mengubah input mapel di form assignment menjadi **select2 dropdown** dari tabel `mapels`
3. Halaman **rekap khusus** di portal wali kelas yang menampilkan siswa **belum absen** per tanggal
4. **Form absensi cepat** di portal wali kelas dengan autocomplete nama siswa

---

## 3. Tujuan & Metrik Keberhasilan

| Tujuan | Metrik | Target |
|--------|--------|--------|
| Guru bisa memilih lebih dari 1 mapel | % guru yang terdata dengan >1 mapel dalam bulan pertama | ≥ 30% guru aktif |
| Input mapel assignment konsisten | % assignment menggunakan mapel dari dropdown (vs manual entry) | 100% |
| Wali kelas bisa lihat siswa belum absen | Jumlah akses menu "Rekap Belum Absen" per minggu | ≥ 20 kali akses per wali kelas |
| Absensi manual per siswa jadi lebih cepat | Rata-rata waktu input absensi per siswa | < 15 detik |

---

## 4. Scope

### In Scope

- **F1 — Guru Multi-Mapel**
  - Pembuatan tabel pivot `guru_mapel` (migration)
  - Hapus/ubah field `mata_pelajaran` di tabel `guru` (opsional: bisa dipertahankan sebagai legacy dengan fallback)
  - Update model `Guru.php` — tambah relasi `mapels()` many-to-many
  - Update form CRUD guru di admin panel — ubah dari single select/input ke multi-select
  - Update form edit guru — tampilkan mapel yang sudah dipilih
  - Update profile guru / detail guru — tampilkan daftar mapel
  - API/endpoint yang menggunakan `mata_pelajaran` guru — sesuaikan

- **F2 — Sinkronisasi Mapel di Assignment**
  - Ubah input `mata_pelajaran` dari text field menjadi `<select>` yang mengambil data dari tabel `mapels`
  - Gunakan Select2 atau dropdown searchable (konsisten dengan style existing)
  - Simpan `mata_pelajaran` tetap sebagai string di tabel `assignments` (sync value dari mapel yang dipilih)
  - Filter assignment berdasarkan mapel di halaman index

- **F3 — Menu Wali Kelas: Rekap Murid Belum Absensi**
  - Menu baru di sidebar wali kelas dengan nama **"Rekap Belum Absen"**
  - Halaman menampilkan daftar siswa di kelas bimbingan wali kelas
  - Filter tanggal (default: hari ini)
  - Tabel dengan kolom: No, NIS/NISN, Nama Siswa, Status Absensi (hanya yang belum), Kolom Aksi (bisa langsung absen)
  - Tombol/aksi untuk langsung mengabsen siswa dari rekap ini

- **F4 — Absensi Manual per Murid (Cepat)**
  - Form absensi manual di portal wali kelas
  - Input **search/autocomplete** nama siswa di kelas bimbingan
  - Setelah siswa dipilih, tampilkan field:
    - Nama siswa (readonly)
    - Tanggal (default hari ini, bisa diubah)
    - Status: Hadir / Sakit / Izin / Alpha / Terlambat (radio/select)
    - Keterangan (textarea, opsional)
  - Tombol submit → simpan ke tabel `absensi_siswa`
  - Validasi: cegah duplikasi absensi (siswa + tanggal yang sama)
  - Notifikasi sukses/gagal (menggunakan session flash & SweetAlert sesuai style existing)

### Out of Scope

- **Tidak termasuk** perubahan struktur tabel `assignments` (field `mata_pelajaran` tetap string)
- **Tidak termasuk** fitur import/export data terkait mapel
- **Tidak termasuk** perubahan role/permission system
- **Tidak termasuk** fitur absensi menggunakan QR code/RFID dari menu ini
- **Tidak termasuk** fitur absensi multi-kelas untuk wali kelas (hanya kelas bimbingannya)
- **Tidak termasuk** history log perubahan mapel guru
- **Tidak termasuk** notifikasi WhatsApp/email otomatis untuk siswa yang belum absen

---

## 5. User Stories

| # | Sebagai | Saya ingin | Sehingga |
|---|---------|------------|----------|
| US-01 | Admin/Operator | Memilih lebih dari satu mata pelajaran saat mendaftarkan/ mengedit data guru | Data guru sesuai dengan kenyataan di lapangan (seorang guru bisa mengajar beberapa mapel) |
| US-02 | Guru | Memilih mata pelajaran dari daftar yang tersedia saat membuat penugasan | Nama mapel konsisten dan tidak perlu mengetik manual |
| US-03 | Wali Kelas | Melihat rekap siswa di kelas saya yang belum melakukan absensi pada hari tertentu | Saya bisa proaktif menghubungi atau menindaklanjuti siswa yang belum absen |
| US-04 | Wali Kelas | Mengabsen satu siswa dengan mencari namanya lalu memilih status | Saya tidak perlu scroll/ mencari di tabel bulk untuk mengabsen satu siswa |
| US-05 | Admin | Melihat daftar mapel yang diampu oleh seorang guru di detail/profile guru | Saya bisa mengecek beban mengajar guru dengan cepat |

---

## 6. Acceptance Criteria

| # | Given | When | Then |
|---|-------|------|------|
| **AC-01** | Admin sedang di halaman tambah/edit guru | Admin memilih beberapa mapel dari multi-select dropdown | Semua mapel yang dipilih tersimpan di tabel pivot `guru_mapel` dan tampil di detail guru |
| **AC-02** | Admin membuka halaman edit guru yang sudah memiliki 3 mapel | Admin melihat field multi-mapel | Ketiga mapel tersebut sudah tercentang/terpilih di multi-select |
| **AC-03** | Guru membuka halaman create assignment | Guru mengklik field "Mata Pelajaran" | Muncul dropdown berisi daftar mapel dari tabel `mapels`, bukan text input |
| **AC-04** | Guru memilih mapel dari dropdown dan submit form | Assignment tersimpan | Field `mata_pelajaran` terisi dengan nama mapel yang dipilih |
| **AC-05** | Wali kelas membuka menu "Rekap Belum Absen" | Sistem menampilkan daftar siswa di kelas bimbingannya | Hanya siswa yang **belum memiliki catatan absensi** di tanggal tersebut yang muncul |
| **AC-06** | Wali kelas mengganti filter tanggal ke 2026-07-20 | Tabel refresh | Menampilkan siswa yang belum absen pada tanggal 2026-07-20 |
| **AC-07** | Wali kelas membuka halaman absensi manual | Wali kelas mulai mengetik nama siswa di field pencarian | Muncul daftar dropdown/suggestions nama siswa dari kelas bimbingannya (min. 2 karakter) |
| **AC-08** | Wali kelas memilih siswa, memilih status "Sakit", mengisi keterangan "Demam", lalu submit | Data tersimpan di tabel `absensi_siswa` | Muncul pesan sukses dan form kembali siap diisi |
| **AC-09** | Wali kelas mencoba mengabsen siswa yang sudah diabsen di tanggal yang sama | Sistem menolak | Muncul pesan error: "Siswa [nama] sudah tercatat absensi pada tanggal [tanggal]" |
| **AC-10** | Admin membuka halaman detail seorang guru | Sistem menampilkan semua mapel yang diampu | Setiap mapel tampil sebagai badge/list item |

---

## 7. Alur Utama (Happy Path)

### F1: Guru Multi-Mapel (Admin/Operator)

1. Admin/login ke sistem dengan role `admin_sekolah` atau `operator`
2. Buka menu manajemen guru
3. Klik "Tambah Guru" atau "Edit Guru" pada guru tertentu
4. Pada form, field "Mata Pelajaran" menampilkan multi-select dengan daftar mapel dari tabel `mapels`
5. Admin memilih satu/beberapa mapel (contoh: Matematika, Fisika)
6. Submit form
7. Sistem menyimpan data guru beserta relasi mapel ke tabel pivot `guru_mapel`
8. Sistem menampilkan pesan sukses

### F2: Sinkronisasi Mapel di Assignment (Guru)

1. Guru login dengan role `guru`
2. Buka menu "Penugasan Siswa"
3. Klik "Tambah Tugas"
4. Pada form, field "Mata Pelajaran" adalah dropdown berisi daftar mapel dari tabel `mapels`
5. Guru memilih mapel (misal: Matematika Wajib)
6. Mengisi field lainnya (kelas, judul, dll)
7. Submit
8. Assignment tersimpan dengan `mata_pelajaran` = "Matematika Wajib"
9. Sistem redirect ke halaman index dengan pesan sukses

### F3: Rekap Murid Belum Absen (Wali Kelas)

1. Wali kelas login dengan role `wali_kelas`
2. Di sidebar, muncul menu baru "Rekap Belum Absen"
3. Halaman menampilkan tabel siswa yang belum absen pada tanggal default (hari ini)
4. Wali kelas bisa mengganti tanggal melalui filter datepicker
5. Tabel otomatis refresh menampilkan data siswa yang belum absen untuk tanggal dipilih
6. Wali kelas bisa klik tombol "Absen Sekarang" pada salah satu siswa
7. Sistem membawa ke halaman absensi manual dengan nama siswa sudah terisi

### F4: Absensi Manual per Murid (Wali Kelas)

1. Wali kelas login dengan role `wali_kelas`
2. Buka menu "Absensi Manual" atau dari rekap belum absen
3. Ketik minimal 2 karakter nama siswa → muncul suggestions
4. Pilih siswa dari suggestions
5. Tanggal terisi otomatis (hari ini), bisa diubah
6. Pilih status absensi (Hadir/Sakit/Izin/Alpha/Terlambat)
7. Isi keterangan (opsional)
8. Klik "Simpan"
9. Sistem validasi: cek duplikasi
10. Jika valid → simpan, tampilkan sukses, reset form
11. Jika duplikasi → tampilkan error

---

## 8. Business Rules

| # | Rule | Keterangan |
|---|------|------------|
| BR-1 | **Satu guru minimal 1 mapel** | Saat create/update guru, wajib memilih minimal 1 mapel |
| BR-2 | **Mapel tidak bisa kosong** | Multi-select validasi: minimal 1 item terpilih |
| BR-3 | **Sinkron assignment** | Nilai `mata_pelajaran` di assignment diambil dari `nama_mapel` yang dipilih, disimpan sebagai string (denormalized) |
| BR-4 | **Wali kelas hanya bisa akses kelas bimbingan** | Menu rekap & absensi manual hanya menampilkan siswa dari kelas dimana guru tersebut adalah wali kelas |
| BR-5 | **Tahun ajaran aktif** | Data siswa dan kelas difilter berdasarkan tahun ajaran yang sedang aktif |
| BR-6 | **Cegah duplikasi absensi** | Satu siswa hanya boleh memiliki 1 catatan absensi per tanggal (unique key: siswa_id + tanggal) |
| BR-7 | **Default tanggal** | Default filter/input tanggal = hari ini (`now()->toDateString()`) |
| BR-8 | **Minimal 2 karakter pencarian** | Autocomplete/search nama siswa diaktifkan setelah minimal 2 karakter diketik |

---

## 9. Data Requirements

### F1: Tabel Pivot Baru — `guru_mapel`

| Field | Tipe | Required | Validasi | Keterangan |
|-------|------|----------|----------|------------|
| id | bigint unsigned | Auto | PK, auto-increment | Primary Key |
| guru_id | bigint unsigned | Ya | FK ke `guru.id`, onDelete cascade | ID Guru |
| mapel_id | bigint unsigned | Ya | FK ke `mapels.id`, onDelete cascade | ID Mapel |

**Unique constraint**: `UNIQUE(guru_id, mapel_id)` — cegah duplikasi relasi

### F1: Perubahan pada Model `Guru`

| Field | Tipe | Status | Keterangan |
|-------|------|--------|------------|
| mata_pelajaran | string | **Opsional: deprecated/ nullable** | Bisa dipertahankan untuk backward compatibility atau dihapus |

### F3: Halaman Rekap Belum Absen (Tidak ada tabel baru — query based)

Menampilkan data dari query:
- `siswa` WHERE `kelas_id` IN (kelas bimbingan wali)
- LEFT JOIN `absensi_siswa` WHERE `tanggal` = [selected date]
- Filter: `absensi_siswa.id IS NULL`

Kolom yang ditampilkan:
| No | NIS/NISN | Nama Siswa | Status Absensi | Aksi |
|----|----------|-----------|----------------|------|
| 1 | 12345 | Andi Pratama | Belum Absen | Absen Sekarang |

### F4: Form Absensi Manual

| Field | Tipe | Required | Validasi | Keterangan |
|-------|------|----------|----------|------------|
| siswa_id | bigint unsigned | Ya | Harus siswa di kelas bimbingan | Dipilih via autocomplete |
| tanggal | date | Ya | Format Y-m-d, max: hari ini | Default: hari ini |
| status | string | Ya | Enum: hadir, sakit, izin, alpha, terlambat | Radio button / select |
| keterangan | text | Tidak | Maks 500 karakter | Opsional |
| metode | string | Auto | — | Diisi "manual" oleh sistem |
| guru_id | bigint unsigned | Auto | — | Diisi dari ID guru yang login |

---

## 10. Non-Functional Requirements

- **Performa**: 
  - Query rekap belum absen harus selesai < 2 detik untuk kelas dengan 50 siswa
  - Autocomplete pencarian siswa merespon < 500ms setelah user berhenti mengetik
- **Keamanan**: 
  - Hanya wali kelas yang berhak mengakses data siswa di kelas bimbingannya
  - Validasi server-side: cegah manipulasi siswa_id dari luar kelas bimbingan
  - Gunakan policy / gate authorization Laravel
- **UX/Kompatibilitas**:
  - Semua halaman mengikuti tema dark-mode yang sudah ada
  - Multi-select mapel menggunakan Select2 atau komponen yang sudah tersedia
  - Autocomplete nama siswa menggunakan Alpine.js / Livewire / Select2 (konsisten dengan gaya existing)
  - Tampilan responsif (mobile-friendly)

---

## 11. Dependencies

| # | Dependency | Tipe | Keterangan |
|---|-----------|------|------------|
| D1 | Data mapels sudah terisi | HARD | Fitur F1 & F2 membutuhkan data di tabel `mapels` sudah ada |
| D2 | Roles & permissions sudah jalan | HARD | `wali_kelas`, `guru`, `admin_sekolah`, `operator` harus sudah terdefinisi |
| D3 | Relasi wali_kelas_id di tabel kelas | HARD | F3 & F4 bergantung pada data `kelas.wali_kelas_id` yang benar |
| D4 | Session tahun ajaran aktif | HARD | Filter data siswa menggunakan tahun ajaran yang sedang aktif |
| D5 | Library Select2 / Alpine.js | SOFT | Jika belum ada, perlu diinstal melalui NPM atau CDN |

---

## 12. Estimasi & Timeline

| Task | Estimasi | Assigned To |
|------|----------|-------------|
| **F1: Guru Multi-Mapel** | | |
| — Migration: tabel pivot `guru_mapel` + opsi drop/ubah field `mata_pelajaran` | 2 jam | Kang Encep |
| — Model: Update `Guru.php` (relasi `mapels()`) | 1 jam | Kang Bayu |
| — Controller: Update CRUD guru (multi-select handling) | 3 jam | Kang Bayu |
| — View: Update form tambah/edit guru (multi-select UI) | 3 jam | Teh Ayu |
| — View: Update detail/profile guru (tampilkan daftar mapel) | 1 jam | Teh Ayu |
| **F2: Sinkronisasi Mapel di Assignment** | | |
| — Controller: Ubah logic store/update assignment (ambil mapel dari dropdown) | 1 jam | Kang Bayu |
| — View: Ubah input text → select dropdown mapel | 2 jam | Teh Ayu |
| — Validasi: Pastikan mapel yang dipilih ada di tabel mapels | 0.5 jam | Kang Bayu |
| **F3: Rekap Murid Belum Absensi** | | |
| — Route & Controller: Buat method baru `rekapBelumAbsen` | 2 jam | Kang Bayu |
| — View: Buat halaman rekap + filter tanggal | 3 jam | Teh Ayu |
| — Logic query: LEFT JOIN absensi_siswa WHERE NULL | 1 jam | Kang Bayu |
| — Menu sidebar: Update `vertical_wali_kelas.json` | 0.5 jam | Teh Ayu |
| **F4: Absensi Manual per Murid** | | |
| — Route & Controller: Buat method `manualCreate` + `manualStore` | 2 jam | Kang Bayu |
| — View: Buat halaman form absensi manual (autocomplete) | 3 jam | Teh Ayu |
| — Logic: Autocomplete endpoint (search siswa by name) | 1.5 jam | Kang Bayu |
| — Validasi: Duplikasi absensi + authorization | 1 jam | Kang Bayu |
| **Testing** | | |
| — Unit test untuk relasi guru_mapel | 1 jam | Kang Asep |
| — Feature test untuk CRUD assignment dengan dropdown | 1 jam | Kang Asep |
| — Feature test untuk rekap belum absen | 1 jam | Kang Asep |
| — Feature test untuk absensi manual | 1.5 jam | Kang Asep |
| — Manual test / UAT | 2 jam | Kang Asep |
| **Total** | **~33 jam** | |

**Estimasi total**: ~4-5 hari kerja (dengan paralelisasi antara backend & frontend)

---

## 13. Risks & Mitigasi

| Risk | Likelihood | Impact | Risk Score | Level | Mitigasi |
|------|-----------|--------|-----------|-------|----------|
| **R1:** Field `mata_pelajaran` di tabel `guru` masih dipakai oleh kode lain (report, API, dll) | 4 | 3 | 12 | **Medium** | Audit kode dulu sebelum hapus field. Jika banyak referensi, pertahankan sebagai nullable + isi dengan mapel pertama sebagai fallback |
| **R2:** Duplikasi absensi saat wali kelas dan guru/scan QR absen di hari yang sama | 3 | 4 | 12 | **Medium** | Validasi unique (siswa_id + tanggal) di level database + aplikasi. Tampilkan error message yang jelas |
| **R3:** Autocomplete siswa lambat jika data siswa besar (1000+) | 2 | 3 | 6 | **Low** | Implementasi debounce 300ms + batasi hasil maksimal 20 siswa + index di kolom `nama_lengkap` |
| **R4:** Wali kelas tidak punya kelas bimbingan (data `wali_kelas_id` null) | 2 | 3 | 6 | **Low** | Tampilkan pesan informatif "Anda belum ditugaskan sebagai wali kelas" + sembunyikan menu yang tidak relevan |
| **R5:** Bentrok dengan fitur absensi yang sudah ada (bulk absensi, QR scan) | 2 | 2 | 4 | **Low** | Absensi manual hanya metode baru, tidak mengubah logika existing. Gunakan `updateOrCreate` untuk konsistensi |

---

## 14. Wireframe / Mockup Reference

> **Catatan**: Wireframe detail akan dibuat oleh Teh Sari (UI Designer) setelah PRD di-approve. Berikut gambaran kasar layout:

### Halaman: Form CRUD Guru — Field Multi-Mapel

```
┌──────────────────────────────────────────────────────────┐
│ Field:  Mata Pelajaran                                   │
│ ┌──────────────────────────────────────────────────────┐ │
│ │ × Matematika  × Fisika  × Kimia  ▼                  │ │
│ │                                                      │ │
│ │ [▣] Matematika Wajib     [☐] Biologi                │ │
│ │ [▣] Fisika               [☐] Kimia                   │ │
│ │ [▣] Kimia                [☐] Bahasa Inggris          │ │
│ └──────────────────────────────────────────────────────┘ │
│ (Select2 Multi-select dengan tagging)                     │
└──────────────────────────────────────────────────────────┘
```

### Halaman: Form Assignment — Field Mapel Dropdown

```
┌───────────────────────────────────────────────┐
│ Mata Pelajaran:                               │
│ ┌─────────────────────────────────────────┐   │
│ │ [Pilih Mata Pelajaran...            ▼]  │   │
│ │ ┌─────────────────────────────────────┐ │   │
│ │ │ Matematika Wajib                    │ │   │
│ │ │ Matematika Minat                    │ │   │
│ │ │ Fisika                              │ │   │
│ │ │ Kimia                               │ │   │
│ │ │ Biologi                             │ │   │
│ │ │ Bahasa Inggris                      │ │   │
│ │ └─────────────────────────────────────┘ │   │
│ └─────────────────────────────────────────┘   │
└───────────────────────────────────────────────┘
```

### Halaman: Rekap Belum Absen (Wali Kelas)

```
┌──────────────────────────────────────────────────────────┐
│ 📋 REKAP BELUM ABSEN                                     │
│                                                          │
│ Tanggal: [2026-07-21  ▼]  (datepicker)                   │
│ Kelas: X MIPA 1 (readonly)                               │
│                                                          │
│ ┌────┬───────┬────────────────┬──────────────┬──────────┐│
│ │ No │ NIS   │ Nama Siswa     │ Status       │ Aksi     ││
│ ├────┼───────┼────────────────┼──────────────┼──────────┤│
│ │ 1  │ 12345 │ Andi Pratama   │ 🔴 Belum     │ [Absen]  ││
│ │ 2  │ 12346 │ Budi Santoso   │ 🔴 Belum     │ [Absen]  ││
│ │ 3  │ 12347 │ Citra Dewi     │ 🔴 Belum     │ [Absen]  ││
│ └────┴───────┴────────────────┴──────────────┴──────────┘│
│                                                          │
│ [✅ Refresh Data]                                         │
└──────────────────────────────────────────────────────────┘
```

### Halaman: Absensi Manual per Murid

```
┌──────────────────────────────────────────────────────────┐
│ 📝 ABSENSI MANUAL                                        │
│                                                          │
│ Cari Nama Siswa:                                         │
│ ┌────────────────────────────────────────────────────┐   │
│ │ An                                               🔍│   │
│ ┌────────────────────────────────────────────────────┘   │
│ │ Andi Pratama - X MIPA 1 (NIS: 12345)                   │
│ │ Anita Wijaya - X MIPA 1 (NIS: 12348)                   │
│ │ Angelina Putri - X MIPA 1 (NIS: 12350)                 │
│ └────────────────────────────────────────────────────────┘│
│                                                          │
│ Siswa Dipilih: [Andi Pratama]                            │
│                                                          │
│ Tanggal: [2026-07-21]                                    │
│                                                          │
│ Status:                                                   │
│ ○ Hadir  ○ Sakit  ○ Izin  ○ Alpha  ○ Terlambat          │
│                                                          │
│ Keterangan:                                              │
│ ┌────────────────────────────────────────────────────┐   │
│ │                                                    │   │
│ └────────────────────────────────────────────────────┘   │
│                                                          │
│ [💾 Simpan]    [↺ Reset]                                  │
└──────────────────────────────────────────────────────────┘
```

---

## 15. Database Schema Changes

### Tabel Baru

| Tabel | Kolom | Tipe | Keterangan |
|-------|-------|------|------------|
| `guru_mapel` | id | bigint unsigned, PK, auto-increment | Primary key |
| `guru_mapel` | guru_id | bigint unsigned, FK → guru.id, onDelete cascade | ID Guru |
| `guru_mapel` | mapel_id | bigint unsigned, FK → mapels.id, onDelete cascade | ID Mapel |
| `guru_mapel` | — | UNIQUE(guru_id, mapel_id) | Cegah duplikasi |

### Perubahan pada Tabel Existing

| Tabel | Kolom | Perubahan | Keterangan |
|-------|-------|-----------|------------|
| `guru` | `mata_pelajaran` | **Opsional**: ubah jadi nullable atau hapus | Setelah migrasi data ke pivot, field ini tidak lagi diperlukan. Ditentukan setelah audit kode |

### Model Updates

File `app/Models/Guru.php`:
```php
// Tambah relasi
public function mapels()
{
    return $this->belongsToMany(Mapel::class, 'guru_mapel', 'guru_id', 'mapel_id')
                ->withTimestamps();
}

// Accessor untuk backward compatibility (opsional)
public function getMataPelajaranListAttribute()
{
    return $this->mapels->pluck('nama_mapel')->implode(', ');
}
```

File `app/Models/Mapel.php`:
```php
// Tambah relasi (opsional, bisa ditambahkan jika diperlukan)
public function guru()
{
    return $this->belongsToMany(Guru::class, 'guru_mapel', 'mapel_id', 'guru_id')
                ->withTimestamps();
}
```

---

## 16. UI/UX Specification

### F1: Multi-Mapel pada Form Guru

| Komponen | Spesifikasi |
|----------|-------------|
| **Tipe input** | Select2 multi-select (dengan tags/chips) |
| **Sumber data** | Tabel `mapels` (`id` → `nama_mapel`) |
| **Validasi** | Minimal 1 mapel harus dipilih |
| **Tampilan** | Chip biru/info untuk setiap mapel terpilih, dengan tombol × untuk menghapus |
| **Dark mode** | Sesuai tema existing (dark background, white text) |

### F2: Dropdown Mapel pada Form Assignment

| Komponen | Spesifikasi |
|----------|-------------|
| **Tipe input** | Select dropdown (searchable dengan Select2) |
| **Sumber data** | Tabel `mapels` |
| **Validasi** | Wajib pilih salah satu |
| **Ketika edit** | Dropdown otomatis memilih mapel yang sudah tersimpan |

### F3: Halaman Rekap Belum Absen

| Komponen | Spesifikasi |
|----------|-------------|
| **Filter** | Datepicker (default: hari ini) |
| **Tabel** | Dark theme, striped rows, hover effect (konsisten dengan existing) |
| **Kolom** | No, NIS/NISN, Nama Siswa, Status, Aksi |
| **Status indicator** | 🔴 Badge merah "Belum Absen" |
| **Tombol aksi** | "Absen Sekarang" → redirect ke halaman absensi manual dengan siswa terisi |

### F4: Form Absensi Manual

| Komponen | Spesifikasi |
|----------|-------------|
| **Pencarian siswa** | Input dengan autocomplete/debounce, minimal 2 karakter |
| **Dropdown hasil** | Menampilkan: Nama - Kelas (NIS) |
| **Tanggal** | Input date (default: hari ini) |
| **Status** | Radio button / select dengan style pill (Hadir=🟢, Sakit=🟡, Izin=🔵, Alpha=🔴, Terlambat=🟠) |
| **Keterangan** | Textarea (opsional) |
| **Tombol** | Simpan (primary), Reset (secondary) |
| **Notifikasi** | SweetAlert sukses/gagal (konsisten dengan gaya existing) |

---

## Changelog

| Versi | Tanggal | Perubahan | Oleh |
|-------|---------|-----------|------|
| 1.0 | 2026-07-21 | Initial draft — PRD lengkap dari A-Z | Sophia |

## Approval

| Role | Nama | Status | Tanggal |
|------|------|--------|---------|
| Product Owner | Mas Lutfi | Pending | — |
| Tech Lead | — | Pending | — |
