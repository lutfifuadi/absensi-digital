@extends('layouts/layoutMaster')

@section('title', 'Tambah Catatan Pelanggaran')

@section('page-style')
  <style>
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

    .form-control::placeholder {
      color: rgba(255, 255, 255, 0.35) !important;
    }

    /* Autocomplete Search Result Box */
    .autocomplete-results {
      position: absolute;
      background: #1a1a2e;
      border: 1px solid rgba(255, 255, 255, 0.15);
      border-radius: 12px;
      width: 100%;
      z-index: 1000;
      max-height: 250px;
      overflow-y: auto;
      box-shadow: 0 15px 35px rgba(0, 0, 0, 0.6);
      backdrop-filter: blur(16px);
    }

    .autocomplete-item {
      padding: 12px 16px;
      cursor: pointer;
      border-bottom: 1px solid rgba(255, 255, 255, 0.05);
      transition: background 0.2s ease;
      color: #fff;
    }

    .autocomplete-item:hover {
      background: rgba(115, 103, 240, 0.15);
    }

    .autocomplete-item:last-child {
      border-bottom: none;
    }

    /* SWEETALERT2 CUSTOM PREMIUM */
    .das-swal-popup {
      background: rgba(26, 26, 46, 0.95) !important;
      backdrop-filter: blur(16px) saturate(180%) !important;
      border: 1px solid rgba(255, 255, 255, 0.1) !important;
      border-radius: 16px !important;
      box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5) !important;
    }

    .das-swal-title {
      color: #fff !important;
      font-weight: 700 !important;
      font-size: 1.5rem !important;
      text-align: center !important;
      width: 100% !important;
    }

    .das-swal-html {
      color: rgba(255, 255, 255, 0.7) !important;
      font-size: 0.95rem !important;
    }

    .das-swal-confirm {
      padding: 10px 24px !important;
      font-weight: 600 !important;
      border-radius: 8px !important;
      font-size: 0.875rem !important;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }

    .das-swal-cancel {
      padding: 10px 24px !important;
      font-weight: 600 !important;
      border-radius: 8px !important;
      font-size: 0.875rem !important;
      background: rgba(255, 255, 255, 0.05) !important;
      color: #fff !important;
      border: 1px solid rgba(255, 255, 255, 0.1) !important;
    }

    .preview-box {
      background: rgba(255, 255, 255, 0.03);
      border: 1px solid rgba(255, 255, 255, 0.08);
      border-radius: 10px;
    }
  </style>
  @vite(['resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'])
@endsection

@section('content')
  <div x-data="createPelanggaranHandler()" class="w-100">
    {{-- HERO HEADER --}}
    <div class="das-hero mb-4">
      <div class="das-hero__bg"></div>
      <div class="das-hero__glass"></div>
      <div class="das-hero__grid-lines"></div>

      <div class="das-hero__inner">
        <div class="das-hero__identity">
          <div class="das-hero__logo-wrapper">
            <div class="das-hero__logo-placeholder">
              <i class="ti tabler-swords text-danger"></i>
            </div>
            <div class="das-hero__logo-glow"></div>
          </div>

          <div class="das-hero__meta">
            <div class="das-hero__badge">
              <span class="pulse-dot"></span>
              Kesiswaan / Pelanggaran Siswa / Tambah
            </div>
            <h4 class="das-hero__title text-gradient-gold">Tambah Catatan Pelanggaran</h4>
            <p class="das-hero__subtitle">Input data pelanggaran siswa, foto bukti kejadian, dan kalkulasi poin otomatis.</p>
          </div>
        </div>

        <div class="das-hero__actions">
          <a href="{{ route('admin.pelanggaran.index') }}" class="btn das-btn --secondary">
            <i class="ti tabler-arrow-left me-1"></i> Kembali ke Daftar
          </a>
        </div>
      </div>
    </div>

    {{-- ALERT VALIDASI ERRORS --}}
    @if ($errors->any())
      <div class="alert alert-danger alert-dismissible d-flex align-items-start gap-2 mb-4 border-0 shadow-sm"
        style="border-radius:10px; background: rgba(234, 84, 85, 0.15); color: #ea5455; border: 1px solid rgba(234, 84, 85, 0.3) !important;">
        <i class="ti tabler-alert-circle fs-5 mt-1 flex-shrink-0"></i>
        <div>
          <span class="fw-semibold d-block mb-1">Terjadi Kesalahan Validasi:</span>
          <ul class="mb-0 ps-3 small">
            @foreach ($errors->all() as $error)
              <li>{{ $error }}</li>
            @endforeach
          </ul>
        </div>
        <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
      </div>
    @endif

    <div class="row g-4">
      <!-- FORM INPUT UTAMA -->
      <div class="col-lg-8">
        <div class="das-panel">
          <div class="das-panel__header border-bottom py-3 px-4 d-flex align-items-center justify-content-between"
            style="border-color:rgba(255,255,255,0.08) !important;">
            <h6 class="das-panel__title mb-0 d-flex align-items-center gap-2">
              <i class="ti tabler-edit text-danger"></i> Formulir Input Pelanggaran
            </h6>
            <span class="das-chip --danger">Formulir Wajib</span>
          </div>

          <div class="das-panel__body p-4">
            <form action="{{ route('admin.pelanggaran.store') }}" method="POST" enctype="multipart/form-data">
              @csrf
              
              <!-- Hidden Inputs -->
              <input type="hidden" name="siswa_id" :value="siswaId" required>
              <input type="hidden" name="tahun_akademik_id" :value="taId" required>

              <!-- Pemilihan Tahun Akademik -->
              <div class="mb-4">
                <label class="form-label fw-semibold small text-white-50" for="tahun_akademik_id_select">
                  <i class="ti tabler-calendar-stats me-1 text-danger"></i> Tahun Akademik <span class="text-danger">*</span>
                </label>
                <select class="form-select" id="tahun_akademik_id_select" name="tahun_akademik_id_select" x-model="taId" @change="onTaChange()">
                  @foreach($tahunAkademiks as $ta)
                    <option value="{{ $ta->id }}">{{ $ta->nama }} ({{ ucfirst($ta->semester) }})</option>
                  @endforeach
                </select>
                <span class="text-white-50 extra-small mt-1 d-block">Poin siswa akan dihitung pada tahun akademik yang dipilih.</span>
              </div>

              <!-- Cari Siswa (Autocomplete) -->
              <div class="mb-4 position-relative">
                <label class="form-label fw-semibold small text-white-50" for="siswa_search">
                  <i class="ti tabler-user-check me-1 text-danger"></i> Cari Nama / NIS Siswa <span class="text-danger">*</span>
                </label>
                <div class="input-group">
                  <span class="input-group-text bg-transparent border-light"><i class="ti tabler-search text-white-50"></i></span>
                  <input type="text" 
                         id="siswa_search"
                         class="form-control" 
                         x-model="siswaQuery" 
                         @input.debounce.300ms="searchSiswa()" 
                         @focus="showResults = true"
                         @click.away="showResults = false"
                         placeholder="Ketik nama lengkap atau NIS siswa..." 
                         autocomplete="off"
                         :disabled="siswaSelected">
                  <template x-if="siswaSelected">
                    <button class="btn das-btn --danger" type="button" @click="resetSiswaSelection()"><i class="ti tabler-x me-1"></i> Ganti</button>
                  </template>
                </div>

                <!-- Autocomplete Result Box -->
                <div class="autocomplete-results" x-show="showResults && searchResults.length > 0">
                  <template x-for="siswa in searchResults" :key="siswa.id">
                    <div class="autocomplete-item d-flex align-items-center" @click="selectSiswa(siswa)">
                      <div>
                        <span class="fw-semibold text-white d-block" x-text="siswa.nama_lengkap"></span>
                        <span class="small text-white-50" x-text="'NIS: ' + siswa.nis + ' | Kelas: ' + siswa.kelas_nama"></span>
                      </div>
                    </div>
                  </template>
                </div>
              </div>

              <!-- Pilihan Jenis Pelanggaran (Dropdown Terkelompok) -->
              <div class="mb-4">
                <label class="form-label fw-semibold small text-white-50" for="jenis_id">
                  <i class="ti tabler-alert-triangle me-1 text-danger"></i> Jenis Pelanggaran <span class="text-danger">*</span>
                </label>
                <select class="form-select" id="jenis_id" name="jenis_id" x-model="jenisId" @change="onJenisChange($el)">
                  <option value="">-- Pilih Jenis Pelanggaran --</option>
                  @foreach($kategoris as $kat)
                    @if($kat->jenisPelanggaran->count() > 0)
                      <optgroup label="Kategori: {{ $kat->nama }}">
                        @foreach($kat->jenisPelanggaran as $j)
                          <option value="{{ $j->id }}" data-poin="{{ $j->bobot_poin }}">{{ $j->nama }} (+{{ $j->bobot_poin }} Poin)</option>
                        @endforeach
                      </optgroup>
                    @endif
                  @endforeach
                </select>
              </div>

              <!-- Tanggal Kejadian & Upload Bukti -->
              <div class="row g-3 mb-4">
                <div class="col-md-6">
                  <label class="form-label fw-semibold small text-white-50" for="tanggal_kejadian">
                    <i class="ti tabler-calendar me-1 text-danger"></i> Tanggal Kejadian <span class="text-danger">*</span>
                  </label>
                  <input type="date" id="tanggal_kejadian" class="form-control" name="tanggal_kejadian" value="{{ date('Y-m-d') }}" required>
                </div>
                <div class="col-md-6">
                  <label class="form-label fw-semibold small text-white-50" for="foto">
                    <i class="ti tabler-camera me-1 text-danger"></i> Upload Foto Bukti
                  </label>
                  <input type="file" id="foto" class="form-control" name="foto" accept="image/*">
                  <span class="text-white-50 extra-small mt-1 d-block">Opsional. Format: JPG, PNG. Maks 2MB.</span>
                </div>
              </div>

              <!-- Keterangan Naratif -->
              <div class="mb-4">
                <label class="form-label fw-semibold small text-white-50" for="keterangan">
                  <i class="ti tabler-file-description me-1 text-danger"></i> Keterangan Kronologi / Catatan <span class="text-danger">*</span>
                </label>
                <textarea id="keterangan" class="form-control" name="keterangan" rows="4" placeholder="Ketik keterangan detail kronologi kejadian di lapangan..." required></textarea>
              </div>

              <!-- TOMBOL AKSI FORM -->
              <div class="d-flex align-items-center justify-content-end gap-2 pt-4 mt-2 border-top"
                style="border-color:rgba(255,255,255,0.08) !important;">
                <a href="{{ route('admin.pelanggaran.index') }}" class="btn das-btn --secondary">
                  <i class="ti tabler-arrow-left me-1"></i> Batal
                </a>
                <button type="submit" class="btn das-btn --primary" :disabled="!siswaSelected || !jenisId">
                  <i class="ti tabler-device-floppy me-1"></i> Simpan Catatan
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>

      <!-- PREVIEW POIN & STATUS SP -->
      <div class="col-lg-4">
        <div class="das-panel sticky-top" style="top: 90px;">
          <div class="das-panel__header border-bottom py-3 px-4 d-flex align-items-center justify-content-between"
            style="border-color:rgba(255,255,255,0.08) !important;">
            <h6 class="das-panel__title mb-0 d-flex align-items-center gap-2">
              <i class="ti tabler-chart-bar text-warning"></i> Live Preview Poin
            </h6>
            <span class="badge bg-label-warning px-2 py-1" style="font-size:0.7rem;">Real-time</span>
          </div>

          <div class="das-panel__body p-4 text-center">
            <!-- State Belum Pilih Siswa -->
            <div x-show="!siswaSelected" class="py-5 text-white-50">
              <i class="ti tabler-user-x fs-1 opacity-50 mb-3 d-block"></i>
              <p class="mb-0 small">Pilih siswa terlebih dahulu untuk melihat analisis proyeksi poin & SP.</p>
            </div>

            <!-- State Siswa Dipilih -->
            <div x-show="siswaSelected" x-transition>
              <!-- Info Singkat Siswa -->
              <div class="d-flex align-items-center justify-content-center flex-column mb-4">
                <div class="rounded-circle mb-3 d-flex align-items-center justify-content-center shadow-sm" 
                     style="width: 72px; height: 72px; background: rgba(115, 103, 240, 0.15); border: 2px solid rgba(115, 103, 240, 0.4);">
                  <i class="ti tabler-user fs-1 text-primary"></i>
                </div>
                <h5 class="text-white mb-1 fw-bold" x-text="selectedSiswaData.nama_lengkap"></h5>
                <span class="badge bg-label-info mb-2" x-text="'Kelas: ' + selectedSiswaData.kelas_nama"></span>
                <span class="small text-white-50" x-text="'SP Aktif saat ini: ' + selectedSiswaData.level_sp"></span>
              </div>

              <!-- Poin Comparison Widget -->
              <div class="row g-3 mb-4">
                <!-- Poin Saat Ini -->
                <div class="col-6">
                  <div class="preview-box p-3">
                    <span class="text-white-50 extra-small d-block mb-1">Poin Saat Ini</span>
                    <h3 class="fw-bold text-white mb-0" x-text="selectedSiswaData.total_poin"></h3>
                  </div>
                </div>
                <!-- Poin Tambahan -->
                <div class="col-6">
                  <div class="preview-box p-3" style="background: rgba(234, 84, 85, 0.15); border-color: rgba(234, 84, 85, 0.3);">
                    <span class="text-danger extra-small d-block mb-1">Tambah Poin</span>
                    <h3 class="fw-bold text-danger mb-0" x-text="'+' + addedPoin"></h3>
                  </div>
                </div>
              </div>

              <!-- Total Poin Setelah Pelanggaran -->
              <div class="p-4 rounded mb-4 shadow-sm" :class="getTotalPoin() >= 25 ? 'bg-danger-transparent border border-danger' : 'bg-success-transparent border border-success'"
                   style="border-radius: 12px !important; background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.1);">
                <span class="text-white-50 small d-block mb-1">Proyeksi Total Poin</span>
                <h2 class="fw-extrabold text-white mb-2" x-text="getTotalPoin() + ' Poin'"></h2>
                
                <!-- Status Peringatan SP -->
                <template x-if="getTotalPoin() >= 75">
                  <div class="text-danger small fw-semibold"><i class="ti tabler-alert-triangle me-1"></i> Proyeksi SP3 (Skorsing/Dikeluarkan)</div>
                </template>
                <template x-if="getTotalPoin() >= 50 && getTotalPoin() < 75">
                  <div class="text-warning small fw-semibold"><i class="ti tabler-alert-triangle me-1"></i> Proyeksi SP2</div>
                </template>
                <template x-if="getTotalPoin() >= 25 && getTotalPoin() < 50">
                  <div class="text-warning small fw-semibold"><i class="ti tabler-alert-triangle me-1"></i> Proyeksi SP1</div>
                </template>
                <template x-if="getTotalPoin() < 25">
                  <div class="text-success small fw-semibold"><i class="ti tabler-circle-check me-1"></i> Kondisi Poin Aman (< 25)</div>
                </template>
              </div>

              <!-- Status WhatsApp Penerima -->
              <div class="preview-box p-3 text-start">
                <span class="text-white-50 extra-small d-block mb-1"><i class="ti tabler-brand-whatsapp text-success me-1"></i> Notifikasi WhatsApp:</span>
                <div class="small text-white">
                  <strong>Orang Tua / Wali Siswa</strong>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection

@section('vendor-script')
  @vite(['resources/assets/vendor/libs/sweetalert2/sweetalert2.js'])
@endsection

@section('page-script')
  <script>
    function createPelanggaranHandler() {
      return {
        siswaId: '',
        taId: '{{ $taAktif?->id ?: "" }}',
        siswaQuery: '',
        showResults: false,
        searchResults: [],
        siswaSelected: false,
        selectedSiswaData: {
          nama_lengkap: '',
          kelas_nama: '',
          foto: '',
          total_poin: 0,
          level_sp: '-',
        },
        jenisId: '',
        addedPoin: 0,

        searchSiswa() {
          if (this.siswaQuery.length < 2) {
            this.searchResults = [];
            return;
          }
          fetch(`{{ route('admin.pelanggaran.search-siswa') }}?q=${this.siswaQuery}&tahun_akademik_id=${this.taId}`)
            .then(res => res.json())
            .then(data => {
              this.searchResults = data;
              this.showResults = true;
            })
            .catch(err => console.error(err));
        },

        selectSiswa(siswa) {
          this.siswaId = siswa.id;
          this.siswaQuery = `${siswa.nama_lengkap} (NIS: ${siswa.nis})`;
          this.siswaSelected = true;
          this.showResults = false;
          this.selectedSiswaData = {
            nama_lengkap: siswa.nama_lengkap,
            kelas_nama: siswa.kelas_nama,
            foto: siswa.foto,
            total_poin: siswa.total_poin,
            level_sp: siswa.level_sp,
          };
          this.searchResults = [];
        },

        resetSiswaSelection() {
          this.siswaId = '';
          this.siswaQuery = '';
          this.siswaSelected = false;
          this.selectedSiswaData = {
            nama_lengkap: '',
            kelas_nama: '',
            foto: '',
            total_poin: 0,
            level_sp: '-',
          };
        },

        onTaChange() {
          if (this.siswaId) {
            // Fetch ulang poin siswa di TA terpilih
            fetch(`/api/internal/siswa/${this.siswaId}/poin?tahun_akademik_id=${this.taId}`)
              .then(res => res.json())
              .then(data => {
                this.selectedSiswaData.total_poin = data.total_poin;
                this.selectedSiswaData.level_sp = data.level_sp;
              })
              .catch(err => console.error(err));
          }
        },

        onJenisChange(el) {
          const selectedOption = el.options[el.selectedIndex];
          if (selectedOption && selectedOption.value) {
            this.addedPoin = parseInt(selectedOption.getAttribute('data-poin')) || 0;
          } else {
            this.addedPoin = 0;
          }
        },

        getTotalPoin() {
          return parseInt(this.selectedSiswaData.total_poin) + parseInt(this.addedPoin);
        }
      }
    }
  </script>
@endsection
