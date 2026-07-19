@extends('layouts/layoutMaster')

@section('title', 'Kelola Template ID Card')

@section('content')

<div class="set-hero mb-5">
  <div class="set-hero__bg"></div>
  <div class="set-hero__glass"></div>
  <div class="set-hero__grid"></div>
  <div class="set-hero__inner">
    <div class="set-hero__identity">
      <div class="set-hero__icon-wrap">
        <i class="ti tabler-credit-card"></i>
        <div class="set-hero__icon-glow" style="background:rgba(115,103,240,.35);"></div>
      </div>
      <div>
        <div class="set-hero__badge">
          <span class="pulse-dot" style="background:#7367f0;"></span>
          ID Card Templates
        </div>
        <h4 class="set-hero__title text-gradient-purple">Kelola Template ID Card</h4>
        <p class="set-hero__sub">Buat, edit, ekspor, dan impor template kartu identitas untuk Siswa, Guru, dan Staff.</p>
      </div>
    </div>
    <div class="set-hero__breadcrumb glass-card">
      <span class="text-muted small"><i class="ti tabler-home me-1"></i>Dashboard</span>
      <i class="ti tabler-chevron-right text-muted mx-1" style="font-size:0.7rem;"></i>
      <a href="{{ route('admin.cetak-kartu.index') }}" class="text-muted small">Cetak Kartu / ID Card</a>
      <i class="ti tabler-chevron-right text-muted mx-1" style="font-size:0.7rem;"></i>
      <span class="small text-white fw-semibold">Templates</span>
    </div>
  </div>
</div>

@if (session('success'))
  <div class="set-toast mb-4" id="successToast">
    <div class="set-toast__icon"><i class="ti tabler-circle-check"></i></div>
    <div class="set-toast__msg">{{ session('success') }}</div>
    <button type="button" class="set-toast__close" onclick="document.getElementById('successToast').style.display='none'"><i class="ti tabler-x"></i></button>
  </div>
@endif

@if (session('error'))
  <div class="set-toast mb-4 --error" id="errorToast" style="background: rgba(234, 84, 85, 0.12); border: 1px solid rgba(234, 84, 85, 0.25);">
    <div class="set-toast__icon" style="color: #ea5455;"><i class="ti tabler-circle-x"></i></div>
    <div class="set-toast__msg" style="color: #fde8e8;">{{ session('error') }}</div>
    <button type="button" class="set-toast__close" onclick="document.getElementById('errorToast').style.display='none'"><i class="ti tabler-x"></i></button>
  </div>
@endif

@if($errors->any())
  <div class="alert alert-danger mb-4" style="border-radius: 5px;">
    <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
  </div>
@endif

<div class="d-flex gap-2 mb-4 align-items-center flex-wrap justify-content-between">
  <a href="{{ route('admin.cetak-kartu.index') }}" class="btn btn-sm btn-outline-secondary">
    <i class="ti tabler-arrow-left me-1"></i>Kembali ke Cetak Kartu
  </a>
  <div class="d-flex gap-2">
    <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#importTemplateModal" style="border-radius: 5px;">
      <i class="ti tabler-upload me-1"></i>Import Template
    </button>
    <a href="{{ route('admin.id-card-templates.create') }}" class="btn btn-sm btn-primary" style="border-radius: 5px;">
      <i class="ti tabler-plus me-1"></i>Buat Template Baru
    </a>
  </div>
</div>

{{-- Panel Daftar Template --}}
<div class="set-panel mb-5">
  <div class="set-panel__head">
    <div class="set-panel__title-wrap">
      <div class="set-panel__icon --primary"><i class="ti tabler-table"></i></div>
      <div>
        <div class="set-panel__title">Daftar Template Smart Card</div>
        <div class="set-panel__sub">Daftar template kartu identitas digital/fisik yang tersedia untuk dicetak.</div>
      </div>
    </div>
  </div>
  <div class="set-panel__body p-0">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0" style="background: transparent;">
        <thead class="table-dark">
          <tr>
            <th class="px-4 py-3">Nama Template</th>
            <th class="py-3">Tipe</th>
            <th class="py-3">Status</th>
            <th class="py-3">Terakhir Diupdate</th>
            <th class="px-4 py-3 text-end" style="width: 180px;">Aksi</th>
          </tr>
        </thead>
        <tbody>
          @forelse($templates as $template)
            <tr>
              <td class="px-4 py-3"><strong>{{ $template->name }}</strong></td>
              <td class="py-3">
                <span class="badge bg-label-info">{{ ucfirst($template->type) }}</span>
              </td>
              <td class="py-3">
                @if($template->is_active)
                  <span class="badge bg-success">Aktif</span>
                @else
                  <span class="badge bg-secondary">Draft</span>
                @endif
              </td>
              <td class="py-3 text-muted small">{{ $template->updated_at->diffForHumans() }}</td>
              <td class="px-4 py-3 text-end">
                <div class="d-flex gap-2 justify-content-end">
                  <a href="{{ route('admin.id-card-templates.export', $template->id) }}" class="btn btn-sm btn-outline-info" data-bs-toggle="tooltip" data-bs-placement="top" title="Ekspor Template" style="border-radius: 5px;">
                    <i class="ti tabler-download"></i>
                  </a>
                  <a href="{{ route('admin.id-card-templates.edit', $template->id) }}" class="btn btn-sm btn-outline-primary" style="border-radius: 5px;" data-bs-toggle="tooltip" data-bs-placement="top" title="Edit Template">
                    <i class="ti tabler-edit"></i>
                  </a>
                  <form action="{{ route('admin.id-card-templates.destroy', $template->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus template ini?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-outline-danger" style="border-radius: 5px;" data-bs-toggle="tooltip" data-bs-placement="top" title="Hapus Template">
                      <i class="ti tabler-trash"></i>
                    </button>
                  </form>
                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="5" class="text-center py-5 text-muted">
                <i class="ti tabler-alert-circle fs-1 d-block mb-2"></i>
                Belum ada template. Silakan buat template baru.
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    @if ($templates->hasPages())
      <div class="px-4 py-3 border-top" style="border-color: var(--das-border) !important;">
        {{ $templates->links() }}
      </div>
    @endif
  </div>
</div>

<!-- Import Template Modal -->
<div class="modal fade" id="importTemplateModal" tabindex="-1" aria-labelledby="importTemplateModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content text-white" style="background: #1e293b; border: 1px solid var(--das-border); border-radius: 5px;">
      <div class="modal-header border-bottom-0 pb-0">
        <h5 class="modal-title text-white fw-bold" id="importTemplateModalLabel">
          <i class="ti tabler-upload me-2 text-primary"></i>Import Template ID Card
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="{{ route('admin.id-card-templates.import') }}" method="POST" enctype="multipart/form-data" id="importTemplateForm">
        @csrf
        <div class="modal-body py-3">
          <div class="mb-3">
            <label for="templateFile" class="form-label text-muted small fw-bold text-uppercase">Berkas Template JSON <span class="text-danger">*</span></label>
            <input class="form-control text-white bg-dark border-secondary" type="file" id="templateFile" name="template_file" accept=".json" required style="border-radius: 5px;">
            <div class="form-text small text-muted">Hanya berkas konfigurasi template format JSON (.json).</div>
          </div>
          <div class="mb-3">
            <label for="templateName" class="form-label text-muted small fw-bold text-uppercase">Nama Baru (Opsional)</label>
            <input type="text" class="form-control text-white bg-dark border-secondary" id="templateName" name="name" placeholder="Biarkan kosong untuk memakai nama bawaan file" style="border-radius: 5px;">
          </div>
          <div class="form-check form-switch mb-1">
            <input class="form-check-input" type="checkbox" role="switch" id="isActiveSwitch" name="is_active" value="1">
            <label class="form-check-label text-muted small fw-bold text-uppercase" for="isActiveSwitch">Aktifkan Template Ini Langsung</label>
          </div>
        </div>
        <div class="modal-footer border-top-0 d-flex gap-2 justify-content-end pb-4 pt-0">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal" style="border-radius: 5px;">Batal</button>
          <button type="submit" class="btn btn-primary" id="btnImportSubmit" style="border-radius: 5px;">
            <span class="spinner-border spinner-border-sm me-1 d-none" role="status" aria-hidden="true"></span>
            Import
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

@endsection

@section('page-style')
<style>
:root {
  --das-primary:      #7367f0;
  --das-primary-soft: rgba(115,103,240,0.12);
  --das-border:        rgba(255,255,255,0.07);
  --das-radius:        5px;
  --das-radius-sm:     5px;
  --das-surface:       rgba(15, 23, 42, 0.45);
}
.glass-card {
  background: rgba(255,255,255,0.03) !important;
  backdrop-filter: blur(12px) saturate(180%);
  -webkit-backdrop-filter: blur(12px) saturate(180%);
  border: 1px solid var(--das-border) !important;
}
.text-gradient-purple {
  background: linear-gradient(to right, #fff, #b3b0ff);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
}
.set-hero {
  position: relative;
  border-radius: var(--das-radius);
  overflow: hidden;
}
.set-hero__bg {
  position: absolute; inset: 0;
  background: linear-gradient(135deg, #1e1b4b 0%, #312d89 45%, #4338ca 100%);
  z-index: 0;
}
.set-hero__glass {
  position: absolute; inset: 0;
  background: radial-gradient(circle at top right, rgba(115,103,240,0.18), transparent 45%);
  z-index: 1;
}
.set-hero__grid {
  position: absolute; inset: 0; z-index: 1;
  background-image:
    linear-gradient(rgba(255,255,255,0.04) 1px, transparent 1px),
    linear-gradient(90deg, rgba(255,255,255,0.04) 1px, transparent 1px);
  background-size: 40px 40px;
}
.set-hero__inner {
  position: relative; z-index: 2;
  display: flex; align-items: center;
  justify-content: space-between;
  padding: 2rem 2.5rem;
  gap: 1.5rem; flex-wrap: wrap;
}
.set-hero__identity { display: flex; align-items: center; gap: 1.25rem; }
.set-hero__icon-wrap {
  position: relative;
  width: 64px; height: 64px; border-radius: 5px;
  background: rgba(115,103,240,0.2);
  border: 1.5px solid rgba(115,103,240,0.4);
  display: flex; align-items: center; justify-content: center;
  font-size: 1.75rem; color: #a5a2f7; flex-shrink: 0;
  animation: heroIconSpin 20s linear infinite;
}
@keyframes heroIconSpin {
  0%,100% { box-shadow: 0 0 15px rgba(115,103,240,0.2); }
  50%      { box-shadow: 0 0 30px rgba(115,103,240,0.5); }
}
.set-hero__icon-glow {
  position: absolute; inset: -8px;
  background: var(--das-primary);
  filter: blur(18px); opacity: 0.2;
  border-radius: 50%; z-index: -1;
}
.set-hero__badge {
  display: inline-flex; align-items: center; gap: 6px;
  font-size: 0.62rem; font-weight: 700;
  letter-spacing: 1.2px; text-transform: uppercase;
  background: rgba(115,103,240,0.18);
  border: 1px solid rgba(115,103,240,0.3);
  color: #a5a2f7;
  padding: 3px 10px; border-radius: 20px; margin-bottom: 6px;
}
.pulse-dot {
  width: 6px; height: 6px; background: #a5a2f7; border-radius: 50%;
  animation: pulseGlow 1.5s infinite;
}
@keyframes pulseGlow {
  50% { transform: scale(1.3); opacity: 1; }
  100% { transform: scale(0.8); opacity: 0.5; }
}
.set-hero__title {
  font-size: 1.5rem; font-weight: 800;
  margin: 0 0 4px;
}
.set-hero__sub {
  margin: 0; font-size: 0.8rem;
  color: rgba(255,255,255,0.5);
  max-width: 500px;
}
.set-hero__breadcrumb {
  border-radius: var(--das-radius-sm);
  padding: 0.6rem 1rem;
  display: flex; align-items: center;
  background: rgba(0,0,0,0.2) !important;
}
.set-toast {
  display: flex; align-items: center; gap: 0.75rem;
  background: rgba(40,199,111,0.12);
  border: 1px solid rgba(40,199,111,0.25);
  border-radius: var(--das-radius-sm);
  padding: 0.85rem 1.1rem;
}
.set-toast__icon { color: #28c76f; font-size: 1.2rem; flex-shrink: 0; }
.set-toast__msg  { flex: 1; font-size: 0.85rem; color: #d1fae5; }
.set-toast__close {
  background: transparent; border: none; color: #888; cursor: pointer;
  padding: 0; font-size: 0.9rem; transition: color 0.15s;
}
.set-toast__close:hover { color: white; }
.set-panel {
  background: var(--das-surface);
  border: 1px solid var(--das-border);
  border-radius: var(--das-radius);
  overflow: hidden;
  backdrop-filter: blur(6px);
}
.set-panel__head {
  padding: 1.25rem 1.5rem;
  border-bottom: 1px solid var(--das-border);
  background: linear-gradient(90deg, rgba(115,103,240,0.06) 0%, transparent 60%);
}
.set-panel__title-wrap {
  display: flex; align-items: center; gap: 1rem;
}
.set-panel__icon {
  width: 44px; height: 44px; border-radius: var(--das-radius);
  display: flex; align-items: center; justify-content: center;
  font-size: 1.25rem; flex-shrink: 0;
}
.set-panel__icon.--primary { background: var(--das-primary-soft); color: var(--das-primary); }
.set-panel__title  { font-size: 1rem; font-weight: 700; color: #e2e8f0; margin: 0 0 2px; }
.set-panel__sub    { font-size: 0.72rem; color: #64748b; margin: 0; }
.set-panel__body   { padding: 1.5rem; }
</style>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Handle loading state/feedback
        const form = document.getElementById('importTemplateForm');
        const submitBtn = document.getElementById('btnImportSubmit');
        const spinner = submitBtn ? submitBtn.querySelector('.spinner-border') : null;

        if (form && submitBtn) {
            form.addEventListener('submit', function () {
                submitBtn.disabled = true;
                if (spinner) {
                    spinner.classList.remove('d-none');
                }
            });
        }

        // Initialize tooltips
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });
</script>
@endpush
