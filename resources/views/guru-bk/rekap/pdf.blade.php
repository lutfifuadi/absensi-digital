<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
  <title>Rekap Pelanggaran Siswa — BK</title>
  <style>
    @page { margin: 12mm 15mm 15mm 15mm; }
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 8.5pt; color: #1e293b; line-height: 1.3; }

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

    .header { text-align: center; margin-bottom: 12px; }
    .header__title { font-size: 12pt; font-weight: bold; color: #0f172a; text-transform: uppercase; }
    .header__subtitle { font-size: 8.5pt; color: #475569; margin-top: 2px; }

    .meta-grid { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
    .meta-grid td { font-size: 8.5pt; color: #334155; padding: 2px 4px; vertical-align: top; }
    .meta-grid td strong { color: #0f172a; }

    .section-title { font-size: 9pt; font-weight: bold; color: #0f172a; border-left: 3px solid #e63946; padding-left: 6px; margin-bottom: 6px; margin-top: 10px; }

    .rekap-kelas-table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
    .rekap-kelas-table th { background: #1e293b; color: #fff; font-size: 8pt; padding: 5px 6px; text-align: left; font-weight: bold; }
    .rekap-kelas-table td { padding: 4px 6px; font-size: 8.5pt; border-bottom: 1px solid #cbd5e1; }
    .rekap-kelas-table tr:nth-child(even) td { background: #f8fafc; }

    .main-table { width: 100%; border-collapse: collapse; }
    .main-table thead th { background: #1e293b; color: #fff; font-size: 8pt; padding: 6px 4px; font-weight: bold; text-align: left; border: 1px solid #334155; }
    .main-table tbody td { font-size: 8pt; padding: 4px; border: 1px solid #cbd5e1; vertical-align: top; }
    .main-table tbody tr:nth-child(even) td { background: #f8fafc; }

    .total-row td { background: #1e293b !important; color: #fff; font-weight: bold; font-size: 8.5pt; }

    .badge-danger  { background: #fee2e2; color: #991b1b; padding: 2px 6px; border-radius: 3px; font-size: 8pt; font-weight: bold; }
    .text-center { text-align: center; }
    .text-right  { text-align: right; }
    .fw-bold     { font-weight: bold; }
    .text-danger { color: #dc2626; }

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
    $hasLogo       = !empty($logoPath) && file_exists($logoPath);
    $namaLembaga   = setting('nama_lembaga');
    $namaSekolah   = setting('nama_sekolah', 'Madrasah Aliyah');
    $alamatSekolah = setting('alamat_sekolah') ?: setting('alamat');
    $kepalaSekolah = setting('kepala_sekolah') ?: '-';
    $nipKepala     = setting('nip_kepala_sekolah') ?: '';
  @endphp

  <!-- KOP SURAT RESMI -->
  <div class="kop-container">
    <table class="kop-table">
      <tr>
        @if($hasLogo)
          <td class="kop-logo">
            <img src="{{ $logoPath }}" alt="Logo">
          </td>
        @endif
        <td class="kop-text" style="{{ !$hasLogo ? 'padding-left:0; padding-right:0;' : '' }}">
          @if(!empty($namaLembaga))
            <div class="nama-lembaga">{{ strtoupper($namaLembaga) }}</div>
          @endif
          <div class="nama-sekolah">{{ strtoupper($namaSekolah) }}</div>
          @if(!empty($alamatSekolah))
            <div class="alamat-sekolah">{{ $alamatSekolah }}</div>
          @endif
        </td>
      </tr>
    </table>
    <div class="kop-divider"></div>
  </div>

  <div class="header">
    <div class="header__title">REKAPITULASI PELANGGARAN SISWA — BIMBINGAN KONSELING (BK)</div>
    <div class="header__subtitle">
      PERIODE: {{ strtoupper($namaBulan) }} {{ $tahun }} | {{ $ta ? $ta->nama : 'Tahun Akademik Aktif' }}
    </div>
  </div>

  <table class="meta-grid">
    <tr>
      <td width="15%">Kelas Filter</td>
      <td width="1%">:</td>
      <td width="34%"><strong>{{ $kelas ? $kelas->nama : 'Semua Kelas' }}</strong></td>
      <td width="15%">Total Kasus</td>
      <td width="1%">:</td>
      <td width="34%"><strong class="text-danger">{{ $pelanggaranList->count() }} Kejadian</strong></td>
    </tr>
    <tr>
      <td>Kategori Filter</td>
      <td>:</td>
      <td><strong>{{ $kategori ? $kategori->nama : 'Semua Kategori' }}</strong></td>
      <td>Total Poin Pelanggaran</td>
      <td>:</td>
      <td><strong class="text-danger">{{ $pelanggaranList->sum('poin_saat_itu') }} Poin</strong></td>
    </tr>
  </table>

  @if($rekapKelas->isNotEmpty())
  <div class="section-title">Rangkuman Kasus Per Kelas</div>
  <table class="rekap-kelas-table">
    <thead>
      <tr>
        <th width="5%">No</th>
        <th width="35%">Kelas</th>
        <th width="30%">Jumlah Kasus</th>
        <th width="30%">Total Poin Pelanggaran</th>
      </tr>
    </thead>
    <tbody>
      @foreach($rekapKelas as $i => $rk)
        <tr>
          <td class="text-center">{{ $i + 1 }}</td>
          <td><strong>{{ $rk->nama_kelas }}</strong></td>
          <td>{{ $rk->total_pelanggaran }} Kasus</td>
          <td><span class="badge-danger">{{ $rk->total_poin }} Poin</span></td>
        </tr>
      @endforeach
    </tbody>
  </table>
  @endif

  <div class="section-title">Detail Rincian Kejadian Pelanggaran</div>

  @if($pelanggaranList->isEmpty())
    <p style="color:#94a3b8; font-size:9pt; text-align:center; padding:16px 0; border:1px dashed #cbd5e1;">
      Tidak ada data pelanggaran pada periode ini.
    </p>
  @else
    <table class="main-table">
      <thead>
        <tr>
          <th width="3%">No</th>
          <th width="9%">Tanggal</th>
          <th width="9%">NIS</th>
          <th width="18%">Nama Siswa</th>
          <th width="6%">Kelas</th>
          <th width="12%">Kategori</th>
          <th width="18%">Jenis Pelanggaran</th>
          <th width="5%">Poin</th>
          <th width="12%">Keterangan</th>
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
          </tr>
        @endforeach
        <tr class="total-row">
          <td colspan="7" class="text-right">TOTAL POIN KESELURUHAN</td>
          <td class="text-center">{{ $pelanggaranList->sum('poin_saat_itu') }}</td>
          <td></td>
        </tr>
      </tbody>
    </table>
  @endif

  <!-- BLOK TANDA TANGAN -->
  <div class="ttd-box">
    <table class="ttd-table">
      <tr>
        <td>
          Guru Bimbingan Konseling (BK),<br><br><br><br><br>
          <strong>{{ auth()->user()->name }}</strong>
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
