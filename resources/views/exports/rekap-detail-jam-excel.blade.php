<table>
  <!-- KOP SURAT -->
  <tr>
    <td colspan="10" style="font-weight:bold; font-size:14pt; text-align:center;">
      {{ strtoupper($namaSekolah) }}
    </td>
  </tr>
  <tr>
    <td colspan="10" style="font-weight:bold; font-size:12pt; text-align:center;">
      LAPORAN RINCIAN JAM PRESENSI SISWA (LOG DETAIL)
    </td>
  </tr>
  <tr>
    <td colspan="10" style="font-weight:bold; font-size:10pt; text-align:center;">
      PERIODE: {{ strtoupper($namaBulan) }} {{ $tahun }} @if($kelas) | KELAS: {{ strtoupper($kelas->nama) }} @endif
    </td>
  </tr>
  <tr></tr>

  <!-- HEADER -->
  <thead>
    <tr>
      <th style="font-weight:bold; background-color:#1E293B; color:#FFFFFF; text-align:center; border:1px solid #475569;">#</th>
      <th style="font-weight:bold; background-color:#1E293B; color:#FFFFFF; text-align:center; border:1px solid #475569;">Tanggal</th>
      <th style="font-weight:bold; background-color:#1E293B; color:#FFFFFF; text-align:center; border:1px solid #475569;">Hari</th>
      <th style="font-weight:bold; background-color:#1E293B; color:#FFFFFF; text-align:center; border:1px solid #475569;">NIS</th>
      <th style="font-weight:bold; background-color:#1E293B; color:#FFFFFF; text-align:left; border:1px solid #475569;">Nama Siswa</th>
      <th style="font-weight:bold; background-color:#1E293B; color:#FFFFFF; text-align:center; border:1px solid #475569;">Kelas</th>
      <th style="font-weight:bold; background-color:#1E293B; color:#FFFFFF; text-align:center; border:1px solid #475569;">Status</th>
      <th style="font-weight:bold; background-color:#1E293B; color:#FFFFFF; text-align:center; border:1px solid #475569;">Jam Masuk</th>
      <th style="font-weight:bold; background-color:#1E293B; color:#FFFFFF; text-align:center; border:1px solid #475569;">Jam Pulang</th>
      <th style="font-weight:bold; background-color:#1E293B; color:#FFFFFF; text-align:center; border:1px solid #475569;">Metode / Keterangan</th>
    </tr>
  </thead>

  <!-- BODY -->
  <tbody>
    @forelse($absensiLogs as $log)
      @php
        $st = strtolower($log->status ?? '');
        $bg = '#FFFFFF';
        $fg = '#000000';
        if ($st === 'hadir') { $bg = '#D1FAE5'; $fg = '#065F46'; }
        elseif ($st === 'sakit') { $bg = '#DBEAFE'; $fg = '#1E40AF'; }
        elseif ($st === 'izin') { $bg = '#FEF3C7'; $fg = '#92400E'; }
        elseif ($st === 'alpha') { $bg = '#FEE2E2'; $fg = '#991B1B'; }
        elseif ($st === 'terlambat') { $bg = '#F3E8FF'; $fg = '#6B21A8'; }
      @endphp
      <tr>
        <td style="text-align:center; border:1px solid #CBD5E1;">{{ $loop->iteration }}</td>
        <td style="text-align:center; border:1px solid #CBD5E1;">{{ $log->tanggal ? $log->tanggal->format('Y-m-d') : '-' }}</td>
        <td style="text-align:center; border:1px solid #CBD5E1;">{{ $log->tanggal ? $log->tanggal->translatedFormat('l') : '-' }}</td>
        <td style="text-align:center; border:1px solid #CBD5E1;">{{ $log->siswa?->nis ?? '-' }}</td>
        <td style="text-align:left; font-weight:bold; border:1px solid #CBD5E1;">{{ $log->siswa?->nama_lengkap ?? '-' }}</td>
        <td style="text-align:center; border:1px solid #CBD5E1;">{{ $log->kelas?->nama ?? '-' }}</td>
        <td style="background-color:{{ $bg }}; color:{{ $fg }}; text-align:center; font-weight:bold; border:1px solid #CBD5E1;">
          {{ ucfirst($log->status ?? '-') }}
        </td>
        <td style="text-align:center; font-weight:bold; border:1px solid #CBD5E1;">
          {{ $log->jam_masuk ? \Carbon\Carbon::parse($log->jam_masuk)->format('H:i:s') : '-' }}
        </td>
        <td style="text-align:center; font-weight:bold; border:1px solid #CBD5E1;">
          {{ $log->jam_pulang ? \Carbon\Carbon::parse($log->jam_pulang)->format('H:i:s') : '-' }}
        </td>
        <td style="text-align:left; border:1px solid #CBD5E1;">
          {{ ucfirst($log->metode ?? 'manual') }} @if($log->keterangan) ({{ $log->keterangan }}) @endif
        </td>
      </tr>
    @empty
      <tr>
        <td colspan="10" style="text-align:center; padding:15px; color:#94A3B8;">Tidak ada data presensi pada periode ini.</td>
      </tr>
    @endforelse
  </tbody>
</table>
