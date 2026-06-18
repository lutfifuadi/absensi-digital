# Checklist Manual Testing — Modul Ekstrakurikuler

> **Tanggal:** 18 Juni 2026
> **Tester:** Farhan (QA)
> **Status:** ⬜ = Belum diuji | ✅ = Pass | ❌ = Fail | ⚠️ = Minor Issue

---

## A. Flow CRUD Ekskul

| ID | Test Case | Langkah | Hasil yang Diharapkan | Status | Catatan |
|----|-----------|---------|----------------------|--------|---------|
| M-01 | Lihat daftar ekskul | 1. Login sebagai admin 2. Buka menu Ekskul | Tabel ekskul muncul dengan filter kategori, status, dan search | ⬜ | |
| M-02 | Filter kategori | Pilih kategori "Olahraga" | Hanya ekskul kategori Olahraga yang muncul | ⬜ | |
| M-03 | Filter status | Pilih status "Aktif" / "Nonaktif" | Data terfilter sesuai status | ⬜ | |
| M-04 | Search ekskul | Ketik nama ekskul di kolom search | Data terfilter sesuai keyword | ⬜ | |
| M-05 | Pagination | Data > 15 ekskul | Pagination muncul dan berfungsi | ⬜ | |
| M-06 | Tambah ekskul baru | 1. Klik "Tambah" 2. Isi form 3. Submit | Redirect ke index, muncul toast success, data tersimpan | ⬜ | |
| M-07 | Tambah ekskul + jadwal | Isi jadwal (hari, jam, lokasi) | Jadwal tersimpan di tabel ekskul_jadwal | ⬜ | |
| M-08 | Tambah ekskul + pembina | Pilih guru pembina | Pembina tersimpan di tabel ekskul_pembina | ⬜ | |
| M-09 | Edit ekskul | 1. Klik edit 2. Ubah data 3. Simpan | Redirect ke index, data terupdate, jadwal/pembina terupdate | ⬜ | |
| M-10 | Hapus ekskul | 1. Klik hapus 2. Konfirmasi | Soft delete — data tidak muncul di index, tapi masih ada di database | ⬜ | |
| M-11 | Toggle status aktif/nonaktif | Klik toggle status | Status berubah, toast muncul, ikon toggle berubah | ⬜ | |
| M-12 | Ekskul nonaktif tidak bisa tambah anggota | Coba tambah anggota ke ekskul nonaktif | Muncul pesan error | ⬜ | |

---

## B. Flow Tambah/Hapus Anggota

| ID | Test Case | Langkah | Hasil yang Diharapkan | Status | Catatan |
|----|-----------|---------|----------------------|--------|---------|
| M-13 | Lihat daftar anggota | Buka halaman anggota per ekskul | Tabel anggota muncul dengan status masing-masing | ⬜ | |
| M-14 | Tambah anggota baru | 1. Pilih siswa dari dropdown 2. Klik tambah | Siswa masuk daftar anggota, toast success | ⬜ | |
| M-15 | Duplikat anggota | Tambah siswa yang sudah terdaftar | Toast error "Siswa sudah terdaftar" | ⬜ | |
| M-16 | Kuota penuh | Tambah anggota melebihi kuota | Toast error "Kuota penuh" | ⬜ | |
| M-17 | Hapus anggota | Klik hapus anggota | Anggota terhapus, toast success | ⬜ | |
| M-18 | Update status anggota | Ubah status ke cuti/keluar | Status berubah, toast success | ⬜ | |
| M-19 | Anggota keluar — tanggal_keluar tercatat | Ubah status ke "keluar" | Field `tanggal_keluar` terisi otomatis | ⬜ | |

---

## C. Flow Absensi Per Pertemuan

| ID | Test Case | Langkah | Hasil yang Diharapkan | Status | Catatan |
|----|-----------|---------|----------------------|--------|---------|
| M-20 | Pilih tanggal absensi | Buka halaman absensi, pilih tanggal | Form absensi muncul dengan daftar anggota aktif | ⬜ | |
| M-21 | Isi absensi (hadir/izin/sakit/alpha/terlambat) | Pilih status untuk setiap anggota | Radio/select status bekerja | ⬜ | |
| M-22 | Simpan absensi | Klik simpan | Redirect sukses, data tersimpan di database | ⬜ | |
| M-23 | Edit absensi (simpan ulang) | Ubah beberapa status, simpan lagi | Data ter-update (updateOrCreate) | ⬜ | |
| M-24 | Absensi tanpa status | Submit dengan status kosong | Validasi error muncul | ⬜ | |
| M-25 | Absensi tanpa pilih siswa | Submit dengan data kosong | Validasi error muncul | ⬜ | |

---

## D. Flow Rekap Bulanan

| ID | Test Case | Langkah | Hasil yang Diharapkan | Status | Catatan |
|----|-----------|---------|----------------------|--------|---------|
| M-26 | Lihat rekap bulanan | Buka halaman rekap absensi | Grafik/tabel rekap per tanggal muncul | ⬜ | |
| M-27 | Filter bulan & tahun | Pilih bulan/tahun berbeda | Data rekap sesuai filter | ⬜ | |
| M-28 | Statistik hadir/izin/sakit/alpha | Cek angka total | Perhitungan sesuai data absensi | ⬜ | |
| M-29 | Rekap bulan kosong | Pilih bulan tanpa data absensi | Tampil pesan "tidak ada data" | ⬜ | |

---

## E. QR Code Generate & Scan

| ID | Test Case | Langkah | Hasil yang Diharapkan | Status | Catatan |
|----|-----------|---------|----------------------|--------|---------|
| M-30 | Generate QR code | Klik tombol generate QR | QR code muncul (gambar/base64) atau token JSON | ⬜ | |
| M-31 | QR code unik per tanggal | Generate QR di tanggal berbeda | Token berbeda setiap kali | ⬜ | |
| M-32 | Scan QR code valid | Scan QR yang valid | Redirect ke form absensi dengan tanggal yang sesuai | ⬜ | |
| M-33 | Scan QR code expired/kadaluarsa | Scan token lama | Error atau invalid | ⬜ | |

---

## F. Responsive Mobile

| ID | Test Case | Langkah | Hasil yang Diharapkan | Status | Catatan |
|----|-----------|---------|----------------------|--------|---------|
| M-34 | Daftar ekskul mobile | Buka di HP (lebar < 768px) | Tabel responsif, tidak overflow horizontal | ⬜ | |
| M-35 | Form tambah ekskul mobile | Buka form di mobile | Layout rapi, semua field bisa diakses | ⬜ | |
| M-36 | Form absensi mobile | Buka form di mobile | Radio/select untuk status mudah diklik | ⬜ | |
| M-37 | Rekap absensi mobile | Buka halaman rekap | Grafik responsif, tidak pecah | ⬜ | |

---

## G. Validasi Form

| ID | Test Case | Langkah | Hasil yang Diharapkan | Status | Catatan |
|----|-----------|---------|----------------------|--------|---------|
| M-38 | Nama ekskul wajib diisi | Submit form dengan nama kosong | Validasi error "Nama ekskul wajib diisi" | ⬜ | |
| M-39 | Kategori wajib dipilih | Submit tanpa pilih kategori | Validasi error "Kategori wajib dipilih" | ⬜ | |
| M-40 | Kategori invalid | Kirim nilai kategori tidak valid | Validasi error "Kategori tidak valid" | ⬜ | |
| M-41 | Kuota minimal 1 | Input kuota 0 atau negatif | Validasi error "Kuota minimal 1" | ⬜ | |
| M-42 | Nama maksimal 255 karakter | Input nama > 255 karakter | Validasi error | ⬜ | |
| M-43 | Jam selesai harus > jam mulai | Input jam_selesai < jam_mulai | Validasi error | ⬜ | |
| M-44 | Hari jadwal harus valid | Input hari invalid | Validasi error | ⬜ | |
| M-45 | Siswa_id wajib saat tambah anggota | Submit tanpa pilih siswa | Validasi error | ⬜ | |
| M-46 | Siswa_id harus exists | Submit dengan ID siswa tidak valid | Validasi error | ⬜ | |

---

## H. Error Handling & Edge Cases

| ID | Test Case | Langkah | Hasil yang Diharapkan | Status | Catatan |
|----|-----------|---------|----------------------|--------|---------|
| M-47 | Akses tanpa login | Akses URL admin/ekskul tanpa login | Redirect ke halaman login | ⬜ | |
| M-48 | Role tidak punya akses | Login sebagai siswa, akses admin/ekskul | 403 Forbidden | ⬜ | |
| M-49 | Ekskul tidak ditemukan | Akses /admin/ekskul/9999 | 404 Not Found | ⬜ | |
| M-50 | Anggota tidak ditemukan | Hapus anggota dengan ID invalid | Toast error | ⬜ | |
| M-51 | Session expired | Biarkan idle > 120 menit, lalu submit form | Redirect login | ⬜ | |
| M-52 | Double submit form | Klik submit 2x cepat | Tidak terjadi duplikasi data | ⬜ | |

---

## I. Security

| ID | Test Case | Langkah | Hasil yang Diharapkan | Status | Catatan |
|----|-----------|---------|----------------------|--------|---------|
| M-53 | CSRF protection | Coba POST tanpa CSRF token | 419 Page Expired | ⬜ | |
| M-54 | XSS pada input nama | Input `<script>alert(1)</script>` di nama | HTML di-escape, tidak dieksekusi | ⬜ | |
| M-55 | SQL injection | Input `'; DROP TABLE ekskul;--` di search | Tidak terjadi apa-apa (parameterized query) | ⬜ | |

---

## 📋 Ringkasan Hasil

| Section | Total | Pass | Fail | Minor | Notes |
|---------|-------|------|------|-------|-------|
| A. CRUD Ekskul | 12 | 0 | 0 | 0 | |
| B. Anggota | 7 | 0 | 0 | 0 | |
| C. Absensi | 6 | 0 | 0 | 0 | |
| D. Rekap | 4 | 0 | 0 | 0 | |
| E. QR Code | 4 | 0 | 0 | 0 | |
| F. Mobile | 4 | 0 | 0 | 0 | |
| G. Validasi | 9 | 0 | 0 | 0 | |
| H. Error Handling | 6 | 0 | 0 | 0 | |
| I. Security | 3 | 0 | 0 | 0 | |
| **TOTAL** | **55** | **0** | **0** | **0** | |

---

> **Catatan Tambahan:**
> _Tulis temuan-temuan penting saat melakukan pengujian manual di sini._
