<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rekap Absensi {{ $ekskul->nama }} - {{ $namaBulan }} {{ $tahun }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 11px; color: #1F2937; padding: 20px; }

        .header { text-align: center; margin-bottom: 16px; border-bottom: 2px solid #374151; padding-bottom: 10px; }
        .header h2 { font-size: 16px; margin-bottom: 4px; color: #111827; }
        .header h3 { font-size: 13px; color: #4B5563; margin-bottom: 2px; }
        .header p { font-size: 11px; color: #6B7280; }

        .summary { margin-bottom: 16px; }
        .summary table { width: 100%; border-collapse: collapse; }
        .summary td { padding: 6px 12px; text-align: center; font-size: 10px; border: 1px solid #D1D5DB; }
        .summary td strong { font-size: 13px; display: block; }

        .main-table { width: 100%; border-collapse: collapse; }
        .main-table thead th { background-color: #374151; color: #FFFFFF; font-size: 9px; text-transform: uppercase; letter-spacing: 0.5px; padding: 8px 6px; border: 1px solid #4B5563; text-align: center; }
        .main-table tbody td { padding: 6px 8px; border: 1px solid #D1D5DB; font-size: 10px; }
        .main-table tbody tr:nth-child(even) { background-color: #F9FAFB; }
        .main-table .text-center { text-align: center; }
        .main-table .text-right { text-align: right; }
        .main-table .number { text-align: center; width: 30px; color: #6B7280; }
    </style>
</head>
<body>
    <div class="header">
        <h2>REKAP ABSENSI EKSTRAKURIKULER</h2>
        <h3>{{ $ekskul->nama }}</h3>
        <p>Periode: {{ $namaBulan }} {{ $tahun }}</p>
    </div>

    <div class="summary">
        <table>
            <tr>
                <td style="background:#D1FAE5;">
                    <strong>{{ $rekap['total']['hadir'] ?? 0 }}</strong>
                    Hadir
                </td>
                <td style="background:#DBEAFE;">
                    <strong>{{ $rekap['total']['izin'] ?? 0 }}</strong>
                    Izin
                </td>
                <td style="background:#FEF3C7;">
                    <strong>{{ $rekap['total']['sakit'] ?? 0 }}</strong>
                    Sakit
                </td>
                <td style="background:#FEE2E2;">
                    <strong>{{ $rekap['total']['alpha'] ?? 0 }}</strong>
                    Alpha
                </td>
                <td style="background:#EDE9FE;">
                    <strong>{{ $rekap['total']['terlambat'] ?? 0 }}</strong>
                    Terlambat
                </td>
                <td style="background:#F3F4F6;">
                    <strong>{{ $rekap['total']['total'] ?? 0 }}</strong>
                    Total
                </td>
            </tr>
        </table>
    </div>

    <table class="main-table">
        <thead>
            <tr>
                <th class="number">No</th>
                <th>NIS</th>
                <th>Nama Siswa</th>
                <th>Kelas</th>
                <th>Hadir</th>
                <th>Izin</th>
                <th>Sakit</th>
                <th>Alpha</th>
                <th>Terlambat</th>
                <th>Persentase</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rekapPerSiswa as $r)
            <tr>
                <td class="number">{{ $loop->iteration }}</td>
                <td>{{ $r->siswa->nis ?? '-' }}</td>
                <td>{{ $r->siswa->nama_lengkap ?? '-' }}</td>
                <td>{{ $r->siswa->kelas->nama ?? '-' }}</td>
                <td class="text-center">{{ $r->hadir }}</td>
                <td class="text-center">{{ $r->izin }}</td>
                <td class="text-center">{{ $r->sakit }}</td>
                <td class="text-center">{{ $r->alpha }}</td>
                <td class="text-center">{{ $r->terlambat }}</td>
                <td class="text-center">{{ $r->persentase }}%</td>
            </tr>
            @empty
            <tr>
                <td colspan="10" class="text-center" style="padding:24px;color:#9CA3AF;">
                    Tidak ada data absensi untuk periode ini.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
