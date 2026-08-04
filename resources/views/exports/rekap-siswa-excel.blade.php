<table>
  <!-- KOP SURAT LAPORAN -->
  @php
    $namaLembaga   = setting('nama_lembaga');
    $namaSekolah   = setting('nama_sekolah') ?: $namaSekolah ?? 'SEKOLAH';
    $alamatSekolah = setting('alamat_sekolah') ?: setting('alamat');
  @endphp
  @if(!empty($namaLembaga) && strtoupper(trim($namaLembaga)) !== strtoupper(trim($namaSekolah)))
  <tr>
    <td colspan="{{ count($dates) + 9 }}" style="font-weight:bold; font-size:11pt; text-align:center; color:#0F172A;">
      {{ strtoupper($namaLembaga) }}
    </td>
  </tr>
  @endif
  <tr>
    <td colspan="{{ count($dates) + 9 }}" style="font-weight:900; font-size:14pt; text-align:center; color:#1E293B;">
      {{ strtoupper($namaSekolah) }}
    </td>
  </tr>
  @if(!empty($alamatSekolah))
  <tr>
    <td colspan="{{ count($dates) + 9 }}" style="font-size:8.5pt; text-align:center; color:#475569;">
      {{ $alamatSekolah }}
    </td>
  </tr>
  @endif
  <tr>
    <td colspan="{{ count($dates) + 9 }}" style="font-weight:bold; font-size:11pt; text-align:center; color:#0F172A;">
      LAPORAN REKAPITULASI PRESENSI SISWA
    </td>
  </tr>
  <tr>
    <td colspan="{{ count($dates) + 9 }}" style="font-weight:bold; font-size:9.5pt; text-align:center; color:#334155;">
      PERIODE: {{ strtoupper($namaBulan) }} {{ $tahun }} @if($kelas) | KELAS: {{ strtoupper($kelas->nama) }} @endif
    </td>
  </tr>
  <tr></tr>

  <!-- HEADER TABEL NAVY ESTETIS -->
  <thead>
    <tr>
      <th rowspan="2" style="font-weight:bold; background-color:#1E293B; color:#FFFFFF; text-align:center; vertical-align:middle; border:1px solid #334155;">#</th>
      <th rowspan="2" style="font-weight:bold; background-color:#1E293B; color:#FFFFFF; text-align:center; vertical-align:middle; border:1px solid #334155;">NIS</th>
      <th rowspan="2" style="font-weight:bold; background-color:#1E293B; color:#FFFFFF; text-align:left; vertical-align:middle; border:1px solid #334155;">Nama Siswa</th>
      @foreach ($dates as $date)
        @php
          $dt = \Carbon\Carbon::parse($date);
          $isWeekend = in_array($dt->dayOfWeek, [\Carbon\Carbon::SATURDAY, \Carbon\Carbon::SUNDAY]);
        @endphp
        <th style="font-weight:bold; background-color:{{ $isWeekend ? '#475569' : '#1E293B' }}; color:#FFFFFF; text-align:center; border:1px solid #334155;">
          {{ (int) $dt->format('d') }}
        </th>
      @endforeach
      <th rowspan="2" style="font-weight:bold; background-color:#065F46; color:#FFFFFF; text-align:center; vertical-align:middle; border:1px solid #334155;">H</th>
      <th rowspan="2" style="font-weight:bold; background-color:#1E40AF; color:#FFFFFF; text-align:center; vertical-align:middle; border:1px solid #334155;">S</th>
      <th rowspan="2" style="font-weight:bold; background-color:#92400E; color:#FFFFFF; text-align:center; vertical-align:middle; border:1px solid #334155;">I</th>
      <th rowspan="2" style="font-weight:bold; background-color:#991B1B; color:#FFFFFF; text-align:center; vertical-align:middle; border:1px solid #334155;">A</th>
      <th rowspan="2" style="font-weight:bold; background-color:#6B21A8; color:#FFFFFF; text-align:center; vertical-align:middle; border:1px solid #334155;">T</th>
      <th rowspan="2" style="font-weight:bold; background-color:#1E293B; color:#FFFFFF; text-align:center; vertical-align:middle; border:1px solid #334155;">%</th>
    </tr>
    <tr>
      @foreach ($dates as $date)
        @php
          $dt = \Carbon\Carbon::parse($date);
          $isWeekend = in_array($dt->dayOfWeek, [\Carbon\Carbon::SATURDAY, \Carbon\Carbon::SUNDAY]);
        @endphp
        <th style="font-weight:bold; background-color:{{ $isWeekend ? '#64748B' : '#334155' }}; color:#FFFFFF; text-align:center; border:1px solid #334155;">
          {{ substr($dt->translatedFormat('D'), 0, 1) }}
        </th>
      @endforeach
    </tr>
  </thead>

  <!-- DATA BODY (DUAL-ROW PER SISWA: BARIS 1 STATUS, BARIS 2 JAM MASUK - PULANG) -->
  <tbody>
    @foreach ($siswaList as $siswa)
      @php
        $pivot = $absensiPivot[$siswa->id] ?? [];
        $h = collect($pivot)->filter(fn($v) => isset($v['status']) ? $v['status'] === 'hadir' : $v === 'hadir')->count();
        $s = collect($pivot)->filter(fn($v) => isset($v['status']) ? $v['status'] === 'sakit' : $v === 'sakit')->count();
        $i = collect($pivot)->filter(fn($v) => isset($v['status']) ? $v['status'] === 'izin' : $v === 'izin')->count();
        $a = collect($pivot)->filter(fn($v) => isset($v['status']) ? $v['status'] === 'alpha' : $v === 'alpha')->count();
        $t = collect($pivot)->filter(fn($v) => isset($v['status']) ? $v['status'] === 'terlambat' : $v === 'terlambat')->count();
        
        $effectiveDays = count($dates);
        $persen = $effectiveDays > 0 ? round((($h + $t) / $effectiveDays) * 100) : 0;
      @endphp
      
      <!-- BARIS 1: IDENTITAS & KODE STATUS -->
      <tr>
        <td rowspan="2" style="text-align:center; vertical-align:middle; border:1px solid #CBD5E1;">{{ $loop->iteration }}</td>
        <td rowspan="2" style="text-align:center; vertical-align:middle; border:1px solid #CBD5E1;">{{ $siswa->nis }}</td>
        <td rowspan="2" style="text-align:left; vertical-align:middle; font-weight:bold; border:1px solid #CBD5E1;">{{ $siswa->nama_lengkap }}</td>
        @foreach ($dates as $date)
          @php 
            $dt = \Carbon\Carbon::parse($date);
            $isWeekend = in_array($dt->dayOfWeek, [\Carbon\Carbon::SATURDAY, \Carbon\Carbon::SUNDAY]);
            $rec = $pivot[$date] ?? null; 
            $st = is_array($rec) ? ($rec['status'] ?? null) : $rec;

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
        <td rowspan="2" style="background-color:#D1FAE5; color:#065F46; text-align:center; vertical-align:middle; font-weight:bold; border:1px solid #CBD5E1;">{{ $h }}</td>
        <td rowspan="2" style="background-color:#DBEAFE; color:#1E40AF; text-align:center; vertical-align:middle; font-weight:bold; border:1px solid #CBD5E1;">{{ $s }}</td>
        <td rowspan="2" style="background-color:#FEF3C7; color:#92400E; text-align:center; vertical-align:middle; font-weight:bold; border:1px solid #CBD5E1;">{{ $i }}</td>
        <td rowspan="2" style="background-color:#FEE2E2; color:#991B1B; text-align:center; vertical-align:middle; font-weight:bold; border:1px solid #CBD5E1;">{{ $a }}</td>
        <td rowspan="2" style="background-color:#F3E8FF; color:#6B21A8; text-align:center; vertical-align:middle; font-weight:bold; border:1px solid #CBD5E1;">{{ $t }}</td>
        <td rowspan="2" style="text-align:center; vertical-align:middle; font-weight:bold; border:1px solid #CBD5E1;">{{ $persen }}%</td>
      </tr>

      <!-- BARIS 2: RINCIAN JAM MASUK - PULANG -->
      <tr>
        @foreach ($dates as $date)
          @php
            $dt = \Carbon\Carbon::parse($date);
            $isWeekend = in_array($dt->dayOfWeek, [\Carbon\Carbon::SATURDAY, \Carbon\Carbon::SUNDAY]);
            $rec = $pivot[$date] ?? null;
            $masuk = is_array($rec) && !empty($rec['jam_masuk']) ? \Carbon\Carbon::parse($rec['jam_masuk'])->format('H:i') : null;
            $pulang = is_array($rec) && !empty($rec['jam_pulang']) ? \Carbon\Carbon::parse($rec['jam_pulang'])->format('H:i') : null;

            $timeStr = '-';
            if ($masuk && $pulang) {
              $timeStr = $masuk . ' - ' . $pulang;
            } elseif ($masuk) {
              $timeStr = $masuk . ' - -';
            }
          @endphp
          <td style="background-color:#F8FAFC; color:#64748B; font-size:7pt; text-align:center; border:1px solid #CBD5E1;">
            {{ $timeStr }}
          </td>
        @endforeach
      </tr>
    @endforeach
  </tbody>
</table>
