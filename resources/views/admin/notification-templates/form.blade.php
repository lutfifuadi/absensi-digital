@extends('layouts/layoutMaster')

@section('title', $isEdit ? 'Edit Redaksi Notifikasi' : 'Buat Redaksi Notifikasi')

@section('page-style')
<style>
  :root {
    --das-primary: #7367f0;
    --das-primary-soft: rgba(115, 103, 240, 0.12);
    --das-info: #00cfe8;
    --das-info-soft: rgba(0, 207, 232, 0.12);
    --das-warning: #ff9f43;
    --das-surface: rgba(15, 23, 42, 0.4);
    --das-surface-hover: rgba(30, 41, 59, 0.6);
    --das-border: rgba(255, 255, 255, 0.06);
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

  /* BUTTONS */
  .das-btn { display: inline-flex; align-items: center; gap: 5px; font-size: .75rem; font-weight: 600; padding: .5rem 1rem; border-radius: 5px; border: 1px solid transparent; cursor: pointer; transition: all .18s ease; text-decoration: none; white-space: nowrap; }
  .das-btn--primary { background: var(--das-primary); color: white !important; border-color: var(--das-primary); }
  .das-btn--primary:hover { background: #6259e8; transform: translateY(-2px); }
  .das-btn--ghost { background: transparent; border-color: var(--das-border); color: #999 !important; }
  .das-btn--ghost:hover { background: var(--das-surface-hover); color: white !important; }

  /* PANEL */
  .das-panel { background: var(--das-surface); border: 1px solid var(--das-border); border-radius: var(--das-radius); overflow: hidden; backdrop-filter: blur(6px); }
  .das-panel__head { display: flex; align-items: center; padding: .9rem 1.25rem; border-bottom: 1px solid var(--das-border); }
  .das-panel__title { font-size: .82rem; font-weight: 700; text-transform: uppercase; letter-spacing: .6px; display: flex; align-items: center; gap: 8px; color: #ccc; }
  .das-panel__icon-dot { width: 8px; height: 8px; border-radius: 50%; background: var(--das-info); box-shadow: 0 0 6px var(--das-info); }
  .das-panel__body { padding: 1.5rem; }

  /* FORM ELEMENTS */
  .das-form-label { font-size: .72rem; font-weight: 700; text-transform: uppercase; letter-spacing: .6px; color: #888; margin-bottom: .5rem; display: block; }
  .das-form-control { background: rgba(255,255,255,.04) !important; border: 1px solid var(--das-border) !important; border-radius: var(--das-radius) !important; color: #e0e0e0 !important; font-size: .85rem !important; transition: border-color .2s, background .2s; }
  .das-form-control:focus { background: rgba(255,255,255,.07) !important; border-color: rgba(115,103,240,.5) !important; outline: none !important; box-shadow: none !important; color: white !important; }
  .das-form-control option { background: #1a1a2e; color: #ccc; }
  .das-form-text { font-size: .75rem; color: #555; margin-top: .4rem; }

  /* VARIABLE CHIPS */
  .var-chip { display: inline-flex; align-items: center; gap: 4px; background: var(--das-primary-soft); border: 1px solid rgba(115,103,240,.2); border-radius: 4px; padding: 2px 8px; font-size: .72rem; color: var(--das-primary); font-family: monospace; cursor: pointer; transition: all .15s; user-select: all; }
  .var-chip:hover { background: rgba(115,103,240,.2); border-color: rgba(115,103,240,.4); color: #c0bcff; }

  /* INFO PANEL */
  .das-info-panel { background: rgba(0,207,232,.05); border: 1px solid rgba(0,207,232,.12); border-radius: var(--das-radius); padding: 1rem; }

  @keyframes slideInUp { from { transform: translateY(20px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
  .slide-in-up { animation: slideInUp .5s ease-out; }
</style>
@endsection

@section('content')

  {{-- ── HERO HEADER ─────────────────────────────────── --}}
  <div class="das-hero slide-in-up">
    <div class="das-hero__bg"></div>
    <div class="das-hero__glass"></div>
    <div class="das-hero__grid-lines"></div>
    <div class="das-hero__inner">
      <div class="das-hero__identity">
        <div class="das-hero__icon">
          <i class="ti tabler-{{ $isEdit ? 'edit' : 'message-plus' }}"></i>
        </div>
        <div>
          <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1" style="font-size:.65rem;text-transform:uppercase;letter-spacing:1px;opacity:.6;">
              <li class="breadcrumb-item"><a href="{{ route('admin.notification-templates.index') }}" class="text-white text-decoration-none">Redaksi Notifikasi</a></li>
              <li class="breadcrumb-item active text-white opacity-100">{{ $isEdit ? 'Edit' : 'Buat Baru' }}</li>
            </ol>
          </nav>
          <h4 class="das-hero__title">{{ $isEdit ? 'Edit Redaksi' : 'Buat Redaksi Baru' }}</h4>
          <p class="das-hero__welcome">{{ $isEdit ? 'Perbarui konten dan tipe notifikasi yang sudah ada.' : 'Tambahkan template pesan WhatsApp otomatis baru.' }}</p>
        </div>
      </div>
      <div>
        <a href="{{ route('admin.notification-templates.index') }}" class="das-btn das-btn--ghost">
          <i class="ti tabler-arrow-left me-1"></i> Kembali
        </a>
      </div>
    </div>
  </div>

  {{-- ── FORM + SIDEBAR ───────────────────────────────── --}}
  <div class="row g-4 slide-in-up">

    {{-- FORM --}}
    <div class="col-md-8">
      <div class="das-panel">
        <div class="das-panel__head">
          <div class="das-panel__title">
            <span class="das-panel__icon-dot"></span>
            Konten Redaksi
          </div>
        </div>
        <div class="das-panel__body">
          <form action="{{ $isEdit ? route('admin.notification-templates.update', $template->id) : route('admin.notification-templates.store') }}" method="POST">
            @csrf
            @if($isEdit) @method('PUT') @endif

            <div class="mb-4">
              <label for="type" class="das-form-label">Tipe Notifikasi <span class="text-danger">*</span></label>
              <select id="type" name="type" class="form-control das-form-control @error('type') is-invalid @enderror" required>
                <option value="" disabled {{ !old('type', $template->type) ? 'selected' : '' }}>— Pilih Tipe Notifikasi —</option>
                @foreach($types as $key => $label)
                  <option value="{{ $key }}" {{ old('type', $template->type) == $key ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
              </select>
              @error('type') <div class="invalid-feedback">{{ $message }}</div> @enderror
              <div class="das-form-text">Pilih jenis peristiwa yang akan memicu pengiriman pesan ini.</div>
            </div>

            <div class="mb-5">
              <label for="content" class="das-form-label">Konten Pesan WhatsApp <span class="text-danger">*</span></label>
              <textarea id="content" name="content" rows="12"
                        class="form-control das-form-control @error('content') is-invalid @enderror"
                        placeholder="Tuliskan redaksi notifikasi di sini..."
                        required>{{ old('content', $template->content) }}</textarea>
              @error('content') <div class="invalid-feedback">{{ $message }}</div> @enderror
              <div class="das-form-text">Mendukung format Markdown WhatsApp: *tebal*, _miring_, ~coret~.</div>
            </div>

            <div class="d-flex align-items-center gap-2 pt-3 border-top" style="border-color:var(--das-border)!important;">
              <button type="submit" class="das-btn das-btn--primary px-4">
                <i class="ti tabler-device-floppy me-1"></i>
                {{ $isEdit ? 'Perbarui Redaksi' : 'Simpan Redaksi' }}
              </button>
              <a href="{{ route('admin.notification-templates.index') }}" class="das-btn das-btn--ghost">Batal</a>
            </div>
          </form>
        </div>
      </div>
    </div>

    {{-- SIDEBAR VARIABEL --}}
    <div class="col-md-4">
      <div class="das-panel h-100">
        <div class="das-panel__head">
          <div class="das-panel__title">
            <span class="das-panel__icon-dot" style="background:var(--das-warning);box-shadow:0 0 6px var(--das-warning);"></span>
            Variabel Tersedia
          </div>
        </div>
        <div class="das-panel__body">
          <p class="small text-muted mb-4">Klik variabel di bawah untuk menyalinnya, lalu tempel ke dalam konten pesan.</p>

          @foreach ([
            ['{nama}',        'Nama lengkap siswa'],
            ['{kelas}',       'Nama kelas siswa'],
            ['{hari}',        'Hari (contoh: Senin)'],
            ['{tanggal}',     'Tanggal absensi lengkap'],
            ['{jam}',         'Jam scan (contoh: 07:15)'],
            ['{waktu}',       'Jam scan (alias {jam})'],
            ['{status}',      'Status kehadiran (HADIR, dll)'],
            ['{lembaga}',     'Nama sekolah/lembaga'],
            ['{keterangan}',  'Keterangan tambahan'],
            ['{badge}',       'Nama badge (ucapan badge)'],
            ['{rank}',        'Peringkat (ucapan leaderboard)'],
            ['{score}',       'Skor keaktifan (ucapan leaderboard)'],
            ['{streak}',      'Hari beruntun (ucapan streak)'],
          ] as [$var, $desc])
          <div class="d-flex align-items-start gap-2 mb-3">
            <span class="var-chip flex-shrink-0" onclick="insertVar('{{ $var }}')" title="Klik untuk menyalin">
              {{ $var }}
            </span>
            <span class="small text-muted" style="font-size:.78rem;line-height:1.5;">{{ $desc }}</span>
          </div>
          @endforeach

          <div class="das-info-panel mt-4">
            <div class="small text-info">
              <i class="ti tabler-info-circle me-1"></i>
              <strong>Tips:</strong> Gunakan kurung kurawal <code>{}</code> dengan tepat agar variabel terbaca oleh sistem.
            </div>
          </div>
        </div>
      </div>
    </div>

  </div>

@endsection

@section('page-script')
<script>
function insertVar(variable) {
  const textarea = document.getElementById('content');
  const start = textarea.selectionStart;
  const end   = textarea.selectionEnd;
  const text  = textarea.value;
  textarea.value = text.substring(0, start) + variable + text.substring(end);
  textarea.selectionStart = textarea.selectionEnd = start + variable.length;
  textarea.focus();

  // copy feedback
  navigator.clipboard.writeText(variable).catch(() => {});
}
</script>
@endsection
