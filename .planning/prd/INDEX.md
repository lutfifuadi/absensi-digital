# PRD Index

> Daftar semua Product Requirements Document dalam project ini.
> Terakhir diperbarui: 2026-08-03

## Statistik
- Total PRD: 8
- Approved: 1
- In Review: 0
- Draft: 6
- Implemented: 1

## Daftar PRD

| ID | Nama Fitur | Status | Versi | Prioritas | RICE | Risk | Quality | Tanggal |
|----|------------|--------|-------|-----------|------|------|---------|---------|
| PRD-003 | Redesign Heading Dashboard Analitik Kehadiran Guru | Draft | 1.0 | High | 102 | Medium | 88/100 | 2026-08-01 |
| PRD-004 | Perapian & Standarisasi Feature Toggle + Penambahan Toggle Fitur Baru | Draft | 1.0 | Critical | 1600 | High | 90/100 | 2026-08-01 |
| PRD-005 | Jadwal Kegiatan Berulang (Recurring Activity Schedule) | Implemented | 1.0 | High | 180 | Medium | 92/100 | 2026-08-01 |
| PRD-006 | Penyatuan Konfigurasi Jadwal Berulang pada Halaman Edit Kegiatan | Approved | 1.0 | High | 667 | Medium | 100/100 | 2026-08-01 |
| PRD-007 | Fitur Izin Pulang Cepat (Early Departure System) Siswa & Guru | Draft | 1.0 | High | 240 | Medium | 95/100 | 2026-08-02 |
| PRD-008 | Absensi Cepat Publik (Public Quick Attendance) | Draft | 1.0 | High | 300 | Medium | 92/100 | 2026-08-03 |
| PRD-009 | Pelacakan & Autentikasi Individual Guru Piket (Public Scanner & Rekap Saya) | In Review | 1.0 | High | 350 | Medium | 95/100 | 2026-08-06 |

## Dependency Map

```text
PRD-004 (Feature Toggle) ← toggle fitur_absensi_kegiatan mengontrol modul ini
  |
  +-- PRD-005 (Jadwal Kegiatan Berulang)
        |
        +-- PRD-006 (Penyatuan Konfigurasi Jadwal Berulang pada Halaman Edit Kegiatan)
```