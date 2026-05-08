# API Front News

## Endpoint

- Method: `GET`
- URL: `/api/front-news`

## Deskripsi

Mengembalikan daftar berita dan pengumuman yang ditampilkan pada homepage website resmi MAN 1 Kota Bandung.

## Route Front-End

- Method: `GET`
- URL: `/news/{slug}`
- Deskripsi: Menampilkan halaman detail berita saat judul diklik dari halaman depan.

## Response

```json
{
  "data": [
    {
      "id": 1,
      "title": "Selamat Datang di Website Resmi MAN 1 Kota Bandung",
      "excerpt": "Informasi kegiatan sekolah, prestasi siswa, dan pengumuman penting tersedia di halaman ini.",
      "published_at": "2026-04-27",
      "slug": "selamat-datang-di-website-resmi-man-1-kota-bandung"
    }
  ]
}
```
