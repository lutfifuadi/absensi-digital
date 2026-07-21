@extends('layouts/layoutMaster')

@section('title', 'Catat Pelanggaran Baru')

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
      border: 1px solid rgba(255, 255, 255, 0.1);
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
  <div class="container-xxl flex-grow-1 container-p-y" x-data="createPelanggaranHandler()">
    <div class="mb-4">
      <h4 class="fw-bold mb-1"><span class="text-muted fw-light">Kesiswaan /</span> Catat Pelanggaran</h4>
      <p class="text-muted mb-0">Catat pelanggaran tata tertib siswa dan kirim notifikasi WhatsApp otomatis ke orang tua.</p>
    </div>

    @if($errors->any())
      <div class="alert alert-danger alert-dismissible mb-4" role="alert">
        <h5 class="alert-heading mb-2 text-white">Terjadi Kesalahan Validasi:</h5>
        <ul class="mb-0">
          @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    @endif

    <div class="row g-4">
      <!-- Form Input Utama -->
      <div class="col-lg-8">
        <div class="card bg-glass border-light shadow-sm">
          <div class="card-header custom-card-header text-white">
            <h5 class="card-title mb-0"><i class="ti ti-edit-circle text-info me-2"></i> Form Data Pelanggaran</h5>
          </div>
          <div class="card-body pt-4">
            <form action="{{ route('admin.pelanggaran.store') }}" method="POST" enctype="multipart/form-data">
              @csrf
              
              <!-- Hidden Inputs -->
              <input type="hidden" name="siswa_id" :value="siswaId" required>
              <input type="hidden" name="tahun_akademik_id" :value="taId" required>

              <!-- Pemilihan Tahun Akademik -->
              <div class="mb-4">
                <label class="form-label text-light fw-medium required">Tahun Akademik</label>
                <select class="form-select" name="tahun_akademik_id_select" x-model="taId" @change="onTaChange()">
                  @foreach($tahunAkademiks as $ta)
                    <option value="{{ $ta->id }}">{{ $ta->nama }} ({{ ucfirst($ta->semester) }})</option>
                  @endforeach
                </select>
                <span class="text-muted small mt-1 d-block">Poin siswa akan dihitung pada tahun akademik yang dipilih.</span>
              </div>

              <!-- Cari Siswa (Autocomplete) -->
              <div class="mb-4 position-relative">
                <label class="form-label text-light fw-medium required">Cari Nama / NIS Siswa</label>
                <div class="input-group">
                  <span class="input-group-text bg-transparent border-light"><i class="ti ti-user text-muted"></i></span>
                  <input type="text" 
                         class="form-control" 
                         x-model="siswaQuery" 
                         @input.debounce.300ms="searchSiswa()" 
                         @focus="showResults = true"
                         @click.away="showResults = false"
                         placeholder="Ketik nama lengkap atau NIS siswa..." 
                         autocomplete="off"
                         :disabled="siswaSelected">
                  <template x-if="siswaSelected">
                    <button class="btn btn-danger" type="button" @click="resetSiswaSelection()"><i class="ti ti-x"></i> Ganti</button>
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
                <label class="form-label text-light fw-medium required">Jenis Pelanggaran</label>
                <select class="form-select" name="jenis_id" x-model="jenisId" @change="onJenisChange($el)">
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
                  <label class="form-label text-light fw-medium required">Tanggal Kejadian</label>
                  <input type="date" class="form-control" name="tanggal_kejadian" value="{{ date('Y-m-d') }}" required>
                </div>
                <div class="col-md-6">
                  <label class="form-label text-light fw-medium">Upload Foto Bukti</label>
                  <input type="file" class="form-control" name="foto" accept="image/*">
                  <span class="text-muted small mt-1 d-block">Opsional. Format: JPG, PNG. Maks 2MB.</span>
                </div>
              </div>

              <!-- Keterangan Naratif -->
              <div class="mb-4">
                <label class="form-label text-light fw-medium required">Keterangan Kronologi / Catatan</label>
                <textarea class="form-control" name="keterangan" rows="4" placeholder="Ketik keterangan detail pelanggaran (misal: pakaian tidak rapi saat upacara, bolos setelah istirahat kedua)..." required></textarea>
              </div>

              <div class="border-top border-light pt-4 d-flex justify-content-end gap-2">
                <a href="{{ route('admin.pelanggaran.index') }}" class="btn btn-secondary">Batal</a>
                <button type="submit" class="btn btn-primary" :disabled="!siswaSelected || !jenisId">
                  <i class="ti ti-device-floppy me-1"></i> Simpan Catatan
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>

      <!-- Preview Poin & Status SP -->
      <div class="col-lg-4">
        <div class="card bg-glass border-light shadow-sm sticky-top" style="top: 80px;">
          <div class="card-header custom-card-header text-white">
            <h5 class="card-title mb-0"><i class="ti ti-chart-bar text-warning me-2"></i> Live Preview Poin</h5>
          </div>
          <div class="card-body pt-4 text-center">
            <!-- State Belum Pilih Siswa -->
            <div x-show="!siswaSelected" class="py-5 text-muted">
              <i class="ti ti-user-x fs-1 text-secondary mb-3"></i>
              <p class="mb-0">Pilih siswa terlebih dahulu untuk melihat analisis akumulasi poin.</p>
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
                  <div class="text-danger small fw-semibold"><i class="ti ti-alert-triangle-filled me-1"></i> Proyeksi SP3 (Skorsing/Dikeluarkan)</div>
                </template>
                <template x-if="getTotalPoin() >= 50 && getTotalPoin() < 75">
                  <div class="text-warning small fw-semibold"><i class="ti ti-alert-triangle-filled me-1"></i> Proyeksi SP2</div>
                </template>
                <template x-if="getTotalPoin() >= 25 && getTotalPoin() < 50">
                  <div class="text-warning small fw-semibold"><i class="ti ti-alert-triangle-filled me-1"></i> Proyeksi SP1</div>
                </template>
                <template x-if="getTotalPoin() < 25">
                  <div class="text-success small fw-semibold"><i class="ti ti-circle-check-filled me-1"></i> Kondisi Poin Aman (< 25)</div>
                </template>
              </div>

              <!-- Status WhatsApp Penerima -->
              <div class="p-3 rounded bg-dark border border-light text-start">
                <span class="text-muted small d-block mb-2"><i class="ti ti-brand-whatsapp text-success me-1"></i> Penerima Notifikasi WhatsApp:</span>
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
