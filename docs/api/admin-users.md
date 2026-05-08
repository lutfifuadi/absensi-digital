# API Dokumentasi Admin Users

## Endpoint

### GET /admin/users
- Deskripsi: Menampilkan halaman manajemen pengguna.
- Query Parameters:
  - `search` (string, optional): pencarian nama, email, atau NIP.
  - `role` (string, optional): filter berdasarkan role.
  - `sort` (string, optional): nama kolom yang akan disortir (contoh: `name`, `email`, `nip`). Default: `created_at`.
  - `direction` (enum: `asc`, `desc`, optional): arah sortir. Default: `desc`.
- Response:
  - HTML full page bila permintaan normal.
  - JSON bila header `Accept: application/json` dikirim, dengan struktur:
    - `html`: potongan tabel hasil pencarian.
    - `total`: jumlah total hasil.
    - `firstItem`: nomor item pertama pada halaman.
    - `lastItem`: nomor item terakhir pada halaman.

### POST /admin/users
- Deskripsi: Menambahkan pengguna baru dari halaman admin.
- Body Parameters:
  - `name` (string, required): Sanitasi input terhadap tag HTML.
  - `email` (string, required): Validasi unique (case-insensitive).
  - `nip` (string, required): Validasi unique (case-insensitive) dan numerik.
  - `role` (string, required)
  - `phone` (string, optional): Sanitasi karakter non-numerik.
  - `position` (string, optional)
  - `is_active` (boolean, optional)
- Response: Redirect ke halaman `/admin/users` dengan flash message.

### PUT /admin/users/{user}
- Deskripsi: Memperbarui data pengguna yang sudah ada.
- Body Parameters:
  - `name` (string, required): Sanitasi input terhadap tag HTML.
  - `email` (string, required): Validasi unique kecuali ID yang sedang diupdate.
  - `nip` (string, required): Validasi unique kecuali ID yang sedang diupdate.
  - `role` (string, required)
  - `phone` (string, optional): Sanitasi karakter non-numerik.
  - `position` (string, optional)
  - `is_active` (boolean, optional)
- Response: Redirect ke halaman `/admin/users` dengan flash message.

### DELETE /admin/users/{user}
- Deskripsi: Menghapus pengguna jika bukan pengguna dengan role sistem.
- Response: Redirect ke halaman `/admin/users` dengan flash message.
