<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rekap Absensi {{ $ekskul->nama }} - {{ $namaBulan }} {{ $tahun }}</title>
    <style>
        @page { margin: 12mm 15mm 15mm 15mm; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 9pt; color: #1F2937; line-height: 1.3; }

        /* KOP SURAT RESMI */
        .kop-container { width: 100%; margin-bottom: 8px; }
        .kop-table { width: 100%; border-collapse: collapse; border: none; }
        .kop-table td { border: none; padding: 0; vertical-align: middle; }
        .kop-logo { width: 65px; text-align: center; }
        .kop-logo img { max-width: 60px; max-height: 60px; object-fit: contain; }
        .kop-text { text-align: center; padding-left: 10px; padding-right: 65px; }
        .kop-text .nama-lembaga { font-size: 11pt; font-weight: bold; text-transform: uppercase; letter-spacing: 0.5px; color: #0f172a; margin: 0; }
        .kop-text .nama-sekolah { font-size: 14pt; font-weight: 900; text-transform: uppercase; color: #1e293b; margin: 2px 0; }
        .kop-text .alamat-sekolah { font-size: 8pt; color: #475569; margin: 0; }

        .kop-divider { border: 0; border-top: 2px solid #0f172a; border-bottom: 1px solid #0f172a; height: 2px; margin-top: 5px; margin-bottom: 12px; }

        .header { text-align: center; margin-bottom: 14px; }
        .header h3 { font-size: 12pt; font-weight: bold; text-transform: uppercase; color: #111827; margin-bottom: 2px; }
        .header p { font-size: 9pt; color: #4B5563; font-weight: 600; }

        .summary { margin-bottom: 14px; }
        .summary table { width: 100%; border-collapse: collapse; }
        .summary td { padding: 6px 10px; text-align: center; font-size: 8.5pt; border: 1px solid #CBD5E1; }
        .summary td strong { font-size: 11pt; display: block; margin-bottom: 2px; }

        .main-table { width: 100%; border-collapse: collapse; }
        .main-table thead th { background-color: #1E293B; color: #FFFFFF; font-size: 8pt; text-transform: uppercase; letter-spacing: 0.5px; padding: 6px; border: 1px solid #475569; text-align: center; }
        .main-table tbody td { padding: 5px 8px; border: 1px solid #CBD5E1; font-size: 8.5pt; }
        .main-table tbody tr:nth-child(even) { background-color: #F8FAFC; }
        .main-table .text-center { text-align: center; }
        .main-table .number { text-align: center; width: 30px; color: #64748B; }

        /* TTD */
        .ttd-box { margin-top: 25px; width: 100%; page-break-inside: avoid; }
        .ttd-table { width: 100%; border-collapse: collapse; border: none; }
        .ttd-table td { border: none; width: 50%; text-align: center; vertical-align: top; font-size: 8.5pt; }
    </style>
</head>
<body>

    @php
      $logoVal = setting('logo_sekolah') ?: setting('logo_url');
      $logoPath = null;
      if (!empty($logoVal)) {
        if (filter_var($logoVal, FILTER_VALIDATE_URL)) {
          $logoPath = $logoVal;
        } else {
          $logoPath = public_path('uploads/logo/' . $logoVal);
          if (!file_exists($logoPath)) {
            $logoPath = public_path($logoVal);
          }
        }
      }
      $namaSekolah   = setting('nama_sekolah', 'Madrasah Aliyah');
      $alamatSekolah = setting('alamat_sekolah') ?: 'Alamat Sekolah Belum Diatur';
      $kepalaSekolah = setting('kepala_sekolah') ?: '-';
      $nipKepala     = setting('nip_kepala_sekolah') ?: '';
    @endphp

    <!-- KOP SURAT RESMI -->
    <div class="kop-container">
      <table class="kop-table">
        <tr>
          <td class="kop-logo">
            @if(!empty($logoPath) && file_exists($logoPath))
              <img src="{{ $logoPath }}" alt="Logo">
            @else
              <div style="font-weight:bold; font-size:16pt; color:#1e293b;">[LOGO]</div>
            @endif
          </td>
          <td class="kop-text">
            <div class="nama-lembaga">{{ setting('nama_yayasan', 'KEMENTERIAN AGAMA / DINAS PENDIDIKAN') }}</div>
            <div class="nama-sekolah">{{ $namaSekolah }}</div>
            <div class="alamat-sekolah">{{ $alamatSekolah }}</div>
          </td>
        </tr>
      </table>
      <div class="kop-divider"></div>
    </div>

    <div class="header">
        <h3>REKAPITULASI PRESENSI EKSTRAKURIKULER: {{ strtoupper($ekskul->nama) }}</h3>
        <p>PERIODE: {{ strtoupper($namaBulan) }} {{ $tahun }}</p>
    </div>

    <div class="summary">
        <table>
            <tr>
                <td style="background:#D1FAE5; color:#065F46;">
                    <strong>{{ $rekap['total']['hadir'] ?? 0 }}</strong>
                    Hadir
                </td>
                <td style="background:#DBEAFE; color:#1E40AF;">
                    <strong>{{ $rekap['total']['izin'] ?? 0 }}</strong>
                    Izin
                </td>
                <td style="background:#FEF3C7; color:#92400E;">
                    <strong>{{ $rekap['total']['sakit'] ?? 0 }}</strong>
                    Sakit
                </td>
                <td style="background:#FEE2E2; color:#991B1B;">
                    <strong>{{ $rekap['total']['alpha'] ?? 0 }}</strong>
                    Alpha
                </td>
                <td style="background:#F3E8FF; color:#6B21A8;">
                    <strong>{{ $rekap['total']['terlambat'] ?? 0 }}</strong>
                    Terlambat
                </td>
                <td style="background:#F1F5F9; color:#334155;">
                    <strong>{{ $rekap['total']['total'] ?? 0 }}</strong>
                    Total Record
                </td>
            </tr>
        </table>
    </div>

    <table class="main-table">
        <thead>
            <tr>
                <th class="number">No</th>
                <th>NIS</th>
                <th style="text-align:left; padding-left:8px;">Nama Siswa</th>
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
                <td class="text-center">{{ $r->siswa->nis ?? '-' }}</td>
                <td style="text-align:left; padding-left:8px; font-weight:600;">{{ $r->siswa->nama_lengkap ?? '-' }}</td>
                <td class="text-center">{{ $r->siswa->kelas->nama ?? '-' }}</td>
                <td class="text-center" style="background:#D1FAE5; color:#065F46; font-weight:bold;">{{ $r->hadir }}</td>
                <td class="text-center" style="background:#DBEAFE; color:#1E40AF;">{{ $r->izin }}</td>
                <td class="text-center" style="background:#FEF3C7; color:#92400E;">{{ $r->sakit }}</td>
                <td class="text-center" style="background:#FEE2E2; color:#991B1B; font-weight:bold;">{{ $r->alpha }}</td>
                <td class="text-center" style="background:#F3E8FF; color:#6B21A8;">{{ $r->terlambat }}</td>
                <td class="text-center" style="font-weight:bold;">{{ $r->persentase }}%</td>
            </tr>
            @empty
            <tr>
                <td colspan="10" class="text-center" style="padding:20px; color:#94A3B8;">
                    Tidak ada data absensi untuk periode ini.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <!-- BLOK TANDA TANGAN -->
    <div class="ttd-box">
      <table class="ttd-table">
        <tr>
          <td>
            Pembina Ekstrakurikuler,<br><br><br><br><br>
            <strong>{{ $ekskul->pembina ? $ekskul->pembina->nama_lengkap : '_________________________' }}</strong>
          </td>
          <td>
            {{ setting('kota_sekolah', 'Kabupaten') }}, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}<br>
            Kepala Sekolah,<br><br><br><br><br>
            <strong>{{ $kepalaSekolah ?: '_________________________' }}</strong><br>
            @if ($nipKepala)
              NIP. {{ $nipKepala }}
            @endif
          </td>
        </tr>
      </table>
    </div>
</body>
</html>
