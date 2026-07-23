# PRD Index

> Daftar semua Product Requirements Document dalam project Aplikasi Presensi.
> Terakhir diperbarui: 2026-07-23

## Statistik
- Total PRD: 5
- Approved: 0
- In Review: 0
- Draft: 3
- Implemented: 2

## Daftar PRD

| ID | Nama Fitur | Status | Versi | Prioritas | RICE | Risk | Quality | Tanggal |
|----|------------|--------|-------|-----------|------|------|---------|---------| 
| PRD-001 | Optimasi Guru Multi-Mapel & Fitur Wali Kelas | Implemented | 1.0 | High | 360 | Medium | 100/100 | 2026-07-21 |
| PRD-002 | Kegiatan Multi-Day & Live Board Absensi Kegiatan + Scanner | Implemented | 1.0 | High | 2000 | Low | 100/100 | 2026-07-21 |
| PRD-003 | Jam Mulai Absensi | Draft | 1.0 | High | 480 | Low | 96/100 | 2026-07-22 |
| PRD-004 | Role-Based Vertical Menu | Draft | 1.0 | Critical | 500 | Medium | 100/100 | 2026-07-22 |
| PRD-009 | Redesign Master Data (Dashboard-First & Glassmorphism Dark Theme) | Draft | 1.0 | High | 450 | Medium | 100/100 | 2026-07-23 |

## Dependency Map

```text
PRD-001 (Optimasi Guru Multi-Mapel & Fitur Wali Kelas)
  ├── Bergantung pada: Data tabel `mapels` sudah terisi
  ├── Bergantung pada: Roles & permissions sudah jalan
  ├── Bergantung pada: Relasi wali_kelas_id di tabel kelas
  └── Bergantung pada: Session tahun ajaran aktif

PRD-002 (Kegiatan Multi-Day & Live Board Absensi Kegiatan + Scanner)
  ├── Bergantung pada: Model & Migrasi `kegiatan` dan `absensi_kegiatan` sudah ada

PRD-003 (Jam Mulai Absensi)
  ├── Bergantung pada: Tabel `pengaturan` key-value sudah ada ✅
  ├── Bergantung pada: PengaturanController dengan $defaults & updateOrCreate ✅
  ├── Bergantung pada: AbsensiMandiriController sudah membaca settings ✅
  └── Bergantung pada: PublicQrScanController::getCachedSettings() sudah ada ✅
  → TIDAK bergantung pada PRD-001 atau PRD-002 (independen)

PRD-009 (Redesign Master Data)
  └── Bergantung pada: PRD-004 (Role-Based Vertical Menu) untuk penyesuaian sidebar menu
```

## Notes
- PRD-001: mencakup 4 fitur yang saling terkait: Guru Multi-Mapel, Sinkronisasi Mapel Assignment, Rekap Belum Absen, dan Absensi Manual per Murid. Estimasi total: ~33 jam kerja (4-5 hari). Urutan eksekusi: F1 → F2 → F3 → F4.
- PRD-003: Fitur ringan ~1 hari kerja. Tidak ada migration database. Dapat dikerjakan paralel dengan PRD lain karena independen. File PRD utama: `.prd/jam-mulai-absensi.md`
- PRD-009: Redesign halaman Master Data menjadi dashboard-first control center dengan visual Glassmorphism Dark Theme. Estimasi total: ~18 jam kerja (~2.5 hari). Bergantung pada menu role-based PRD-004.
