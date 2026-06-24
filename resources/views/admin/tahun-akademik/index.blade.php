@extends('layouts/layoutMaster')

@section('title', 'Tahun Ajaran')

@section('page-style')
  <style>
    .ta-row-hover {
      transition: background 0.15s ease;
    }

    .ta-row-hover:hover {
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
      transition: opacity 0.15s ease, transform 0.15s ease;
      border: none;
      cursor: pointer;
    }

    .action-btn:hover {
      opacity: 0.85;
      transform: translateY(-1px);
    }

    /* Modal custom */
    #modalTambahEdit .modal-content {
      border: 1px solid rgba(255, 255, 255, 0.1);
      background: #1e1e2d;
      border-radius: 12px;
      overflow: hidden;
    }

    #modalTambahEdit .modal-header {
      background: linear-gradient(135deg, #1a1a2e 0%, #0f3460 100%);
      border-bottom: 1px solid rgba(255, 255, 255, 0.08);
      padding: 1.25rem 1.5rem;
    }

    #modalTambahEdit .modal-body {
      padding: 1.5rem;
    }

    #modalTambahEdit .modal-footer {
      border-top: 1px solid rgba(255, 255, 255, 0.08);
      padding: 1rem 1.5rem;
      background: rgba(255, 255, 255, 0.02);
    }

    #modalTambahEdit .form-control,
    #modalTambahEdit .form-select {
      background: rgba(255, 255, 255, 0.06);
      border: 1px solid rgba(255, 255, 255, 0.12);
      color: inherit;
      border-radius: 8px;
      transition: border-color 0.2s ease, background 0.2s ease;
    }

    #modalTambahEdit .form-control:focus,
    #modalTambahEdit .form-select:focus {
      background: rgba(255, 255, 255, 0.09);
      border-color: rgba(255, 193, 7, 0.6);
      box-shadow: 0 0 0 3px rgba(255, 193, 7, 0.12);
    }

    #modalTambahEdit .form-control::placeholder {
      opacity: 0.4;
    }

    .modal-icon-header {
      width: 44px;
      height: 44px;
      border-radius: 10px;
      display: flex;
      align-items: center;
      justify-content: center;
      background: rgba(255, 193, 7, 0.2);
      border: 1px solid rgba(255, 193, 7, 0.35);
      flex-shrink: 0;
    }

    /* Delete confirm modal */
    #modalHapus .modal-content {
      border: 1px solid rgba(255, 255, 255, 0.1);
      background: #1e1e2d;
      border-radius: 12px;
      overflow: hidden;
    }

    #modalHapus .modal-header {
      background: linear-gradient(135deg, #2d1a1a 0%, #3d0f0f 100%);
      border-bottom: 1px solid rgba(255, 255, 255, 0.08);
    }

    #modalHapus .modal-footer {
      border-top: 1px solid rgba(255, 255, 255, 0.08);
      background: rgba(255, 255, 255, 0.02);
    }
    /* Toggle switch styling */
    .form-check-input:checked {
      background-color: #28c76f !important;
      border-color: #28c76f !important;
    }

    .form-check-input:focus {
      border-color: #28c76f !important;
      box-shadow: 0 0 0 0.2rem rgba(40, 199, 111, 0.25) !important;
    }

    /* Loading state */
    .toggle-loading {
      opacity: 0.5;
      pointer-events: none;
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
            <i class="ti tabler-calendar-stats text-warning"></i>
          </div>
          <div class="das-hero__logo-glow"></div>
        </div>

        <div class="das-hero__meta">
          <div class="das-hero__badge">
            <span class="pulse-dot"></span>
            <a href="{{ route('admin.master-data') }}" class="text-white text-decoration-none">Master Data</a> / Tahun Ajaran
          </div>
          <h4 class="das-hero__title text-gradient-gold">Data Tahun Ajaran</h4>
          <p class="das-hero__subtitle">Kelola tahun ajaran beserta status aktifnya.</p>
        </div>
      </div>

      <div class="das-hero__actions">
        <button type="button" class="btn das-btn --warning" onclick="openTambahModal()">
          <i class="ti tabler-plus me-1"></i> Tambah Tahun Ajaran
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

  {{-- TABLE CARD --}}
  <div class="das-panel">
    <div class="das-panel__header border-bottom py-3 px-4 d-flex align-items-center justify-content-between"
      style="border-color:rgba(255,255,255,0.08) !important;">
      <h6 class="das-panel__title mb-0 d-flex align-items-center gap-2">
        <i class="ti tabler-list text-warning"></i> Daftar Tahun Ajaran
      </h6>
      <span class="das-chip --warning">{{ count($tahunAkademik) }} Data</span>
    </div>
    <div class="das-panel__body p-0">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" style="color:inherit;">
          <thead
            style="background:rgba(255,255,255,0.04);font-size:0.75rem;text-transform:uppercase;letter-spacing:0.8px;opacity:0.7;">
            <tr>
              <th class="ps-4 py-3" style="width:50px;">#</th>
              <th class="py-3">Nama</th>
              <th class="py-3">Semester</th>
              <th class="py-3">Periode</th>
              <th class="py-3 text-center">Status</th>
              <th class="py-3 pe-4 text-end">Aksi</th>
            </tr>
          </thead>
          <tbody>
            @forelse($tahunAkademik as $item)
              <tr class="ta-row-hover">
                <td class="ps-4 text-white-50">{{ $loop->iteration }}</td>
                <td>
                  <div class="d-flex align-items-center gap-2">
                    <div class="avatar avatar-xs">
                      <span class="avatar-initial rounded bg-label-warning">
                        <i class="ti tabler-calendar-event" style="font-size:0.8rem;"></i>
                      </span>
                    </div>
                    <span class="fw-semibold">{{ $item->nama }}</span>
                  </div>
                </td>
                <td>
                  @if ($item->semester === 'ganjil')
                    <span class="badge bg-label-primary">Ganjil</span>
                  @else
                    <span class="badge bg-label-info">Genap</span>
                  @endif
                </td>
                <td>
                  <div class="d-flex align-items-center gap-1 text-white-50 small">
                    <i class="ti tabler-calendar me-1"></i>
                    {{ \Carbon\Carbon::parse($item->tanggal_mulai)->format('d M Y') }}
                    <i class="ti tabler-arrow-right mx-1" style="font-size:0.7rem;"></i>
                    {{ \Carbon\Carbon::parse($item->tanggal_selesai)->format('d M Y') }}
                  </div>
                </td>
                <td class="text-center">
                  <div class="form-check form-switch d-inline-block mb-0" style="padding-left:2em;">
                    <input class="form-check-input" type="checkbox" role="switch"
                      id="toggle-{{ $item->id }}"
                      {{ $item->is_aktif ? 'checked' : '' }}
                      onchange="toggleAktif({{ $item->id }}, this)"
                      style="cursor:pointer; width:2.5rem; height:1.3rem;">
                  </div>
                </td>
                <td class="pe-4 text-end">
                  <button type="button" class="action-btn bg-label-warning text-warning me-1"
                    onclick="openEditModal({
                      id: {{ $item->id }},
                      nama: '{{ addslashes($item->nama) }}',
                      semester: '{{ $item->semester }}',
                      tanggal_mulai: '{{ \Carbon\Carbon::parse($item->tanggal_mulai)->format('Y-m-d') }}',
                      tanggal_selesai: '{{ \Carbon\Carbon::parse($item->tanggal_selesai)->format('Y-m-d') }}',
                      is_aktif: {{ $item->is_aktif ? 'true' : 'false' }}
                    })">
                    <i class="ti tabler-pencil"></i> Ubah
                  </button>
                  <button type="button" class="action-btn bg-label-danger text-danger"
                    onclick="openHapusModal({{ $item->id }}, '{{ addslashes($item->nama) }}')">
                    <i class="ti tabler-trash"></i> Hapus
                  </button>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="6" class="text-center py-5">
                  <div class="d-flex flex-column align-items-center gap-2 opacity-50">
                    <i class="ti tabler-calendar-off" style="font-size:2.5rem;"></i>
                    <span class="small">Belum ada data tahun ajaran.</span>
                    <button type="button" class="btn btn-sm btn-label-warning mt-1" onclick="openTambahModal()">
                      <i class="ti tabler-plus me-1"></i> Tambah Sekarang
                    </button>
                  </div>
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>

  {{-- ═══════════════════════════════════════════ --}}
  {{-- MODAL TAMBAH / UBAH --}}
  {{-- ═══════════════════════════════════════════ --}}
  <div class="modal fade" id="modalTambahEdit" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:520px;">
      <div class="modal-content shadow-lg">

        {{-- Header --}}
        <div class="modal-header">
          <div class="d-flex align-items-center gap-3">
            <div class="modal-icon-header">
              <i id="modalIcon" class="ti tabler-plus text-warning fs-5"></i>
            </div>
            <div>
              <h5 id="modalTitle" class="modal-title mb-0 text-white fw-bold">Tambah Tahun Ajaran</h5>
              <small id="modalSubtitle" class="text-white-50">Isi form di bawah untuk menambah data baru.</small>
            </div>
          </div>
          <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="modal"></button>
        </div>

        {{-- Form --}}
        <form id="formTambahEdit" method="POST">
          @csrf
          <span id="methodSpoof"></span>

          <div class="modal-body">

            {{-- Validasi error (jika ada redirect back) --}}
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

            {{-- Nama --}}
            <div class="mb-3">
              <label class="form-label fw-semibold small" for="modal_nama">
                <i class="ti tabler-tag me-1 text-warning"></i> Nama Tahun Ajaran
              </label>
              <input id="modal_nama" name="nama" type="text"
                class="form-control @error('nama') is-invalid @enderror" placeholder="Contoh: 2025/2026"
                value="{{ old('nama') }}" required>
              @error('nama')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            {{-- Semester --}}
            <div class="mb-3">
              <label class="form-label fw-semibold small" for="modal_semester">
                <i class="ti tabler-calendar-half me-1 text-warning"></i> Semester
              </label>
              <select id="modal_semester" name="semester" class="form-select @error('semester') is-invalid @enderror"
                required>
                <option value="ganjil" {{ old('semester') === 'ganjil' ? 'selected' : '' }}>Ganjil</option>
                <option value="genap" {{ old('semester') === 'genap' ? 'selected' : '' }}>Genap</option>
              </select>
              @error('semester')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            {{-- Tanggal --}}
            <div class="row g-3 mb-3">
              <div class="col-6">
                <label class="form-label fw-semibold small" for="modal_mulai">
                  <i class="ti tabler-calendar-event me-1 text-warning"></i> Tanggal Mulai
                </label>
                <input id="modal_mulai" name="tanggal_mulai" type="date"
                  class="form-control @error('tanggal_mulai') is-invalid @enderror" value="{{ old('tanggal_mulai') }}"
                  required>
                @error('tanggal_mulai')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
              <div class="col-6">
                <label class="form-label fw-semibold small" for="modal_selesai">
                  <i class="ti tabler-calendar-due me-1 text-warning"></i> Tanggal Selesai
                </label>
                <input id="modal_selesai" name="tanggal_selesai" type="date"
                  class="form-control @error('tanggal_selesai') is-invalid @enderror"
                  value="{{ old('tanggal_selesai') }}" required>
                @error('tanggal_selesai')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
            </div>

            {{-- Status Aktif --}}
            <div class="rounded p-3"
              style="background:rgba(255,193,7,0.06);border:1px solid rgba(255,193,7,0.15);border-radius:8px;">
              <div class="form-check form-switch mb-0">
                <input class="form-check-input" type="checkbox" name="is_aktif" id="modal_is_aktif" value="1">
                <label class="form-check-label fw-semibold small" for="modal_is_aktif">
                  Tetapkan sebagai tahun ajaran aktif
                </label>
              </div>
              <div class="text-white-50 mt-1 ms-4 ps-1" style="font-size:0.75rem;">
                <i class="ti tabler-info-circle me-1"></i>
                Hanya satu tahun ajaran yang dapat aktif pada satu waktu.
              </div>
            </div>

          </div>

          {{-- Footer --}}
          <div class="modal-footer gap-2">
            <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">
              <i class="ti tabler-x me-1"></i> Batal
            </button>
            <button type="submit" class="btn btn-warning fw-semibold px-4 shadow-sm">
              <i id="submitIcon" class="ti tabler-device-floppy me-1"></i>
              <span id="submitText">Simpan</span>
            </button>
          </div>

        </form>
      </div>
    </div>
  </div>

  {{-- ═══════════════════════════════════════════ --}}
  {{-- MODAL HAPUS --}}
  {{-- ═══════════════════════════════════════════ --}}
  <div class="modal fade" id="modalHapus" tabindex="-1" aria-hidden="true">
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
          <p class="mb-1">Yakin ingin menghapus tahun ajaran:</p>
          <p class="fw-bold text-warning fs-6 mb-0" id="hapusNama">—</p>
        </div>
        <div class="modal-footer gap-2 justify-content-center">
          <button type="button" class="btn btn-label-secondary px-4" data-bs-dismiss="modal">
            <i class="ti tabler-x me-1"></i> Batal
          </button>
          <form id="formHapus" method="POST">
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
    const storeUrl = "{{ route('admin.tahun-akademik.store') }}";
    const updateBase = "{{ url('admin/tahun-akademik') }}";

    function openTambahModal() {
      const form = document.getElementById('formTambahEdit');
      form.action = storeUrl;

      // Reset method spoof
      document.getElementById('methodSpoof').innerHTML = '';

      // Reset fields
      document.getElementById('modal_nama').value = '';
      document.getElementById('modal_semester').value = 'ganjil';
      document.getElementById('modal_mulai').value = '';
      document.getElementById('modal_selesai').value = '';
      document.getElementById('modal_is_aktif').checked = false;

      // UI text
      document.getElementById('modalTitle').textContent = 'Tambah Tahun Ajaran';
      document.getElementById('modalSubtitle').textContent = 'Isi form di bawah untuk menambah data baru.';
      document.getElementById('modalIcon').className = 'ti tabler-plus text-warning fs-5';
      document.getElementById('submitText').textContent = 'Simpan';
      document.getElementById('submitIcon').className = 'ti tabler-device-floppy me-1';

      new bootstrap.Modal(document.getElementById('modalTambahEdit')).show();
    }

    function openEditModal(data) {
      const form = document.getElementById('formTambahEdit');
      form.action = updateBase + '/' + data.id;

      // PUT spoof
      document.getElementById('methodSpoof').innerHTML = '<input type="hidden" name="_method" value="PUT">';

      // Populate fields
      document.getElementById('modal_nama').value = data.nama;
      document.getElementById('modal_semester').value = data.semester;
      document.getElementById('modal_mulai').value = data.tanggal_mulai;
      document.getElementById('modal_selesai').value = data.tanggal_selesai;
      document.getElementById('modal_is_aktif').checked = data.is_aktif;

      // UI text
      document.getElementById('modalTitle').textContent = 'Ubah Tahun Ajaran';
      document.getElementById('modalSubtitle').textContent = 'Perbarui data yang ingin diubah.';
      document.getElementById('modalIcon').className = 'ti tabler-pencil text-warning fs-5';
      document.getElementById('submitText').textContent = 'Perbarui';
      document.getElementById('submitIcon').className = 'ti tabler-refresh me-1';

      new bootstrap.Modal(document.getElementById('modalTambahEdit')).show();
    }

    function openHapusModal(id, nama) {
      document.getElementById('hapusNama').textContent = nama;
      document.getElementById('formHapus').action = updateBase + '/' + id;
      new bootstrap.Modal(document.getElementById('modalHapus')).show();
    }

    // Jika ada validation error, buka kembali modal tambah
    @if ($errors->any())
      document.addEventListener('DOMContentLoaded', function() {
        new bootstrap.Modal(document.getElementById('modalTambahEdit')).show();
      });
    @endif

    // Toggle aktif/nonaktif tahun ajaran via AJAX
    function toggleAktif(id, checkbox) {
      const originalState = checkbox.checked;

      // Disable toggle selama proses
      checkbox.disabled = true;
      checkbox.closest('td').classList.add('toggle-loading');

      // Ambil CSRF token
      const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

      fetch('{{ url("admin/tahun-akademik") }}/' + id + '/toggle-aktif', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': csrfToken,
          'Accept': 'application/json'
        },
        body: JSON.stringify({})
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          checkbox.checked = data.is_aktif;

          // Update semua toggle sesuai status di database
          document.querySelectorAll('[id^="toggle-"]').forEach(el => {
            const toggleId = parseInt(el.id.replace('toggle-', ''));
            if (toggleId !== id) {
              el.checked = false;
            }
          });

          // Tampilkan notifikasi sukses
          showToast('success', data.message);
        } else {
          // Kembalikan ke posisi semula
          checkbox.checked = originalState;
          showToast('error', data.message || 'Gagal mengubah status');
        }
      })
      .catch(error => {
        // Kembalikan ke posisi semula
        checkbox.checked = originalState;
        showToast('error', 'Terjadi kesalahan jaringan. Silakan coba lagi.');
        console.error('Toggle error:', error);
      })
      .finally(() => {
        // Enable toggle kembali
        checkbox.disabled = false;
        checkbox.closest('td').classList.remove('toggle-loading');
      });
    }

    // Fungsi toast sederhana
    function showToast(type, message) {
      const container = document.querySelector('.das-hero') || document.querySelector('.das-panel');
      if (!container) return;

      const alertDiv = document.createElement('div');
      alertDiv.className = `alert alert-${type === 'success' ? 'success' : 'danger'} alert-dismissible d-flex align-items-center gap-2 mb-4 border-0 shadow-sm`;
      alertDiv.style.cssText = 'border-radius:8px;position:fixed;top:20px;right:20px;z-index:9999;max-width:400px;';
      alertDiv.innerHTML = `
        <i class="ti ${type === 'success' ? 'tabler-circle-check' : 'tabler-alert-circle'} fs-5"></i>
        <span>${message}</span>
        <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
      `;
      document.body.appendChild(alertDiv);

      // Auto hide setelah 3 detik
      setTimeout(() => {
        alertDiv.remove();
      }, 3000);
    }
  </script>
@endsection
