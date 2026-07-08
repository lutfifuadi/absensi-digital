# PRODUCT REQUIREMENTS DOCUMENT (PRD)
## Fitur Filter dan Sortir Kolom di Manajemen User (/admin/users)

| Field | Detail |
|-------|--------|
| PRD ID | PRD-005 |
| Versi | 1.0 |
| Status | Draft |
| Penulis | Kang Dadang (PRD Specialist) & Sophia (Asisten PM) |
| Tanggal | 2026-07-09 |
| Prioritas | High |
| RICE Score | 75 (Reach: 3, Impact: 2, Confidence: 80%, Effort: 1.5) |

---

## 1. Ringkasan & Tujuan Bisnis

### Ringkasan Fitur
Fitur ini bertujuan untuk meningkatkan fungsionalitas dan efisiensi pengelolaan data pada halaman Manajemen User (`/admin/users`). Saat ini, administrator hanya dapat melakukan pencarian berbasis teks biasa yang relatif lambat dan tidak spesifik. Dengan fitur baru ini, pengguna dapat menyaring data berdasarkan filter teks spesifik (Nama, Username, Email), dropdown Hak Akses (Role) secara dinamis, dan rentang tanggal pembuatan akun (Date Range Picker), serta mengurutkan (sorting) kolom-kolom kunci secara asinkronus (AJAX) tanpa memuat ulang seluruh halaman (full reload).

### Masalah Saat Ini
1. Pengguna kesulitan memilah user berdasarkan role tertentu secara cepat jika jumlah user sudah mencapai ratusan/ribuan.
2. Tidak adanya filter berdasarkan rentang tanggal menyulitkan pelacakan pendaftaran pengguna baru pada periode tertentu.
3. Pengurutan data nama user bersifat statis (hardcoded A-Z dari backend) dan tidak dapat diubah-ubah secara dinamis oleh admin.
4. Setiap interaksi pencarian atau navigasi pagination masih memicu reload penuh, yang mengurangi kenyamanan dan kecepatan operasional (user experience).

### Tujuan Bisnis
- **Meningkatkan Efisiensi Operasional**: Meminimalkan waktu pencarian dan pengelolaan data user oleh Admin/Super Admin hingga 50%.
- **Optimalisasi User Experience**: Menghadirkan antarmuka pengelolaan data yang interaktif, responsif, dan modern dengan transisi AJAX yang mulus.

---

## 2. Target User & User Stories

### Target User
- **Super Admin**: Pengguna dengan hak akses tertinggi yang mengelola semua jenis user di platform.
- **Admin Sekolah / Operator**: Pengguna tingkat menengah yang memantau penambahan guru, siswa, maupun staff di lingkungan sekolahnya.

### User Stories
| ID | Sebagai | Saya ingin... | Sehingga... |
|----|---------|---------------|-------------|
| US-01 | Super Admin / Admin | Memfilter user berdasarkan teks nama, username, atau email secara langsung di satu field filter teks | Saya bisa dengan cepat menemukan individu spesifik tanpa harus mencari manual di tabel. |
| US-02 | Super Admin / Admin | Memilih opsi filter Hak Akses (Role) tertentu dari dropdown dinamis | Tabel hanya menampilkan user yang memiliki role tersebut (misalnya, hanya menampilkan semua "Guru"). |
| US-03 | Super Admin / Admin | Memilih rentang tanggal pendaftaran (Date Range Join) menggunakan kalender | Saya dapat memverifikasi user-user baru yang bergabung pada minggu atau bulan berjalan saja. |
| US-04 | Super Admin / Admin | Mengeklik header kolom Informasi User atau Tanggal Join untuk mengurutkannya (A-Z, Z-A, Terbaru, Terlama) | Saya dapat mengorganisasi visualisasi tabel dengan prioritas abjad atau urutan kronologis. |
| US-05 | Super Admin / Admin | Melakukan filter, pengurutan, dan navigasi halaman (pagination) tanpa reload layar | Alur kerja saya menjadi sangat responsif dan bebas hambatan jeda pemuatan halaman. |

---

## 3. Detail Fitur

### 3.1. Komponen Antarmuka Filter (Filter Panel)
Diletakkan di bagian atas tabel sebelum data dimuat, terdiri dari:
1. **Filter Pencarian Teks (Informasi User)**:
   - Input teks placeholder: `"Cari nama, username, atau email..."`.
   - Mengirimkan query parameter `search`.
2. **Filter Hak Akses (Role)**:
   - Elemen `<select>` dropdown dinamis.
   - Pilihan diambil dari method `roleOptions()` di `UserController.php` (dinamis dari database `roles` atau array fallback).
   - Menambahkan opsi default: `"Semua Hak Akses"`.
   - Mengirimkan query parameter `role`.
3. **Filter Tanggal Join (Date Range)**:
   - Input berbasis flatpickr / bootstrap-datepicker (sesuai package frontend yang terpasang di layout).
   - Mendukung pemilihan tanggal mulai (Start Date) dan tanggal akhir (End Date).
   - Format visual: `DD/MM/YYYY` atau sesuai standar UI.
   - Mengirimkan query parameter `start_date` dan `end_date`.

### 3.2. Fitur Sortir (Sorting) pada Header Tabel
1. **Header Kolom Informasi User**:
   - Judul kolom dapat diklik.
   - Mengirimkan query parameter `sort_by=name` dan `sort_direction=asc|desc`.
   - Menampilkan indikator ikon penah (sorting icon) yang berubah dinamis:
     - Default (belum diurutkan): Double-arrow (`ti tabler-selector` atau sejenisnya).
     - Ascending (A-Z): Arrow-up (`ti tabler-chevron-up`).
     - Descending (Z-A): Arrow-down (`ti tabler-chevron-down`).
2. **Header Kolom Tanggal Join**:
   - Judul kolom dapat diklik.
   - Mengirimkan query parameter `sort_by=created_at` dan `sort_direction=asc|desc`.
   - Indikator ikon serupa dengan kolom Informasi User.

### 3.3. Integrasi AJAX & State Management
- **Debouncing**: Input pencarian teks harus menerapkan debounce sebesar `500ms` sebelum memicu request AJAX ke server untuk menghindari overload database query.
- **Event Listeners**: Perubahan pada dropdown Role, tanggal, klik sorting header, dan pagination link harus memicu fungsi pemanggilan AJAX yang sama.
- **URL Synchronization**: Ketika filter/sortir diubah, URL browser harus diupdate menggunakan `history.pushState()` tanpa me-reload halaman agar state pencarian tetap dapat dibagikan (shareable link) atau di-refresh dengan hasil yang konsisten.
- **Loading State**: Menampilkan indikator loading (transparansi tabel atau spinner) saat data sedang di-request via AJAX.

---

## 4. Kriteria Penerimaan (Acceptance Criteria)

### AC-01: Pencarian & Filter Kolom
- **Given** Admin berada di halaman Manajemen User `/admin/users`.
- **When** Admin mengetik `"budi"` pada filter pencarian, memilih role `"Guru"`, dan mengisi rentang tanggal join dari `"01/07/2026"` s.d `"09/07/2026"`.
- **Then** Sistem mengirimkan request AJAX dengan payload `search=budi&role=guru&start_date=2026-07-01&end_date=2026-07-09` ke backend.
- **Then** Tabel ter-update secara dinamis hanya menampilkan user bernama/email Budi dengan role Guru yang terdaftar pada rentang tanggal tersebut, tanpa memicu reload layar penuh.

### AC-02: Sortir Kolom
- **Given** Tabel menampilkan daftar user.
- **When** Admin mengklik header kolom "Informasi User".
- **Then** Sistem mengurutkan data user berdasarkan nama secara ascending (A-Z), ikon sorting berubah menjadi panah atas, dan URL diselaraskan dengan parameter `sort_by=name&sort_direction=asc`.
- **When** Admin mengklik kembali header kolom "Informasi User".
- **Then** Urutan berubah menjadi descending (Z-A), ikon sorting berubah menjadi panah bawah, dan URL diselaraskan dengan parameter `sort_by=name&sort_direction=desc`.

### AC-03: Navigasi Pagination AJAX
- **Given** Hasil filter user tersebar di beberapa halaman (lebih dari 10 baris).
- **When** Admin mengklik tombol halaman `"2"` pada pagination.
- **Then** Sistem meminta data halaman kedua melalui AJAX dengan tetap mempertahankan parameter filter/sortir yang aktif saat itu.
- **Then** Data halaman kedua tampil secara mulus di tabel.

---

## 5. Alur Desain UI/UX (Wireframe Flow)

```
+-----------------------------------------------------------------------------+
|  MANAJEMEN USER                                          [ + Tambah User ]  |
+-----------------------------------------------------------------------------+
|  [ Cari nama/username/email... ] [ Semua Hak Akses [v]] [ Rentang Join [v]] |
+-----------------------------------------------------------------------------+
|  #  | Informasi User (A-Z) | Hak Akses (Role) | Tanggal Join (Newest) | Aksi|
|-----+----------------------+------------------+-----------------------+-----|
|  1  | Budi Setiawan        | Guru             | 05/07/2026            | [ ] |
|     | budi@school.id       |                  |                       |     |
|  2  | Jane Doe             | Admin Sekolah    | 02/07/2026            | [ ] |
|     | jane@school.id       |                  |                       |     |
+-----------------------------------------------------------------------------+
|  Halaman: [ < ] [ 1 ] [ 2 ] [ > ]                                           |
+-----------------------------------------------------------------------------+
```

### Panduan Desain Visual
1. Filter panel ditempatkan dalam grid responsif (misal: `row g-3 mb-4`).
2. Indikator loading berupa overlay opacity (misal: `.das-table { opacity: 0.5; }` ditambah spinner di tengah area tabel) untuk kenyamanan visual.
3. Tombol header sortir yang bisa diklik diberi style pointer hover (`cursor: pointer; user-select: none;`).

---

## 6. Rencana Teknis

### 6.1. Backend / Controller (`UserController.php`)
Modifikasi method `index()` untuk membaca parameter query tambahan:
```php
public function index(Request $request)
{
    $search = $request->query('search');
    $role = $request->query('role');
    $startDate = $request->query('start_date');
    $endDate = $request->query('end_date');
    
    $sortBy = $request->query('sort_by', 'name'); // default sorting by name
    $sortDirection = $request->query('sort_direction', 'asc'); // default asc
    
    // Whitelist columns to avoid SQL Injection
    $allowedSortColumns = ['name', 'created_at'];
    if (!in_array($sortBy, $allowedSortColumns)) {
        $sortBy = 'name';
    }
    if (!in_array($sortDirection, ['asc', 'desc'])) {
        $sortDirection = 'asc';
    }

    $perPage = $request->query('per_page', 10);

    $users = User::query()
        ->when($search, function ($query, $search) {
            return $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        })
        ->when($role, function ($query, $role) {
            // Memfilter berdasarkan kolom role JSON atau relasi
            return $query->where(function($q) use ($role) {
                $q->where('role', $role)
                  ->orWhereJsonContains('roles', $role);
            });
        })
        ->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
            return $query->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
        })
        ->orderBy($sortBy, $sortDirection)
        ->paginate($perPage)
        ->withQueryString();

    if ($request->ajax()) {
        return view('admin.users.table', compact('users'))->render();
    }

    $roles = $this->roleOptions();

    return view('admin.users.index', compact('users', 'roles'));
}
```

### 6.2. Frontend (`index.blade.php` & `table.blade.php`)
1. **Markup Filter Panel**:
   Letakkan form/input pencarian di index.blade.php.
2. **Header Tabel (`table.blade.php`)**:
   Implementasikan elemen interaktif pada header kolom:
   ```html
   <th class="sortable cursor-pointer" data-sort="name">
       Informasi User 
       <i class="ti tabler-selector text-muted float-end"></i>
   </th>
   ```
3. **Javascript / AJAX**:
   - Gunakan `fetch` API atau `jQuery.ajax` untuk me-load `table.blade.php` ketika input, filter, sorting, atau pagination mengalami perubahan.
   - Ambil URL yang ditargetkan (misal dari paginator link atau kalkulasi query string dari input filter), lakukan call AJAX, lalu timpa elemen container tabel dengan respon html yang dikembalikan.
   - Contoh snippet update state:
     ```javascript
     function fetchUsers(url) {
         $('#table-container').addClass('opacity-50');
         $.ajax({
             url: url,
             type: 'GET',
             success: function(response) {
                 $('#table-container').html(response).removeClass('opacity-50');
                 // Re-initialize tooltips/popovers if any
             }
         });
     }
     ```
