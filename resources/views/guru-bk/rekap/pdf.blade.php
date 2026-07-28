<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
  <title>Rekap Pelanggaran Siswa — BK</title>
  <style>
    * { box-sizing: border-box; margin: 0; padding: 0; }

    body {
      font-family: 'DejaVu Sans', Arial, sans-serif;
      font-size: 9px;
      color: #1a1a2e;
      background: #fff;
    }

    /* ── HEADER ── */
    .header {
      text-align: center;
      border-bottom: 2.5px solid #e63946;
      padding-bottom: 8px;
      margin-bottom: 12px;
    }
    .header__title {
      font-size: 14px;
      font-weight: bold;
      color: #1a1a2e;
      letter-spacing: 0.04em;
      text-transform: uppercase;
    }
    .header__subtitle {
      font-size: 8.5px;
      color: #555;
      margin-top: 2px;
    }
    .header__badge {
      display: inline-block;
      background: #e63946;
      color: #fff;
      padding: 2px 10px;
      border-radius: 3px;
      font-size: 7.5px;
      font-weight: bold;
      letter-spacing: 0.06em;
      text-transform: uppercase;
      margin-top: 4px;
    }

    /* ── META INFO ── */
    .meta-grid { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
    .meta-grid td { font-size: 8.5px; color: #333; padding: 1px 4px; vertical-align: top; }
    .meta-grid td strong { color: #1a1a2e; }

    /* ── SECTION TITLE ── */
    .section-title {
      font-size: 9.5px;
      font-weight: bold;
      color: #1a1a2e;
      border-left: 3px solid #e63946;
      padding-left: 6px;
      margin-bottom: 5px;
      margin-top: 10px;
    }

    /* ── REKAP PER KELAS ── */
    .rekap-kelas-table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
    .rekap-kelas-table th {
      background: #1a1a2e; color: #fff;
      font-size: 8px; padding: 4px 6px;
      text-align: left; font-weight: bold;
    }
    .rekap-kelas-table td {
      padding: 3px 6px; font-size: 8.5px;
      border-bottom: 1px solid #e8e8e8;
    }
    .rekap-kelas-table tr:nth-child(even) td { background: #f5f5f5; }

    /* ── MAIN TABLE ── */
    .main-table { width: 100%; border-collapse: collapse; }
    .main-table thead th {
      background: #1a1a2e; color: #fff;
      font-size: 7.5px; padding: 5px 4px;
      font-weight: bold; text-align: left;
      border: 1px solid #0d1117;
    }
    .main-table tbody td {
      font-size: 8px; padding: 3px 4px;
      border: 1px solid #ddd; vertical-align: top;
    }
    .main-table tbody tr:nth-child(even) td { background: #fafafa; }

    /* ── TOTAL ROW ── */
    .total-row td {
      background: #1a1a2e !important;
      color: #fff;
      font-weight: bold;
      font-size: 8.5px;
    }

    /* ── BADGES ── */
    .badge-danger  { background: #e63946; color: #fff; padding: 1px 6px; border-radius: 3px; font-size: 8px; font-weight: bold; }
    .badge-warning { background: #f4a261; color: #fff; padding: 1px 6px; border-radius: 3px; font-size: 8px; font-weight: bold; }

    /* ── UTILS ── */
    .text-center { text-align: center; }
    .text-right  { text-align: right; }
    .fw-bold     { font-weight: bold; }
    .text-danger { color: #e63946; }

    /* ── FOOTER ── */
    .footer { margin-top: 14px; border-top: 1px solid #ddd; padding-top: 6px; width: 100%; }
    .footer-left  { font-size: 7.5px; color: #888; width: 60%; display: table-cell; vertical-align: top; }
    .footer-sign  { font-size: 8px; text-align: center; width: 40%; display: table-cell; vertical-align: top; }
    .footer-wrap  { display: table; width: 100%; }
    .footer-sign .name-line {
      border-top: 1px solid #333; padding-top: 2px;
      margin-top: 30px; font-weight: bold; font-size: 8.5px;
    }
    .footer-sign .title-line { font-size: 7.5px; color: #555; }
  </style>
</head>
<body>

  {{-- ── HEADER ── --}}
  <div class="header">
    <div class="header__title">Rekap Pelanggaran Siswa — Unit Bimbingan Konseling</div>
    <div class="header__subtitle">
      {{ $ta ? $ta->nama . ' — ' . ($ta->semester ?? '') : 'Tahun Akademik Aktif' }}
      &nbsp;|&nbsp;
      Dicetak: {{ \Carbon\Carbon::now()->locale('id')->translatedFormat('d F Y, H:i') }} WIB
    </div>
    <div class="header__badge">Dokumen Resmi BK</div>
  </div>

  {{-- ── META INFO ── --}}
  <table class="meta-grid">
    <tr>
      <td width="15%">Periode</td>
      <td width="1%">:</td>
      <td width="34%"><strong>{{ $namaBulan }} {{ $tahun }}</strong></td>
      <td width="15%">Total Kejadian</td>
      <td width="1%">:</td>
      <td width="34%"><strong class="text-danger">{{ $pelanggaranList->count() }} kasus</strong></td>
    </tr>
    <tr>
      <td>Kelas</td>
      <td>:</td>
      <td><strong>{{ $kelas ? $kelas->nama : 'Semua Kelas' }}</strong></td>
      <td>Total Poin</td>
      <td>:</td>
      <td><strong class="text-danger">{{ $pelanggaranList->sum('poin_saat_itu') }} poin</strong></td>
    </tr>
    <tr>
      <td>Kategori</td>
      <td>:</td>
      <td><strong>{{ $kategori ? $kategori->nama : 'Semua Kategori' }}</strong></td>
      <td></td><td></td><td></td>
    </tr>
  </table>

  {{-- ── REKAP PER KELAS ── --}}
  @if($rekapKelas->isNotEmpty())
  <div class="section-title">Rangkuman Per Kelas</div>
  <table class="rekap-kelas-table">
    <thead>
      <tr>
        <th width="4%">No</th>
        <th width="20%">Kelas</th>
        <th width="12%">Jumlah Kasus</th>
        <th width="12%">Total Poin</th>
      </tr>
    </thead>
    <tbody>
      @foreach($rekapKelas as $i => $rk)
        <tr>
          <td class="text-center">{{ $i + 1 }}</td>
          <td><strong>{{ $rk->nama_kelas }}</strong></td>
          <td>{{ $rk->total_pelanggaran }} kasus</td>
          <td><span class="badge-danger">{{ $rk->total_poin }} Poin</span></td>
        </tr>
      @endforeach
    </tbody>
  </table>
  @endif

  {{-- ── DETAIL PELANGGARAN ── --}}
  <div class="section-title">Detail Rincian Pelanggaran</div>

  @if($pelanggaranList->isEmpty())
    <p style="color:#888; font-size:9px; text-align:center; padding:16px 0; border:1px dashed #ddd;">
      Tidak ada data pelanggaran pada periode ini.
    </p>
  @else
    <table class="main-table">
      <thead>
        <tr>
          <th width="3%">No</th>
          <th width="8%">Tanggal</th>
          <th width="8%">NIS</th>
          <th width="18%">Nama Siswa</th>
          <th width="6%">Kelas</th>
          <th width="10%">Kategori</th>
          <th width="16%">Jenis Pelanggaran</th>
          <th width="5%">Poin</th>
          <th width="16%">Keterangan</th>
          <th width="10%">Pencatat</th>
        </tr>
      </thead>
      <tbody>
        @foreach($pelanggaranList as $i => $p)
          <tr>
            <td class="text-center">{{ $i + 1 }}</td>
            <td>{{ $p->tanggal_kejadian ? \Carbon\Carbon::parse($p->tanggal_kejadian)->format('d/m/Y') : '-' }}</td>
            <td>{{ $p->siswa->nis ?? '-' }}</td>
            <td class="fw-bold">{{ $p->siswa->nama_lengkap ?? '-' }}</td>
            <td class="text-center">{{ $p->siswa->kelas->nama ?? '-' }}</td>
            <td>{{ $p->jenisPelanggaran->kategori->nama ?? '-' }}</td>
            <td>{{ $p->jenisPelanggaran->nama ?? '-' }}</td>
            <td class="text-center fw-bold text-danger">{{ $p->poin_saat_itu }}</td>
            <td>{{ $p->keterangan ?? '-' }}</td>
            <td>{{ $p->pencatat->name ?? '-' }}</td>
          </tr>
        @endforeach
        <tr class="total-row">
          <td colspan="7" class="text-right">TOTAL KESELURUHAN</td>
          <td class="text-center">{{ $pelanggaranList->sum('poin_saat_itu') }}</td>
          <td colspan="2"></td>
        </tr>
      </tbody>
    </table>
  @endif

  {{-- ── FOOTER ── --}}
  <div class="footer">
    <div class="footer-wrap">
      <div class="footer-left">
        <div>* Dokumen ini dihasilkan secara otomatis oleh Sistem Informasi Absensi Digital.</div>
        <div>* Data bersumber dari rekap pelanggaran siswa tahun akademik yang sedang berjalan.</div>
      </div>
      <div class="footer-sign">
        <div>Mengetahui, Guru Bimbingan Konseling</div>
        <div class="name-line">{{ auth()->user()->name }}</div>
        <div class="title-line">Guru BK</div>
      </div>
    </div>
  </div>

</body>
</html>

