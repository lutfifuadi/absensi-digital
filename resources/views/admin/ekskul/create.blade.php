@extends('layouts/layoutMaster')

@section('title', 'Tambah Ekstrakurikuler')

@section('page-style')
<style>
  :root {
    --das-primary: #7367f0;
    --das-primary-soft: rgba(115, 103, 240, 0.12);
    --das-success: #28c76f;
    --das-info: #00cfe8;
    --das-warning: #ff9f43;
    --das-danger: #ea5455;
    --das-danger-soft: rgba(234, 84, 85, 0.12);
    --das-surface: rgba(15, 23, 42, 0.4);
    --das-surface-hover: rgba(30, 41, 59, 0.6);
    --das-border: rgba(255, 255, 255, 0.06);
    --das-radius: 5px;
  }

  .das-btn { display: inline-flex; align-items: center; gap: 5px; font-size: .75rem; font-weight: 600; padding: .5rem 1rem; border-radius: 5px; border: 1px solid transparent; cursor: pointer; transition: all .18s ease; text-decoration: none; white-space: nowrap; }
  .das-btn--primary { background: var(--das-primary); color: white !important; border-color: var(--das-primary); }
  .das-btn--primary:hover { background: #6259e8; transform: translateY(-2px); }
  .das-btn--ghost { background: transparent; border-color: var(--das-border); color: #999 !important; }
  .das-btn--ghost:hover { background: var(--das-surface-hover); color: white !important; }
  .das-btn--danger { background: transparent; border-color: var(--das-danger); color: var(--das-danger) !important; }
  .das-btn--danger:hover { background: var(--das-danger-soft); }

  .das-form-control { background: rgba(255,255,255,.04) !important; border: 1px solid var(--das-border) !important; border-radius: var(--das-radius) !important; color: #e0e0e0 !important; font-size: .85rem !important; transition: border-color .2s, background .2s; }
  .das-form-control:focus { background: rgba(255,255,255,.07) !important; border-color: rgba(115,103,240,.5) !important; outline: none !important; box-shadow: none !important; color: white !important; }
  .das-form-control option { background: #1a1a2e; color: #ccc; }
  .das-form-label { font-size: .72rem; font-weight: 700; text-transform: uppercase; letter-spacing: .6px; color: #888; margin-bottom: .5rem; display: block; }

  @keyframes slideInUp { from { transform: translateY(20px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
  .slide-in-up { animation: slideInUp .5s ease-out; }

  .section-card { background: rgba(255,255,255,.025); border: 1px solid var(--das-border); border-radius: var(--das-radius); }
  .section-card__head { padding: .75rem 1rem; border-bottom: 1px solid var(--das-border); display: flex; align-items: center; justify-content: space-between; }
  .section-card__title { font-size: .78rem; font-weight: 700; color: #ccc; display: flex; align-items: center; gap: 8px; }
  .section-card__body { padding: 1rem; }

  .add-row-btn { font-size: .7rem; color: var(--das-info); background: transparent; border: 1px dashed rgba(0,207,232,.3); border-radius: var(--das-radius); padding: .4rem .8rem; cursor: pointer; transition: all .2s; display: inline-flex; align-items: center; gap: 4px; }
  .add-row-btn:hover { background: rgba(0,207,232,.08); border-color: var(--das-info); }

  .dynamic-row { background: rgba(255,255,255,.02); border: 1px solid var(--das-border); border-radius: var(--das-radius); padding: .75rem 1rem; margin-bottom: .5rem; position: relative; }
  .dynamic-row:last-child { margin-bottom: 0; }
</style>
@endsection

@section('content')

  {{-- ═══════════ HERO HEADER ═══════════ --}}
  <div class="row mb-4 slide-in-up">
    <div class="col-12">
      <div class="card border-0 text-white overflow-hidden shadow-lg"
        style="background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%); border-radius: 4px;">
        <div class="card-body p-4">
          <div class="d-flex align-items-center gap-3">
            <div class="rounded d-flex align-items-center justify-content-center shadow-sm"
              style="width:52px;height:52px;border-radius:12px !important;background:rgba(0,207,232,0.2);border:1px solid rgba(0,207,232,0.4);">
              <i class="ti tabler-plus text-info fs-3"></i>
            </div>
            <div>
              <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1" style="font-size:0.72rem;opacity:0.6;">
                  <li class="breadcrumb-item"><a href="{{ route('admin.ekskul.index') }}" class="text-white text-decoration-none">Ekstrakurikuler</a></li>
                  <li class="breadcrumb-item active text-white">Tambah</li>
                </ol>
              </nav>
              <h4 class="mb-0 text-white fw-bold" style="letter-spacing:-0.5px;">Tambah Ekstrakurikuler Baru</h4>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- Validation Errors --}}
  @if ($errors->any())
    <div class="alert alert-danger alert-dismissible d-flex align-items-start gap-2 mb-4 border-0 shadow-sm slide-in-up"
      style="border-radius:8px; background: rgba(234, 84, 85, 0.15); color: #ea5455;">
      <i class="ti tabler-alert-circle fs-5 mt-1 flex-shrink-0"></i>
      <ul class="mb-0 ps-3 small">
        @foreach ($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
      <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
    </div>
  @endif

  {{-- ═══════════ FORM ═══════════ --}}
  <form action="{{ route('admin.ekskul.store') }}" method="POST" id="ekskulForm">
    @csrf

    {{-- Informasi Utama --}}
    <div class="card border-0 shadow-sm mb-4 slide-in-up"
      style="background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.08) !important;">
      <div class="card-header border-bottom py-3 d-flex align-items-center gap-2"
        style="border-color:rgba(255,255,255,0.08) !important;background:transparent;">
        <i class="ti tabler-forms text-info"></i>
        <h6 class="card-title mb-0">Informasi Ekstrakurikuler</h6>
      </div>
      <div class="card-body p-4">
        <div class="row g-4">
          {{-- Nama --}}
          <div class="col-md-8">
            <label class="das-form-label" for="nama">
              <i class="ti tabler-pencil me-1 text-info"></i> Nama Ekskul <span class="text-danger">*</span>
            </label>
            <input type="text" name="nama" id="nama"
              class="form-control das-form-control @error('nama') is-invalid @enderror"
              placeholder="Contoh: Pramuka, Basket, Paduan Suara"
              value="{{ old('nama') }}" required maxlength="255">
            @error('nama') <div class="invalid-feedback" style="font-size:.7rem;">{{ $message }}</div> @enderror
          </div>

          {{-- Kategori --}}
          <div class="col-md-4">
            <label class="das-form-label" for="kategori">
              <i class="ti tabler-category me-1 text-info"></i> Kategori <span class="text-danger">*</span>
            </label>
            <select name="kategori" id="kategori"
              class="form-select das-form-control @error('kategori') is-invalid @enderror" required>
              <option value="">Pilih Kategori</option>
              @foreach(['wajib'=>'Wajib','pilihan'=>'Pilihan','olahraga'=>'Olahraga','seni'=>'Seni','akademik'=>'Akademik','lainnya'=>'Lainnya'] as $val=>$label)
                <option value="{{ $val }}" {{ old('kategori') == $val ? 'selected' : '' }}>{{ $label }}</option>
              @endforeach
            </select>
            @error('kategori') <div class="invalid-feedback" style="font-size:.7rem;">{{ $message }}</div> @enderror
          </div>

          {{-- Deskripsi --}}
          <div class="col-12">
            <label class="das-form-label" for="deskripsi">
              <i class="ti tabler-align-left me-1 text-info"></i> Deskripsi
            </label>
            <textarea name="deskripsi" id="deskripsi" rows="3"
              class="form-control das-form-control @error('deskripsi') is-invalid @enderror"
              placeholder="Deskripsikan kegiatan ekskul ini...">{{ old('deskripsi') }}</textarea>
            @error('deskripsi') <div class="invalid-feedback" style="font-size:.7rem;">{{ $message }}</div> @enderror
          </div>

          {{-- Kuota --}}
          <div class="col-md-4">
            <label class="das-form-label" for="kuota">
              <i class="ti tabler-users me-1 text-info"></i> Kuota Anggota
            </label>
            <input type="number" name="kuota" id="kuota"
              class="form-control das-form-control @error('kuota') is-invalid @enderror"
              placeholder="Kosongkan jika tidak terbatas"
              value="{{ old('kuota') }}" min="1">
            @error('kuota') <div class="invalid-feedback" style="font-size:.7rem;">{{ $message }}</div> @enderror
          </div>

          {{-- Status --}}
          <div class="col-md-4">
            <label class="das-form-label" for="status">
              <i class="ti tabler-circle-check me-1 text-info"></i> Status
            </label>
            <div class="form-check form-switch mt-2">
              <input class="form-check-input" type="hidden" name="status" value="0">
              <input class="form-check-input" type="checkbox" role="switch" name="status" id="status" value="1"
                {{ old('status', '1') == '1' ? 'checked' : '' }}
                style="width:2.5rem;height:1.3rem;background-color:rgba(255,255,255,.2);border-color:rgba(255,255,255,.3);">
              <label class="form-check-label text-white-50 small ms-2" for="status">Aktif</label>
            </div>
          </div>

          {{-- Icon --}}
          <div class="col-md-4">
            <label class="das-form-label" for="icon">
              <i class="ti tabler-icons me-1 text-info"></i> Icon
            </label>
            <select name="icon" id="icon"
              class="form-select das-form-control @error('icon') is-invalid @enderror">
              <option value="">Default (star)</option>
              @foreach([
                'star'             => '⭐ Star',
                'trophy'           => '🏆 Trophy',
                'music'            => '🎵 Musik',
                'ball-football'    => '⚽ Sepak Bola',
                'ball-basketball'  => '🏀 Basket',
                'ball-volleyball'  => '🏐 Voli',
                'run'              => '🏃 Lari',
                'palette'          => '🎨 Seni',
                'camera'           => '📷 Fotografi',
                'microphone'       => '🎤 Paduan Suara',
                'users-group'      => '👥 Kelompok',
                'school'           => '🏫 Akademik',
                'books'            => '📚 Literasi',
                'message-chatbot'  => '🤖 Robotik',
                'users'            => '🧑 Pramuka',
                'heart-handshake'  => '🤝 Sosial',
                'shield'           => '🛡️ Paskibra',
                'first-aid-kit'    => '🏥 PMR',
                'leaf'             => '🌿 Pecinta Alam',
              ] as $val => $label)
                <option value="{{ $val }}" {{ old('icon') == $val ? 'selected' : '' }}>{{ $label }}</option>
              @endforeach
            </select>
            @error('icon') <div class="invalid-feedback" style="font-size:.7rem;">{{ $message }}</div> @enderror
          </div>
        </div>
      </div>
    </div>

    {{-- ═══════════ SECTION: JADWAL ═══════════ --}}
    <div class="section-card mb-4 slide-in-up" x-data="jadwalManager()">
      <div class="section-card__head">
        <div class="section-card__title">
          <i class="ti tabler-calendar-clock text-warning"></i> Jadwal Kegiatan
        </div>
        <button type="button" class="add-row-btn" @click="addRow">
          <i class="ti tabler-plus"></i> Tambah Jadwal
        </button>
      </div>
      <div class="section-card__body">
        <p class="text-white-50 small mb-3" x-show="rows.length === 0">
          <i class="ti tabler-info-circle me-1"></i> Belum ada jadwal. Klik "Tambah Jadwal" untuk menambahkan.
        </p>

        <template x-for="(row, index) in rows" :key="index">
          <div class="dynamic-row">
            <div class="row g-2 align-items-end">
              <div class="col-md-3 col-sm-6">
                <label class="text-white-50 small fw-semibold d-block mb-1" style="font-size:.65rem;">Hari <span class="text-danger">*</span></label>
                <select :name="'jadwal['+index+'][hari]'" class="form-select das-form-control"
                  x-model="row.hari" required
                  style="font-size:.78rem !important; padding:.35rem .65rem !important;">
                  <option value="">Pilih Hari</option>
                  <option value="senin">Senin</option>
                  <option value="selasa">Selasa</option>
                  <option value="rabu">Rabu</option>
                  <option value="kamis">Kamis</option>
                  <option value="jumat">Jumat</option>
                  <option value="sabtu">Sabtu</option>
                </select>
              </div>
              <div class="col-md-2 col-sm-6">
                <label class="text-white-50 small fw-semibold d-block mb-1" style="font-size:.65rem;">Jam Mulai <span class="text-danger">*</span></label>
                <input type="time" :name="'jadwal['+index+'][jam_mulai]'"
                  class="form-control das-form-control"
                  x-model="row.jam_mulai" required
                  style="font-size:.78rem !important; padding:.35rem .65rem !important;">
              </div>
              <div class="col-md-2 col-sm-6">
                <label class="text-white-50 small fw-semibold d-block mb-1" style="font-size:.65rem;">Jam Selesai <span class="text-danger">*</span></label>
                <input type="time" :name="'jadwal['+index+'][jam_selesai]'"
                  class="form-control das-form-control"
                  x-model="row.jam_selesai" required
                  style="font-size:.78rem !important; padding:.35rem .65rem !important;">
              </div>
              <div class="col-md-4 col-sm-6">
                <label class="text-white-50 small fw-semibold d-block mb-1" style="font-size:.65rem;">Lokasi <span class="text-danger">*</span></label>
                <input type="text" :name="'jadwal['+index+'][lokasi]'"
                  class="form-control das-form-control"
                  x-model="row.lokasi" required
                  placeholder="Nama ruangan/lapangan"
                  style="font-size:.78rem !important; padding:.35rem .65rem !important;">
              </div>
              <div class="col-md-1 col-sm-6 d-flex align-items-end">
                <button type="button" class="das-btn das-btn--danger" @click="removeRow(index)"
                  style="padding:.2rem .5rem;font-size:.7rem;" title="Hapus jadwal">
                  <i class="ti tabler-trash"></i>
                </button>
              </div>
            </div>
          </div>
        </template>
      </div>
    </div>

    {{-- ═══════════ SECTION: PEMBINA ═══════════ --}}
    <div class="section-card mb-4 slide-in-up" x-data="pembinaManager()">
      <div class="section-card__head">
        <div class="section-card__title">
          <i class="ti tabler-chalkboard-teacher text-success"></i> Pembina
        </div>
        <button type="button" class="add-row-btn" @click="addRow">
          <i class="ti tabler-plus"></i> Tambah Pembina
        </button>
      </div>
      <div class="section-card__body">
        <p class="text-white-50 small mb-3" x-show="rows.length === 0">
          <i class="ti tabler-info-circle me-1"></i> Belum ada pembina. Klik "Tambah Pembina" untuk menambahkan.
        </p>

        <template x-for="(row, index) in rows" :key="index">
          <div class="dynamic-row">
            <div class="row g-2 align-items-end">
              <div class="col-md-6 col-sm-6">
                <label class="text-white-50 small fw-semibold d-block mb-1" style="font-size:.65rem;">Guru Pembina <span class="text-danger">*</span></label>
                <select :name="'pembina['+index+'][guru_id]'"
                  class="form-select das-form-control"
                  x-model="row.guru_id" required
                  style="font-size:.78rem !important; padding:.35rem .65rem !important;">
                  <option value="">Pilih Guru</option>
                  @foreach($guruOptions as $guru)
                    <option value="{{ $guru->id }}">{{ $guru->nama_lengkap }}</option>
                  @endforeach
                </select>
              </div>
              <div class="col-md-5 col-sm-6">
                <label class="text-white-50 small fw-semibold d-block mb-1" style="font-size:.65rem;">Jabatan</label>
                <input type="text" :name="'pembina['+index+'][jabatan]'"
                  class="form-control das-form-control"
                  x-model="row.jabatan"
                  placeholder="Contoh: Pembina Utama, Pelatih"
                  style="font-size:.78rem !important; padding:.35rem .65rem !important;">
              </div>
              <div class="col-md-1 col-sm-6 d-flex align-items-end">
                <button type="button" class="das-btn das-btn--danger" @click="removeRow(index)"
                  style="padding:.2rem .5rem;font-size:.7rem;" title="Hapus pembina">
                  <i class="ti tabler-trash"></i>
                </button>
              </div>
            </div>
          </div>
        </template>
      </div>
    </div>

    {{-- ═══════════ ACTION BUTTONS ═══════════ --}}
    <div class="d-flex align-items-center justify-content-end gap-3 slide-in-up">
      <a href="{{ route('admin.ekskul.index') }}" class="btn btn-label-secondary">
        <i class="ti tabler-arrow-left me-1"></i> Batal
      </a>
      <button type="submit" class="btn btn-info fw-semibold px-4 shadow-sm">
        <i class="ti tabler-device-floppy me-1"></i> Simpan Ekskul
      </button>
    </div>
  </form>

@endsection

@section('page-script')
<script>
  function jadwalManager() {
    return {
      rows: [],
      addRow() {
        this.rows.push({ hari: '', jam_mulai: '', jam_selesai: '', lokasi: '' });
      },
      removeRow(index) {
        this.rows.splice(index, 1);
      }
    }
  }

  function pembinaManager() {
    return {
      rows: [],
      addRow() {
        this.rows.push({ guru_id: '', jabatan: '' });
      },
      removeRow(index) {
        this.rows.splice(index, 1);
      }
    }
  }
</script>
@endsection
