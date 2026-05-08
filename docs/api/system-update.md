# API Dokumentasi — System Update

Dokumentasi ini menjelaskan endpoint yang digunakan untuk fitur pengecekan dan eksekusi pembaruan sistem.

## 1. Periksa Pembaruan
Digunakan untuk mengecek apakah ada versi terbaru di server pusat.

- **URL:** `/admin/update/check`
- **Method:** `POST`
- **Auth Required:** Ya (Super Admin)
- **Headers:** 
  - `X-CSRF-TOKEN`: Laravel CSRF Token
  - `Accept`: `application/json`

### Response Berhasil
```json
{
    "success": true,
    "update_available": true,
    "data": {
        "status": true,
        "update_available": true,
        "latest_version": "1.0.1",
        "changelog": "- Perbaikan bug pada scan QR\n- Penambahan fitur sinkronisasi update...",
        "release_date": "2026-05-05"
    }
}
```

### Response Gagal
```json
{
    "success": false,
    "message": "Gagal terhubung ke server update."
}
```

---

## 2. Jalankan Pembaruan
Digunakan untuk memulai proses download dan instalasi pembaruan.

- **URL:** `/admin/update/run`
- **Method:** `POST`
- **Auth Required:** Ya (Super Admin)
- **Headers:** 
  - `X-CSRF-TOKEN`: Laravel CSRF Token
  - `Accept`: `application/json`

### Response Berhasil
```json
{
    "success": true,
    "message": "Sistem berhasil diperbarui ke versi terbaru."
}
```

### Response Gagal
```json
{
    "success": false,
    "message": "Update failed: [Error Message]"
}
```

---

## Alur Kerja (Workflow)
1. User masuk ke Dashboard.
2. `contentNavbarLayout` mengecek key `update_available_version` di tabel `pengaturan`.
3. Jika ada, tampilkan `Alert` di atas konten.
4. User klik link di Alert -> Diarahkan ke `/admin/update`.
5. User klik "Mulai Perbarui" -> AJAX request ke `/admin/update/run`.
6. `UpdateService` memproses update, mengubah `.env`, menjalankan migrasi, dan membersihkan cache.
7. Redirect kembali ke halaman update dengan status berhasil.
