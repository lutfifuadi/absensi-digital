<table>
  <!-- KOP JUDUL LAPORAN -->
  <tr>
    <td colspan="{{ count($dates) + 9 }}" style="font-weight:bold; font-size:14pt; text-align:center;">
      {{ strtoupper($namaSekolah) }}
    </td>
  </tr>
  <tr>
    <td colspan="{{ count($dates) + 9 }}" style="font-weight:bold; font-size:12pt; text-align:center;">
      LAPORAN REKAPITULASI PRESENSI SISWA
    </td>
  </tr>
  <tr>
    <td colspan="{{ count($dates) + 9 }}" style="font-weight:bold; font-size:10pt; text-align:center;">
      PERIODE: {{ strtoupper($namaBulan) }} {{ $tahun }} @if($kelas) | KELAS: {{ strtoupper($kelas->nama) }} @endif
    </td>
  </tr>
  <tr></tr>

  <!-- HEADER TABEL -->
  <thead>
    <tr>
      <th rowspan="2" style="font-weight:bold; background-color:#1E293B; color:#FFFFFF; text-align:center; vertical-align:middle; border:1px solid #475569;">#</th>
      <th rowspan="2" style="font-weight:bold; background-color:#1E293B; color:#FFFFFF; text-align:center; vertical-align:middle; border:1px solid #475569;">NIS</th>
      <th rowspan="2" style="font-weight:bold; background-color:#1E293B; color:#FFFFFF; text-align:left; vertical-align:middle; border:1px solid #475569;">Nama Siswa</th>
      @foreach ($dates as $date)
        @php
          $dt = \Carbon\Carbon::parse($date);
          $isWeekend = in_array($dt->dayOfWeek, [\Carbon\Carbon::SATURDAY, \Carbon\Carbon::SUNDAY]);
        @endphp
        <th style="font-weight:bold; background-color:{{ $isWeekend ? '#475569' : '#1E293B' }}; color:#FFFFFF; text-align:center; border:1px solid #475569;">
          {{ (int) $dt->format('d') }}
        </th>
      @endforeach
      <th rowspan="2" style="font-weight:bold; background-color:#065F46; color:#FFFFFF; text-align:center; vertical-align:middle; border:1px solid #475569;">H</th>
      <th rowspan="2" style="font-weight:bold; background-color:#1E40AF; color:#FFFFFF; text-align:center; vertical-align:middle; border:1px solid #475569;">S</th>
      <th rowspan="2" style="font-weight:bold; background-color:#92400E; color:#FFFFFF; text-align:center; vertical-align:middle; border:1px solid #475569;">I</th>
      <th rowspan="2" style="font-weight:bold; background-color:#991B1B; color:#FFFFFF; text-align:center; vertical-align:middle; border:1px solid #475569;">A</th>
      <th rowspan="2" style="font-weight:bold; background-color:#6B21A8; color:#FFFFFF; text-align:center; vertical-align:middle; border:1px solid #475569;">T</th>
      <th rowspan="2" style="font-weight:bold; background-color:#1E293B; color:#FFFFFF; text-align:center; vertical-align:middle; border:1px solid #475569;">%</th>
    </tr>
    <tr>
      @foreach ($dates as $date)
        @php
          $dt = \Carbon\Carbon::parse($date);
          $isWeekend = in_array($dt->dayOfWeek, [\Carbon\Carbon::SATURDAY, \Carbon\Carbon::SUNDAY]);
        @endphp
        <th style="font-weight:bold; background-color:{{ $isWeekend ? '#64748B' : '#334155' }}; color:#FFFFFF; text-align:center; border:1px solid #475569;">
          {{ substr($dt->translatedFormat('D'), 0, 1) }}
        </th>
      @endforeach
    </tr>
  </thead>

  <!-- DATA BODY -->
  <tbody>
    @foreach ($siswaList as $siswa)
      @php
        $pivot = $absensiPivot[$siswa->id] ?? [];
        $h = collect($pivot)->filter(fn($v) => $v === 'hadir')->count();
        $s = collect($pivot)->filter(fn($v) => $v === 'sakit')->count();
        $i = collect($pivot)->filter(fn($v) => $v === 'izin')->count();
        $a = collect($pivot)->filter(fn($v) => $v === 'alpha')->count();
        $t = collect($pivot)->filter(fn($v) => $v === 'terlambat')->count();
        
        $effectiveDays = count($dates);
        $persen = $effectiveDays > 0 ? round((($h + $t) / $effectiveDays) * 100) : 0;
      @endphp
      <tr>
        <td style="text-align:center; border:1px solid #CBD5E1;">{{ $loop->iteration }}</td>
        <td style="text-align:center; border:1px solid #CBD5E1;">{{ $siswa->nis }}</td>
        <td style="text-align:left; font-weight:bold; border:1px solid #CBD5E1;">{{ $siswa->nama_lengkap }}</td>
        @foreach ($dates as $date)
          @php 
            $dt = \Carbon\Carbon::parse($date);
            $isWeekend = in_array($dt->dayOfWeek, [\Carbon\Carbon::SATURDAY, \Carbon\Carbon::SUNDAY]);
            $st = $pivot[$date] ?? null; 
            
            $bg = '#FFFFFF';
            $fg = '#000000';
            if ($st === 'hadir') { $bg = '#D1FAE5'; $fg = '#065F46'; }
            elseif ($st === 'sakit') { $bg = '#DBEAFE'; $fg = '#1E40AF'; }
            elseif ($st === 'izin') { $bg = '#FEF3C7'; $fg = '#92400E'; }
            elseif ($st === 'alpha') { $bg = '#FEE2E2'; $fg = '#991B1B'; }
            elseif ($st === 'terlambat') { $bg = '#F3E8FF'; $fg = '#6B21A8'; }
            elseif ($isWeekend) { $bg = '#F1F5F9'; $fg = '#94A3B8'; }
          @endphp
          <td style="background-color:{{ $bg }}; color:{{ $fg }}; text-align:center; font-weight:bold; border:1px solid #CBD5E1;">
            {{ $st ? strtoupper(substr($st, 0, 1)) : ($isWeekend ? '-' : '') }}
          </td>
        @endforeach
        <td style="background-color:#D1FAE5; color:#065F46; text-align:center; font-weight:bold; border:1px solid #CBD5E1;">{{ $h }}</td>
        <td style="background-color:#DBEAFE; color:#1E40AF; text-align:center; font-weight:bold; border:1px solid #CBD5E1;">{{ $s }}</td>
        <td style="background-color:#FEF3C7; color:#92400E; text-align:center; font-weight:bold; border:1px solid #CBD5E1;">{{ $i }}</td>
        <td style="background-color:#FEE2E2; color:#991B1B; text-align:center; font-weight:bold; border:1px solid #CBD5E1;">{{ $a }}</td>
        <td style="background-color:#F3E8FF; color:#6B21A8; text-align:center; font-weight:bold; border:1px solid #CBD5E1;">{{ $t }}</td>
        <td style="text-align:center; font-weight:bold; border:1px solid #CBD5E1;">{{ $persen }}%</td>
      </tr>
    @endforeach
  </tbody>
</table>
