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
      background: #16213e;
      border: 1px solid rgba(255, 255, 255, 0.15);
      border-radius: 8px;
      width: 100%;
      z-index: 1000;
      max-height: 250px;
      overflow-y: auto;
      box-shadow: 0 10px 25px rgba(0, 0, 0, 0.5);
    }

    .autocomplete-item {
      padding: 10px 15px;
      cursor: pointer;
      border-bottom: 1px solid rgba(255, 255, 255, 0.05);
      transition: background 0.2s ease;
      color: #fff;
    }

    .autocomplete-item:hover {
      background: rgba(255, 255, 255, 0.08);
    }

    .autocomplete-item:last-child {
      border-bottom: none;
    }

    .poin-badge-premium {
      background: linear-gradient(135deg, #ea5455 0%, #ff7676 100%);
      box-shadow: 0 4px 15px rgba(234, 84, 85, 0.4);
    }

    .custom-card-header {
      border-bottom: 1px solid rgba(255, 255, 255, 0.08);
    }
  </style>
@endsection

@section('content')
  <div x-data="createPelanggaranHandler()">
    {{-- HERO HEADER --}}
    <div class="row mb-4">
      <div class="col-12">
        <div class="card border-0 text-white overflow-hidden shadow-lg"
          style="background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%); border-radius: 4px;">
          <div class="card-body p-4">
            <div class="d-flex align-items-center gap-3">
              <div class="rounded d-flex align-items-center justify-content-center shadow-sm"
                style="width:52px;height:52px;border-radius:12px !important;background:rgba(0,207,232,0.2);border:1px solid rgba(0,207,232,0.4);">
                <i class="ti tabler-swords text-info fs-3"></i>
              </div>
              <div>
                <nav aria-label="breadcrumb">
                  <ol class="breadcrumb mb-1" style="font-size:0.72rem;opacity:0.6;">
                    <li class="breadcrumb-item"><a href="{{ route('admin.master-data') }}"
                        class="text-white text-decoration-none">Master Data</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.pelanggaran.index') }}"
                        class="text-white text-decoration-none">Pelanggaran</a></li>
                    <li class="breadcrumb-item active text-white">Tambah</li>
                  </ol>
                </nav>
                <h4 class="mb-0 text-white fw-bold" style="letter-spacing:-0.5px;">
                  Tambah Catatan Pelanggaran
                </h4>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    {{-- ALERT ERRORS --}}
    @if ($errors->any())
      <div class="alert alert-danger alert-dismissible d-flex align-items-start gap-2 mb-4 border-0 shadow-sm"
        style="border-radius:8px; background: rgba(234, 84, 85, 0.15); color: #ea5455;">
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
      <!-- Form Input Utama -->
      <div class="col-lg-8">
        <div class="card border-0 shadow-sm"
          style="background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.08) !important;">
          <div class="card-header border-bottom py-3 d-flex align-items-center gap-2"
            style="border-color:rgba(255,255,255,0.08) !important;background:transparent;">
            <i class="ti tabler-swords text-info"></i>
            <h6 class="card-title mb-0 text-white">Form Data Pelanggaran</h6>
          </div>
          <div class="card-body p-4">
            <form action="{{ route('admin.pelanggaran.store') }}" method="POST" enctype="multipart/form-data">
              @csrf
              
              <!-- Hidden Inputs -->
              <input type="hidden" name="siswa_id" :value="siswaId" required>
              <input type="hidden" name="tahun_akademik_id" :value="taId" required>

              <!-- Pemilihan Tahun Akademik -->
              <div class="mb-4">
                <label class="form-label fw-semibold small text-white" for="tahun_akademik_id_select">
                  <i class="ti tabler-calendar-stats me-1 text-info"></i> Tahun Akademik <span class="text-danger">*</span>
                </label>
                <select class="form-select" id="tahun_akademik_id_select" name="tahun_akademik_id_select" x-model="taId" @change="onTaChange()">
                  @foreach($tahunAkademiks as $ta)
                    <option value="{{ $ta->id }}">{{ $ta->nama }} ({{ ucfirst($ta->semester) }})</option>
                  @endforeach
                </select>
                <span class="text-muted small mt-1 d-block">Poin siswa akan dihitung pada tahun akademik yang dipilih.</span>
              </div>

              <!-- Cari Siswa (Autocomplete) -->
              <div class="mb-4 position-relative">
                <label class="form-label fw-semibold small text-white" for="siswa_search">
                  <i class="ti tabler-user me-1 text-info"></i> Cari Nama / NIS Siswa <span class="text-danger">*</span>
                </label>
                <div class="input-group">
                  <span class="input-group-text bg-transparent border-light"><i class="ti tabler-search text-muted"></i></span>
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
                    <button class="btn btn-danger" type="button" @click="resetSiswaSelection()"><i class="ti tabler-x"></i> Ganti</button>
                  </template>
                </div>

                <!-- Autocomplete Result Box -->
                <div class="autocomplete-results" x-show="showResults && searchResults.length > 0">
                  <template x-for="siswa in searchResults" :key="siswa.id">
                    <div class="autocomplete-item d-flex align-items-center" @click="selectSiswa(siswa)">
                      <img :src="siswa.foto" alt="Avatar" class="rounded-circle me-3" width="30" height="30" style="object-fit: cover;">
                      <div>
                        <span class="fw-semibold text-white d-block" x-text="siswa.nama_lengkap"></span>
                        <span class="small text-muted" x-text="'NIS: ' + siswa.nis + ' | Kelas: ' + siswa.kelas_nama"></span>
                      </div>
                    </div>
                  </template>
                </div>
              </div>

              <!-- Pilihan Jenis Pelanggaran (Dropdown Terkelompok) -->
              <div class="mb-4">
                <label class="form-label fw-semibold small text-white" for="jenis_id">
                  <i class="ti tabler-alert-triangle me-1 text-info"></i> Jenis Pelanggaran <span class="text-danger">*</span>
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
                  <label class="form-label fw-semibold small text-white" for="tanggal_kejadian">
                    <i class="ti tabler-calendar me-1 text-info"></i> Tanggal Kejadian <span class="text-danger">*</span>
                  </label>
                  <input type="date" id="tanggal_kejadian" class="form-control" name="tanggal_kejadian" value="{{ date('Y-m-d') }}" required>
                </div>
                <div class="col-md-6">
                  <label class="form-label fw-semibold small text-white" for="foto">
                    <i class="ti tabler-camera me-1 text-info"></i> Upload Foto Bukti
                  </label>
                  <input type="file" id="foto" class="form-control" name="foto" accept="image/*">
                  <span class="text-muted small mt-1 d-block">Opsional. Format: JPG, PNG. Maks 2MB.</span>
                </div>
              </div>

              <!-- Keterangan Naratif -->
              <div class="mb-4">
                <label class="form-label fw-semibold small text-white" for="keterangan">
                  <i class="ti tabler-file-description me-1 text-info"></i> Keterangan Kronologi / Catatan <span class="text-danger">*</span>
                </label>
                <textarea id="keterangan" class="form-control" name="keterangan" rows="4" placeholder="Ketik keterangan detail pelanggaran (misal: pakaian tidak rapi saat upacara, bolos setelah istirahat kedua)..." required></textarea>
              </div>

              <div class="d-flex align-items-center justify-content-end gap-3 pt-4 mt-2 border-top"
                style="border-color:rgba(255,255,255,0.08) !important;">
                <a href="{{ route('admin.pelanggaran.index') }}" class="btn btn-label-secondary">
                  <i class="ti tabler-arrow-left me-1"></i> Kembali
                </a>
                <button type="submit" class="btn btn-info fw-semibold px-4 shadow-sm" :disabled="!siswaSelected || !jenisId">
                  <i class="ti tabler-device-floppy me-1"></i> Simpan Catatan
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>

      <!-- Preview Poin & Status SP -->
      <div class="col-lg-4">
        <div class="card border-0 shadow-sm sticky-top"
          style="background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.08) !important;top: 80px;">
          <div class="card-header border-bottom py-3 d-flex align-items-center gap-2"
            style="border-color:rgba(255,255,255,0.08) !important;background:transparent;">
            <i class="ti tabler-chart-bar text-warning"></i>
            <h6 class="card-title mb-0 text-white">Live Preview Poin</h6>
          </div>
          <div class="card-body p-4 text-center">
            <!-- State Belum Pilih Siswa -->
            <div x-show="!siswaSelected" class="py-5 text-muted">
              <i class="ti tabler-user-x fs-1 text-secondary mb-3"></i>
              <p class="mb-0 small">Pilih siswa terlebih dahulu untuk melihat analisis akumulasi poin.</p>
            </div>

            <!-- State Siswa Dipilih -->
            <div x-show="siswaSelected" x-transition>
              <!-- Info Singkat Siswa -->
              <div class="d-flex align-items-center justify-content-center flex-column mb-4">
                <img :src="selectedSiswaData.foto" alt="Foto Siswa" class="rounded-circle mb-3 border border-light" width="72" height="72" style="object-fit: cover;">
                <h5 class="text-white mb-1" x-text="selectedSiswaData.nama_lengkap"></h5>
                <span class="badge bg-secondary mb-2" x-text="'Kelas: ' + selectedSiswaData.kelas_nama"></span>
                <span class="small text-muted" x-text="'SP Aktif Saat Ini: ' + selectedSiswaData.level_sp"></span>
              </div>

              <!-- Poin Comparison Widget -->
              <div class="row g-3 mb-4">
                <!-- Poin Saat Ini -->
                <div class="col-6">
                  <div class="p-3 rounded bg-dark border border-light">
                    <span class="text-muted small d-block mb-1">Poin Saat Ini</span>
                    <h3 class="fw-bold text-white mb-0" x-text="selectedSiswaData.total_poin"></h3>
                  </div>
                </div>
                <!-- Poin Tambahan -->
                <div class="col-6">
                  <div class="p-3 rounded bg-danger-transparent border border-danger">
                    <span class="text-danger small d-block mb-1">Poin Pelanggaran</span>
                    <h3 class="fw-bold text-danger mb-0" x-text="'+' + addedPoin"></h3>
                  </div>
                </div>
              </div>

              <!-- Total Poin Setelah Pelanggaran -->
              <div class="p-4 rounded mb-4 shadow-sm" :class="getTotalPoin() >= 25 ? 'bg-danger-transparent border border-danger' : 'bg-success-transparent border border-success'">
                <span class="text-white small d-block mb-1">Proyeksi Akumulasi Poin</span>
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
              <div class="p-3 rounded bg-dark border border-light text-start">
                <span class="text-muted small d-block mb-2"><i class="ti tabler-brand-whatsapp text-success me-1"></i> Penerima Notifikasi WhatsApp:</span>
                <div class="small text-light">
                  <strong>Orang Tua/Wali</strong>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
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
