@extends('layouts/layoutMaster')

@section('title', 'Kelola Keyword Autoreply')

@section('content')

<div class="set-hero mb-5">
  <div class="set-hero__bg"></div>
  <div class="set-hero__glass"></div>
  <div class="set-hero__grid"></div>
  <div class="set-hero__inner">
    <div class="set-hero__identity">
      <div class="set-hero__icon-wrap">
        <i class="ti tabler-key"></i>
        <div class="set-hero__icon-glow" style="background:rgba(115,103,240,.35);"></div>
      </div>
      <div>
        <div class="set-hero__badge">
          <span class="pulse-dot" style="background:#7367f0;"></span>
          Autoreply Keywords
        </div>
        <h4 class="set-hero__title text-gradient-gold">Kelola Keyword Autoreply</h4>
        <p class="set-hero__sub">Kelola kata kunci pesan masuk WhatsApp dan template jawaban otomatisnya.</p>
      </div>
    </div>
    <div class="set-hero__breadcrumb glass-card">
      <span class="text-muted small"><i class="ti tabler-home me-1"></i>Dashboard</span>
      <i class="ti tabler-chevron-right text-muted mx-1" style="font-size:0.7rem;"></i>
      <a href="{{ route('admin.wa-gateway.index') }}" class="text-muted small">WA Gateway</a>
      <i class="ti tabler-chevron-right text-muted mx-1" style="font-size:0.7rem;"></i>
      <span class="small text-white fw-semibold">Keywords</span>
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

@if($errors->any())
  <div class="alert alert-danger mb-4" style="border-radius: 5px;">
    <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
  </div>
@endif

<div class="d-flex gap-2 mb-4 align-items-center flex-wrap justify-content-between">
  <a href="{{ route('admin.wa-gateway.index') }}" class="btn btn-sm btn-outline-secondary">
    <i class="ti tabler-arrow-left me-1"></i>Kembali ke WA Gateway
  </a>
  <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modalAddKeyword">
    <i class="ti tabler-plus me-1"></i>Tambah Keyword
  </button>
</div>

{{-- Panel Daftar Keyword --}}
<div class="set-panel mb-5">
  <div class="set-panel__head">
    <div class="set-panel__title-wrap">
      <div class="set-panel__icon --primary"><i class="ti tabler-table"></i></div>
      <div>
        <div class="set-panel__title">Daftar Kata Kunci (Keywords)</div>
        <div class="set-panel__sub">Semua pola pesan masuk yang akan memicu balasan otomatis dari sistem.</div>
      </div>
    </div>
  </div>
  <div class="set-panel__body p-0">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0" style="background: transparent;">
        <thead class="table-dark">
          <tr>
            <th class="px-4 py-3" style="width: 50px;">No</th>
            <th class="py-3">Keyword</th>
            <th class="py-3">Tipe</th>
            <th class="py-3">Validasi Nomor</th>
            <th class="py-3">Template Jawaban</th>
            <th class="py-3 text-center" style="width: 100px;">Status</th>
            <th class="px-4 py-3 text-end" style="width: 150px;">Aksi</th>
          </tr>
        </thead>
        <tbody>
          @forelse ($keywords as $index => $kw)
            <tr>
              <td class="px-4 py-3">{{ $keywords->firstItem() + $index }}</td>
              <td class="py-3"><code class="fs-6 px-2 py-1 bg-black text-white rounded fw-semibold">{{ $kw->keyword }}</code></td>
              <td class="py-3">
                <span class="badge {{ $kw->match_type === 'Exact' ? 'bg-info' : 'bg-primary' }}">
                  {{ $kw->match_type }}
                </span>
              </td>
              <td class="py-3">
                <span class="badge {{ $kw->is_validation_required ? 'bg-danger' : 'bg-secondary' }}">
                  {{ $kw->is_validation_required ? 'Wajib' : 'Tidak' }}
                </span>
              </td>
              <td class="py-3">
                <span class="text-muted small">{{ $kw->template->type ?? $kw->notification_template_type }}</span>
                <div class="small fw-bold text-truncate" style="max-width: 250px;">
                  {{ $kw->template ? \Illuminate\Support\Str::limit(strip_tags($kw->template->content), 50) : '-' }}
                </div>
              </td>
              <td class="py-3 text-center">
                <span class="badge {{ $kw->is_active ? 'bg-success' : 'bg-secondary' }}">
                  {{ $kw->is_active ? 'Aktif' : 'Nonaktif' }}
                </span>
              </td>
              <td class="px-4 py-3 text-end">
                <button type="button" class="btn btn-sm btn-outline-warning me-1" 
                        data-bs-toggle="modal" data-bs-target="#modalEditKeyword"
                        data-id="{{ $kw->id }}"
                        data-keyword="{{ $kw->keyword }}"
                        data-match_type="{{ $kw->match_type }}"
                        data-is_validation_required="{{ $kw->is_validation_required ? 1 : 0 }}"
                        data-is_active="{{ $kw->is_active ? 1 : 0 }}"
                        data-template="{{ $kw->notification_template_type }}"
                        style="border-radius: 5px;">
                  <i class="ti tabler-edit"></i>
                </button>
                <form action="{{ route('admin.wa-gateway.keywords.destroy', $kw->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus keyword ini?')">
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="btn btn-sm btn-outline-danger" style="border-radius: 5px;">
                    <i class="ti tabler-trash"></i>
                  </button>
                </form>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="7" class="text-center py-5 text-muted">
                <i class="ti tabler-alert-circle fs-1 d-block mb-2"></i>
                Belum ada keyword autoreply yang didaftarkan.
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    @if ($keywords->hasPages())
      <div class="px-4 py-3 border-top" style="border-color: var(--das-border) !important;">
        {{ $keywords->links() }}
      </div>
    @endif
  </div>
</div>

{{-- MODAL TAMBAH KEYWORD --}}
<div class="modal fade" id="modalAddKeyword" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content text-white" style="background: #1e293b; border: 1px solid var(--das-border); border-radius: 5px;">
      <div class="modal-header border-bottom-0">
        <h5 class="modal-title text-white fw-bold"><i class="ti tabler-plus me-2 text-primary"></i>Tambah Keyword Autoreply</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="{{ route('admin.wa-gateway.keywords.store') }}" method="POST">
        @csrf
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label text-muted small fw-bold text-uppercase">Kata Kunci / Keyword <span class="text-danger">*</span></label>
            <input type="text" name="keyword" class="form-control text-white bg-dark border-secondary" placeholder="Contoh: absen, rekap, bantuan" required style="border-radius: 5px;">
          </div>
          <div class="mb-3">
            <label class="form-label text-muted small fw-bold text-uppercase">Tipe Kecocokan <span class="text-danger">*</span></label>
            <select name="match_type" class="form-select text-white bg-dark border-secondary" required style="border-radius: 5px;">
              <option value="Exact">Exact (Sama persis secara keseluruhan)</option>
              <option value="Contains">Contains (Mengandung kata kunci ini)</option>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label text-muted small fw-bold text-uppercase">Validasi Nomor WA <span class="text-danger">*</span></label>
            <select name="is_validation_required" class="form-select text-white bg-dark border-secondary" required style="border-radius: 5px;">
              <option value="1">Wajib Terdaftar (Nomor pengirim harus ada di data siswa/ortu/guru)</option>
              <option value="0">Tidak Wajib (Bisa dibalas oleh siapa saja)</option>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label text-muted small fw-bold text-uppercase">Template Notifikasi Jawaban <span class="text-danger">*</span></label>
            <select name="notification_template_type" class="form-select text-white bg-dark border-secondary" required style="border-radius: 5px;">
              <option value="" disabled selected>Pilih Template...</option>
              @foreach ($templates as $t)
                <option value="{{ $t->type }}">{{ \App\Models\NotificationTemplate::TYPES[$t->type] ?? $t->type }}</option>
              @endforeach
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label text-muted small fw-bold text-uppercase">Status <span class="text-danger">*</span></label>
            <select name="is_active" class="form-select text-white bg-dark border-secondary" required style="border-radius: 5px;">
              <option value="1">Aktif</option>
              <option value="0">Nonaktif</option>
            </select>
          </div>
        </div>
        <div class="modal-footer border-top-0 d-flex gap-2 justify-content-end">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal" style="border-radius: 5px;">Batal</button>
          <button type="submit" class="btn btn-primary" style="border-radius: 5px;">Simpan Keyword</button>
        </div>
      </form>
    </div>
  </div>
</div>

{{-- MODAL EDIT KEYWORD --}}
<div class="modal fade" id="modalEditKeyword" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content text-white" style="background: #1e293b; border: 1px solid var(--das-border); border-radius: 5px;">
      <div class="modal-header border-bottom-0">
        <h5 class="modal-title text-white fw-bold"><i class="ti tabler-edit me-2 text-warning"></i>Edit Keyword Autoreply</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="" method="POST" id="formEditKeyword">
        @csrf
        @method('PUT')
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label text-muted small fw-bold text-uppercase">Kata Kunci / Keyword <span class="text-danger">*</span></label>
            <input type="text" name="keyword" id="edit_keyword" class="form-control text-white bg-dark border-secondary" required style="border-radius: 5px;">
          </div>
          <div class="mb-3">
            <label class="form-label text-muted small fw-bold text-uppercase">Tipe Kecocokan <span class="text-danger">*</span></label>
            <select name="match_type" id="edit_match_type" class="form-select text-white bg-dark border-secondary" required style="border-radius: 5px;">
              <option value="Exact">Exact (Sama persis secara keseluruhan)</option>
              <option value="Contains">Contains (Mengandung kata kunci ini)</option>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label text-muted small fw-bold text-uppercase">Validasi Nomor WA <span class="text-danger">*</span></label>
            <select name="is_validation_required" id="edit_is_validation_required" class="form-select text-white bg-dark border-secondary" required style="border-radius: 5px;">
              <option value="1">Wajib Terdaftar (Nomor pengirim harus ada di data siswa/ortu/guru)</option>
              <option value="0">Tidak Wajib (Bisa dibalas oleh siapa saja)</option>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label text-muted small fw-bold text-uppercase">Template Notifikasi Jawaban <span class="text-danger">*</span></label>
            <select name="notification_template_type" id="edit_notification_template_type" class="form-select text-white bg-dark border-secondary" required style="border-radius: 5px;">
              @foreach ($templates as $t)
                <option value="{{ $t->type }}">{{ \App\Models\NotificationTemplate::TYPES[$t->type] ?? $t->type }}</option>
              @endforeach
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label text-muted small fw-bold text-uppercase">Status <span class="text-danger">*</span></label>
            <select name="is_active" id="edit_is_active" class="form-select text-white bg-dark border-secondary" required style="border-radius: 5px;">
              <option value="1">Aktif</option>
              <option value="0">Nonaktif</option>
            </select>
          </div>
        </div>
        <div class="modal-footer border-top-0 d-flex gap-2 justify-content-end">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal" style="border-radius: 5px;">Batal</button>
          <button type="submit" class="btn btn-warning" style="border-radius: 5px;">Update Keyword</button>
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
.text-gradient-gold {
  background: linear-gradient(to right, #fff, #ffd700);
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
document.addEventListener('DOMContentLoaded', function() {
  const modalEditKeyword = document.getElementById('modalEditKeyword');
  if (modalEditKeyword) {
    modalEditKeyword.addEventListener('show.bs.modal', function(event) {
      const button = event.relatedTarget;
      const id = button.getAttribute('data-id');
      const keyword = button.getAttribute('data-keyword');
      const matchType = button.getAttribute('data-match_type');
      const isValidationRequired = button.getAttribute('data-is_validation_required');
      const isActive = button.getAttribute('data-is_active');
      const template = button.getAttribute('data-template');

      const form = document.getElementById('formEditKeyword');
      form.setAttribute('action', `{{ url('admin/wa-gateway/keywords') }}/${id}`);

      document.getElementById('edit_keyword').value = keyword;
      document.getElementById('edit_match_type').value = matchType;
      document.getElementById('edit_is_validation_required').value = isValidationRequired;
      document.getElementById('edit_is_active').value = isActive;
      document.getElementById('edit_notification_template_type').value = template;
    });
  }
});
</script>
@endpush
