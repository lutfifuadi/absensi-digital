# PRD: Cascade Delete — Hapus Guru juga Hapus Akun User Terkait

**Versi:** v1.0
**Tanggal:** 24 Juni 2026
**Penulis:** PRD Specialist (Otomatis)
**Status:** Draft
**PIC Produk:** Mas Lutfi
**Tipe:** Lite
**Estimasi:** 🔵 1 SP (Small) — ~0.5 hari
**Quality Score:** 🟢 82/100 — Good
**RICE:** 🟡 4.5 (Medium Priority)

---

## 1. Executive Summary

Saat ini ketika admin menghapus data guru di `/admin/guru`, yang terhapus **hanya record di tabel `guru`** — akun User (login) dengan role guru tetap tersisa di database. Ini menghasilkan **akun yatim (orphan account)** yang tidak memiliki profil guru tetapi masih bisa login. Fitur ini memastikan bahwa ketika data guru dihapus, akun user terkait juga ikut terhapus.

---

## 2. Background & Problem

### Temuan
- **Migration `guru`** sudah memiliki `cascadeOnDelete()` pada `user_id` → jika **User dihapus**, data Guru ikut terhapus ✅
- Tapi **sebaliknya tidak**: ketika Guru dihapus via `GuruController@destroy`, **User TIDAK ikut terhapus** ❌

### Dampak
- **Akun yatim (orphan):** User dengan role `guru` tetap ada di DB tanpa profil guru
- **Keamanan:** Akun tersebut masih bisa login ke sistem
- **Inkonsistensi data:** Data User & Guru tidak sinkron
- **Berbeda dengan modul Siswa:** Perlu dicek apakah modul Siswa sudah memiliki perilaku serupa

### Akar Masalah
```php
// Current code — hanya hapus guru
public function destroy(Guru $guru)
{
    $guru->delete(); // User tidak ikut terhapus!
    return redirect()->with('success', 'Guru berhasil dihapus.');
}
```

---

## 3. Goals & Success Metrics

| Goal | Metric | Target | Measurement |
|------|--------|--------|-------------|
| Tidak ada orphan user | Jumlah User role guru tanpa profil Guru | 0 | Query: User dengan role guru tanpa relasi guru |
| Hapus data konsisten | Guru + User terhapus dalam 1 klik | 100% | Cek DB setelah delete |
| Aman untuk data terkait | Data absensi, jadwal, dll tidak corrupt | Tidak ada error | Cek log & relasi DB |

---

## 4. Technical Analysis

### Relasi Database Saat Ini

```
users (parent)
  ↑ FK: user_id CASCADE ON DELETE  (dari migration guru)
  |
guru (child)
```

- `guru.user_id` → FK ke `users.id` dengan `cascadeOnDelete`
- Artinya: **hapus User → Guru otomatis kehapus** (already works)
- Tapi: **hapus Guru → User TIDAK kehapus** (yang ini perlu diperbaiki)

### Data Terkait yang Perlu Dipertimbangkan
| Tabel | Relasi ke Guru | Risiko jika Guru dihapus |
|-------|---------------|--------------------------|
| `kelas` (wali_kelas_id) | FK nullable → `SET NULL` | ✅ Aman (wali_kelas_id jadi null) |
| `ekskul_pembina` | FK | ⚠️ Perlu dicek cascade |
| `absensi_guru` | FK | ⚠️ Perlu dicek cascade |
| `assignment` | FK | ⚠️ Perlu dicek cascade |
| `jadwal_pelajaran` | FK | ⚠️ Perlu dicek cascade |

### Hal yang Sama di Modul Siswa
Lihat `User@booted()`:
```php
protected static function booted(): void
{
    static::deleting(function (User $user) {
        if ($user->siswa) {
            $user->siswa->delete(); // Hapus siswa dulu sebelum user
        }
        if ($user->activityLogs()->exists()) {
            $user->activityLogs()->delete();
        }
    });
}
```

Saat User Siswa dihapus, record Siswa ikut dihapus (kebalikan dari yang kita butuhkan).

---

## 5. Functional Requirements

| ID | Requirement |
|----|-------------|
| FR-001 | Saat admin menghapus data guru, akun User terkait (role guru) juga ikut terhapus |
| FR-002 | Proses hapus dilakukan dalam 1 klik — tidak perlu konfirmasi tambahan |
| FR-003 | Tampilkan pesan sukses: "Guru dan akun user berhasil dihapus." |
| FR-004 | Jika User gagal dihapus (karena constraint DB), transaksi di-rollback dan tampilkan error |
| FR-005 | Data terkait guru (absensi, jadwal, ekskul pembina, dll) tetap aman — gunakan DB transaction |

---

## 6. Non-Functional Requirements

| ID | Requirement |
|----|-------------|
| NFR-001 | Gunakan **DB transaction** agar atomic — gagal semua atau berhasil semua |
| NFR-002 | Hapus User terlebih dahulu (agar DB cascade delete menangani Guru secara otomatis) |
| NFR-003 | Tetap pakai konfirmasi `confirm()` yang sudah ada di view (tidak perlu diubah) |

---

## 7. Acceptance Criteria

- [ ] **AC-001:** Hapus guru di halaman `/admin/guru` → User terkait juga terhapus dari DB
- [ ] **AC-002:** User yang terhapus tidak bisa login lagi
- [ ] **AC-003:** Data guru di tabel `guru` ikut terhapus (via DB cascade)
- [ ] **AC-004:** Jika User memiliki data lain (activity_logs), data tersebut ikut terhapus (via `User@booted()`)
- [ ] **AC-005:** Jika terjadi error, transaksi di-rollback dan data tetap utuh
- [ ] **AC-006:** Pesan sukses berubah menjadi "Guru dan akun user berhasil dihapus."

---

## 8. Business Rules

| ID | Rule |
|----|------|
| BR-001 | Hanya user dengan role **super_admin**, **admin_sekolah**, dan **operator** yang bisa menghapus guru (middleware sudah ada) |
| BR-002 | Data guru yang sudah memiliki aktivitas (absensi, dll) tetap bisa dihapus |
| BR-003 | Tidak ada soft delete — hapus permanen (mengikuti pola yang sudah ada) |

---

## 9. Out of Scope

- ❌ Soft delete / restore data guru
- ❌ Logika cascade untuk modul Siswa (sudah berjalan)
- ❌ Perubahan UI (konfirmasi hapus sudah ada)
- ❌ Migration database baru

---

## 10. Revision History

| Versi | Tanggal | Perubahan | Author |
|-------|---------|-----------|--------|
| v1.0 | 24 Jun 2026 | Dokumen awal | PRD Specialist |

---

## 📋 Task Breakdown untuk Tim

### Backend (Bayu) — 0.5 SP
Modifikasi `GuruController@destroy`:

```php
public function destroy(Guru $guru)
{
    $user = $guru->user;

    DB::transaction(function () use ($guru, $user) {
        // Hapus user dulu → DB cascade akan menghapus guru otomatis
        if ($user) {
            $user->delete();
        } else {
            // Fallback: jika user tidak ada, hapus guru langsung
            $guru->delete();
        }
    });

    return redirect()->route('admin.guru.index')
        ->with('success', 'Guru dan akun user berhasil dihapus.');
}
```

**Catatan:** Pastikan import `DB` sudah ada di file. Karena `guru.user_id` memiliki `cascadeOnDelete`, menghapus User akan otomatis menghapus record Guru di database.

### Testing (Farhan) — 0.5 SP
| Test Case | Expected |
|-----------|----------|
| Hapus guru dengan user valid | ✅ Guru + User terhapus |
| Cek DB setelah hapus | ✅ Tidak ada record di tabel `guru` dan `users` |
| Coba login dengan user yang sudah dihapus | ❌ Gagal login |
| Hapus guru tanpa user (jika ada data inconsistent) | ✅ Tetap berhasil, hanya guru yang dihapus |
| Cek activity_logs | ✅ User's activity_logs ikut terhapus (via booted event) |

### Dependencies
- Testing tergantung Backend selesai
- Tidak ada perubahan Frontend/DB

---

## ⚠️ Catatan Penting

- Karena `guru.user_id` sudah memiliki **`cascadeOnDelete`** di level database, menghapus User akan **otomatis menghapus record Guru** — tidak perlu manual `$guru->delete()`
- `User@booted()` sudah menangani penghapusan `activity_logs` saat User dihapus
- Tidak perlu migration baru
