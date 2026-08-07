@extends('layouts/layoutMaster')

@section('title', 'Manajemen Pengumuman')

@section('page-style')
  <style>
    .pengumuman-row-hover {
      transition: background 0.15s ease;
    }

    .pengumuman-row-hover:hover {
      background: rgba(255, 255, 255, 0.04) !important;
    }

    .row-pinned {
      background: rgba(255, 159, 67, 0.06) !important;
      border-left: 4px solid #ff9f43 !important;
    }

    .badge-glass {
      padding: 4px 10px;
      border-radius: 4px;
      font-size: 0.7rem;
      font-weight: 700;
      letter-spacing: 0.4px;
      display: inline-flex;
      align-items: center;
      text-transform: uppercase;
    }

    .badge-glass.--informasi {
      background: rgba(0, 207, 234, 0.15);
      color: #00cfe8;
      border: 1px solid rgba(0, 207, 234, 0.35);
    }

    .badge-glass.--penting {
      background: rgba(255, 159, 67, 0.15);
      color: #ff9f43;
      border: 1px solid rgba(255, 159, 67, 0.35);
    }

    .badge-glass.--kegiatan {
      background: rgba(115, 103, 240, 0.15);
      color: #a5a2f7;
      border: 1px solid rgba(115, 103, 240, 0.35);
    }

    .badge-glass.--mendesak {
      background: rgba(234, 84, 85, 0.15);
      color: #ea5455;
      border: 1px solid rgba(234, 84, 85, 0.35);
    }

    .badge-glass.--libur {
      background: rgba(40, 199, 111, 0.15);
      color: #28c76f;
      border: 1px solid rgba(40, 199, 111, 0.35);
    }

    .badge-target {
      padding: 4px 10px;
      border-radius: 4px;
      font-size: 0.75rem;
      font-weight: 600;
      display: inline-flex;
      align-items: center;
      gap: 5px;
    }

    .badge-target.--semua {
      background: rgba(115, 103, 240, 0.12);
      color: #a5a2f7;
      border: 1px solid rgba(115, 103, 240, 0.3);
    }

    .badge-target.--guru {
      background: rgba(0, 207, 234, 0.12);
      color: #00cfe8;
      border: 1px solid rgba(0, 207, 234, 0.3);
    }

    .badge-target.--siswa {
      background: rgba(255, 159, 67, 0.12);
      color: #ff9f43;
      border: 1px solid rgba(255, 159, 67, 0.3);
    }

    .badge-target.--orang_tua {
      background: rgba(40, 199, 111, 0.12);
      color: #28c76f;
      border: 1px solid rgba(40, 199, 111, 0.3);
    }

    .badge-target.--staff {
      background: rgba(168, 170, 174, 0.12);
      color: #d0d2d6;
      border: 1px solid rgba(168, 170, 174, 0.3);
    }

    .badge-target.--kelas {
      background: rgba(234, 84, 85, 0.12);
      color: #ea5455;
      border: 1px solid rgba(234, 84, 85, 0.3);
    }

    .badge-status {
      padding: 4px 10px;
      border-radius: 4px;
      font-size: 0.75rem;
      font-weight: 600;
      display: inline-flex;
      align-items: center;
      width: fit-content;
    }

    .badge-status.--aktif {
      background: rgba(40, 199, 111, 0.15) !important;
      color: #28c76f !important;
      border: 1px solid rgba(40, 199, 111, 0.35) !important;
    }

    .badge-status.--nonaktif {
      background: rgba(168, 170, 174, 0.15) !important;
      color: #a6a8ab !important;
      border: 1px solid rgba(168, 170, 174, 0.35) !important;
    }

    .action-btn {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      width: 32px;
      height: 32px;
      border-radius: 4px;
      transition: all 0.2s ease;
      border: none;
      background: rgba(255, 255, 255, 0.05);
      color: inherit;
    }

    .action-btn:hover {
      transform: translateY(-2px);
      background: rgba(255, 255, 255, 0.1);
    }

    .das-modal {
      background: #1a1a2e !important;
      border: 1px solid rgba(255, 255, 255, 0.08) !important;
      border-radius: 5px !important;
      overflow: hidden;
      backdrop-filter: blur(12px) saturate(180%);
    }

    .das-modal-head {
      border-bottom: 1px solid rgba(255, 255, 255, 0.08);
      background: rgba(115, 103, 240, 0.05);
      padding: 1.25rem;
    }

    .das-modal-title {
      font-size: 1rem;
      font-weight: 700;
      color: #fff;
      margin: 0;
    }

    .das-modal-body {
      padding: 1.5rem;
    }

    .form-control,
    .form-select {
      background: rgba(255, 255, 255, 0.05) !important;
      border: 1px solid rgba(255, 255, 255, 0.1) !important;
      color: #fff !important;
    }

    .form-control:focus,
    .form-select:focus {
      background: rgba(255, 255, 255, 0.08) !important;
      border-color: var(--bs-info) !important;
    }

    .form-control::placeholder,
    #filterSearch::placeholder {
      color: rgba(255, 255, 255, 0.35) !important;
    }

    .form-select option {
      background: #1a1a2e;
      color: #ccc;
    }

    .das-swal-popup {
      background: rgba(26, 26, 46, 0.95) !important;
      backdrop-filter: blur(16px) saturate(180%) !important;
      border: 1px solid rgba(255, 255, 255, 0.1) !important;
      border-radius: 20px !important;
      box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5) !important;
    }

    .das-swal-title {
      color: #fff !important;
      font-weight: 700 !important;
      font-size: 1.5rem !important;
      text-align: center !important;
    }

    .das-swal-html {
      color: rgba(255, 255, 255, 0.7) !important;
      font-size: 0.95rem !important;
    }

    .das-swal-confirm {
      padding: 10px 24px !important;
      font-weight: 600 !important;
      border-radius: 10px !important;
      font-size: 0.875rem !important;
    }

    .extra-small {
      font-size: 0.7rem;
    }

    .das-btn.--purple {
      background: rgba(115, 103, 240, 0.15);
      border-color: rgba(115, 103, 240, 0.35);
      color: #a5a2f7;
    }
    .das-btn.--purple:hover {
      background: rgba(115, 103, 240, 0.3);
      color: #ffffff;
      box-shadow: 0 0 12px rgba(115, 103, 240, 0.2);
    }
  </style>
@endsection

@section('content')
  {{-- HERO HEADER --}}
  <div class="das-hero mb-4">
    <div class="das-hero__bg"></div>
    <div class="das-hero__glass"></div>
    <div class="das-hero__grid-lines"></div>

    <div class="das-hero__inner">
      <div class="das-hero__identity">
        <div class="das-hero__logo-wrapper">
          <div class="das-hero__logo-placeholder">
            <i class="ti tabler-speakerphone text-info"></i>
          </div>
          <div class="das-hero__logo-glow"></div>
        </div>

        <div class="das-hero__meta">
          <div class="das-hero__badge">
            <span class="pulse-dot"></span>
            Manajemen Informasi / Pengumuman Target
          </div>
          <h4 class="das-hero__title text-gradient-gold">Manajemen Pengumuman</h4>
          <p class="das-hero__subtitle">Buat dan kelola pengumuman berdasarkan target sasaran penerima.</p>
        </div>
      </div>

      <div class="das-hero__actions">
        <button type="button" class="btn das-btn --purple" onclick="openCreateModal()">
          <i class="ti tabler-plus me-1"></i> Tambah Pengumuman
        </button>
      </div>
    </div>
  </div>

  {{-- FLASH MESSAGES --}}
  @if (session('success'))
    <div class="alert alert-success alert-dismissible d-flex align-items-center gap-2 mb-4 border-0 shadow-sm" role="alert" style="border-radius:8px;">
      <i class="ti tabler-circle-check fs-5"></i>
      <span>{{ session('success') }}</span>
      <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
    </div>
  @endif

  {{-- FILTER & SEARCH PANEL --}}
  <div class="das-panel mb-4">
    <div class="das-panel__body py-3 px-4">
      <form id="filterForm" class="row g-3 align-items-center">
        <div class="col-12 col-md-4">
          <div class="input-group input-group-merge">
            <span class="input-group-text bg-transparent border-secondary text-white-50"><i class="ti tabler-search"></i></span>
            <input type="text" id="filterSearch" name="search" class="form-control border-secondary text-white" placeholder="Cari judul atau konten..." value="{{ request('search') }}">
          </div>
        </div>
        <div class="col-6 col-md-2">
          <select id="filterTarget" name="target" class="form-select border-secondary">
            <option value="">-- Target Sasaran --</option>
            <option value="semua" {{ request('target') === 'semua' ? 'selected' : '' }}>Semua Pengguna</option>
            <option value="guru" {{ request('target') === 'guru' ? 'selected' : '' }}>Khusus Guru</option>
            <option value="siswa" {{ request('target') === 'siswa' ? 'selected' : '' }}>Khusus Siswa</option>
            <option value="orang_tua" {{ request('target') === 'orang_tua' ? 'selected' : '' }}>Khusus Wali/Ortu</option>
            <option value="staff" {{ request('target') === 'staff' ? 'selected' : '' }}>Khusus Staff</option>
            <option value="kelas" {{ request('target') === 'kelas' ? 'selected' : '' }}>Spesifik Kelas</option>
          </select>
        </div>
        <div class="col-6 col-md-2">
          <select id="filterKategori" name="kategori" class="form-select border-secondary">
            <option value="">-- Kategori --</option>
            <option value="informasi" {{ request('kategori') === 'informasi' ? 'selected' : '' }}>Informasi</option>
            <option value="penting" {{ request('kategori') === 'penting' ? 'selected' : '' }}>Penting</option>
            <option value="kegiatan" {{ request('kategori') === 'kegiatan' ? 'selected' : '' }}>Kegiatan</option>
            <option value="mendesak" {{ request('kategori') === 'mendesak' ? 'selected' : '' }}>Mendesak</option>
            <option value="libur" {{ request('kategori') === 'libur' ? 'selected' : '' }}>Libur</option>
          </select>
        </div>
        <div class="col-6 col-md-2">
          <select id="filterStatus" name="is_aktif" class="form-select border-secondary">
            <option value="">-- Status --</option>
            <option value="1" {{ request('is_aktif') === '1' ? 'selected' : '' }}>Aktif</option>
            <option value="0" {{ request('is_aktif') === '0' ? 'selected' : '' }}>Nonaktif</option>
          </select>
        </div>
        <div class="col-6 col-md-2 d-flex gap-2">
          <button type="submit" class="btn btn-primary w-100"><i class="ti tabler-filter me-1"></i> Filter</button>
          <a href="{{ route('admin.pengumuman.index') }}" class="btn btn-secondary" title="Reset Filter"><i class="ti tabler-refresh"></i></a>
        </div>
      </form>
    </div>
  </div>

  {{-- DATA PANEL --}}
  <div class="das-panel" id="tableContainer">
    @include('admin.pengumuman.table')
  </div>

  {{-- MODAL TAMBAH / EDIT --}}
  <div class="modal fade" id="modalPengumuman" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
      <div class="modal-content das-modal">
        <div class="das-modal-head d-flex align-items-center justify-content-between">
          <h5 class="das-modal-title" id="modalTitle">Tambah Pengumuman</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form id="formPengumuman" method="POST" action="" enctype="multipart/form-data">
          @csrf
          <input type="hidden" name="_method" id="formMethod" value="POST">
          <div class="das-modal-body text-white">
            <div class="mb-3">
              <label for="inputJudul" class="form-label">Judul Pengumuman <span class="text-danger">*</span></label>
              <input type="text" class="form-control" id="inputJudul" name="judul" required placeholder="Contoh: Pengumuman Jadwal Ujian Semester Genap">
            </div>

            <div class="row mb-3">
              <div class="col-12 col-md-6">
                <label for="inputKategori" class="form-label">Kategori <span class="text-danger">*</span></label>
                <select class="form-select" id="inputKategori" name="kategori" required>
                  <option value="informasi">Informasi (Biasa)</option>
                  <option value="penting">Penting</option>
                  <option value="kegiatan">Kegiatan Sekolah</option>
                  <option value="mendesak">Mendesak / Darurat</option>
                  <option value="libur">Pengumuman Libur</option>
                </select>
              </div>
              <div class="col-12 col-md-6">
                <label for="inputTarget" class="form-label">Target Penerima <span class="text-danger">*</span></label>
                <select class="form-select" id="inputTarget" name="target" required>
                  <option value="semua">Semua Pengguna (Publik)</option>
                  <option value="guru">Khusus Guru</option>
                  <option value="siswa">Khusus Siswa</option>
                  <option value="orang_tua">Khusus Orang Tua / Wali</option>
                  <option value="staff">Khusus Staff / Tata Usaha</option>
                  <option value="kelas">Spesifik Kelas</option>
                </select>
              </div>
            </div>

            <div class="mb-3 d-none" id="wrapperTargetKelas">
              <label for="inputTargetKelas" class="form-label">Pilih Kelas Target <span class="text-danger">*</span></label>
              <select class="form-select" id="inputTargetKelas" name="target_kelas_id">
                <option value="">-- Pilih Kelas --</option>
                @foreach($kelases as $k)
                  <option value="{{ $k->id }}">{{ $k->nama }} ({{ $k->jurusan }})</option>
                @endforeach
              </select>
            </div>

            <div class="mb-3">
              <label for="inputKonten" class="form-label">Isi Pengumuman <span class="text-danger">*</span></label>
              <textarea class="form-control" id="inputKonten" name="konten" rows="5" required placeholder="Tuliskan isi detail pengumuman di sini..."></textarea>
            </div>

            <div class="row mb-3">
              <div class="col-12 col-md-6">
                <label for="inputTanggalMulai" class="form-label">Jadwal Tampil (Mulai)</label>
                <input type="datetime-local" class="form-control" id="inputTanggalMulai" name="tanggal_mulai">
                <div class="form-text text-white-50 extra-small">Kosongkan jika langsung ingin ditampilkan saat ini.</div>
              </div>
              <div class="col-12 col-md-6">
                <label for="inputTanggalSelesai" class="form-label">Jadwal Berakhir (Selesai)</label>
                <input type="datetime-local" class="form-control" id="inputTanggalSelesai" name="tanggal_selesai">
                <div class="form-text text-white-50 extra-small">Kosongkan jika pengumuman berlaku selamanya.</div>
              </div>
            </div>

            <div class="mb-3">
              <label for="inputLampiran" class="form-label">File Lampiran (PDF / Gambar / Dokumen)</label>
              <input type="file" class="form-control" id="inputLampiran" name="lampiran" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.xls,.xlsx">
              <div class="form-text text-white-50 extra-small" id="lampiranInfo">Maksimal file 10 MB.</div>
            </div>

            <div class="row g-3 mb-3">
              <div class="col-6 col-md-3">
                <div class="form-check form-switch">
                  <input class="form-check-input" type="checkbox" id="inputIsPinned" name="is_pinned" value="1">
                  <label class="form-check-label text-white" for="inputIsPinned">Sematkan (Pin)</label>
                </div>
              </div>
              <div class="col-6 col-md-3">
                <div class="form-check form-switch">
                  <input class="form-check-input" type="checkbox" id="inputIsPopup" name="is_popup" value="1">
                  <label class="form-check-label text-warning fw-bold" for="inputIsPopup">Popup Modal</label>
                </div>
              </div>
              <div class="col-6 col-md-3">
                <div class="form-check form-switch">
                  <input class="form-check-input" type="checkbox" id="inputForceRead" name="force_read" value="1">
                  <label class="form-check-label text-info" for="inputForceRead">Wajib Baca</label>
                </div>
              </div>
              <div class="col-6 col-md-3">
                <div class="form-check form-switch">
                  <input class="form-check-input" type="checkbox" id="inputIsAktif" name="is_aktif" value="1" checked>
                  <label class="form-check-label text-white" for="inputIsAktif">Status Aktif</label>
                </div>
              </div>
            </div>
          </div>
          <div class="modal-footer border-top border-secondary p-3">
            <button type="button" class="btn btn-outline-secondary text-white" data-bs-dismiss="modal">Batal</button>
            <button type="submit" class="btn btn-primary" id="btnSubmit">Simpan Pengumuman</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  {{-- MODAL DETAIL --}}
  <div class="modal fade" id="modalDetail" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
      <div class="modal-content das-modal">
        <div class="das-modal-head d-flex align-items-center justify-content-between">
          <h5 class="das-modal-title" id="detailJudul">Detail Pengumuman</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="das-modal-body text-white">
          <div class="d-flex align-items-center gap-2 mb-3">
            <span class="badge bg-primary" id="detailKategori"></span>
            <span class="badge bg-label-info" id="detailTarget"></span>
            <span class="text-white-50 small ms-auto" id="detailTanggal"></span>
          </div>

          <div class="p-3 mb-3 text-white" id="detailKonten" style="background: rgba(255, 255, 255, 0.05) !important; border: 1px solid rgba(255, 255, 255, 0.1) !important; border-radius: 4px !important; white-space: pre-line; line-height: 1.6; color: #ffffff !important;"></div>

          <div id="detailLampiranWrapper" class="d-none">
            <h6 class="text-white-50 small mb-2"><i class="ti tabler-paperclip me-1"></i> File Lampiran:</h6>
            <a href="#" id="detailLampiranLink" target="_blank" class="btn btn-sm btn-outline-info">
              <i class="ti tabler-download me-1"></i> Unduh File Lampiran
            </a>
          </div>
        </div>
        <div class="modal-footer border-top border-secondary p-3">
          <button type="button" class="btn btn-secondary text-white" data-bs-dismiss="modal">Tutup</button>
        </div>
      </div>
    </div>
  </div>
@endsection

@section('page-script')
  <script>
    var _modalInstance = null;
    var _modalDetailInstance = null;

    function _getModalRefs() {
      if (!_modalInstance) {
        _modalInstance = new bootstrap.Modal(document.getElementById('modalPengumuman'));
      }
      return {
        modal:  _modalInstance,
        form:   document.getElementById('formPengumuman'),
        title:  document.getElementById('modalTitle'),
        method: document.getElementById('formMethod'),
        btn:    document.getElementById('btnSubmit')
      };
    }

    function toggleTargetKelasField() {
      const targetVal = document.getElementById('inputTarget').value;
      const wrapper = document.getElementById('wrapperTargetKelas');
      const selectKelas = document.getElementById('inputTargetKelas');

      if (targetVal === 'kelas') {
        wrapper.classList.remove('d-none');
        selectKelas.required = true;
      } else {
        wrapper.classList.add('d-none');
        selectKelas.required = false;
        selectKelas.value = "";
      }
    }

    document.getElementById('inputTarget').addEventListener('change', toggleTargetKelasField);

    function openCreateModal() {
      var refs = _getModalRefs();
      refs.title.innerText = "Tambah Pengumuman";
      refs.form.action = "{{ route('admin.pengumuman.store') }}";
      refs.method.value = "POST";

      document.getElementById('inputJudul').value = "";
      document.getElementById('inputKategori').value = "informasi";
      document.getElementById('inputTarget').value = "semua";
      document.getElementById('inputTargetKelas').value = "";
      document.getElementById('inputKonten').value = "";
      document.getElementById('inputTanggalMulai').value = "";
      document.getElementById('inputTanggalSelesai').value = "";
      document.getElementById('inputLampiran').value = "";
      document.getElementById('inputIsPinned').checked = false;
      document.getElementById('inputIsPopup').checked = false;
      document.getElementById('inputForceRead').checked = false;
      document.getElementById('inputIsAktif').checked = true;
      document.getElementById('lampiranInfo').innerText = "Maksimal file 10 MB.";

      toggleTargetKelasField();
      refs.modal.show();
    }

    function openEditModal(data) {
      var refs = _getModalRefs();
      refs.title.innerText = "Ubah Pengumuman";
      refs.form.action = "{{ route('admin.pengumuman.update', ':id') }}".replace(':id', data.id);
      refs.method.value = "PUT";

      document.getElementById('inputJudul').value = data.judul;
      document.getElementById('inputKategori').value = data.kategori || "informasi";
      document.getElementById('inputTarget').value = data.target || "semua";
      document.getElementById('inputTargetKelas').value = data.target_kelas_id || "";
      document.getElementById('inputKonten').value = data.konten || "";
      
      document.getElementById('inputTanggalMulai').value = data.tanggal_mulai ? data.tanggal_mulai.replace(' ', 'T').substring(0, 16) : "";
      document.getElementById('inputTanggalSelesai').value = data.tanggal_selesai ? data.tanggal_selesai.replace(' ', 'T').substring(0, 16) : "";
      
      document.getElementById('inputLampiran').value = "";
      document.getElementById('inputIsPinned').checked = data.is_pinned;
      document.getElementById('inputIsPopup').checked = data.is_popup || false;
      document.getElementById('inputForceRead').checked = data.force_read || false;
      document.getElementById('inputIsAktif').checked = data.is_aktif;
      
      if (data.lampiran) {
        document.getElementById('lampiranInfo').innerHTML = 'File terpasang: <span class="text-info">' + data.lampiran.split('/').pop() + '</span> (Biarkan kosong jika tidak ingin mengganti).';
      } else {
        document.getElementById('lampiranInfo').innerText = "Maksimal file 10 MB.";
      }

      toggleTargetKelasField();
      refs.modal.show();
    }

    document.addEventListener('DOMContentLoaded', function () {
      const container = document.getElementById('tableContainer');
      const filterForm = document.getElementById('filterForm');

      const formPengumuman = document.getElementById('formPengumuman');
      if (formPengumuman) {
        formPengumuman.addEventListener('submit', function (e) {
          e.preventDefault();
          const refs = _getModalRefs();
          const btnSubmit = refs.btn;
          const originalBtnHtml = btnSubmit.innerHTML;

          btnSubmit.disabled = true;
          btnSubmit.innerHTML = '<i class="ti tabler-loader spinner me-1"></i> Menyimpan...';

          const formData = new FormData(formPengumuman);

          fetch(formPengumuman.action, {
            method: 'POST',
            headers: {
              'X-Requested-With': 'XMLHttpRequest',
              'Accept': 'application/json'
            },
            body: formData
          })
          .then(async function (res) {
            const data = await res.json().catch(() => ({}));
            btnSubmit.disabled = false;
            btnSubmit.innerHTML = originalBtnHtml;

            if (res.ok && data.success) {
              refs.modal.hide();
              Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: data.message || 'Pengumuman berhasil disimpan.',
                timer: 1500,
                showConfirmButton: false,
                customClass: {
                  popup: 'das-swal-popup',
                  title: 'das-swal-title',
                  htmlContainer: 'das-swal-html'
                }
              });
              loadTable(window.location.href);
            } else {
              let errorMsg = data.message || 'Gagal menyimpan pengumuman.';
              if (data.errors) {
                errorMsg = Object.values(data.errors).flat().join('<br>');
              }
              Swal.fire({
                icon: 'error',
                title: 'Gagal Menyimpan!',
                html: errorMsg,
                customClass: {
                  popup: 'das-swal-popup',
                  title: 'das-swal-title',
                  htmlContainer: 'das-swal-html',
                  confirmButton: 'btn btn-primary das-swal-confirm'
                },
                buttonsStyling: false
              });
            }
          })
          .catch(function (err) {
            btnSubmit.disabled = false;
            btnSubmit.innerHTML = originalBtnHtml;
            console.error('Form submit error:', err);
            Swal.fire({
              icon: 'error',
              title: 'Error!',
              text: 'Terjadi kesalahan sistem saat menyimpan data.',
              customClass: {
                popup: 'das-swal-popup',
                title: 'das-swal-title',
                htmlContainer: 'das-swal-html',
                confirmButton: 'btn btn-primary das-swal-confirm'
              },
              buttonsStyling: false
            });
          });
        });
      }

      function loadTable(url) {
        if (!container) return;
        container.classList.add('opacity-50');

        let targetUrl = url;
        if (filterForm) {
          const formData = new FormData(filterForm);
          const params = new URLSearchParams(formData);
          if (!targetUrl.includes('?')) {
            targetUrl += '?' + params.toString();
          }
        }

        fetch(targetUrl, {
          headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'text/html, application/xhtml+xml, */*'
          }
        })
        .then(function (res) {
          if (!res.ok) throw new Error('Network error');
          return res.text();
        })
        .then(function (html) {
          container.innerHTML = html;
          container.classList.remove('opacity-50');
        })
        .catch(function (err) {
          console.error('Load table error:', err);
          container.classList.remove('opacity-50');
        });
      }

      if (filterForm) {
        filterForm.addEventListener('submit', function (e) {
          e.preventDefault();
          loadTable("{{ route('admin.pengumuman.index') }}");
        });
      }

      document.addEventListener('click', function (e) {
        const paginationLink = e.target.closest('.pagination a');
        if (paginationLink) {
          e.preventDefault();
          loadTable(paginationLink.getAttribute('href'));
          return;
        }

        const btnEdit = e.target.closest('.btn-edit-pengumuman');
        if (btnEdit) {
          e.preventDefault();
          const data = JSON.parse(btnEdit.dataset.data);
          openEditModal(data);
          return;
        }

        const btnTogglePin = e.target.closest('.btn-toggle-pin');
        if (btnTogglePin) {
          e.preventDefault();
          const id = btnTogglePin.dataset.id;
          fetch("{{ route('admin.pengumuman.toggle-pin', ':id') }}".replace(':id', id), {
            method: 'POST',
            headers: {
              'Content-Type': 'application/x-www-form-urlencoded',
              'X-CSRF-TOKEN': '{{ csrf_token() }}',
              'X-Requested-With': 'XMLHttpRequest',
              'Accept': 'application/json'
            },
            body: '_token=' + encodeURIComponent('{{ csrf_token() }}') + '&_method=PATCH'
          })
          .then(res => res.json())
          .then(res => {
            if (res.success) {
              loadTable(window.location.href);
            }
          });
          return;
        }

        const btnDelete = e.target.closest('.btn-delete-pengumuman');
        if (btnDelete) {
          e.preventDefault();
          const deleteUrl = btnDelete.dataset.url;
          const judul = btnDelete.dataset.judul || 'pengumuman ini';

          Swal.fire({
            title: 'Hapus Pengumuman?',
            html: 'Pengumuman <b>' + judul + '</b> akan dihapus secara permanen.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ea5455',
            cancelButtonColor: '#82868b',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal',
            reverseButtons: true,
            customClass: {
              popup: 'das-swal-popup',
              title: 'das-swal-title',
              htmlContainer: 'das-swal-html',
              confirmButton: 'btn btn-danger das-swal-confirm me-2',
              cancelButton: 'btn btn-secondary das-swal-cancel'
            }
          }).then((result) => {
            if (result.isConfirmed) {
              fetch(deleteUrl, {
                method: 'POST',
                headers: {
                  'Content-Type': 'application/x-www-form-urlencoded',
                  'X-CSRF-TOKEN': '{{ csrf_token() }}',
                  'X-Requested-With': 'XMLHttpRequest',
                  'Accept': 'application/json'
                },
                body: '_token=' + encodeURIComponent('{{ csrf_token() }}') + '&_method=DELETE'
              })
              .then(res => res.json())
              .then(response => {
                if (response.success) {
                  Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: response.message,
                    timer: 1500,
                    showConfirmButton: false,
                    customClass: {
                      popup: 'das-swal-popup',
                      title: 'das-swal-title',
                      htmlContainer: 'das-swal-html'
                    }
                  });
                  loadTable(window.location.href);
                }
              });
            }
          });
          return;
        }

        const btnDetail = e.target.closest('.btn-detail-pengumuman');
        if (btnDetail) {
          e.preventDefault();
          const id = btnDetail.dataset.id;
          fetch("{{ route('admin.pengumuman.show', ':id') }}".replace(':id', id), {
            headers: {
              'X-Requested-With': 'XMLHttpRequest',
              'Accept': 'application/json'
            }
          })
          .then(res => res.json())
          .then(res => {
            if (res.success && res.data) {
              const d = res.data;
              document.getElementById('detailJudul').innerText = d.judul;
              document.getElementById('detailKategori').innerText = d.kategori.toUpperCase();
              document.getElementById('detailTarget').innerText = 'Target: ' + d.target.toUpperCase() + (d.target_kelas ? ' (' + d.target_kelas.nama + ')' : '');
              document.getElementById('detailTanggal').innerText = new Date(d.created_at).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
              document.getElementById('detailKonten').innerText = d.konten;

              const lampiranWrapper = document.getElementById('detailLampiranWrapper');
              const lampiranLink = document.getElementById('detailLampiranLink');
              if (d.lampiran) {
                lampiranLink.href = "{{ asset('storage') }}/" + d.lampiran;
                lampiranWrapper.classList.remove('d-none');
              } else {
                lampiranWrapper.classList.add('d-none');
              }

              if (!_modalDetailInstance) {
                _modalDetailInstance = new bootstrap.Modal(document.getElementById('modalDetail'));
              }
              _modalDetailInstance.show();
            }
          });
        }
      });
    });
  </script>
@endsection
