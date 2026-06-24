# PRD: Perbaikan Struktur HTML & Konsistensi Halaman Guru

**Versi:** v1.0
**Tanggal:** 24 Juni 2026
**Penulis:** PRD Specialist (Otomatis)
**Status:** Draft
**PIC Produk:** Mas Lutfi
**Tipe:** Lite
**Estimasi:** 🔵 2 SP (Small) — ~1 hari
**Quality Score:** 🟢 88/100 — Good

---

## 1. Executive Summary

Perbaikan struktur HTML halaman `/admin/guru` yang ditemukan memiliki **critical bug**: modal delete terletak **di luar** `@section('content')`, sehingga tidak dirender di area konten utama. Selain itu ada beberapa inkonsistensi styling antar modal, flash message yang tidak handle error state, dan aksesibilitas yang kurang.

---

## 2. Hasil Audit — Temuan

| # | Severity | Temuan | Lokasi |
|:-:|:--------:|--------|--------|
| 🔴 | **Critical** | Modal hapus guru **berada di luar** `@section('content')` — antara `@endsection` dan `@section('page-script')`. HTML akan dirender di luar layout utama | Line 284-332 |
| 🟡 | **Medium** | Flash message **hanya handle** `session('success')` — tidak ada untuk `error` | Line 92-99 |
| 🟡 | **Medium** | **Inkonsistensi modal**: Import Modal pakai class `das-modal`, Delete Modal pakai inline styles langsung | Line 243 vs 289 |
| 🔵 | **Low** | Search filter **client-side** menyembunyikan baris, tapi kolom hidden (NIP, Email) tetap ikut di-search — user bisa cari data yang tidak kelihatan | Line 343-355 |
| 🔵 | **Low** | **Aksesibilitas**: Action buttons tidak punya `aria-label`, heading `<h6>` untuk "Daftar Guru" bukan `<h2>` | Line 105, 192-207 |
| 🔵 | **Low** | Indentation **tidak konsisten** — beberapa elemen mix 2 spasi dan 4 spasi | Seluruh file |

---

## 3. Goals & Success Metrics

| Goal | Metric | Target |
|------|--------|--------|
| HTML valid & rapi | Semua konten dalam section yang benar | 100% |
| Modal hapus muncul di posisi benar | Tidak ada perbedaan rendering | ✅ |
| Konsistensi modal | Import & Delete modal pakai styling yang sama | Identik |
| Flash message lengkap | Error & Success flash message tampil | Keduanya |

---

## 4. User Stories

- [ ] **US-001** — Sebagai **Admin**, saya ingin **modal hapus muncul di tengah layar dengan benar** sehingga **saya bisa konfirmasi hapus tanpa masalah**.
- [ ] **US-002** — Sebagai **Admin**, saya ingin **melihat notifikasi error** jika hapus guru gagal sehingga **saya tahu ada masalah**.
- [ ] **US-003** — Sebagai **Admin**, saya ingin **tombol aksi punya label yang jelas** sehingga **saya tahu fungsi setiap tombol**.

---

## 5. Perbaikan yang Dibutuhkan

### 5.1 🔴 Critical: Pindahkan Modal Delete ke Dalam `@section('content')`

**Lokasi:** Line 284-332

**Sekarang:**
```blade
@endsection  ← content ditutup di line 282

...modal HTML...  ← OUTSIDE content (line 284-332)

@section('page-script')  ← page-script dibuka (line 334)
```

**Seharusnya:**
```blade
  ...modal HTML...  ← INSIDE content

@endsection  ← content ditutup SETELAH modal

@section('page-script')
```

Perbaikan: Pindahkan seluruh modal hapus guru (line 284-332) ke dalam `@section('content')`, letakkan setelah import modal dan sebelum `@endsection`.

### 5.2 🟡 Medium: Lengkapi Flash Message untuk Error

**Lokasi:** Line 91-99

Tambahkan handling untuk `session('error')`:
```blade
@if (session('error'))
  <div class="alert alert-danger alert-dismissible d-flex align-items-center gap-2 mb-4 border-0 shadow-sm"
    role="alert" style="border-radius:8px;">
    <i class="ti tabler-alert-circle fs-5"></i>
    <span>{{ session('error') }}</span>
    <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
  </div>
@endif
```

### 5.3 🟡 Medium: Konsistensi Styling Modal

**Import Modal** (line 243):
```blade
<div class="modal-content das-modal shadow-lg">
```

**Delete Modal** (line 289):
```blade
<div class="modal-content shadow-lg" style="border:1px solid...;background:#1e1e2d;border-radius:12px;overflow:hidden;">
```

Keduanya harus konsisten. Pilih salah satu pendekatan (**rekomendasi: pakai class `das-modal`** yang sudah terdefinisi, karena lebih clean).

### 5.4 🔵 Low: Tambahkan `aria-label` pada Action Buttons

Setiap action button perlu `aria-label` untuk aksesibilitas:
```blade
<button type="button" class="action-btn text-danger" 
    title="Hapus" aria-label="Hapus {{ $displayName }}"
    data-bs-toggle="tooltip">
```

### 5.5 🔵 Low: Ganti `<h6>` dengan `<h2>` untuk Judul Panel

**Lokasi:** Line 105

```blade
<h2 class="das-panel__title mb-0 d-flex align-items-center gap-2" style="font-size:1rem;">
  <i class="ti tabler-list text-info"></i> Daftar Guru
</h2>
```

---

## 6. Acceptance Criteria

- [ ] **AC-001:** Modal hapus guru berada di DALAM `@section('content')` — tidak ada HTML yang menggantung di luar section
- [ ] **AC-002:** Modal hapus tampil di tengah layar dengan benar saat tombol hapus diklik
- [ ] **AC-003:** Flash message `error` muncul jika ada error dari backend
- [ ] **AC-004:** Import Modal & Delete Modal memiliki styling yang konsisten
- [ ] **AC-005:** setiap action button punya `aria-label`
- [ ] **AC-006:** Judul "Daftar Guru" menggunakan `<h2>` bukan `<h6>`
- [ ] **AC-007:** Tidak ada perubahan fungsi — hapus, import, export tetap berjalan normal

---

## 7. Out of Scope

- ❌ Perubahan backend (controller, model, route)
- ❌ Migration database
- ❌ Penambahan fitur baru
- ❌ Perubahan halaman lain selain `/admin/guru`

---

## 8. Revision History

| Versi | Tanggal | Perubahan | Author |
|-------|---------|-----------|--------|
| v1.0 | 24 Jun 2026 | Dokumen awal | PRD Specialist |

---

## 📋 Task Breakdown

### Frontend (Ayu) — 2 SP
File: `D:\Project\Aplikasi Presensi\resources\views\admin\guru\index.blade.php`

1. **Pindahkan** modal hapus (baris 284-332) ke dalam `@section('content')` — sebelum `@endsection` di baris 282
2. **Tambahkan** flash message untuk `session('error')` setelah flash message `success`
3. **Konsistensi modal:** Ubah styling delete modal menggunakan class `das-modal` yang sama dengan import modal
4. **Tambahkan** `aria-label` pada setiap action button (Login, QR, Edit, Hapus)
5. **Ganti** `<h6>` jadi `<h2>` untuk "Daftar Guru"
6. **Rapikan** indentation seluruh file

### Testing (Farhan) — 0.5 SP
- Buka halaman guru — modal hapus muncul di posisi benar ✅
- Hapus guru — sukses, flash message muncul ✅
- Error scenario — flash message error muncul ✅
- Tab navigation — semua tombol bisa diakses via keyboard ✅
- Validasi HTML — tidak ada tag yang salah letak ✅
