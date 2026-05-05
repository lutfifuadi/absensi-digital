@extends('layouts/layoutMaster')

@section('title', 'Jadwal Pelajaran')

@section('page-style')
  <style>
    .jadwal-row-hover {
      transition: background 0.15s ease;
    }

    .jadwal-row-hover:hover {
      background: rgba(255, 255, 255, 0.04) !important;
    }

    .action-btn {
      display: inline-flex;
      align-items: center;
      gap: 5px;
      padding: 5px 12px;
      border-radius: 6px;
      font-size: 0.8rem;
      font-weight: 500;
      text-decoration: none;
      border: none;
      cursor: pointer;
      transition: opacity 0.15s ease, transform 0.15s ease;
    }

    .action-btn:hover {
      opacity: 0.85;
      transform: translateY(-1px);
    }

    #modalJadwal .modal-content {
      border: 1px solid rgba(255, 255, 255, 0.1);
      background: #1e1e2d;
      border-radius: 12px;
      overflow: hidden;
    }

    #modalJadwal .modal-header {
      background: linear-gradient(135deg, #1a1a2e 0%, #0f3460 100%);
      border-bottom: 1px solid rgba(255, 255, 255, 0.08);
      padding: 1.25rem 1.5rem;
    }

    #modalJadwal .modal-body {
      padding: 1.5rem;
    }

    #modalJadwal .modal-footer {
      border-top: 1px solid rgba(255, 255, 255, 0.08);
      padding: 1rem 1.5rem;
      background: rgba(255, 255, 255, 0.02);
    }

    #modalJadwal .form-control,
    #modalJadwal .form-select {
      background: rgba(255, 255, 255, 0.06);
      border: 1px solid rgba(255, 255, 255, 0.12);
      color: inherit;
      border-radius: 8px;
      transition: border-color 0.2s ease, background 0.2s ease;
    }

    #modalJadwal .form-control:focus,
    #modalJadwal .form-select:focus {
      background: rgba(255, 255, 255, 0.09);
      border-color: rgba(0, 207, 232, 0.6);
      box-shadow: 0 0 0 3px rgba(0, 207, 232, 0.12);
    }

    #modalJadwal .form-control::placeholder {
      opacity: 0.4;
    }

    #modalJadwal .form-select option {
      background: #1e1e2d;
      color: #cdd2e0;
    }

    #modalHapusJadwal .modal-content {
      border: 1px solid rgba(255, 255, 255, 0.1);
      background: #1e1e2d;
      border-radius: 12px;
      overflow: hidden;
    }

    #modalHapusJadwal .modal-header {
      background: linear-gradient(135deg, #2d1a1a 0%, #3d0f0f 100%);
      border-bottom: 1px solid rgba(255, 255, 255, 0.08);
    }

    #modalHapusJadwal .modal-footer {
      border-top: 1px solid rgba(255, 255, 255, 0.08);
      background: rgba(255, 255, 255, 0.02);
    }

    .modal-icon-header {
      width: 44px;
      height: 44px;
      border-radius: 10px;
      display: flex;
      align-items: center;
      justify-content: center;
      flex-shrink: 0;
    }
  </style>
@endsection

@section('content')

  {{-- ═══════════════════════════════════════════════════════
       SECTION 1: HERO HEADER
  ═══════════════════════════════════════════════════════ --}}
  <div class="das-hero mb-4">
    <div class="das-hero__bg"></div>
    <div class="das-hero__glass"></div>
    <div class="das-hero__grid-lines"></div>

    <div class="das-hero__inner">
      <div class="das-hero__identity">
        <div class="das-hero__logo-wrapper">
          <div class="das-hero__logo-placeholder">
            <i class="ti tabler-calendar-event text-info"></i>
          </div>
          <div class="das-hero__logo-glow"></div>
        </div>

        <div class="das-hero__meta">
          <div class="das-hero__badge">
            <span class="pulse-dot"></span>
            <a href="{{ route('admin.master-data') }}" class="text-white text-decoration-none">Master Data</a> / Jadwal
          </div>
          <h4 class="das-hero__title text-gradient-gold">Jadwal Pelajaran</h4>
          <p class="das-hero__subtitle">Kelola jadwal kegiatan belajar mengajar setiap kelas.</p>
        </div>
      </div>

      <div class="das-hero__actions">
        <button type="button" class="btn das-btn --primary" onclick="openTambahJadwal()">
          <i class="ti tabler-plus me-1"></i> Tambah Jadwal
        </button>
      </div>
    </div>
  </div>

  {{-- FLASH MESSAGE --}}
  @if (session('success'))
    <div class="alert alert-success alert-dismissible d-flex align-items-center gap-2 mb-4 border-0 shadow-sm"
      role="alert" style="border-radius:8px;">
      <i class="ti tabler-circle-check fs-5"></i>
      <span>{{ session('success') }}</span>
      <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
    </div>
  @endif

  {{-- FILTER & TABLE CARD --}}
  <div class="das-panel">
    <div class="das-panel__header border-bottom py-3 px-4 d-flex align-items-center justify-content-between flex-wrap gap-2"
      style="border-color:rgba(255,255,255,0.08) !important;">
      <h6 class="das-panel__title mb-0 d-flex align-items-center gap-2">
        <i class="ti tabler-list text-info"></i> Daftar Jadwal
      </h6>
      <div class="d-flex align-items-center gap-2">
        <form method="GET" class="d-flex align-items-center" id="filterForm">
          <select name="kelas_id" class="form-select form-select-sm" onchange="this.form.submit()"
            style="background:rgba(255,255,255,0.05); border-color:rgba(255,255,255,0.1); color:inherit; min-width:180px;">
            <option value="">Semua Kelas</option>
            @foreach ($kelasOptions as $k)
              <option value="{{ $k->id }}" @selected(request('kelas_id') == $k->id)>{{ $k->nama }}</option>
            @endforeach
          </select>
        </form>
        <span class="das-chip --info">{{ $jadwal->total() }} Item</span>
      </div>
    </div>
    <div class="das-panel__body p-0">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" style="color:inherit;">
          <thead
            style="background:rgba(255,255,255,0.04);font-size:0.75rem;text-transform:uppercase;letter-spacing:0.8px;opacity:0.7;">
            <tr>
              <th class="ps-4 py-3" style="width:46px;">#</th>
              <th class="py-3">Kelas</th>
              <th class="py-3">Hari</th>
              <th class="py-3">Jam</th>
              <th class="py-3">Mata Pelajaran</th>
              <th class="py-3">Guru</th>
              <th class="py-3 pe-4 text-end">Aksi</th>
            </tr>
          </thead>
          <tbody>
            @forelse($jadwal as $item)
              <tr class="jadwal-row-hover">
                <td class="ps-4 text-white-50 small">{{ $jadwal->firstItem() + $loop->index }}</td>
                <td>
                  <div class="d-flex align-items-center gap-2">
                    <div class="avatar avatar-xs">
                      <span class="avatar-initial rounded bg-label-info">
                        <i class="ti tabler-door" style="font-size:0.8rem;"></i>
                      </span>
                    </div>
                    <span class="fw-semibold">{{ $item->kelas->nama ?? '-' }}</span>
                  </div>
                </td>
                <td>
                  <span class="badge bg-label-primary px-3 rounded-pill">{{ $item->hari }}</span>
                </td>
                <td>
                  <div class="d-flex align-items-center gap-1 text-white-50">
                    <i class="ti tabler-clock small"></i>
                    <span class="small fw-medium">{{ substr($item->jam_mulai, 0, 5) }} -
                      {{ substr($item->jam_selesai, 0, 5) }}</span>
                  </div>
                </td>
                <td>
                  <span class="fw-medium">{{ $item->mata_pelajaran }}</span>
                </td>
                <td>
                  @if ($item->guru)
                    <div class="d-flex align-items-center gap-2">
                      <div class="avatar avatar-xs">
                        <span class="avatar-initial rounded-circle bg-label-success" style="font-size:0.65rem;">
                          {{ strtoupper(substr($item->guru->nama_lengkap ?? 'G', 0, 1)) }}
                        </span>
                      </div>
                      <span class="small">{{ $item->guru->nama_lengkap }}</span>
                    </div>
                  @else
                    <span class="text-white-50 small">—</span>
                  @endif
                </td>
                <td class="pe-4 text-end">
                  <button type="button" class="action-btn bg-label-info text-info me-1"
                    onclick="openEditJadwal({
                      id: {{ $item->id }},
                      kelas_id: {{ $item->kelas_id }},
                      guru_id: {{ $item->guru_id ?? 'null' }},
                      mata_pelajaran: '{{ addslashes($item->mata_pelajaran) }}',
                      hari: '{{ $item->hari }}',
                      jam_mulai: '{{ substr($item->jam_mulai, 0, 5) }}',
                      jam_selesai: '{{ substr($item->jam_selesai, 0, 5) }}'
                    })">
                    <i class="ti tabler-pencil"></i> Ubah
                  </button>
                  <button type="button" class="action-btn bg-label-danger text-danger"
                    onclick="openHapusJadwal({{ $item->id }}, '{{ addslashes($item->mata_pelajaran) }} ({{ $item->hari }})')">
                    <i class="ti tabler-trash"></i> Hapus
                  </button>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="7" class="text-center py-5">
                  <div class="d-flex flex-column align-items-center gap-2 opacity-50">
                    <i class="ti tabler-calendar-off" style="font-size:2.5rem;"></i>
                    <span class="small">Belum ada data jadwal pelajaran.</span>
                    <button type="button" class="btn btn-sm btn-label-info mt-1" onclick="openTambahJadwal()">
                      <i class="ti tabler-plus me-1"></i> Tambah Sekarang
                    </button>
                  </div>
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
      @if ($jadwal->hasPages())
        <div class="card-footer border-top d-flex justify-content-center py-3"
          style="border-color:rgba(255,255,255,0.08) !important; background:transparent;">
          {{ $jadwal->links() }}
        </div>
      @endif
    </div>
  </div>

  {{-- ══════════════════════════════════════════════ --}}
  {{-- MODAL TAMBAH / UBAH JADWAL --}}
  {{-- ══════════════════════════════════════════════ --}}
  <div class="modal fade" id="modalJadwal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:560px;">
      <div class="modal-content shadow-lg">

        <div class="modal-header">
          <div class="d-flex align-items-center gap-3">
            <div class="modal-icon-header"
              style="background:rgba(0,207,232,0.2);border:1px solid rgba(0,207,232,0.35);">
              <i id="modalJadwalIcon" class="ti tabler-calendar-plus text-info fs-5"></i>
            </div>
            <div>
              <h5 id="modalJadwalTitle" class="modal-title mb-0 text-white fw-bold">Tambah Jadwal</h5>
              <small id="modalJadwalSubtitle" class="text-white-50">Isi form di bawah untuk menambah jadwal
                pelajaran.</small>
            </div>
          </div>
          <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="modal"></button>
        </div>

        <form id="formJadwal" method="POST">
          @csrf
          <span id="methodSpoofJadwal"></span>

          <div class="modal-body">

            @if ($errors->any())
              <div class="alert alert-danger alert-dismissible d-flex align-items-start gap-2 border-0 mb-3"
                style="border-radius:8px;font-size:0.85rem;">
                <i class="ti tabler-alert-circle fs-5 flex-shrink-0 mt-1"></i>
                <ul class="mb-0 ps-2">
                  @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                  @endforeach
                </ul>
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
              </div>
            @endif

            <div class="row g-3">
              {{-- Kelas --}}
              <div class="col-md-12">
                <label class="form-label fw-semibold small" for="modal_kelas_id">
                  <i class="ti tabler-door me-1 text-info"></i> Kelas <span class="text-danger">*</span>
                </label>
                <select id="modal_kelas_id" name="kelas_id" class="form-select @error('kelas_id') is-invalid @enderror"
                  required>
                  <option value="">-- Pilih Kelas --</option>
                  @foreach ($kelasOptions as $k)
                    <option value="{{ $k->id }}" @selected(old('kelas_id') == $k->id)>{{ $k->nama }}</option>
                  @endforeach
                </select>
              </div>

              {{-- Guru --}}
              <div class="col-md-12">
                <label class="form-label fw-semibold small" for="modal_guru_id">
                  <i class="ti tabler-user-check me-1 text-info"></i> Guru Pengampu
                </label>
                <select id="modal_guru_id" name="guru_id" class="form-select @error('guru_id') is-invalid @enderror">
                  <option value="">-- Pilih Guru (Opsional) --</option>
                  @foreach ($guruOptions as $g)
                    <option value="{{ $g->id }}" @selected(old('guru_id') == $g->id)>{{ $g->nama_lengkap }}</option>
                  @endforeach
                </select>
              </div>

              {{-- Mata Pelajaran --}}
              <div class="col-md-12">
                <label class="form-label fw-semibold small" for="modal_mata_pelajaran">
                  <i class="ti tabler-book me-1 text-info"></i> Mata Pelajaran <span class="text-danger">*</span>
                </label>
                <input id="modal_mata_pelajaran" name="mata_pelajaran" type="text"
                  class="form-control @error('mata_pelajaran') is-invalid @enderror" placeholder="Contoh: Matematika"
                  value="{{ old('mata_pelajaran') }}" required>
              </div>

              {{-- Hari --}}
              <div class="col-md-12">
                <label class="form-label fw-semibold small" for="modal_hari">
                  <i class="ti tabler-calendar-event me-1 text-info"></i> Hari <span class="text-danger">*</span>
                </label>
                <select id="modal_hari" name="hari" class="form-select @error('hari') is-invalid @enderror" required>
                  <option value="">-- Pilih Hari --</option>
                  @foreach ($hariOptions as $h)
                    <option value="{{ $h }}" @selected(old('hari') === $h)>{{ $h }}</option>
                  @endforeach
                </select>
              </div>

              {{-- Jam Mulai --}}
              <div class="col-md-6">
                <label class="form-label fw-semibold small" for="modal_jam_mulai">
                  <i class="ti tabler-clock-play me-1 text-info"></i> Jam Mulai <span class="text-danger">*</span>
                </label>
                <input id="modal_jam_mulai" name="jam_mulai" type="time"
                  class="form-control @error('jam_mulai') is-invalid @enderror" value="{{ old('jam_mulai') }}"
                  required>
              </div>

              {{-- Jam Selesai --}}
              <div class="col-md-6">
                <label class="form-label fw-semibold small" for="modal_jam_selesai">
                  <i class="ti tabler-clock-stop me-1 text-info"></i> Jam Selesai <span class="text-danger">*</span>
                </label>
                <input id="modal_jam_selesai" name="jam_selesai" type="time"
                  class="form-control @error('jam_selesai') is-invalid @enderror" value="{{ old('jam_selesai') }}"
                  required>
              </div>
            </div>

          </div>

          <div class="modal-footer gap-2">
            <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">
              <i class="ti tabler-x me-1"></i> Batal
            </button>
            <button type="submit" class="btn btn-info fw-semibold px-4 shadow-sm">
              <i id="jadwalSubmitIcon" class="ti tabler-device-floppy me-1"></i>
              <span id="jadwalSubmitText">Simpan</span>
            </button>
          </div>
        </form>

      </div>
    </div>
  </div>

  {{-- ══════════════════════════════════════════════ --}}
  {{-- MODAL HAPUS JADWAL --}}
  {{-- ══════════════════════════════════════════════ --}}
  <div class="modal fade" id="modalHapusJadwal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:420px;">
      <div class="modal-content shadow-lg">
        <div class="modal-header">
          <div class="d-flex align-items-center gap-3">
            <div
              style="width:44px;height:44px;border-radius:10px;display:flex;align-items:center;justify-content:center;background:rgba(234,84,85,0.2);border:1px solid rgba(234,84,85,0.35);">
              <i class="ti tabler-alert-triangle text-danger fs-5"></i>
            </div>
            <div>
              <h5 class="modal-title mb-0 text-white fw-bold">Konfirmasi Hapus</h5>
              <small class="text-white-50">Tindakan ini tidak dapat dibatalkan.</small>
            </div>
          </div>
          <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body text-center py-4">
          <p class="mb-1 text-white-50">Yakin ingin menghapus jadwal:</p>
          <p class="fw-bold text-info fs-6 mb-0" id="hapusJadwalNama">—</p>
        </div>
        <div class="modal-footer gap-2 justify-content-center">
          <button type="button" class="btn btn-label-secondary px-4" data-bs-dismiss="modal">
            <i class="ti tabler-x me-1"></i> Batal
          </button>
          <form id="formHapusJadwal" method="POST">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger fw-semibold px-4 shadow-sm">
              <i class="ti tabler-trash me-1"></i> Hapus
            </button>
          </form>
        </div>
      </div>
    </div>
  </div>

@endsection

@section('page-script')
  <script>
    const jadwalStoreUrl = "{{ route('admin.jadwal.store') }}";
    const jadwalBaseUrl = "{{ url('admin/jadwal') }}";

    function openTambahJadwal() {
      const form = document.getElementById('formJadwal');
      form.action = jadwalStoreUrl;
      document.getElementById('methodSpoofJadwal').innerHTML = '';

      document.getElementById('modal_kelas_id').value = '';
      document.getElementById('modal_guru_id').value = '';
      document.getElementById('modal_mata_pelajaran').value = '';
      document.getElementById('modal_hari').value = '';
      document.getElementById('modal_jam_mulai').value = '';
      document.getElementById('modal_jam_selesai').value = '';

      document.getElementById('modalJadwalTitle').textContent = 'Tambah Jadwal';
      document.getElementById('modalJadwalSubtitle').textContent = 'Isi form di bawah untuk menambah jadwal pelajaran.';
      document.getElementById('modalJadwalIcon').className = 'ti tabler-calendar-plus text-info fs-5';
      document.getElementById('jadwalSubmitText').textContent = 'Simpan';
      document.getElementById('jadwalSubmitIcon').className = 'ti tabler-device-floppy me-1';

      new bootstrap.Modal(document.getElementById('modalJadwal')).show();
    }

    function openEditJadwal(data) {
      const form = document.getElementById('formJadwal');
      form.action = jadwalBaseUrl + '/' + data.id;
      document.getElementById('methodSpoofJadwal').innerHTML = '<input type="hidden" name="_method" value="PUT">';

      document.getElementById('modal_kelas_id').value = data.kelas_id;
      document.getElementById('modal_guru_id').value = data.guru_id || '';
      document.getElementById('modal_mata_pelajaran').value = data.mata_pelajaran;
      document.getElementById('modal_hari').value = data.hari;
      document.getElementById('modal_jam_mulai').value = data.jam_mulai;
      document.getElementById('modal_jam_selesai').value = data.jam_selesai;

      document.getElementById('modalJadwalTitle').textContent = 'Ubah Jadwal';
      document.getElementById('modalJadwalSubtitle').textContent = 'Perbarui data jadwal pelajaran yang dipilih.';
      document.getElementById('modalJadwalIcon').className = 'ti tabler-pencil text-info fs-5';
      document.getElementById('jadwalSubmitText').textContent = 'Perbarui';
      document.getElementById('jadwalSubmitIcon').className = 'ti tabler-refresh me-1';

      new bootstrap.Modal(document.getElementById('modalJadwal')).show();
    }

    function openHapusJadwal(id, nama) {
      document.getElementById('hapusJadwalNama').textContent = nama;
      document.getElementById('formHapusJadwal').action = jadwalBaseUrl + '/' + id;
      new bootstrap.Modal(document.getElementById('modalHapusJadwal')).show();
    }

    @if ($errors->any())
      document.addEventListener('DOMContentLoaded', function() {
        new bootstrap.Modal(document.getElementById('modalJadwal')).show();
      });
    @endif
  </script>
@endsection
