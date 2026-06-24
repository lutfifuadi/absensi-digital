# PRD: Modal Konfirmasi Hapus Guru (UI/UX)

**Versi:** v1.0
**Tanggal:** 24 Juni 2026
**Penulis:** PRD Specialist (Otomatis)
**Status:** Draft
**PIC Produk:** Mas Lutfi
**Tipe:** Lite
**Estimasi:** 🔵 1 SP (Small) — ~0.5 hari
**Quality Score:** 🟢 85/100 — Good

---

## 1. Executive Summary

Ganti `confirm()` dialog bawaan browser dengan **Bootstrap modal** yang _cantik, seimbang, dan konsisten_ dengan tema Vuexy untuk konfirmasi hapus data guru. Mengadopsi pola yang sudah berhasil di halaman Tahun Ajaran.

---

## 2. Background & Problem

### Saat Ini
```blade
<form ... onsubmit="return confirm('Yakin ingin menghapus guru ini?');">
```
- Menggunakan **native browser confirm()** — tampilannya jelek, tidak konsisten dengan tema aplikasi
- Font dan ikon tidak bisa diatur
- Tidak ada info detail guru yang akan dihapus (hanya teks polos)

### Sudah Ada Contoh Baik
Halaman Tahun Ajaran (`/admin/tahun-akademik`) sudah punya modal hapus yang **cantik**:
- Icon container 44x44px dengan background merah transparan
- Icon warning `tabler-alert-triangle`
- Judul & subtitle jelas
- Nama item yang akan dihapus ditampilkan dengan font bold
- Tombol Batal & Hapus seimbang

---

## 3. Goals & Success Metrics

| Goal | Metric | Target |
|------|--------|--------|
| UI konsisten | Modal hapus Guru = style modal hapus TA | 100% sama |
| UX lebih baik | Tidak ada native browser popup | 0 native confirm |
| Info jelas | Nama & NIP guru tampil di modal | Tampil selalu |

---

## 4. User Stories

- [ ] **US-001** — Sebagai **Admin**, saya ingin melihat **modal konfirmasi yang cantik** saat menghapus guru sehingga **saya yakin data yang dihapus benar**.
- [ ] **US-002** — Sebagai **Admin**, saya ingin **melihat nama & NIP guru** di modal konfirmasi sehingga **tidak salah hapus**.

---

## 5. Visual Reference

Modal yang sudah ada di halaman TA (sebagai referensi):

```
┌──────────────────────────────────────┐
│  ⚠️  Konfirmasi Hapus           [X] │
│      Tindakan ini tidak         │
│      dapat dibatalkan.              │
│                                      │
│         Yakin ingin menghapus        │
│         ❮Nama Guru❯                  │
│         NIP: xxxxx                   │
│                                      │
│         [ Batal ]  [ Hapus ]         │
└──────────────────────────────────────┘
```

### Spesifikasi Visual
| Elemen | Spesifikasi |
|--------|-------------|
| Icon container | 44x44px, border-radius 10px, background `rgba(234,84,85,0.2)`, border `rgba(234,84,85,0.35)` |
| Icon | `ti tabler-alert-triangle text-danger fs-5` |
| Title | `h5`, fw-bold, text-white |
| Subtitle | `small text-white-50` |
| Nama guru | `fw-bold text-warning fs-6` atau `fs-5` |
| Tombol Batal | `btn btn-label-secondary` dengan icon `tabler-x` |
| Tombol Hapus | `btn btn-danger fw-semibold px-4 shadow-sm` dengan icon `tabler-trash` |
| Lebar modal | max-width 420px, centered |
| Background modal | `#1e1e2d` dengan border transparan |

---

## 6. Functional Requirements

### Frontend (Ayu)

| ID | Requirement |
|----|-------------|
| FR-001 | Hapus `onsubmit="return confirm(...)"` dari form delete guru |
| FR-002 | Buat modal Bootstrap dengan ID `modalHapusGuru` di bagian bawah halaman (sebelum `@endsection`) |
| FR-003 | Modal memiliki: icon warning, title, subtitle, nama guru, NIP guru, tombol Batal & Hapus |
| FR-004 | JS function `openHapusModal(id, nama, nip)` untuk trigger modal & set data dinamis |
| FR-005 | Form delete di-submit via form dalam modal (bukan form di tombol aksi) |
| FR-006 | Styling konsisten dengan dark theme Vuexy yang sudah ada |

---

## 7. Acceptance Criteria

- [ ] **AC-001:** Klik tombol Hapus → muncul modal (bukan browser confirm)
- [ ] **AC-002:** Modal menampilkan nama & NIP guru yang akan dihapus
- [ ] **AC-003:** Tombol "Batal" menutup modal tanpa menghapus
- [ ] **AC-004:** Tombol "Hapus" meng-submit form dan menghapus data
- [ ] **AC-005:** Styling modal identik dengan modal hapus di halaman TA
- [ ] **AC-006:** Tidak ada perubahan fungsi backend

---

## 8. Out of Scope

- ❌ Perubahan backend (destroy method sudah diubah)
- ❌ Migration database
- ❌ Modal hapus untuk halaman lain (hanya Guru dulu)

---

## 9. Revision History

| Versi | Tanggal | Perubahan | Author |
|-------|---------|-----------|--------|
| v1.0 | 24 Jun 2026 | Dokumen awal | PRD Specialist |

---

## 📋 Task

### Frontend (Ayu) — 1 SP
File: `D:\Project\Aplikasi Presensi\resources\views\admin\guru\index.blade.php`

1. **Hapus** `onsubmit` dari form delete (line 204-210)
2. **Ganti** button hapus jadi button biasa (bukan submit) yang panggil `openHapusModal(id, nama, nip)`
3. **Tambah** modal hapus di akhir file (sebelum `@endsection`) — copy styling dari TA
4. **Tambah** JS function `openHapusModal()` di section `page-script`
5. **Pastikan** form di modal punya action & method DELETE yang benar

### Testing (Farhan) — 0.5 SP
- Klik hapus → modal muncul ✅
- Nama & NIP tampil ✅
- Batal → modal tertutup, tidak ada perubahan ✅
- Hapus → data terhapus ✅
- Styling konsisten ✅
