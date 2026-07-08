# PRD: Fitur Pembuatan Akun Orang Tua Otomatis & Massal (Auto-Ortu Siswa)

| Field | Detail |
|-------|--------|
| PRD ID | PRD-006 |
| Versi | 1.0 |
| Status | Approved |
| Penulis | Sophia (Asisten PM) & Kang Dadang (PRD Specialist) |
| Tanggal | 2026-07-09 |
| Prioritas | High |
| RICE Score | 80 |

---

## 1. Deskripsi Fitur
Fitur **Auto-Ortu Siswa** dirancang untuk memperbaiki bug pada sinkronisasi akun orang tua serta memfasilitasi pembuatan/generasi akun orang tua (Wali Murid) secara massal langsung dari halaman Manajemen Siswa. Fitur ini akan mengotomatisasi proses pembuatan akun `User` dengan peran (role) orang tua, menghubungkannya ke siswa terkait via tabel pivot `siswa_ortu`, serta memperbarui referensi `ortu_user_id` pada tabel `siswa`.

---

## 2. Latar Belakang & Masalah
1. **Error `assignRole()` pada Sinkronisasi Orang Tua**:
   Saat admin menekan tombol "Sinkronisasi Data" di halaman `/admin/orang-tua`, terjadi error `Call to undefined method App\Models\User::assignRole()`. Hal ini disebabkan sistem saat ini menggunakan custom field `role` (string) dan `roles` (array/json) di model `User`, bukan library Spatie Laravel-Permission.
2. **Ketiadaan Fitur Pembuatan Massal Akun Orang Tua**:
   Di halaman `/admin/siswa`, admin kesulitan membuatkan akun orang tua untuk siswa lama yang belum memiliki akun orang tua karena tidak ada tombol/opsi untuk memprosesnya secara massal. Pembuatan secara manual satu per satu sangat tidak efisien untuk jumlah siswa yang banyak.

---

## 3. Tujuan Fitur
- Memperbaiki bug error `assignRole()` pada `OrangTuaController@syncData`.
- Menyediakan tombol aksi massal "Generate Akun Ortu" di halaman `/admin/siswa` untuk mengotomatiskan pembuatan akun orang tua bagi seluruh siswa yang belum memilikinya.
- Mengurangi beban administratif admin dalam mengelola akun wali murid.

---

## 4. User Stories
| ID | Sebagai | Saya ingin | Sehingga |
|---|---------|------------|----------|
| US-1 | Administrator | Menekan tombol "Sinkronisasi Data" di halaman Orang Tua tanpa ada error crash | Proses sinkronisasi data orang tua berjalan lancar dan akun terbuat dengan role yang benar. |
| US-2 | Administrator | Menekan tombol "Generate Akun Ortu" di halaman Manajemen Siswa | Akun orang tua bagi semua siswa yang belum memiliki akun ortu akan digenerasikan secara massal secara instan di background. |
| US-3 | Administrator | Mendapatkan feedback status dan jumlah data yang berhasil diproses setelah proses selesai | Saya mengetahui berapa banyak akun orang tua baru yang berhasil dibuat. |

---

## 5. Acceptance Criteria

### AC-1: Perbaikan Bug `assignRole`
- **Given**: Admin berada di halaman `/admin/orang-tua` dan menekan tombol "Sinkronisasi Data".
- **When**: Sistem memproses pembuatan/sinkronisasi model `User` untuk orang tua.
- **Then**: Sistem tidak lagi memanggil method `$user->assignRole()`, melainkan langsung mengisi atribut `'role' => User::ROLE_ORANG_TUA` dan `'roles' => [User::ROLE_ORANG_TUA]`, lalu menyimpannya ke database dengan sukses.

### AC-2: Tombol Generate Akun Ortu Massal di Halaman Siswa
- **Given**: Admin membuka halaman `/admin/siswa`.
- **When**: Admin melihat daftar siswa. Terdapat sebuah tombol/aksi baru dengan label "Generate Akun Ortu".
- **Then**: Tombol tersebut dapat diklik dan memicu konfirmasi konfirmasi tindakan (SweetAlert).

### AC-3: Eksekusi Request AJAX & Respon Massal
- **Given**: Admin mengonfirmasi untuk melakukan generate akun ortu massal.
- **When**: Request dikirimkan via AJAX ke endpoint backend.
- **Then**: 
  - Backend memproses seluruh siswa yang memiliki `ortu_user_id` null (atau belum memiliki relasi orang tua).
  - Backend membuatkan akun `User` baru dengan aturan nama, username, email, dan password yang telah ditentukan.
  - Backend memasukkan relasi ke tabel `siswa_ortu` (pivot) dan meng-update kolom `ortu_user_id` pada baris siswa bersangkutan.
  - Backend mengembalikan JSON response berupa status sukses dan jumlah akun yang berhasil digenerate.
  - Frontend menampilkan SweetAlert sukses dengan detail jumlah data yang berhasil diproses.

---

## 6. Desain UI/UX Flow

### A. Alur Halaman `/admin/orang-tua`
1. Admin menekan tombol "Sinkronisasi Data".
2. Tampil loading indicator.
3. Proses selesai tanpa error, data tersinkronisasi, dan menampilkan pesan sukses.

### B. Alur Halaman `/admin/siswa`
1. Admin masuk ke menu **Data Siswa** (`/admin/siswa`).
2. Di atas tabel data siswa atau di area action global, terdapat tombol **"Generate Akun Ortu"** berwarna biru/hijau dengan ikon kunci atau user.
3. Ketika diklik, muncul modal konfirmasi SweetAlert:
   - *Title*: "Generate Akun Orang Tua?"
   - *Text*: "Tindakan ini akan membuatkan akun orang tua secara otomatis untuk semua siswa yang belum memiliki akun orang tua."
   - *Buttons*: "Ya, Generate!" dan "Batal"
4. Jika disetujui, tombol berubah menjadi loading state / menampilkan progress loader.
5. Setelah AJAX backend selesai merespon, tampilkan SweetAlert Sukses:
   - *Title*: "Berhasil!"
   - *Text*: "Berhasil membuat [X] akun orang tua baru."
   - Halaman me-reload atau me-refresh tabel secara dinamis.

---

## 7. Rencana Teknis & Logika Pembuatan Akun

### A. Aturan Penulisan Atribut Akun Baru:
1. **Name**: `Wali Murid [Nama Lengkap Siswa]` (diambil dari kolom nama siswa).
2. **Username**: `ortu.[NISN/NIS]`
3. **Email**: `ortu.[NISN/NIS]@[domain_lembaga]`
   - Domain diambil dari pengaturan website lembaga (`website_lembaga` dari tabel setting/opsi aplikasi).
   - Jika pengaturan kosong atau tidak ditemukan, gunakan default: `madrasah.sch.id`.
4. **Password**: Menggunakan string `NISN` siswa. Jika `NISN` kosong, gunakan `NIS` siswa. Jika keduanya kosong, gunakan password default `password123` (dienkripsi menggunakan `bcrypt` atau `Hash::make`).
5. **Role & Roles**:
   - `role` => `User::ROLE_ORANG_TUA` (atau `'orang_tua'`)
   - `roles` => `[User::ROLE_ORANG_TUA]` (disimpan sebagai JSON/array)

### B. Alur Database & Hubungan Relasi:
Untuk setiap siswa yang terpilih diproses:
1. Buat record baru di tabel `users` dengan atribut di atas.
2. Dapatkan ID user yang baru dibuat (`$userId`).
3. Tambahkan relasi di tabel pivot `siswa_ortu` menghubungkan `siswa_id` dan `ortu_user_id` / `ortu_id` (sesuai skema yang ada).
4. Update kolom `ortu_user_id` di tabel `siswa` dengan ID user orang tua yang baru dibuat.
5. Gunakan `DB::transaction()` untuk memastikan konsistensi data jika terjadi kegagalan di tengah jalan.

---

## 8. Target File yang Akan Dimodifikasi
- `App\Http\Controllers\Admin\OrangTuaController.php` (Fix bug `assignRole`)
- `App\Http\Controllers\Admin\SiswaController.php` (Menambahkan method AJAX endpoint untuk generate massal)
- File Routing Admin (`routes/web.php` atau sejenisnya)
- `resources/views/admin/siswa/index.blade.url` atau file view siswa terkait (Menambahkan tombol & script AJAX SweetAlert)
