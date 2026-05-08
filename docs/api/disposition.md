# Disposisi Surat

## Endpoint
- `POST /transaction/{letter}/disposition`
- `PUT /transaction/{letter}/disposition/{disposition}`

## Payload
- `due_date` (string, required): tanggal tenggat disposisi dalam format `YYYY-MM-DD`.
- `letter_status` (integer, required): `id` status disposisi dari tabel `letter_statuses`.
- `note` (string, optional): catatan tambahan.
- `options[]` (array, optional): daftar pilihan arahan disposisi, misalnya:
  - `Mohon dikaji/dipelajari`
  - `Mohon dibantu/dilayani`
  - `Mohon diikuti/partisipasi`
  - `Mohon dikoordinasikan`
  - `Mohon diarsipkan`
- `custom_content` (string, optional): keterangan tambahan di luar pilihan arahan.
- `recipients[]` (array, optional): daftar penerima disposisi, misalnya:
  - `Wakamad`
  - `Koordinator BP/BK`
  - `Kepala Tata Usaha`
  - `Kepala Perpustakaan`
- `other_recipient` (string, optional): nama penerima tambahan jika tidak tersedia di pilihan.

## Data yang disimpan
- `options`: disimpan sebagai JSON di kolom `options`.
- `recipients`: disimpan sebagai JSON di kolom `recipients`.
- `to`: disimpan sebagai daftar penerima dalam format teks, dihasilkan dari `recipients[]` dan `other_recipient`.
- `content`: menyimpan `custom_content` atau teks disposisi `content` lama apabila tidak menggunakan opsi baru.
