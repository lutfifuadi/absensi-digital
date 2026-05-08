# API Dokumentasi — Admin Guru & Wali Kelas

## GET /admin/guru

Menampilkan daftar guru dengan fitur pencarian, filter, dan pagination.

### Query Parameters

| Parameter   | Tipe    | Wajib | Default | Keterangan                                    |
|-------------|---------|-------|---------|-----------------------------------------------|
| `search`    | string  | Tidak | —       | Cari berdasarkan nama, email, atau NIP/NUPTK  |
| `status`    | string  | Tidak | —       | Filter status: `active` atau `inactive`       |
| `subject_id`| integer | Tidak | —       | Filter berdasarkan ID mata pelajaran          |
| `per_page`  | integer | Tidak | 15      | Jumlah data per halaman: 10, 15, 25, 50, 100  |
| `page`      | integer | Tidak | 1       | Halaman yang ditampilkan (dari pagination)    |

### Response HTML (default)

Mengembalikan halaman Blade `admin-guru` dengan data tabel.

### Response JSON (wantsJson / AJAX)

Dipicu saat request menyertakan header `Accept: application/json` dan `X-Requested-With: XMLHttpRequest`.

```json
{
  "html": "<div class=\"table-responsive\">...</div>",
  "total": 25,
  "firstItem": 1,
  "lastItem": 15
}
```

### Contoh Request AJAX

```javascript
fetch('/admin/guru?search=budi&status=active&per_page=25', {
  headers: {
    'Accept': 'application/json',
    'X-Requested-With': 'XMLHttpRequest'
  }
})
```

---

## GET /admin/wali-kelas

Menampilkan daftar wali kelas dengan fitur pencarian, filter, dan pagination.

### Query Parameters

| Parameter      | Tipe    | Wajib | Default | Keterangan                                     |
|----------------|---------|-------|---------|------------------------------------------------|
| `search`       | string  | Tidak | —       | Cari berdasarkan nama, email, atau NIP/NUPTK   |
| `status`       | string  | Tidak | —       | Filter status: `active` atau `inactive`        |
| `classroom_id` | integer | Tidak | —       | Filter berdasarkan ID kelas yang dibimbing     |
| `per_page`     | integer | Tidak | 15      | Jumlah data per halaman: 10, 15, 25, 50, 100   |
| `page`         | integer | Tidak | 1       | Halaman yang ditampilkan (dari pagination)     |

### Response HTML (default)

Mengembalikan halaman Blade `admin-wali-kelas` dengan data tabel.

### Response JSON (wantsJson / AJAX)

```json
{
  "html": "<div class=\"table-responsive\">...</div>",
  "total": 10,
  "firstItem": 1,
  "lastItem": 10
}
```

---

*Dicatat oleh Agen Eka — 27 April 2026*
