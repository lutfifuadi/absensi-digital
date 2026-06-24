# PRD: Toggle Aktif/Nonaktif Tahun Ajaran (AJAX Switch)

**Versi:** v1.0
**Tanggal:** 24 Juni 2026
**Penulis:** PRD Specialist (Otomatis)
**Status:** Draft
**PIC Produk:** Mas Lutfi
**Tipe:** Lite
**Estimasi:** 🟡 3 SP (Small) — ~1 hari
**Quality Score:** 🟢 85/100 — Good
**RICE:** 🟡 6.0 (Medium Priority)

---

## 1. Executive Summary

Saat ini halaman `/admin/tahun-akademik` menampilkan status Aktif/Nonaktif dalam bentuk **badge statis**. Admin harus membuka modal edit, mencari checkbox "Tetapkan sebagai tahun ajaran aktif", menyimpan, dan halaman di-reload untuk mengganti status aktif TA. Ini lambat dan tidak efisien, apalagi untuk kebutuhan sering berganti TA.

Fitur ini akan mengganti badge status dengan **toggle switch (on/off) AJAX** yang langsung mengubah status tanpa reload halaman.

---

## 2. Background & Problem

### Masalah Saat Ini
1. Admin ingin mengganti TA aktif (misal dari 2025-2026 ke 2026-2027) harus:
   - Klik tombol "Ubah" → modal terbuka → centang checkbox → scroll → klik "Perbarui" → page reload
   - **Rata-rata 5-7 detik per切换**, padahal cukup 1 klik
2. Tidak ada **feedback visual cepat** — admin tidak bisa langsung melihat mana TA yang aktif
3. Percuma ada kolom `is_aktif` di DB kalau aksesnya lambat

### Dampak
- **User experience** kurang optimal untuk operasional harian
- **Produktivitas admin** terhambat untuk tugas rutin (ganti TA, verifikasi data per TA)

---

## 3. Goals & Success Metrics

| Goal | Metric | Target | Measurement |
|------|--------|--------|-------------|
| Mempercepat切换 TA aktif | Waktu yang dibutuhkan | < 1 detik | Stopwatch dari klik toggle sampai status berubah |
| Mengurangi langkah | Jumlah klik yang diperlukan | 1 klik (dari 5 klik) | Hitung klik di current flow vs new flow |
| Feedback visual | Toggle menunjukkan status real-time | 100% | Toggle switch berubah tanpa page reload |
| Tidak ada error | Error rate AJAX call | 0% | Monitor error response dari server |

---

## 4. User Personas

| Role | Deskripsi | Pain Point | Goal |
|------|-----------|------------|------|
| **Admin Sekolah** | Mengelola data master & tahun ajaran | Harus buka modal edit untuk ganti status aktif | Satu klik toggle untuk ganti status |
| **Operator** | Input data presensi, ganti TA sesuai kebutuhan | Sering ganti TA untuk verifikasi data | Toggle cepat tanpa reload |

---

## 5. User Stories

- [ ] **US-001** — Sebagai **Admin Sekolah**, saya ingin **men-toggle status aktif/nonaktif TA langsung dari tabel** sehingga **saya tidak perlu membuka modal edit**.
- [ ] **US-002** — Sebagai **Operator**, saya ingin **melihat indikator loading saat toggle diproses** sehingga **saya tahu sistem sedang memproses**.
- [ ] **US-003** — Sebagai **Admin Sekolah**, saya ingin **jika ada error pada toggle, muncul notifikasi error** sehingga **saya tahu ada masalah dan bisa coba lagi**.
- [ ] **US-004** — Sebagai **Admin Sekolah**, saya ingin **saat satu TA di-aktifkan, TA lain otomatis nonaktif** sehingga **hanya satu TA aktif dalam satu waktu**.

---

## 6. Functional Requirements

### 6.1 Toggle Switch di Tabel (Frontend)

| ID | Requirement |
|----|-------------|
| FR-001 | Kolom "Status" pada tabel TA menampilkan **toggle switch (Bootstrap form-switch)** bukan badge static |
| FR-002 | Toggle menggunakan **AJAX POST** ke endpoint backend (tanpa reload halaman) |
| FR-003 | Saat toggle ON → TA menjadi **Aktif (is_aktif = true)**, TA lain otomatis nonaktif |
| FR-004 | Saat toggle OFF → tidak boleh (karena harus ada 1 TA aktif) — **notification warning** |
| FR-005 | Tampilkan **loading spinner** pada toggle yang sedang diproses (disabled state) |
| FR-006 | Jika sukses → toggle berubah, TA lain yang tadinya aktif berubah jadi nonaktif secara real-time |
| FR-007 | Jika gagal → tampilkan **alert error** (bisa toast/alert) dan toggle kembali ke posisi semula |
| FR-008 | Toggle **hanya bisa diklik oleh role**: super_admin, admin_sekolah, operator |

### 6.2 Backend Endpoint

| ID | Requirement |
|----|-------------|
| FR-009 | Endpoint `POST /admin/tahun-akademik/{id}/toggle-aktif` — toggle `is_aktif` untuk TA tertentu |
| FR-010 | Validasi: TA dengan ID tersebut harus ada |
| FR-011 | Jika `is_aktif` akan di-set ke `true`, set semua TA lain ke `false` (hanya satu yang aktif) |
| FR-012 | Jika `is_aktif` akan di-set ke `false` dan TA tersebut sedang aktif → **tolak** dengan pesan "Harus ada minimal satu tahun ajaran yang aktif" |
| FR-013 | Response JSON: `{ success: true, message: "...", is_aktif: true/false }` |
| FR-014 | Catat aktivitas ke **ActivityLog** setiap kali toggle |

### 6.3 Route

| ID | Requirement |
|----|-------------|
| FR-015 | Route name: `admin.tahun-akademik.toggle-aktif` |
| FR-016 | Middleware: `role:super_admin,admin_sekolah,operator` |

---

## 7. Non-Functional Requirements

| ID | Requirement |
|----|-------------|
| NFR-001 | Response AJAX maksimal **2 detik** (termasuk DB query) |
| NFR-002 | Toggle harus **CSRF-protected** (mengikuti pattern Laravel) |
| NFR-003 | Konsisten dengan **dark theme Vuexy** yang sudah ada |
| NFR-004 | Support **mobile view** — toggle harus tetap rapih di layar kecil |
| NFR-005 | **Accessibility**: toggle bisa diakses via keyboard (tab + enter) |

---

## 8. User Flow

```
Flow: Mengganti Status Aktif TA

1. Admin membuka halaman /admin/tahun-akademik
2. Admin melihat daftar TA dengan toggle switch di kolom "Status"
   - TA yang aktif → toggle ON (warna hijau)
   - TA yang nonaktif → toggle OFF (warna abu-abu)
3. Admin klik toggle pada TA yang ingin diaktifkan
4. ✅ Tampilkan loading spinner pada toggle tersebut (disabled)
5. ✅ AJAX POST ke /admin/tahun-akademik/{id}/toggle-aktif
6. ✅ Server:
   a. Set is_aktif = true untuk TA yang diklik
   b. Set is_aktif = false untuk semua TA lain
   c. Catat ke ActivityLog
7. ✅ Response sukses:
   - Toggle berubah jadi ON (hijau)
   - TA lain yang tadinya aktif berubah jadi OFF (abu-abu) secara otomatis
   - (Tidak perlu reload halaman)
8. ❌ Jika gagal:
   - Toggle kembali ke posisi semula
   - Tampilkan alert/notification error
```

---

## 9. Business Rules & Validation

| ID | Rule |
|----|------|
| BR-001 | **Hanya satu TA yang boleh aktif dalam satu waktu.** Jika TA A diaktifkan, semua TA lain otomatis nonaktif |
| BR-002 | **Minimal satu TA harus aktif.** Tidak boleh semua TA nonaktif. Backend wajib menolak jika mencoba menonaktifkan satu-satunya TA yang aktif |
| BR-003 | **Role-based access:** Hanya super_admin, admin_sekolah, dan operator yang bisa melakukan toggle |
| BR-004 | **Audit trail:** Setiap toggle aktivitas dicatat di ActivityLog |

---

## 10. Data Requirements

### Existing Field (sudah ada, tidak perlu modifikasi)

| Tabel | Field | Tipe | Keterangan |
|-------|-------|------|------------|
| `tahun_akademik` | `is_aktif` | `boolean` | Status aktif TA (default: false) |

**Tidak ada tabel/field baru yang diperlukan.** Hanya perlu endpoint baru untuk toggle.

---

## 11. Integration & Dependencies

| Dependency | Detail |
|------------|--------|
| **CSRF Token** | Setiap AJAX request harus menyertakan `X-CSRF-TOKEN` dari meta tag |
| **Bootstrap JS** | Modal, form-switch, alert/notifikasi (sudah ada) |
| **Tabler Icons** | Icon loading spinner (`tabler-loader`), icon untuk toggle (sudah ada) |
| **jQuery / Vanilla JS** | AJAX call bisa pakai `fetch()` vanilla atau jQuery yang sudah ada |

---

## 12. Acceptance Criteria

- [ ] **AC-001:** Toggle switch muncul di kolom "Status" setiap baris tabel TA
- [ ] **AC-002:** Saat tombol toggle di-click, status berubah via AJAX tanpa reload
- [ ] **AC-003:** Loading spinner muncul selama proses AJAX berjalan
- [ ] **AC-004:** Jika toggle ON, TA tersebut jadi Aktif, TA lain jadi Nonaktif (real-time)
- [ ] **AC-005:** Jika toggle OFF pada satu-satunya TA aktif → muncul error "Minimal satu TA harus aktif"
- [ ] **AC-006:** Jika error terjadi, toggle kembali ke posisi semula
- [ ] **AC-007:** ActivityLog tercatat setiap kali toggle
- [ ] **AC-008:** Hanya user dengan role super_admin, admin_sekolah, operator yang bisa toggle
- [ ] **AC-009:** Response JSON sesuai format: `{ success: bool, message: string, is_aktif: bool }`

---

## 13. Out of Scope

- ❌ Membuat halaman baru (hanya modifikasi halaman existing)
- ❌ Migration database baru (field `is_aktif` sudah ada)
- ❌ Multiple TA aktif dalam satu waktu
- ❌ Logika bisnis terkait presensi/siswa (hanya toggle status)
- ❌ Batch toggle (toggle banyak TA sekaligus)

---

## 14. Open Questions

- Q: Apakah toggle perlu konfirmasi terlebih dahulu? (misal "Yakin ingin mengganti TA aktif?")
  - **Asumsi:** Tidak perlu, toggle langsung eksekusi. Cukup ada notifikasi sukses/gagal.
- Q: Apakah perlu mengubah session user saat TA aktif diganti?
  - **Asumsi:** Tidak untuk saat ini. Toggle hanya mengubah `is_aktif` di database. Session user tetap.

---

## 15. Risks & Mitigation

| Risk | Impact | Likelihood | Mitigation |
|------|--------|------------|------------|
| Toggle tidak sinkron dengan session user | Medium | Low | Toggle hanya untuk DB `is_aktif`. Session tidak terpengaruh |
| Dua admin toggle bersamaan → race condition | Low | Low | DB transaction + Eloquent `update()` atomic |
| User tanpa role akses melihat toggle | Low | Low | Middleware role + Blade `@can` / role check |

---

## 16. Revision History

| Versi | Tanggal | Perubahan | Author |
|-------|---------|-----------|--------|
| v1.0 | 24 Jun 2026 | Dokumen awal | PRD Specialist |

---

## 📋 Task Breakdown untuk Tim

### Backend (Bayu) — 1 SP
- [ ] Buat method `toggleAktif(TahunAkademik $tahunAkademik)` di `TahunAkademikController`
- [ ] Validasi: tidak boleh nonaktifkan satu-satunya TA yang aktif
- [ ] Logic: jika aktifkan → set semua TA lain ke false
- [ ] Response JSON + ActivityLog
- [ ] Route: `POST /admin/tahun-akademik/{tahunAkademik}/toggle-aktif` → `admin.tahun-akademik.toggle-aktif`

### Frontend (Ayu) — 1.5 SP
- [ ] Modifikasi `resources/views/admin/tahun-akademik/index.blade.php`
- [ ] Ganti badge status dengan toggle switch (`form-check form-switch`)
- [ ] JS function `toggleAktif(id)` dengan AJAX fetch/axios
- [ ] Loading state, error handling, real-time update toggle lain
- [ ] CSRF token dari meta tag

### Testing (Farhan) — 0.5 SP
- [ ] Test toggle ON → berhasil
- [ ] Test toggle OFF pada satu-satunya TA aktif → error
- [ ] Test role selain super_admin/admin/operator → 403
- [ ] Test AJAX error response → toggle kembali, muncul alert

### Dependencies
- Frontend tergantung Backend selesai (endpoint & route)
- Testing bisa paralel setelah Backend + Frontend selesai
