# API Dokumentasi: Pencarian Surat Masuk

## Endpoint

- `GET /transaction/incoming`

## Tujuan

Menampilkan daftar surat masuk dengan kemampuan pencarian otomatis berdasarkan:
- nomor surat (reference_number / agenda_number)
- pengirim (`from`)
- perihal (`description`)

## Parameter

- `search` (opsional): string pencarian. Dapat berisi nomor surat, bagian awal pengirim, atau perihal.

## Behavior

- Jika `search` diisi, sistem akan mencari surat masuk yang cocok dengan nilai tersebut.
- Pencarian dilakukan dengan `LIKE` di kolom `reference_number`, `agenda_number`, `from`, `description`, dan `to`.
- Hasil pencarian dikembalikan dalam bentuk daftar surat pada halaman `/transaction/incoming`.
- Pada UI, input pencarian akan memicu permintaan AJAX otomatis saat mengetik, sehingga hasil diperbarui tanpa reload halaman.

## Contoh Request

```
GET /transaction/incoming?search=Permintaan%20Dana
```

## Contoh Response

Response berupa halaman HTML yang menampilkan daftar surat masuk sesuai hasil pencarian.
