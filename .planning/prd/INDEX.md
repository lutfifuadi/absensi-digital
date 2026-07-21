# PRD Index

> Daftar semua Product Requirements Document dalam project Aplikasi Presensi.
> Terakhir diperbarui: 2026-07-21

## Statistik
- Total PRD: 2
- Approved: 0
- In Review: 0
- Draft: 0
- Implemented: 2

## Daftar PRD

| ID | Nama Fitur | Status | Versi | Prioritas | RICE | Risk | Quality | Tanggal |
|----|------------|--------|-------|-----------|------|------|---------|---------|
| PRD-001 | Optimasi Guru Multi-Mapel & Fitur Wali Kelas | Implemented | 1.0 | High | 360 | Medium | 100/100 | 2026-07-21 |
| PRD-002 | Kegiatan Multi-Day & Live Board Absensi Kegiatan + Scanner | Implemented | 1.0 | High | 2000 | Low | 100/100 | 2026-07-21 |

## Dependency Map

```text
PRD-001 (Optimasi Guru Multi-Mapel & Fitur Wali Kelas)
  ├── Bergantung pada: Data tabel `mapels` sudah terisi
  ├── Bergantung pada: Roles & permissions sudah jalan
  ├── Bergantung pada: Relasi wali_kelas_id di tabel kelas
  └── Bergantung pada: Session tahun ajaran aktif

PRD-002 (Kegiatan Multi-Day & Live Board Absensi Kegiatan + Scanner)
  ├── Bergantung pada: Model & Migrasi `kegiatan` dan `absensi_kegiatan` sudah ada
```

## Notes
- PRD ini mencakup 4 fitur yang saling terkait: Guru Multi-Mapel, Sinkronisasi Mapel Assignment, Rekap Belum Absen, dan Absensi Manual per Murid
- Estimasi total: ~33 jam kerja (4-5 hari)
- Urutan eksekusi: F1 → F2 → F3 → F4 (berurutan karena ada ketergantungan data)
