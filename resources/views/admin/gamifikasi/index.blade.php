@extends('layouts/layoutMaster')

@section('title', 'Gamifikasi Presensi')

@section('content')
<div class="das-hero mb-4">
  <div class="das-hero__bg"></div>
  <div class="das-hero__glass"></div>
  <div class="das-hero__grid-lines"></div>

  <div class="das-hero__inner">
    <div class="das-hero__identity">
      <div class="das-hero__logo-wrapper">
        <div class="das-hero__logo-placeholder">
          <i class="ti tabler-trophy"></i>
        </div>
        <div class="das-hero__logo-glow"></div>
      </div>
      <div class="das-hero__meta">
        <div class="das-hero__badge">
          <span class="pulse-dot"></span>
          Achievement System
        </div>
        <h4 class="das-hero__title text-gradient-gold">Gamifikasi & Prestise</h4>
        <p class="das-hero__subtitle">Tingkatkan kedisiplinan siswa melalui sistem badge dan leaderboard kelas.</p>
      </div>
    </div>
    <div class="das-hero__actions">
      <button class="das-btn das-btn--primary shadow-lg" onclick="calculateLeaderboard()">
        <i class="ti tabler-refresh"></i> Hitung Ulang Skor
      </button>
    </div>
  </div>
</div>

<div class="row mb-4">
  <div class="col-md-4">
    <div class="das-stat-card das-stat-card--warning">
      <div class="das-stat-card__icon"><i class="ti tabler-award"></i></div>
      <div class="das-stat-card__body">
        <div class="das-stat-card__val" id="totalBadges">-</div>
        <div class="das-stat-card__label">Badge Tersedia</div>
      </div>
      <div class="das-stat-card__arrow"><i class="ti tabler-chevron-right"></i></div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="das-stat-card das-stat-card--success">
      <div class="das-stat-card__icon"><i class="ti tabler-medal"></i></div>
      <div class="das-stat-card__body">
        <div class="das-stat-card__val" id="studentEarned">-</div>
        <div class="das-stat-card__label">Siswa Berprestasi</div>
      </div>
      <div class="das-stat-card__arrow"><i class="ti tabler-chevron-right"></i></div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="das-stat-card das-stat-card--primary">
      <div class="das-stat-card__icon"><i class="ti tabler-school"></i></div>
      <div class="das-stat-card__body">
        <div class="das-stat-card__val" id="totalKelas">-</div>
        <div class="das-stat-card__label">Kelas Berpartisipasi</div>
      </div>
      <div class="das-stat-card__arrow"><i class="ti tabler-chevron-right"></i></div>
    </div>
  </div>
</div>

<div class="row">
  <div class="col-xl-8">
    <div class="das-panel mb-4">
      <div class="das-panel__head">
        <div class="das-panel__title">
          <span class="das-panel__icon-dot --primary"></span>
          Leaderboard Kelas Global
        </div>
        <div class="das-chip --info">Bulan Ini</div>
      </div>
      <div class="table-responsive">
        <table class="das-table">
          <thead>
            <tr>
              <th class="text-center">RANK</th>
              <th>KELAS</th>
              <th class="text-center">ABSENSI</th>
              <th class="text-center">KEHADIRAN (%)</th>
              <th class="text-center">PERFORMA</th>
            </tr>
          </thead>
          <tbody id="leaderboardBody">
            <tr>
              <td colspan="5" class="text-center py-5 text-muted">
                <div class="spinner-border spinner-border-sm text-primary me-2"></div> Memuat peringkat...
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
  
  <div class="col-xl-4">
    <div class="das-panel mb-4">
      <div class="das-panel__head">
        <div class="das-panel__title">
          <span class="das-panel__icon-dot --warning"></span>
          Badge Mastery
        </div>
        <button class="das-btn das-btn--ghost-sm text-primary p-0" onclick="openBadgeModal()">
          <i class="ti tabler-circle-plus fs-4"></i>
        </button>
      </div>
      <div class="das-panel__body">
        <div id="badgesContainer" class="d-flex flex-column gap-3">
          <!-- Badges will be loaded here -->
           <div class="text-center py-4 text-muted small">Memuat daftar badge...</div>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="das-panel">
  <div class="das-panel__head">
    <div class="das-panel__title">
      <span class="das-panel__icon-dot --success"></span>
      Perolehan Badge Terbaru
    </div>
  </div>
  <div class="table-responsive">
    <table class="das-table">
      <thead>
        <tr>
          <th>SISWA</th>
          <th>KELAS</th>
          <th>BADGE</th>
          <th>TANGGAL PEROLEH</th>
        </tr>
      </thead>
      <tbody id="studentBadgesBody">
        <tr>
          <td colspan="4" class="text-center py-4 text-muted opacity-50">Belum ada aktivitas perolehan badge terbaru.</td>
        </tr>
      </tbody>
    </table>
  </div>
</div>

{{-- Modal Badge Style --}}
<div class="modal fade" id="badgeModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content das-modal border-0 shadow-lg">
      <div class="das-modal-head">
        <h5 class="das-modal-title">Konfigurasi Badge Baru</h5>
        <p class="text-muted small mb-0 mt-1">Badge akan otomatis diberikan jika syarat terpenuhi.</p>
      </div>
      <div class="das-modal-body p-4">
        <form id="badgeForm">
          <div class="row gy-3">
            <div class="col-12">
              <label class="form-label text-white-50 small fw-bold">NAMA BADGE</label>
              <input type="text" id="badgeName" class="form-control bg-dark border-0 text-white" placeholder="Contoh: Sang Juara Absensi">
            </div>
            <div class="col-md-6">
              <label class="form-label text-white-50 small fw-bold">ICON (TABLER)</label>
              <input type="text" id="badgeIcon" class="form-control bg-dark border-0 text-white" placeholder="tabler-star">
            </div>
            <div class="col-md-6">
              <label class="form-label text-white-50 small fw-bold">TIPE</label>
              <select id="badgeType" class="form-select bg-dark border-0 text-white">
                <option value="individual">Individual</option>
                <option value="class">Kelas</option>
              </select>
            </div>
            <div class="col-12">
              <label class="form-label text-white-50 small fw-bold">DESKRIPSI</label>
              <textarea id="badgeDescription" class="form-control bg-dark border-0 text-white" rows="2"></textarea>
            </div>
            <div class="col-md-6">
              <label class="form-label text-white-50 small fw-bold">JUMLAH HARI</label>
              <input type="number" id="badgeRequirement" class="form-control bg-dark border-0 text-white" value="30">
            </div>
            <div class="col-md-6">
              <label class="form-label text-white-50 small fw-bold">SYARAT</label>
              <select id="badgeRequirementType" class="form-select bg-dark border-0 text-white">
                <option value="consecutive">Beruntun</option>
                <option value="total">Total Akumulasi</option>
              </select>
            </div>
          </div>
        </form>
      </div>
      <div class="d-flex gap-2 p-4 pt-0">
        <button type="button" class="das-btn das-btn--secondary w-100" data-bs-dismiss="modal">Batal</button>
        <button type="button" class="das-btn das-btn--primary w-100" onclick="saveBadge()">Simpan Achievement</button>
      </div>
    </div>
  </div>
</div>
@endsection

@section('page-script')
<script>
document.addEventListener('DOMContentLoaded', function() {
  loadLeaderboard();
  loadBadges();
  loadStudentBadges();
});

async function loadLeaderboard() {
  try {
    const response = await fetch('/api/v1/innovation/leaderboard');
    const result = await response.json();
    
    const tbody = document.getElementById('leaderboardBody');
    const data = result.data || [];
    
    document.getElementById('totalKelas').textContent = data.length;
    
    if (data.length === 0) {
      tbody.innerHTML = `<tr><td colspan="5" class="text-center py-5 text-muted opacity-50">Belum ada data peringkat.</td></tr>`;
      return;
    }
    
    tbody.innerHTML = data.map((item, index) => {
      const percentage = parseFloat(item.percentage || 0);
      let chipClass = '--success';
      let statusText = 'Sangat Baik';
      
      if (percentage < 70) { chipClass = '--danger'; statusText = 'Buruk'; }
      else if (percentage < 85) { chipClass = '--warning'; statusText = 'Cukup'; }
      
      const rankBadge = index === 0 ? '🏆' : index === 1 ? '🥈' : index === 2 ? '🥉' : (index + 1);
      
      return `
        <tr>
          <td class="text-center fw-bold fs-5">${rankBadge}</td>
          <td>
            <div class="fw-bold text-white">${item.kelas?.nama || '-'}</div>
            <div class="small text-muted">${item.kelas?.jurusan || 'Semua Jurusan'}</div>
          </td>
          <td class="text-center">${item.total_present} / ${item.total_attendance}</td>
          <td class="text-center">
            <div class="d-flex align-items-center justify-content-center gap-2">
              <div class="progress w-px-75" style="height: 6px; background: rgba(255,255,255,0.05);">
                <div class="progress-bar ${percentage > 85 ? 'bg-success' : (percentage > 70 ? 'bg-warning' : 'bg-danger')}" style="width: ${percentage}%"></div>
              </div>
              <span class="fw-bold">${percentage.toFixed(1)}%</span>
            </div>
          </td>
          <td class="text-center"><span class="das-chip ${chipClass}">${statusText}</span></td>
        </tr>
      `;
    }).join('');
    
  } catch (e) {
    console.error('Error loading leaderboard:', e);
  }
}

async function calculateLeaderboard() {
  const btn = event.currentTarget;
  const originalHtml = btn.innerHTML;
  btn.disabled = true;
  btn.innerHTML = '<i class="ti tabler-loader-2 spin"></i> Menghitung...';

  try {
    const response = await fetch('/api/v1/innovation/leaderboard/calculate', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
      }
    });
    const result = await response.json();
    if (result.success) {
      await loadLeaderboard();
      await loadStudentBadges();
    }
  } catch (e) {
    console.error('Error:', e);
  } finally {
    btn.disabled = false;
    btn.innerHTML = originalHtml;
  }
}

async function loadBadges() {
  try {
    const response = await fetch('/api/v1/innovation/badges');
    const result = await response.json();
    
    const container = document.getElementById('badgesContainer');
    const data = result.data || [];
    
    document.getElementById('totalBadges').textContent = data.length;
    
    if (data.length === 0) {
      container.innerHTML = `<div class="text-center py-4 text-muted">Belum ada badge.</div>`;
      return;
    }
    
    container.innerHTML = data.map(badge => `
      <div class="d-flex align-items-center gap-3 p-2 rounded" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.05);">
        <div class="das-stat-card__icon" style="width: 40px; height: 40px; background: rgba(255, 215, 0, 0.1); color: #ffd700;">
          <i class="ti ${badge.icon}"></i>
        </div>
        <div class="flex-grow-1">
          <div class="d-flex align-items-center justify-content-between">
            <span class="fw-bold text-white small">${badge.name}</span>
            <span class="badge bg-label-primary" style="font-size: 0.6rem;">${badge.badge_type}</span>
          </div>
          <div class="text-muted" style="font-size: 0.7rem;">${badge.requirement_days} hari ${badge.requirement_type}</div>
        </div>
      </div>
    `).join('');
    
  } catch (e) {
    console.error('Error:', e);
  }
}

async function loadStudentBadges() {
  try {
    const response = await fetch('/api/v1/innovation/badges/history');
    const result = await response.json();
    
    const tbody = document.getElementById('studentBadgesBody');
    const data = result.data || [];
    
    document.getElementById('studentEarned').textContent = result.total_earned_students || '0';
    
    if (data.length === 0) {
      tbody.innerHTML = `<tr><td colspan="4" class="text-center py-4 text-muted opacity-50">Belum ada aktivitas perolehan badge terbaru.</td></tr>`;
      return;
    }
    
    tbody.innerHTML = data.map(item => {
      const earnedAt = item.earned_at ? new Date(item.earned_at).toLocaleDateString('id-ID', {
        day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit'
      }) : '-';
      
      return `
        <tr>
          <td>
            <div class="fw-bold text-white small">${item.siswa?.nama_lengkap || '-'}</div>
            <div class="text-muted" style="font-size: 0.7rem;">NISN: ${item.siswa?.nisn || '-'}</div>
          </td>
          <td>
            <span class="badge bg-label-secondary">${item.siswa?.kelas?.nama || '-'}</span>
          </td>
          <td>
            <div class="d-flex align-items-center gap-2">
              <span class="das-stat-card__icon" style="width: 28px; height: 28px; background: rgba(255, 215, 0, 0.1); color: #ffd700; border-radius: 4px; display: inline-flex; align-items: center; justify-content: center; font-size: 0.90rem;">
                <i class="ti ${item.badge?.icon || 'tabler-award'}"></i>
              </span>
              <div>
                <div class="fw-bold text-white" style="font-size: 0.8rem;">${item.badge?.name || '-'}</div>
                <div class="text-muted" style="font-size: 0.65rem;">${item.badge?.description || '-'}</div>
              </div>
            </div>
          </td>
          <td class="text-muted small">${earnedAt}</td>
        </tr>
      `;
    }).join('');
    
  } catch (e) {
    console.error('Error loading student badges:', e);
  }
}

function openBadgeModal() {
  const modal = new bootstrap.Modal(document.getElementById('badgeModal'));
  modal.show();
}

async function saveBadge() {
  const btn = event.currentTarget;
  btn.disabled = true;
  
  const data = {
    name: document.getElementById('badgeName').value,
    icon: document.getElementById('badgeIcon').value,
    description: document.getElementById('badgeDescription').value,
    badge_type: document.getElementById('badgeType').value,
    requirement_days: document.getElementById('badgeRequirement').value,
    requirement_type: document.getElementById('badgeRequirementType').value
  };
  
  try {
    const response = await fetch('/api/v1/innovation/badges', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
      },
      body: JSON.stringify(data)
    });
    
    if (response.ok) {
      await loadBadges();
      bootstrap.Modal.getInstance(document.getElementById('badgeModal')).hide();
      document.getElementById('badgeForm').reset();
    }
  } catch (e) {
    console.error('Error:', e);
  } finally {
    btn.disabled = false;
  }
}
</script>

<style>
.spin { animation: spin 1s linear infinite; }
@keyframes spin { 100% { transform: rotate(360deg); } }
</style>
@endsection