# PRD Index

> Daftar semua Product Requirements Document dalam project ini.
> Terakhir diperbarui: 2026-07-23

## Statistik
- **Total PRD**: 16
- **Approved**: 4
- **In Review**: 2
- **Draft**: 7
- **Implemented**: 3

## Daftar PRD

| ID | Nama Fitur | Status | Versi | Prioritas | RICE | Risk | Quality | Tanggal |
|----|------------|--------|-------|-----------|------|------|---------|---------|
| PRD-001 | Batasan Perizinan (Limit Kuota Sakit/Izin) | In Review | 1.0 | High | 320 | Medium | 95/100 | 2026-07-20 |
| PRD-001 | Guide-Driven AI Knowledge System | Draft | 1.0 | High | 533 | Medium | 90/100 | 2026-07-18 |
| PRD-001 | Optimasi Guru Multi-Mapel & Fitur Wali Kelas | Implemented | 1.0 | High | 360 | Medium | N/A | 2026-07-21 |
| PRD-002 | Kegiatan Multi-Day & Live Board Absensi Kegiatan + Scanner | Implemented | 1.0 | High | 2000 | N/A | N/A | 2026-07-21 |
| PRD-002 | Fitur 'Login As' (Impersonation) Siswa | Approved | 1.0 | High | 200 | Medium | N/A | 2026-07-19 |
| PRD-003 | Jam Mulai Absensi | Draft | 1.0 | High | 480 | Low | 96/100 | 2026-07-22 |
| PRD-003 | Kustomisasi Warna Tema UI | Draft | 1.0 | Medium | 168 | Medium | 95/100 | 2026-07-19 |
| PRD-004 | UI/UX Refresh Dashboard Siswa | Implemented | 1.0 | High | 280 | Medium | 97/100 | 2026-07-19 |
| PRD-004 | Role-Based Vertical Menu (Pemurnian Tampilan Menu per Role) | Draft | 1.0 | Critical | 500 | Medium | 92/100 | 2026-07-22 |
| PRD-006 | Export & Import Settingan Template ID Card | Draft | 1.0 | Medium | 180 | Low | 100/100 | 2026-07-19 |
| PRD-007 | Penambahan Menu Wali Kelas di Panel Admin | In Review | 1.0 | High | 200 | Low | 100/100 | 2026-07-20 |
| PRD-008 | Point Pelanggaran Siswa | Draft | 1.0 | High | 480 | Medium | N/A | 2026-07-21 |
| PRD-009 | Redesign Master Data (Dashboard-First & Glassmorphism Dark Theme) | Draft | 1.0 | High | 450 | Medium | 100/100 | 2026-07-23 |
| PRD-010 | Standarisasi Kategori Pengaduan menggunakan Select2 | Approved | 1.0 | High | 320 | Medium | N/A | 2026-07-23 |
| PRD-011 | Proteksi Absensi Siswa pada Hari Libur | Approved | 1.0 | High | 3333 | Medium | 100/100 | 2026-07-23 |
| PRD-012 | Modal Form Pengaduan pada Portal Orang Tua | Approved | 1.0 | Critical | 2000 | Medium | 100/100 | 2026-07-23 |

## Dependency Map

```text
PRD-003 (Kustomisasi Warna Tema UI) ──> PRD-004 (UI/UX Refresh Dashboard Siswa)
PRD-004 (Role-Based Vertical Menu) ──> PRD-009 (Redesign Master Data)
```

### Detail Keterkaitan:
- **PRD-001 (Batasan Perizinan)**: Membutuhkan modul manajemen user (PRD-000) dan kalender akademik/hari libur (PRD-002).
- **PRD-001 (Guide-Driven AI Knowledge System)**: Bergantung pada modul Guide System, Pengaturan, FloatingChat & AiChat.
- **PRD-001 (Optimasi Guru Multi-Mapel)**: Bergantung pada database mapels, roles & permissions, wali kelas.
- **PRD-002 (Kegiatan Multi-Day & Live Board)**: Bergantung pada database dan basic event management.
- **PRD-002 (Login As Siswa)**: Bergantung pada Multi-Guard Auth & Role/Permission.
- **PRD-003 (Jam Mulai Absensi)**: Bergantung pada Pengaturan, AbsensiMandiriController, PublicQrScanController.
- **PRD-003 (Kustomisasi Warna Tema UI)**: Bergantung pada Pengaturan, layoutMaster, `@simonwep/pickr`.
- **PRD-004 (UI/UX Refresh Dashboard Siswa)**: Membutuhkan PRD-003 (Kustomisasi Warna Tema UI) untuk implementasi custom variables.
- **PRD-004 (Role-Based Vertical Menu)**: Independen, mempengaruhi tampilan navigasi global.
- **PRD-006 (Export & Import Settingan ID Card)**: Bergantung pada model IdCardTemplate, Storage, dan GoogleDriveService.
- **PRD-007 (Penambahan Menu Wali Kelas)**: Independen, memodifikasi `vertical_admin.json`.
- **PRD-008 (Point Pelanggaran Siswa)**: Bergantung pada data Siswa, Kelas, Tahun Akademik, Users, dan relasi siswa_ortu.
- **PRD-009 (Redesign Master Data)**: Membutuhkan PRD-004 (Role-Based Vertical Menu) untuk integrasi sidebar menu.
- **PRD-010 (Standarisasi Kategori Pengaduan)**: Bergantung pada jQuery dan Select2.
- **PRD-011 (Proteksi Absensi Siswa pada Hari Libur)**: Bergantung pada Holiday model.
- **PRD-012 (Modal Form Pengaduan pada Portal Orang Tua)**: Bergantung pada SweetAlert2, Select2, dan jQuery/AJAX.

## Notes
- Terdapat beberapa nomor PRD yang sama (e.g., PRD-001, PRD-002, PRD-003, PRD-004) karena penulisan oleh penulis/spesialis yang berbeda atau pembagian fitur pada iterasi terpisah. Pemetaan pada tabel di atas menggunakan file aslinya secara utuh untuk konsistensi pelacakan.
