@extends('layouts/layoutMaster')

@section('title', 'Activity Log / Audit Trail')

@section('page-style')
  <style>
    .log-row-hover {
      transition: background 0.15s ease;
    }

    .log-row-hover:hover {
      background: rgba(255, 255, 255, 0.04) !important;
    }

    .action-btn {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      width: 32px;
      height: 32px;
      border-radius: 8px;
      transition: all 0.2s ease;
      border: none;
      background: rgba(255, 255, 255, 0.05);
      color: inherit;
    }

    .action-btn:hover {
      transform: translateY(-2px);
      background: rgba(255, 255, 255, 0.1);
    }
  </style>
@endsection

@section('content')
  {{-- HERO HEADER --}}
  <div class="row mb-4">
    <div class="col-12">
      <div class="card border-0 text-white overflow-hidden shadow-lg"
        style="background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%); border-radius: 12px;">
        <div class="card-body p-4">
          <div class="row align-items-center">
            <div class="col-md-7">
              <div class="d-flex align-items-center gap-3">
                <div class="rounded d-flex align-items-center justify-content-center shadow-sm"
                  style="width:52px;height:52px;border-radius:12px !important;background:rgba(0,207,232,0.2);border:1px solid rgba(0,207,232,0.4);">
                  <i class="ti tabler-history text-info fs-3"></i>
                </div>
                <div>
                  <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-1" style="font-size:0.72rem;opacity:0.6;">
                      <li class="breadcrumb-item">
                        <span class="text-white opacity-50">Sistem</span>
                      </li>
                      <li class="breadcrumb-item active text-white">Activity Log</li>
                    </ol>
                  </nav>
                  <h4 class="mb-0 text-white fw-bold" style="letter-spacing:-0.5px;">Activity Log</h4>
                  <p class="mb-0 text-white opacity-60 small">Audit trail semua aktivitas pengguna di sistem.
                  </p>
                </div>
              </div>
            </div>
            <div class="col-md-5 text-md-end mt-3 mt-md-0">
              <div class="d-flex gap-2 justify-content-md-end flex-wrap">
                <a href="{{ route('admin.activity-log.index') }}" class="btn btn-label-secondary fw-semibold">
                  <i class="ti tabler-refresh me-1"></i> Reset Filter
                </a>
                <button type="button" class="btn btn-label-danger fw-semibold" data-bs-toggle="modal"
                  data-bs-target="#modalDeleteAll">
                  <i class="ti tabler-trash me-1"></i> Hapus Semua Log
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- FLASH MESSAGES --}}
  @if (session('success'))
    <div class="alert alert-success alert-dismissible d-flex align-items-center gap-2 mb-4 border-0 shadow-sm"
      role="alert" style="border-radius:8px;">
      <i class="ti tabler-circle-check fs-5"></i>
      <span>{{ session('success') }}</span>
      <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
    </div>
  @endif

  {{-- Stats Row --}}
  @php
    $totalLogs = $logs->total();
    $todayLogs = \App\Models\ActivityLog::whereDate('created_at', today())->count();
    $loginCount = \App\Models\ActivityLog::where('action', 'login')->count();
    $deleteCount = \App\Models\ActivityLog::where('action', 'delete')->count();
  @endphp
  <div class="row gy-3 mb-4">
    @foreach ([['Total Log', $totalLogs, 'tabler-list', 'primary'], ['Log Hari Ini', $todayLogs, 'tabler-calendar-today', 'success'], ['Login', $loginCount, 'tabler-login', 'info'], ['Hapus Data', $deleteCount, 'tabler-trash', 'danger']] as [$label, $val, $icon, $color])
      <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm"
          style="background: rgba(255,255,255,0.04); border:1px solid rgba(255,255,255,0.08) !important;">
          <div class="card-body d-flex align-items-center gap-3 py-3">
            <div class="avatar bg-label-{{ $color }}">
              <span class="avatar-initial rounded"><i class="ti {{ $icon }}"></i></span>
            </div>
            <div>
              <div class="h5 mb-0 fw-bold text-white">{{ number_format($val) }}</div>
              <div class="small text-white-50" style="font-size:0.75rem;">{{ $label }}</div>
            </div>
          </div>
        </div>
      </div>
    @endforeach
  </div>

  {{-- Filter Card --}}
  <div class="card border-0 shadow-sm mb-4"
    style="background: rgba(255,255,255,0.04); border:1px solid rgba(255,255,255,0.08) !important;">
    <div class="card-header py-3 d-flex align-items-center gap-2 border-bottom"
      style="background: transparent; border-color: rgba(255,255,255,0.08) !important;">
      <i class="ti tabler-filter text-info"></i>
      <h6 class="card-title mb-0 text-white">Filter Log</h6>
    </div>
    <div class="card-body mt-2">
      <form method="GET" action="{{ route('admin.activity-log.index') }}">
        <div class="row g-3 align-items-end">
          <div class="col-md-3">
            <label class="form-label text-white-50 small fw-bold">User</label>
            <select name="user_id" class="form-select form-select-sm bg-dark text-white border-secondary">
              <option value="">— Semua User —</option>
              @foreach ($users as $u)
                <option value="{{ $u->id }}" @selected(request('user_id') == $u->id)>{{ $u->name }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-md-2">
            <label class="form-label text-white-50 small fw-bold">Modul</label>
            <select name="module" class="form-select form-select-sm bg-dark text-white border-secondary">
              <option value="">— Semua —</option>
              @foreach ($modules as $mod)
                <option value="{{ $mod }}" @selected(request('module') === $mod)>{{ ucfirst($mod) }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-md-2">
            <label class="form-label text-white-50 small fw-bold">Aksi</label>
            <select name="action" class="form-select form-select-sm bg-dark text-white border-secondary">
              <option value="">— Semua —</option>
              @foreach ($actions as $act)
                <option value="{{ $act }}" @selected(request('action') === $act)>{{ ucfirst($act) }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-md-2">
            <label class="form-label text-white-50 small fw-bold">Dari</label>
            <input type="date" name="date_from" class="form-control form-control-sm bg-dark text-white border-secondary"
              value="{{ request('date_from') }}">
          </div>
          <div class="col-md-2">
            <label class="form-label text-white-50 small fw-bold">Sampai</label>
            <input type="date" name="date_to" class="form-control form-control-sm bg-dark text-white border-secondary"
              value="{{ request('date_to') }}">
          </div>
          <div class="col-md-1">
            <button type="submit" class="btn btn-primary btn-sm w-100">
              <i class="ti tabler-search"></i>
            </button>
          </div>
        </div>
      </form>
    </div>
  </div>

  {{-- Log Table --}}
  <div class="card border-0 shadow-sm"
    style="background: rgba(255,255,255,0.04); border:1px solid rgba(255,255,255,0.08) !important;">
    <div class="card-header py-3 d-flex align-items-center justify-content-between border-bottom"
      style="background: transparent; border-color: rgba(255,255,255,0.08) !important;">
      <h6 class="card-title mb-0 d-flex align-items-center gap-2 text-white">
        <i class="ti tabler-file-text text-info"></i> Daftar Log
      </h6>
      <span class="badge bg-label-info">{{ number_format($logs->total()) }} Entri</span>
    </div>
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0" style="color: inherit;">
        <thead
          style="background:rgba(255,255,255,0.04); font-size:0.7rem; text-transform:uppercase; letter-spacing:0.8px; opacity:0.7;">
          <tr>
            <th class="ps-4" width="160">Waktu</th>
            <th>Pengguna</th>
            <th width="110" class="text-center">Modul</th>
            <th width="100" class="text-center">Aksi</th>
            <th>Deskripsi</th>
            <th width="120">IP Address</th>
            <th width="80" class="text-center">Detail</th>
          </tr>
        </thead>
        <tbody>
          @forelse ($logs as $log)
            @php
              $actionColors = [
                  'login' => 'info',
                  'logout' => 'secondary',
                  'create' => 'success',
                  'update' => 'warning',
                  'delete' => 'danger',
                  'scan' => 'primary',
                  'export' => 'dark',
                  'approve' => 'success',
                  'reject' => 'danger',
              ];
              $color = $actionColors[$log->action] ?? 'secondary';
            @endphp
            <tr class="log-row-hover">
              <td class="ps-4">
                <div class="small fw-bold text-white">{{ $log->created_at?->format('d M Y') }}</div>
                <div class="small text-white-50 font-monospace" style="font-size: 0.7rem;">{{ $log->created_at?->format('H:i:s') }}</div>
              </td>
              <td>
                @if ($log->user)
                  <div class="d-flex align-items-center gap-2">
                    <img
                      src="https://ui-avatars.com/api/?name={{ urlencode($log->user->name) }}&background=7367f0&color=fff"
                      class="rounded-circle" width="28">
                    <div>
                      <div class="small fw-bold text-white">{{ $log->user->name }}</div>
                      <div class="text-white-50" style="font-size: 0.65rem;">{{ $log->user->email }}</div>
                    </div>
                  </div>
                @else
                  <span class="text-white-50 small">— System —</span>
                @endif
              </td>
              <td class="text-center">
                <span class="badge bg-label-primary px-2 py-1 small rounded-pill">{{ ucfirst($log->module ?? '—') }}</span>
              </td>
              <td class="text-center">
                <span class="badge bg-{{ $color }} px-2 py-1 small rounded-pill">{{ ucfirst($log->action) }}</span>
              </td>
              <td>
                <span class="text-white-50" style="font-size: 0.85rem;">{{ \Illuminate\Support\Str::limit($log->description, 80) }}</span>
              </td>
              <td>
                <span class="small font-monospace text-white-50">{{ $log->ip_address }}</span>
              </td>
              <td class="text-center">
                @if ($log->old_data || $log->new_data)
                  <button class="action-btn text-info btn-detail" data-id="{{ $log->id }}" title="Lihat Detail">
                    <i class="ti tabler-eye fs-5"></i>
                  </button>
                @else
                  <span class="text-white-50">—</span>
                @endif
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="7" class="text-center py-5">
                <div class="d-flex flex-column align-items-center gap-2 opacity-50">
                  <i class="ti tabler-database-off" style="font-size:2.5rem;"></i>
                  <span class="small">Tidak ada log sesuai filter.</span>
                </div>
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
    @if ($logs->hasPages())
      <div class="card-footer py-3 d-flex justify-content-center border-top"
        style="border-color: rgba(255,255,255,0.08) !important; background: transparent;">
        {{ $logs->links() }}
      </div>
    @endif
  </div>

  {{-- Modal Detail Log --}}
  <div class="modal fade" id="modalDetail" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
      <div class="modal-content border-0 shadow-lg" style="background: #1e1e2d;">
        <div class="modal-header border-bottom" style="border-color: rgba(255,255,255,0.08) !important;">
          <h5 class="modal-title text-white">
            <i class="ti tabler-git-compare text-info me-2"></i> Detail Perubahan Data
          </h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body p-0">
          <div class="row g-0">
            <div class="col-md-6 border-end" style="border-color: rgba(255,255,255,0.08) !important;">
              <div class="p-3 border-bottom" style="background: rgba(0,0,0,0.1); border-color: rgba(255,255,255,0.08) !important;">
                <h6 class="mb-0 text-danger small fw-bold">
                  <i class="ti tabler-minus me-1"></i> DATA SEBELUMNYA
                </h6>
              </div>
              <pre id="old-data-content" class="p-4 mb-0 small text-white-50"
                   style="max-height: 400px; overflow-y: auto; background: transparent; white-space: pre-wrap; font-family: 'Fira Code', monospace;"></pre>
            </div>
            <div class="col-md-6">
              <div class="p-3 border-bottom" style="background: rgba(0,0,0,0.1); border-color: rgba(255,255,255,0.08) !important;">
                <h6 class="mb-0 text-success small fw-bold">
                  <i class="ti tabler-plus me-1"></i> DATA SESUDAHNYA
                </h6>
              </div>
              <pre id="new-data-content" class="p-4 mb-0 small text-white-50"
                   style="max-height: 400px; overflow-y: auto; background: transparent; white-space: pre-wrap; font-family: 'Fira Code', monospace;"></pre>
            </div>
          </div>
        </div>
        <div class="modal-footer border-top" style="border-color: rgba(255,255,255,0.08) !important;">
          <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Tutup</button>
        </div>
      </div>
    </div>
  </div>

  {{-- Modal Hapus Semua Log --}}
  <div class="modal fade" id="modalDeleteAll" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content border-0 shadow-lg" style="background: #1e1e2d;">
        <div class="modal-header border-0 pb-0">
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body text-center pt-0">
          <div
            class="rounded-circle d-flex align-items-center justify-content-center mx-auto mb-4 shadow-sm shadow-danger"
            style="width:80px; height:80px; background: rgba(234,84,85,0.1); border: 2px solid rgba(234,84,85,0.2);">
            <i class="ti tabler-trash-x text-danger fs-1"></i>
          </div>
          <h4 class="mb-2 text-white">Kosongkan Semua Log?</h4>
          <p class="text-white-50 mb-0">Tindakan ini akan menghapus <strong>seluruh data riwayat aktivitas</strong> secara
            permanen dan tidak dapat dibatalkan.</p>
        </div>
        <div class="modal-footer justify-content-center border-0 pb-4 mt-3">
          <button type="button" class="btn btn-label-secondary border-0 px-4" data-bs-dismiss="modal">Batal</button>
          <form action="{{ route('admin.activity-log.destroy-all') }}" method="POST">
            @csrf @method('DELETE')
            <button type="submit" class="btn btn-danger px-4 shadow-sm">Ya, Hapus Semua</button>
          </form>
        </div>
      </div>
    </div>
  </div>
@endsection

@section('page-script')
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // Handler Detail Log
      document.querySelectorAll('.btn-detail').forEach(btn => {
        btn.addEventListener('click', async function() {
          const id = this.dataset.id;
          const originalContent = this.innerHTML;
          this.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
          this.disabled = true;

          try {
            const resp = await fetch(`/admin/activity-log/${id}`);
            const data = await resp.json();
            document.getElementById('old-data-content').textContent =
              data.old_data ? JSON.stringify(data.old_data, null, 2) : '— Tidak ada data —';
            document.getElementById('new-data-content').textContent =
              data.new_data ? JSON.stringify(data.new_data, null, 2) : '— Tidak ada data —';
            const modal = new bootstrap.Modal(document.getElementById('modalDetail'));
            modal.show();
          } catch (e) {
            console.error(e);
            alert('Gagal mengambil detail log.');
          } finally {
            this.innerHTML = originalContent;
            this.disabled = false;
          }
        });
      });
    });
  </script>
@endsection

