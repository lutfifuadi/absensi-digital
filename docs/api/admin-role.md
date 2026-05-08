# API Dokumentasi Admin Role

## Endpoint

### GET /admin/role
- Deskripsi: Menampilkan halaman manajemen role.
- Query Parameters:
  - `search` (string, optional): pencarian berdasarkan nama role atau deskripsi.
  - `per_page` (integer, optional): jumlah item per halaman. Pilihan: `10`, `15`, `25`, `50`, `100`.
- Response:
  - HTML full page bila permintaan biasa.
  - JSON bila header `Accept: application/json` dikirim, dengan struktur:
    - `html`: potongan tabel hasil pencarian.

### POST /admin/role
- Deskripsi: Menambahkan role baru.
- Body Parameters:
  - `name` (string, required): nama role, unique.
  - `description` (string, required): deskripsi role.
- Response: Redirect ke halaman `/admin/role` dengan flash message.

### PUT /admin/role/{role}
- Deskripsi: Memperbarui role yang sudah ada.
- Body Parameters:
  - `name` (string, required): nama role, unique kecuali untuk role yang sedang diupdate.
  - `description` (string, required): deskripsi role.
- Response: Redirect ke halaman `/admin/role` dengan flash message.

### DELETE /admin/role/{role}
- Deskripsi: Menghapus role dari database.
- Response: Redirect ke halaman `/admin/role` dengan flash message.
