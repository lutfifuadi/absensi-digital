@extends('layouts/layoutMaster')

@section('title', 'Papan Peringkat')

@section('page-style')
<style>
  body, .layout-page, .content-wrapper {
    background: #0a0e1a !important;
  }

  /* ── Loading Spinner ── */
  .lb-spinner {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 4rem 0;
    gap: 1rem;
  }
  .lb-spinner__ring {
    width: 44px;
    height: 44px;
    border-radius: 50%;
    border: 4px solid rgba(255,255,255,0.04);
    border-top-color: #ffd700;
    animation: lbSpin 0.8s linear infinite;
  }
  @keyframes lbSpin { to { transform: rotate(360deg); } }
  .lb-spinner__text {
    font-size: 0.82rem;
    color: rgba(255,255,255,0.35);
    letter-spacing: 0.5px;
    font-weight: 500;
  }

  /* ── Rank Medals ── */
  .lb-rank {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    border-radius: 50%;
    font-weight: 800;
    font-size: 0.72rem;
  }
  .lb-rank--gold {
    background: linear-gradient(135deg, #ffd700, #f0c75e);
    color: #1a1a2e;
    box-shadow: 0 0 20px rgba(255, 215, 0, 0.35);
  }
  .lb-rank--silver {
    background: linear-gradient(135deg, #e2e8f0, #94a3b8);
    color: #1a1a2e;
    box-shadow: 0 0 16px rgba(148, 163, 184, 0.3);
  }
  .lb-rank--bronze {
    background: linear-gradient(135deg, #cd7f32, #a0522d);
    color: #fff;
    box-shadow: 0 0 16px rgba(205, 127, 50, 0.3);
  }
  .lb-rank--default {
    background: rgba(255,255,255,0.05);
    color: rgba(255,255,255,0.5);
    font-size: 0.78rem;
  }

  /* ── Badge icon group ── */
  .lb-badges {
    display: inline-flex;
    align-items: center;
    gap: 3px;
  }
  .lb-badge-icon {
    width: 24px;
    height: 24px;
    background: rgba(255, 215, 0, 0.08);
    color: #ffd700;
    border-radius: 4px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 0.7rem;
  }

  /* ── Student avatar placeholder ── */
  .lb-avatar {
    width: 34px;
    height: 34px;
    border-radius: 50%;
    background: linear-gradient(135deg, rgba(115,103,240,0.2), rgba(115,103,240,0.05));
    border: 1.5px solid rgba(115,103,240,0.25);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.75rem;
    font-weight: 700;
    color: #a5a2f7;
    flex-shrink: 0;
  }

  /* ── Empty state ── */
  .lb-empty {
    text-align: center;
    padding: 4rem 1rem;
    color: rgba(255,255,255,0.25);
  }
  .lb-empty__icon {
    font-size: 3rem;
    margin-bottom: 0.75rem;
    opacity: 0.5;
  }
  .lb-empty__text {
    font-size: 0.85rem;
    font-weight: 500;
  }

  /* ── Row highlight for current user ── */
  .das-table tbody tr.lb-row--me td {
    background: rgba(115, 103, 240, 0.08) !important;
    border-bottom-color: rgba(115, 103, 240, 0.15);
  }
  .das-table tbody tr.lb-row--me:hover td {
    background: rgba(115, 103, 240, 0.12) !important;
  }

  /* ── Responsive tweaks ── */
  @media (max-width: 576px) {
    .das-table { font-size: 0.72rem; }
    .das-table thead th,
    .das-table tbody td { padding: 0.5rem 0.6rem; }
    .lb-rank { width: 26px; height: 26px; font-size: 0.6rem; }
    .lb-avatar { width: 28px; height: 28px; font-size: 0.6rem; }
    .lb-badge-icon { width: 20px; height: 20px; font-size: 0.6rem; }
  }
</style>
@endsection

@section('content')
<div class="das-hero mb-4">
  <div class="das-hero__bg"></div>
  <div class="das-hero__glass"></div>
  <div class="das-hero__grid-lines"></div>

  <div class="das-hero__inner">
    <div class="das-hero__identity">
      <div class="das-hero__logo-wrapper">
        <div class="das-hero__logo-placeholder" style="width:64px;height:64px;border-radius:5px;display:flex;align-items:center;justify-content:center;background:rgba(255,215,0,0.1);border:2px solid rgba(255,215,0,0.25);">
          <i class="ti tabler-trophy" style="font-size:1.6rem;color:#ffd700;"></i>
        </div>
        <div class="das-hero__logo-glow"></div>
      </div>
      <div class="das-hero__meta">
        <div class="das-hero__badge">
          <span class="pulse-dot"></span>
          Papan Peringkat
        </div>
        <h4 class="das-hero__title text-gradient-gold">Peringkat Siswa</h4>
        <p class="das-hero__subtitle">Semakin tinggi skormu, semakin dekat dengan prestise tertinggi!</p>
      </div>
    </div>
    <div class="das-hero__actions">
      <span class="das-chip --info" id="leaderboardPeriod">
        <i class="ti tabler-refresh me-1"></i> Live — update 30 detik
      </span>
    </div>
  </div>
</div>

<div class="das-panel">
  <div class="das-panel__head">
    <div class="das-panel__title">
      <span class="das-panel__icon-dot --warning"></span>
      Papan Peringkat Siswa
    </div>
    <div class="d-flex align-items-center gap-2">
      <span style="font-size:0.7rem;color:rgba(255,255,255,0.3);" id="lastUpdateLabel">
        Terakhir diperbarui: —
      </span>
    </div>
  </div>
  <div class="table-responsive">
    <table class="das-table">
      <thead>
        <tr>
          <th class="text-center" style="width:60px;">RANK</th>
          <th>NAMA SISWA</th>
          <th style="width:120px;">KELAS</th>
          <th class="text-center" style="width:100px;">HADIR</th>
          <th class="text-center" style="width:80px;">SKOR</th>
          <th class="text-center" style="width:110px;">BADGE</th>
        </tr>
      </thead>
      <tbody id="studentLeaderboardBody">
        <tr>
          <td colspan="6" class="text-center py-5">
            <div class="lb-spinner">
              <div class="lb-spinner__ring"></div>
              <div class="lb-spinner__text">Memuat papan peringkat...</div>
            </div>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</div>
@endsection

@section('page-script')
<script>
const CURRENT_STUDENT_NIS = '{{ $siswa->nis }}';

document.addEventListener('DOMContentLoaded', function() {
  loadStudentLeaderboard();

  // Auto-refresh setiap 30 detik
  setInterval(loadStudentLeaderboard, 30000);
});

function getInitials(name) {
  if (!name) return '?';
  return name.split(' ').map(w => w.charAt(0)).join('').substring(0, 2).toUpperCase();
}

function formatTime(now) {
  const h = String(now.getHours()).padStart(2, '0');
  const m = String(now.getMinutes()).padStart(2, '0');
  const s = String(now.getSeconds()).padStart(2, '0');
  return `${h}:${m}:${s}`;
}

async function loadStudentLeaderboard() {
  const tbody = document.getElementById('studentLeaderboardBody');

  try {
    const response = await fetch('/api/v1/innovation/leaderboard/students?limit=20');
    const result = await response.json();

    const data = result.data || [];

    if (data.length === 0) {
      tbody.innerHTML = `
        <tr>
          <td colspan="6" class="text-center py-5">
            <div class="lb-empty">
              <div class="lb-empty__icon"><i class="ti tabler-trophy-off"></i></div>
              <div class="lb-empty__text">Belum ada data peringkat. Ayo ikuti kegiatan presensi!</div>
            </div>
          </td>
        </tr>
      `;
      document.getElementById('lastUpdateLabel').textContent = 'Terakhir diperbarui: ' + formatTime(new Date());
      return;
    }

    tbody.innerHTML = data.map((item, index) => {
      const rank = index + 1;

      // Kelas badge
      const namaKelas = item.siswa?.kelas?.nama || '-';
      const namaLengkap = item.siswa?.nama_lengkap || 'Siswa';
      const nis = item.siswa?.nis || '-';
      const totalHadir = item.total_present ?? 0;
      const totalKehadiran = item.total_attendance ?? 0;
      const score = item.score ?? 0;

      // Badge icons (max 3)
      const badges = item.siswa?.student_badges || [];
      const badgeHtml = badges.length > 0
        ? badges.slice(0, 3).map(b =>
            `<span class="lb-badge-icon"><i class="ti ${b.badge?.icon || 'tabler-award'}"></i></span>`
          ).join('')
        : '<span style="color:rgba(255,255,255,0.15);font-size:0.65rem;">—</span>';

      // Rank element
      let rankHtml;
      if (index === 0) {
        rankHtml = `<span class="lb-rank lb-rank--gold"><i class="ti tabler-crown" style="font-size:0.9rem;"></i></span>`;
      } else if (index === 1) {
        rankHtml = `<span class="lb-rank lb-rank--silver">2</span>`;
      } else if (index === 2) {
        rankHtml = `<span class="lb-rank lb-rank--bronze">3</span>`;
      } else {
        rankHtml = `<span class="lb-rank lb-rank--default">${rank}</span>`;
      }

      // Score color
      const scoreColor = score > 80 ? '#ffd700' : score > 40 ? '#28c76f' : 'rgba(255,255,255,0.5)';

      const isMe = item.siswa?.nis === CURRENT_STUDENT_NIS;
      const rowClass = isMe ? 'lb-row--me' : '';

      return `
        <tr class="${rowClass}">
          <td class="text-center">${rankHtml}</td>
          <td>
            <div class="d-flex align-items-center gap-2">
              <div class="lb-avatar">${getInitials(namaLengkap)}</div>
              <div>
                <div class="fw-bold text-white" style="font-size:0.82rem;">${namaLengkap}</div>
                <div class="text-muted" style="font-size:0.65rem;">NIS: ${nis}</div>
              </div>
            </div>
          </td>
          <td>
            <span class="badge bg-label-secondary" style="font-size:0.65rem;background:rgba(255,255,255,0.04) !important;color:rgba(255,255,255,0.5) !important;border:1px solid rgba(255,255,255,0.06);font-weight:600;">
              ${namaKelas}
            </span>
          </td>
          <td class="text-center fw-semibold" style="color:var(--das-success);font-size:0.82rem;">
            ${totalHadir}<span style="color:rgba(255,255,255,0.2);font-weight:400;">/${totalKehadiran}</span>
          </td>
          <td class="text-center">
            <span class="fw-bold" style="color:${scoreColor};font-size:0.95rem;">${score}</span>
          </td>
          <td class="text-center">
            <div class="lb-badges">
              ${badgeHtml}
            </div>
          </td>
        </tr>
      `;
    }).join('');

    document.getElementById('lastUpdateLabel').textContent = 'Terakhir diperbarui: ' + formatTime(new Date());

  } catch (e) {
    console.error('Gagal memuat leaderboard:', e);
    tbody.innerHTML = `
      <tr>
        <td colspan="6" class="text-center py-5">
          <div class="lb-empty">
            <div class="lb-empty__icon"><i class="ti tabler-cloud-off"></i></div>
            <div class="lb-empty__text" style="color:rgba(234,84,85,0.6);">Gagal memuat data. Coba refresh halaman.</div>
          </div>
        </td>
      </tr>
    `;
  }
}
</script>
@endsection
