@extends('layouts/layoutMaster')

@section('title', 'Tambah Kegiatan Baru')

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

  /* ICON BUTTON */
  .das-icon-btn { width: 36px; height: 36px; border-radius: 5px; border: 1px solid var(--das-border); background: transparent; color: #888; display: inline-flex; align-items: center; justify-content: center; transition: all .2s; text-decoration: none; cursor: pointer; position: relative; }
  .das-icon-btn:hover { background: var(--das-surface-hover); color: white; transform: translateY(-2px); }
  .das-icon-btn--secondary { border-color: var(--das-border); color: #999; }
  .das-icon-btn--secondary:hover { background: var(--das-surface-hover); color: white; border-color: var(--das-border-hover); }
  .das-icon-btn--primary { background: var(--das-primary); color: white !important; border-color: var(--das-primary); }
  .das-icon-btn--primary:hover { background: #6259e8; transform: translateY(-2px); }

  /* BUTTONS */
  .das-btn { display: inline-flex; align-items: center; gap: 5px; font-size: .75rem; font-weight: 600; padding: .5rem 1rem; border-radius: 5px; border: 1px solid transparent; cursor: pointer; transition: all .18s ease; text-decoration: none; white-space: nowrap; }
  .das-btn--primary { background: var(--das-primary); color: white !important; border-color: var(--das-primary); }
  .das-btn--primary:hover { background: #6259e8; transform: translateY(-2px); }
  .das-btn--ghost { background: transparent; border-color: var(--das-border); color: #999 !important; }
  .das-btn--ghost:hover { background: var(--das-surface-hover); color: white !important; }

  /* PANEL */
  .das-panel { background: var(--das-surface); border: 1px solid var(--das-border); border-radius: var(--das-radius); overflow: hidden; backdrop-filter: blur(6px); }
  .das-panel__head { display: flex; align-items: center; justify-content: space-between; padding: .9rem 1.25rem; border-bottom: 1px solid var(--das-border); }
  .das-panel__title { font-size: .82rem; font-weight: 700; text-transform: uppercase; letter-spacing: .6px; display: flex; align-items: center; gap: 8px; color: #ccc; }
  .das-panel__icon-dot { width: 8px; height: 8px; border-radius: 50%; background: var(--das-info); box-shadow: 0 0 6px var(--das-info); }
  .das-panel__body { padding: 1.5rem; }

  /* FORM ELEMENTS */
  .das-form-label { font-size: .72rem; font-weight: 700; text-transform: uppercase; letter-spacing: .6px; color: #888; margin-bottom: .5rem; display: block; }
  .das-form-control { background: rgba(255,255,255,.04) !important; border: 1px solid var(--das-border) !important; border-radius: var(--das-radius) !important; color: #e0e0e0 !important; font-size: .85rem !important; transition: border-color .2s, background .2s; }
  .das-form-control:focus { background: rgba(255,255,255,.07) !important; border-color: rgba(115,103,240,.5) !important; outline: none !important; box-shadow: none !important; color: white !important; }
  .das-form-control option { background: #1a1a2e; color: #ccc; }
  textarea.das-form-control { resize: vertical; }

  /* ALERT */
  .das-alert { display: flex; align-items: flex-start; gap: 10px; padding: .85rem 1.1rem; border-radius: var(--das-radius); font-size: .82rem; border: 1px solid transparent; }
  .das-alert--danger { background: var(--das-danger-soft); border-color: rgba(234,84,85,.25); color: #f7a7a8; }
  .das-alert__icon { font-size: 1.1rem; flex-shrink: 0; margin-top: 1px; }
  .das-alert__list { margin: 0; padding-left: 1.2rem; }

  /* ANIMATION */
  @keyframes slideInUp { from { transform: translateY(20px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
  .slide-in-up { animation: slideInUp .5s ease-out; }

  /* TOOLTIP */
  .das-tooltip { position: relative; }
  .das-tooltip:hover::after { content: attr(data-tip); position: absolute; bottom: calc(100% + 8px); left: 50%; transform: translateX(-50%); background: #1a1a2e; color: #ccc; font-size: .65rem; font-weight: 600; padding: 4px 10px; border-radius: 4px; border: 1px solid var(--das-border); white-space: nowrap; z-index: 10; }
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
          <i class="ti tabler-calendar-plus"></i>
        </div>
        <div>
          <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1" style="font-size:.65rem;text-transform:uppercase;letter-spacing:1px;opacity:.6;">
              <li class="breadcrumb-item text-white opacity-60">Modul Khusus</li>
              <li class="breadcrumb-item text-white opacity-60">Kegiatan</li>
              <li class="breadcrumb-item active text-white opacity-100">Tambah Baru</li>
            </ol>
          </nav>
          <h4 class="das-hero__title">Tambah Kegiatan Baru</h4>
          <p class="das-hero__welcome">Masukkan detail kegiatan untuk role operator.</p>
        </div>
      </div>
      <div class="das-hero__actions" style="display:flex;gap:.5rem;">
        <a href="{{ route('admin.kegiatan.index') }}"
           class="das-icon-btn das-icon-btn--secondary das-tooltip"
           data-tip="Kembali"
           data-bs-toggle="tooltip"
           title="Kembali ke daftar kegiatan">
          <i class="ti tabler-arrow-left"></i>
        </a>
      </div>
    </div>
  </div>

  {{-- ── ERROR VALIDATION ────────────────────────────── --}}
  @if ($errors->any())
    <div class="das-alert das-alert--danger slide-in-up mb-4">
      <i class="ti tabler-alert-triangle das-alert__icon"></i>
      <div>
        <strong class="d-block mb-1">Terdapat kesalahan input:</strong>
        <ul class="das-alert__list">
          @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
      </div>
    </div>
  @endif

  {{-- ── FORM PANEL ──────────────────────────────────── --}}
  <div class="das-panel slide-in-up">
    <div class="das-panel__head">
      <div class="das-panel__title">
        <span class="das-panel__icon-dot" style="background:var(--das-primary);box-shadow:0 0 6px var(--das-primary);"></span>
        Formulir Kegiatan Baru
      </div>
    </div>
    <div class="das-panel__body">
      <form action="{{ route('admin.kegiatan.store') }}" method="POST">
        @csrf

        <div class="row g-4">
          {{-- Nama Kegiatan --}}
          <div class="col-md-6">
            <label class="das-form-label">Nama Kegiatan <span style="color:var(--das-danger);">*</span></label>
            <input type="text" name="nama_kegiatan" value="{{ old('nama_kegiatan') }}"
                   class="form-control das-form-control" placeholder="Contoh: Upacara Bendera" required>
          </div>

          {{-- Jenis --}}
          <div class="col-md-6">
            <label class="das-form-label">Jenis <span style="color:var(--das-danger);">*</span></label>
            <select name="jenis" class="form-select das-form-control" required>
              <option value="EKSTRAKURIKULER" {{ old('jenis') === 'EKSTRAKURIKULER' ? 'selected' : '' }}>Ekstrakurikuler</option>
              <option value="UJIAN" {{ old('jenis') === 'UJIAN' ? 'selected' : '' }}>Ujian</option>
              <option value="RAPAT" {{ old('jenis') === 'RAPAT' ? 'selected' : '' }}>Rapat</option>
              <option value="LAINNYA" {{ old('jenis') === 'LAINNYA' ? 'selected' : '' }}>Lainnya</option>
            </select>
          </div>

          {{-- Tanggal --}}
          <div class="col-md-4" id="tanggal_wrapper">
            <label class="das-form-label">Tanggal</label>
            <input type="date" name="tanggal_pelaksanaan" id="tanggal_pelaksanaan" value="{{ old('tanggal_pelaksanaan', date('Y-m-d')) }}"
                   class="form-control das-form-control">
          </div>

          {{-- Tanpa Tanggal Pasti --}}
          <div class="col-12">
            <div class="p-3 mb-3" style="background:rgba(255,255,255,0.02); border:1px solid var(--das-border); border-radius:var(--das-radius);">
              <div class="form-check">
                <input type="checkbox" id="tanpa_tanggal_pasti" class="form-check-input"
                       style="width:18px;height:18px;cursor:pointer;"
                       onchange="toggleTanggal(this)">
                <label class="form-check-label text-white small fw-semibold" for="tanpa_tanggal_pasti" style="cursor:pointer;font-size:.82rem;">
                  <i class="ti tabler-calendar-off text-warning me-1"></i>
                  Tanpa tanggal pasti (kegiatan rutin/fleksibel)
                </label>
                <small class="text-muted d-block mt-1" style="font-size:.7rem;">
                  <i class="ti tabler-info-circle"></i> Jika diaktifkan, kegiatan tidak terikat pada tanggal tertentu (contoh: Sholat Dhuha).
                </small>
              </div>
            </div>
          </div>

          {{-- Tanpa Batas Waktu --}}
          <div class="col-12">
            <div class="p-3 mb-3" style="background:rgba(255,255,255,0.02); border:1px solid var(--das-border); border-radius:var(--das-radius);">
              <div class="form-check">
                <input type="checkbox" id="tanpa_batas_waktu" class="form-check-input"
                       style="width:18px;height:18px;cursor:pointer;"
                       onchange="toggleWaktu(this)">
                <label class="form-check-label text-white small fw-semibold" for="tanpa_batas_waktu" style="cursor:pointer;font-size:.82rem;">
                  <i class="ti tabler-clock-off text-info me-1"></i>
                  Kegiatan seharian penuh (tanpa batas waktu)
                </label>
                <small class="text-muted d-block mt-1" style="font-size:.7rem;">
                  <i class="ti tabler-info-circle"></i> Jika diaktifkan, kegiatan berlangsung seharian penuh dan input waktu mulai & selesai tidak diperlukan.
                </small>
              </div>
            </div>
          </div>

          {{-- Waktu Mulai --}}
          <div class="col-md-4" id="waktu_mulai_wrapper">
            <label class="das-form-label">Waktu Mulai</label>
            <input type="time" name="waktu_mulai" id="waktu_mulai" value="{{ old('waktu_mulai') }}"
                   class="form-control das-form-control">
          </div>

          {{-- Waktu Selesai --}}
          <div class="col-md-4" id="waktu_selesai_wrapper">
            <label class="das-form-label">Waktu Selesai</label>
            <input type="time" name="waktu_selesai" id="waktu_selesai" value="{{ old('waktu_selesai') }}"
                   class="form-control das-form-control">
          </div>

          {{-- Lokasi --}}
          <div class="col-12">
            <label class="das-form-label">Lokasi</label>
            <input type="text" name="lokasi" value="{{ old('lokasi') }}"
                   class="form-control das-form-control" placeholder="Nama Ruangan/Lapangan">
          </div>

          {{-- Wajib / Opsional --}}
          <div class="col-12">
            <div class="p-3" style="background:rgba(255,255,255,0.02); border:1px solid var(--das-border); border-radius:var(--das-radius);">
              <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" name="is_wajib" value="1" id="is_wajib"
                       style="width:40px;height:20px;cursor:pointer;"
                       {{ old('is_wajib') ? 'checked' : '' }}>
                <label class="form-check-label text-white small fw-semibold" for="is_wajib" style="cursor:pointer;font-size:.82rem;">
                  <i class="ti tabler-alert-triangle text-warning me-1"></i>
                  Wajib Hadir
                </label>
                <small class="text-muted d-block mt-1" style="font-size:.7rem;">
                  <i class="ti tabler-info-circle"></i> Jika diaktifkan, peserta diwajibkan hadir pada kegiatan ini.
                </small>
              </div>
            </div>
          </div>

          {{-- Deskripsi --}}
          <div class="col-12">
            <label class="das-form-label">Deskripsi Kegiatan</label>
            <textarea name="keterangan" class="form-control das-form-control" rows="3"
                      placeholder="Tuliskan deskripsi singkat kegiatan">{{ old('keterangan') }}</textarea>
          </div>

          {{-- Target Peserta (Berdasarkan Tingkat) --}}
          <div class="col-12">
            <label class="das-form-label">Target Peserta (Berdasarkan Tingkat)</label>
            <div class="row g-2 p-3 mb-3" style="background:rgba(255,255,255,0.02); border:1px solid var(--das-border); border-radius:var(--das-radius);">
              @foreach($tingkat as $t)
                <div class="col-md-2 col-4">
                  <div class="form-check">
                    <input class="form-check-input checkbox-tingkat" type="checkbox" name="target_tingkat[]" value="{{ $t }}" id="tingkat_{{ $t }}"
                           data-tingkat="{{ $t }}"
                           {{ is_array(old('target_tingkat')) && in_array($t, old('target_tingkat')) ? 'checked' : '' }}>
                    <label class="form-check-label text-white small" for="tingkat_{{ $t }}">
                      Tingkat {{ $t }}
                    </label>
                  </div>
                </div>
              @endforeach
            </div>
          </div>

          {{-- Target Peserta (Jurusan) --}}
          <div class="col-12">
            <label class="das-form-label">Target Jurusan <small class="text-muted" style="font-size:.65rem;text-transform:none;letter-spacing:0;">(Opsional)</small></label>
            <div class="row g-2 p-3 mb-3" style="background:rgba(255,255,255,0.02); border:1px solid var(--das-border); border-radius:var(--das-radius);">
              @forelse($jurusanList as $jurusan)
                <div class="col-md-2 col-4">
                  <div class="form-check">
                    <input class="form-check-input jurusan-checkbox" type="checkbox" name="target_jurusan[]" value="{{ $jurusan }}" id="jurusan_{{ Str::slug($jurusan) }}"
                           {{ in_array($jurusan, old('target_jurusan', [])) ? 'checked' : '' }}>
                    <label class="form-check-label text-white small" for="jurusan_{{ Str::slug($jurusan) }}">
                      {{ $jurusan }}
                    </label>
                  </div>
                </div>
              @empty
                <div class="col-12">
                  <p class="text-muted small mb-0" style="font-size:.75rem;">
                    <i class="ti tabler-info-circle me-1"></i>Tidak ada data jurusan tersedia.
                  </p>
                </div>
              @endforelse
            </div>
          </div>

          {{-- Target Peserta (Kelas) --}}
          <div class="col-12">
            <label class="das-form-label">Target Peserta (Kelas Spesifik)</label>
            <div class="row g-2 p-3" style="background:rgba(255,255,255,0.02); border:1px solid var(--das-border); border-radius:var(--das-radius);">
              @foreach($kelas as $k)
                <div class="col-md-3 col-6 checkbox-kelas-wrapper" data-tingkat="{{ $k->tingkat }}" data-jurusan="{{ $k->jurusan?->nama ?? '' }}">
                  <div class="form-check">
                    <input class="form-check-input checkbox-kelas" type="checkbox" name="target_peserta[]" value="{{ $k->id }}" id="kelas_{{ $k->id }}"
                           {{ is_array(old('target_peserta')) && in_array($k->id, old('target_peserta')) ? 'checked' : '' }}>
                    <label class="form-check-label text-white small" for="kelas_{{ $k->id }}">
                      {{ $k->nama }}
                    </label>
                  </div>
                </div>
              @endforeach
            </div>
            <small class="text-muted mt-2 d-block" style="font-size: .7rem;">
              <i class="ti tabler-info-circle"></i> Jika tingkat dipilih, seluruh kelas di tingkat tersebut akan otomatis menjadi target. Gunakan "Kelas Spesifik" jika hanya ingin memilih kelas tertentu.
            </small>
          </div>
        </div>

        {{-- Submit --}}
        <div class="d-flex justify-content-end gap-2 mt-4 pt-3" style="border-top:1px solid var(--das-border);">
          <a href="{{ route('admin.kegiatan.index') }}" class="das-btn das-btn--ghost">
            <i class="ti tabler-x"></i> Batal
          </a>
          <button type="submit" class="das-btn das-btn--primary px-4">
            <i class="ti tabler-device-floppy"></i> Simpan Agenda
          </button>
        </div>
      </form>
    </div>
  </div>

@endsection

@section('page-script')
<script>
  document.addEventListener('DOMContentLoaded', function() {
    const tooltips = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltips.map(el => new bootstrap.Tooltip(el));

    const tingkatCheckboxes = document.querySelectorAll('.checkbox-tingkat');
    const kelasWrappers = document.querySelectorAll('.checkbox-kelas-wrapper');

    function updateKelasVisibility() {
      const selectedTingkats = Array.from(tingkatCheckboxes)
        .filter(cb => cb.checked)
        .map(cb => cb.dataset.tingkat);

      kelasWrappers.forEach(wrapper => {
        const tingkat = wrapper.dataset.tingkat;
        const checkbox = wrapper.querySelector('.checkbox-kelas');
        
        if (selectedTingkats.includes(tingkat)) {
          wrapper.style.opacity = '0.5';
          wrapper.style.pointerEvents = 'none';
          checkbox.checked = false; // Uncheck because level already covers it
        } else {
          wrapper.style.opacity = '1';
          wrapper.style.pointerEvents = 'auto';
        }
      });
    }

    tingkatCheckboxes.forEach(cb => {
      cb.addEventListener('change', updateKelasVisibility);
    });

    // Initial check
    updateKelasVisibility();

    // ── Jurusan Checkboxes ──────────────────────────────
    const jurusanCheckboxes = document.querySelectorAll('.jurusan-checkbox');

    /** Check/uncheck semua kelas dalam jurusan tertentu */
    function updateKelasByJurusan() {
      const selectedJurusan = Array.from(jurusanCheckboxes)
        .filter(cb => cb.checked)
        .map(cb => cb.value);

      kelasWrappers.forEach(wrapper => {
        const jurusan = wrapper.dataset.jurusan;
        if (!jurusan) return;

        const checkbox = wrapper.querySelector('.checkbox-kelas');
        const isDisabled = wrapper.style.pointerEvents === 'none';

        if (selectedJurusan.includes(jurusan) && !isDisabled) {
          checkbox.checked = true;
        }
      });
    }

    /** Auto centang jurusan jika semua kelas di jurusan itu dicentang */
    function updateJurusanFromKelas() {
      jurusanCheckboxes.forEach(jcb => {
        const jurusan = jcb.value;
        const relatedWrappers = Array.from(kelasWrappers)
          .filter(w => w.dataset.jurusan === jurusan && w.style.pointerEvents !== 'none');

        if (relatedWrappers.length > 0) {
          const allChecked = relatedWrappers.every(w => w.querySelector('.checkbox-kelas').checked);
          jcb.checked = allChecked;
        }
      });
    }

    // Event: jurusan checkbox berubah → centang kelas terkait
    jurusanCheckboxes.forEach(cb => {
      cb.addEventListener('change', function() {
        updateKelasByJurusan();
        updateJurusanFromKelas();
      });
    });

    // Event: kelas checkbox berubah → update jurusan
    document.querySelectorAll('.checkbox-kelas').forEach(cb => {
      cb.addEventListener('change', updateJurusanFromKelas);
    });

    // Integrasi: saat tingkat berubah, refresh jurusan state
    tingkatCheckboxes.forEach(cb => {
      cb.addEventListener('change', updateJurusanFromKelas);
    });

    // Initial check untuk jurusan
    updateKelasByJurusan();
    updateJurusanFromKelas();

    // Toggle tanggal berdasarkan checkbox tanpa tanggal pasti
    window.toggleTanggal = function(checkbox) {
      const tanggalWrapper = document.getElementById('tanggal_wrapper');
      const tanggalInput = document.getElementById('tanggal_pelaksanaan');

      if (checkbox.checked) {
        tanggalWrapper.style.display = 'none';
        tanggalInput.value = '';
      } else {
        tanggalWrapper.style.display = 'block';
      }
    };

    // Toggle waktu berdasarkan checkbox tanpa batas waktu
    window.toggleWaktu = function(checkbox) {
      const mulaiWrapper = document.getElementById('waktu_mulai_wrapper');
      const selesaiWrapper = document.getElementById('waktu_selesai_wrapper');
      const waktuMulai = document.getElementById('waktu_mulai');
      const waktuSelesai = document.getElementById('waktu_selesai');

      if (checkbox.checked) {
        mulaiWrapper.style.display = 'none';
        selesaiWrapper.style.display = 'none';
        waktuMulai.value = '';
        waktuSelesai.value = '';
      } else {
        mulaiWrapper.style.display = 'block';
        selesaiWrapper.style.display = 'block';
      }
    };
  });
</script>
@endsection
