@extends('layouts/layoutMaster')

@section('title', 'Absensi Kegiatan & Ekstrakurikuler')

@section('page-style')
<style>
  :root {
    --das-primary: #7367f0;
    --das-primary-soft: rgba(115, 103, 240, 0.12);
    --das-success: #28c76f;
    --das-success-soft: rgba(40, 199, 111, 0.12);
    --das-info: #00cfe8;
    --das-info-soft: rgba(0, 207, 232, 0.12);
    --das-warning: #ff9f43;
    --das-warning-soft: rgba(255, 159, 67, 0.12);
    --das-danger: #ea5455;
    --das-danger-soft: rgba(234, 84, 85, 0.12);
    --das-surface: rgba(15, 23, 42, 0.4);
    --das-surface-hover: rgba(30, 41, 59, 0.6);
    --das-border: rgba(255, 255, 255, 0.06);
    --das-border-hover: rgba(255, 255, 255, 0.12);
    --das-radius: 5px;
  }

  /* HERO */
  .das-hero { position: relative; border-radius: var(--das-radius); overflow: hidden; margin-bottom: 2rem; }
  .das-hero__bg { position: absolute; inset: 0; background: linear-gradient(135deg, #1e1b4b 0%, #312d89 40%, #4338ca 100%); z-index: 0; }
  .das-hero__glass { position: absolute; inset: 0; background: radial-gradient(circle at top right, rgba(115,103,240,.15), transparent 40%); z-index: 1; }
  .das-hero__grid-lines { position: absolute; inset: 0; background-image: linear-gradient(rgba(255,255,255,.04) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,.04) 1px, transparent 1px); background-size: 40px 40px; z-index: 1; }
  .das-hero__inner { position: relative; z-index: 2; display: flex; align-items: center; justify-content: space-between; padding: 2.5rem; gap: 1.5rem; flex-wrap: wrap; }
  .das-hero__identity { display: flex; align-items: center; gap: 1.25rem; }
  .das-hero__icon { width: 64px; height: 64px; background: rgba(115,103,240,.2); border: 1px solid rgba(115,103,240,.3); border-radius: 5px; display: flex; align-items: center; justify-content: center; font-size: 1.75rem; color: #a5a2f7; }
  .das-hero__title { font-size: 1.5rem; font-weight: 800; color: white; margin: 0 0 4px; }
  .das-hero__welcome { margin: 0; font-size: .88rem; color: rgba(255,255,255,.6); }

  /* PANEL */
  .das-panel { background: var(--das-surface); border: 1px solid var(--das-border); border-radius: var(--das-radius); overflow: hidden; backdrop-filter: blur(6px); }
  .das-panel__head { display: flex; align-items: center; justify-content: space-between; padding: .9rem 1.25rem; border-bottom: 1px solid var(--das-border); }
  .das-panel__title { font-size: .82rem; font-weight: 700; text-transform: uppercase; letter-spacing: .6px; display: flex; align-items: center; gap: 8px; color: #ccc; }
  .das-panel__icon-dot { width: 8px; height: 8px; border-radius: 50%; background: var(--das-info); box-shadow: 0 0 6px var(--das-info); }

  /* TABLE */
  .das-table { width: 100%; border-collapse: collapse; font-size: .82rem; }
  .das-table thead th { font-size: .65rem; font-weight: 700; text-transform: uppercase; letter-spacing: .8px; color: #666; padding: .75rem 1rem; border-bottom: 1px solid var(--das-border); background: rgba(255,255,255,.02); }
  .das-table tbody td { padding: .75rem 1rem; border-bottom: 1px solid var(--das-border); color: #ccc; vertical-align: middle; transition: background .2s ease; }
  .das-table tbody tr:hover td { background: var(--das-surface-hover); }

  /* CHIP */
  .das-chip { display: inline-flex; align-items: center; font-size: .65rem; font-weight: 700; padding: 2px 10px; border-radius: 20px; text-transform: uppercase; letter-spacing: .5px; }
  .das-chip--success { background: var(--das-success-soft); color: var(--das-success); }
  .das-chip--info { background: var(--das-info-soft); color: var(--das-info); }
  .das-chip--primary { background: var(--das-primary-soft); color: var(--das-primary); }
  .das-chip--warning { background: var(--das-warning-soft); color: var(--das-warning); }
  .das-chip--danger { background: var(--das-danger-soft); color: var(--das-danger); }

  /* BUTTONS */
  .das-btn { display: inline-flex; align-items: center; gap: 5px; font-size: .75rem; font-weight: 600; padding: .5rem 1rem; border-radius: 5px; border: 1px solid transparent; cursor: pointer; transition: all .18s ease; text-decoration: none; white-space: nowrap; }
  .das-btn--primary { background: var(--das-primary); color: white !important; border-color: var(--das-primary); }
  .das-btn--primary:hover { background: #6259e8; transform: translateY(-2px); }
  .das-btn--ghost { background: transparent; border-color: var(--das-border); color: #999 !important; }
  .das-btn--ghost:hover { background: var(--das-surface-hover); color: white !important; }

  .activity-icon { width: 38px; height: 38px; border-radius: 8px; background: var(--das-primary-soft); border: 1px solid rgba(115,103,240,.2); display: flex; align-items: center; justify-content: center; color: var(--das-primary); font-size: 1rem; }

  @keyframes slideInUp { from { transform: translateY(20px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
  .slide-in-up { animation: slideInUp .5s ease-out; }
</style>
@endsection

@section('content')

  {{-- ── HERO HEADER ────────────────────────────────── --}}
  <div class="das-hero slide-in-up">
    <div class="das-hero__bg"></div>
    <div class="das-hero__glass"></div>
    <div class="das-hero__grid-lines"></div>
    <div class="das-hero__inner">
      <div class="das-hero__identity">
        <div class="das-hero__icon">
          <i class="ti tabler-calendar-event"></i>
        </div>
        <div>
          <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1" style="font-size:.65rem;text-transform:uppercase;letter-spacing:1px;opacity:.6;">
              <li class="breadcrumb-item text-white opacity-60">Admin</li>
              <li class="breadcrumb-item active text-white opacity-100">Absensi Kegiatan</li>
            </ol>
          </nav>
          <h4 class="das-hero__title">Absensi Kegiatan</h4>
          <p class="das-hero__welcome">Monitoring dan pencatatan kehadiran siswa pada berbagai agenda sekolah.</p>
        </div>
      </div>
      <div class="d-flex align-items-center gap-2">
        <a href="{{ route('admin.absensi-kegiatan.scan') }}" class="das-btn das-btn--primary">
          <i class="ti tabler-qrcode me-1"></i> Scan QR Kegiatan
        </a>
        <a href="{{ route('admin.kegiatan.index') }}" class="das-btn das-btn--ghost">
          <i class="ti tabler-settings me-1"></i> Kelola Kegiatan
        </a>
      </div>
    </div>
  </div>

  {{-- ── MAIN PANEL ────────────────────────────── --}}
  <div class="das-panel slide-in-up mb-5">
    <div class="das-panel__head">
      <div class="das-panel__title">
        <span class="das-panel__icon-dot"></span>
        Daftar Kegiatan Terbaru
      </div>
      <span class="das-chip das-chip--info">{{ $kegiatans->count() }} Kegiatan</span>
    </div>

    <div class="table-responsive">
      <table class="das-table">
        <thead>
          <tr>
            <th>Nama Kegiatan</th>
            <th>Waktu &amp; Lokasi</th>
            <th class="text-center">Peserta</th>
            <th class="text-center">Wajib?</th>
            <th class="text-end px-4">Aksi</th>
          </tr>
        </thead>
        <tbody>
          @forelse($kegiatans as $k)
          <tr>
            <td>
              <div class="d-flex align-items-center gap-3">
                <div class="activity-icon">
                  <i class="ti tabler-{{ $k->jenis === 'Ekstrakurikuler' ? 'run' : ($k->jenis === 'Seminar' ? 'school' : 'star') }}"></i>
                </div>
                <div>
                  <div class="fw-bold text-white mb-0" style="font-size:.85rem;">{{ $k->nama_kegiatan }}</div>
                  <div class="text-muted" style="font-size:.72rem;">{{ $k->jenis }}</div>
                </div>
              </div>
            </td>
            <td>
              <div class="text-white small fw-medium mb-1">
                {{ $k->tanggal_pelaksanaan->translatedFormat('d F Y') }}
              </div>
              <div class="text-muted d-flex align-items-center gap-1" style="font-size:.7rem;">
                <i class="ti tabler-map-pin" style="font-size:.8rem;"></i> {{ $k->lokasi ?? 'Lokasi tidak diatur' }}
              </div>
            </td>
            <td class="text-center">
              @if($k->target_peserta)
                <span class="das-chip das-chip--primary">{{ count($k->target_peserta) }} Kelas</span>
              @else
                <span class="das-chip das-chip--success">Seluruh Siswa</span>
              @endif
            </td>
            <td class="text-center">
              @if($k->is_wajib)
                <span class="das-chip das-chip--danger">Wajib</span>
              @else
                <span class="das-chip das-chip--secondary">Opsional</span>
              @endif
            </td>
            <td class="px-4 text-end">
              <button class="das-btn das-btn--primary das-btn--sm" onclick="viewAbsensi({{ $k->id }}, '{{ $k->nama_kegiatan }}')">
                <i class="ti tabler-checkbox me-1"></i> Absensi
              </button>
            </td>
          </tr>
          @empty
          <tr>
            <td colspan="5" class="py-5 text-center">
              <div class="d-flex flex-column align-items-center gap-2 opacity-30">
                <i class="ti tabler-calendar-off" style="font-size:3rem;"></i>
                <span class="small font-monospace uppercase">Belum ada data kegiatan</span>
              </div>
            </td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

  {{-- ── DETAIL ABSENSI PANEL ────────────────────────── --}}
  <div class="das-panel slide-in-up" id="detailCard" style="display: none;">
    <div class="das-panel__head">
      <div class="das-panel__title">
        <span class="das-panel__icon-dot" style="background:var(--das-success);box-shadow:0 0 6px var(--das-success);"></span>
        Detail Absensi: <span id="kegiatanName" class="text-white ms-1"></span>
      </div>
      <div id="loadingDetail" style="display: none;">
        <div class="spinner-border spinner-border-sm text-info" role="status"></div>
      </div>
    </div>
    <div class="das-panel__body p-0">
      <div class="table-responsive">
        <table class="das-table">
          <thead>
            <tr>
              <th>Nama Siswa</th>
              <th>Kelas</th>
              <th class="text-center">Status</th>
              <th>Keterangan</th>
              <th class="text-end px-4">Aksi</th>
            </tr>
          </thead>
          <tbody id="absensiPesertaBody">
          </tbody>
        </table>
      </div>
    </div>
  </div>

@endsection

@section('page-script')
<script>
async function viewAbsensi(id, nama) {
  const detailCard = document.getElementById('detailCard');
  const kegiatanName = document.getElementById('kegiatanName');
  const loading = document.getElementById('loadingDetail');
  
  kegiatanName.textContent = nama;
  detailCard.style.display = 'block';
  loading.style.display = 'block';
  
  detailCard.scrollIntoView({ behavior: 'smooth' });

  try {
    const response = await fetch(`/api/v1/innovation/activity-attendance?kegiatan_id=${id}`);
    const result = await response.json();
    
    const tbody = document.getElementById('absensiPesertaBody');
    const data = result.data || [];
    
    loading.style.display = 'none';
    
    if (data.length === 0) {
      tbody.innerHTML = `
        <tr>
          <td colspan="5" class="py-5 text-center">
            <div class="d-flex flex-column align-items-center gap-2 opacity-30">
              <i class="ti tabler-users-minus" style="font-size:2.5rem;"></i>
              <span class="small font-monospace uppercase">Belum ada data absensi untuk kegiatan ini</span>
            </div>
          </td>
        </tr>
      `;
      return;
    }
    
    const statusBadges = {
      'hadir': 'das-chip--success',
      'tidak_hadir': 'das-chip--danger',
      'izin': 'das-chip--warning',
      'sakit': 'das-chip--info',
      'alpha': 'das-chip--danger'
    };
    
    tbody.innerHTML = data.map(a => `
      <tr>
        <td>
          <div class="fw-bold text-white" style="font-size:.82rem;">${a.siswa?.nama_lengkap || '-'}</div>
          <div class="text-muted" style="font-size:.7rem;">NIS: ${a.siswa?.nis || '-'}</div>
        </td>
        <td>
          <span class="das-chip das-chip--primary">${a.siswa?.kelas?.nama || '-'}</span>
        </td>
        <td class="text-center">
          <span class="das-chip ${statusBadges[a.status] || 'das-chip--secondary'}">
            ${a.status ? a.status.charAt(0).toUpperCase() + a.status.slice(1) : '-'}
          </span>
        </td>
        <td>
          <div class="text-muted small">${a.keterangan || '-'}</div>
        </td>
        <td class="text-end px-4">
          <select class="form-select form-select-sm text-white border-secondary" style="font-size: .75rem; background-color: var(--das-surface); width: auto; display: inline-block;" onchange="updateStatus(${a.id}, this.value, ${a.kegiatan_id}, ${a.siswa_id})">
            <option value="hadir" class="bg-dark text-white" ${a.status === 'hadir' ? 'selected' : ''}>Hadir</option>
            <option value="izin" class="bg-dark text-white" ${a.status === 'izin' ? 'selected' : ''}>Izin</option>
            <option value="sakit" class="bg-dark text-white" ${a.status === 'sakit' ? 'selected' : ''}>Sakit</option>
            <option value="alpha" class="bg-dark text-white" ${a.status === 'alpha' ? 'selected' : ''}>Alpha</option>
          </select>
        </td>
      </tr>
    `).join('');
    
  } catch (e) {
    console.error('Error:', e);
    loading.style.display = 'none';
  }
}

async function updateStatus(attendanceId, status, kegiatanId, siswaId) {
  let keterangan = '';
  if (status === 'izin' || status === 'sakit') {
    keterangan = prompt('Masukkan keterangan alasan (opsional):') || '';
  }

  try {
    const response = await fetch('/api/v1/innovation/activity-attendance', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
        'Accept': 'application/json'
      },
      body: JSON.stringify({
        kegiatan_id: kegiatanId,
        siswa_id: siswaId,
        status: status,
        keterangan: keterangan
      })
    });
    
    const result = await response.json();
    if (result.success) {
      // Re-load the list to show updated status
      viewAbsensi(kegiatanId, document.getElementById('kegiatanName').textContent);
    } else {
      alert('Gagal memperbarui status: ' + (result.message || 'Error unknown'));
    }
  } catch (e) {
    console.error('Error:', e);
    alert('Terjadi kesalahan saat menghubungi server.');
  }
}
</script>
@endsection