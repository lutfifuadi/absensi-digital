# API — License Verify

**Base URL**: `https://saas-presensi.lutfifuadi.my.id`

---

## POST `/api/license/verify`

Verifikasi license key yang dimasukkan oleh installer klien saat proses instalasi (Step 2).

### Rate Limit
`30 request per menit per IP`

### Request

**Headers**
```
Content-Type: application/x-www-form-urlencoded
Accept: application/json
```

**Body Parameters**

| Parameter     | Type   | Required | Keterangan |
|---------------|--------|----------|------------|
| `license_key` | string | ✅ Ya    | License key format `PRE-XXXX-XXXX-XXXX-XXXX`. Hanya boleh: huruf, angka, tanda `-` dan `_`. Max 100 karakter. |
| `domain`      | string | ✅ Ya    | Domain instalasi (tanpa `https://`, tanpa `/` di akhir). Max 255 karakter. |
| `school_name` | string | ❌ Opsional | Nama sekolah. Akan disimpan di catatan jika belum ada. |

### Response

#### ✅ Sukses `200 OK`
```json
{
  "success": true,
  "message": "Lisensi valid.",
  "school_name": "SMA Negeri 1 Bandung",
  "expires_at": null
}
```

| Field        | Keterangan |
|--------------|------------|
| `success`    | `true` jika lisensi valid |
| `message`    | Pesan hasil verifikasi |
| `school_name`| Nama klien yang terdaftar |
| `expires_at` | Tanggal kadaluarsa (`YYYY-MM-DD`) atau `null` jika seumur hidup |

#### ❌ License Not Found `404`
```json
{
  "success": false,
  "message": "License key tidak ditemukan."
}
```

#### ❌ Payment Not Confirmed `403`
```json
{
  "success": false,
  "message": "Pembayaran belum dikonfirmasi untuk lisensi ini."
}
```

#### ❌ License Revoked `403`
```json
{
  "success": false,
  "message": "Lisensi ini telah dicabut. Hubungi penyedia layanan."
}
```

#### ❌ License Expired `403`
```json
{
  "success": false,
  "message": "Lisensi sudah kadaluarsa. Silakan perpanjang."
}
```

#### ❌ Domain Mismatch `422`
```json
{
  "success": false,
  "message": "License key tidak valid untuk domain: absensi.sekolah.sch.id"
}
```

#### ❌ Validation Error `422`
```json
{
  "message": "The license key field format is invalid.",
  "errors": {
    "license_key": ["The license key field format is invalid."]
  }
}
```

#### ❌ Too Many Requests `429`
```json
{
  "message": "Too Many Attempts."
}
```

---

## Alur Integrasi dengan Installer

Endpoint ini dipanggil dari `InstallerController::step2Submit()` dengan:

```php
$response = Http::asForm()->timeout(30)->post('https://saas-presensi.lutfifuadi.my.id/api/license/verify', [
    'license_key' => $license,
    'domain'      => $domain,
]);
```

Logika installer:
1. Jika `success: true` → ekstrak `school_name` dari response, simpan ke session `install_school_name` dan `.env SCHOOL_NAME`, lanjut ke Step 3
2. `school_name` yang tersimpan di session akan ditampilkan sebagai read-only di Step 2 (jika user kembali ke halaman tersebut) dan otomatis mengisi field "Nama Sekolah" di Step 4 (Profil Sekolah)
3. Jika `success: false` → tampilkan pesan error, minta ulang license key

---

## Catatan

- **Domain matching**: Huruf besar/kecil tidak berpengaruh. Scheme `https://` atau `http://` diabaikan.
- **Pendaftaran domain pertama kali**: Jika domain pada license key masih kosong (baru pertama kali aktivasi), domain dari request akan otomatis didaftarkan.
- **DEV-MASTER-KEY**: License key `DEV-MASTER-KEY` adalah bypass khusus development — selalu dianggap valid (tidak memanggil API ini; dihandle di installer sendiri).
