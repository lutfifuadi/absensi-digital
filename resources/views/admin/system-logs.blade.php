@extends('layouts/layoutMaster')

@section('title', 'Log Sistem')

@section('page-style')
  @vite(['resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'])
  <style>
    /* Styling kustom terminal premium */
    .terminal-container {
      background-color: #1a1a2e; /* Selaras dengan panel utama */
      border-radius: 16px;
      border: 1px solid rgba(255, 255, 255, 0.08);
      box-shadow: 0 15px 35px rgba(0, 0, 0, 0.4);
      display: flex;
      flex-direction: column;
      height: 600px;
      overflow: hidden;
      position: relative;
    }

    .terminal-header {
      background-color: #12121a;
      border-bottom: 1px solid rgba(255, 255, 255, 0.08);
      padding: 12px 20px;
      display: flex;
      align-items: center;
      justify-content: space-between;
    }

    .terminal-dots {
      display: flex;
      gap: 6px;
    }

    .terminal-dot {
      width: 12px;
      height: 12px;
      border-radius: 50%;
    }

    .terminal-dot-red { background-color: #ff5f56; }
    .terminal-dot-yellow { background-color: #ffbd2e; }
    .terminal-dot-green { background-color: #27c93f; }

    .terminal-title {
      font-family: 'Fira Code', 'Courier New', Courier, monospace;
      color: rgba(255, 255, 255, 0.5);
      font-size: 0.85rem;
      font-weight: 500;
    }

    .terminal-body {
      padding: 20px;
      overflow-y: auto;
      flex-grow: 1;
      font-family: 'Fira Code', 'Courier New', Courier, monospace;
      font-size: 0.85rem;
      line-height: 1.6;
      color: #50fa7b; /* Dracula green lembut, tidak menusuk mata */
      white-space: pre-wrap;
      word-break: break-all;
    }

    /* Scrollbar kustom terminal */
    .terminal-body::-webkit-scrollbar {
      width: 8px;
      height: 8px;
    }

    .terminal-body::-webkit-scrollbar-track {
      background: #1a1a2e;
    }

    .terminal-body::-webkit-scrollbar-thumb {
      background: rgba(255, 255, 255, 0.1);
      border-radius: 4px;
    }

    .terminal-body::-webkit-scrollbar-thumb:hover {
      background: rgba(255, 255, 255, 0.2);
    }

    /* SWEETALERT2 CUSTOM PREMIUM */
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
      font-size: 1.35rem !important;
    }

    .das-swal-html {
      color: rgba(255, 255, 255, 0.7) !important;
      font-size: 0.9rem !important;
    }

    .das-swal-confirm {
      padding: 10px 24px !important;
      font-weight: 600 !important;
      border-radius: 10px !important;
      font-size: 0.875rem !important;
      background-color: #ea5455 !important;
      color: #fff !important;
      box-shadow: 0 4px 12px rgba(234, 84, 85, 0.3) !important;
      border: none !important;
    }

    .das-swal-cancel {
      padding: 10px 24px !important;
      font-weight: 600 !important;
      border-radius: 10px !important;
      font-size: 0.875rem !important;
      background: rgba(255, 255, 255, 0.05) !important;
      color: #fff !important;
      border: 1px solid rgba(255, 255, 255, 0.1) !important;
    }

    .das-swal-icon {
      border-color: rgba(255, 255, 255, 0.15) !important;
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
            <i class="ti tabler-terminal-2 text-info"></i>
          </div>
          <div class="das-hero__logo-glow"></div>
        </div>

        <div class="das-hero__meta">
          <div class="das-hero__badge">
            <span class="pulse-dot"></span>
            Sistem / Log
          </div>
          <h4 class="das-hero__title text-gradient-gold">Log Sistem & Server</h4>
          <p class="das-hero__subtitle">Pantau aktivitas server dan proses scan QR secara realtime.</p>
        </div>
      </div>

      <div class="das-hero__actions d-flex align-items-center gap-2">
        <select id="logFileSelect" class="form-select border-0 text-white w-auto"
          style="background: rgba(255, 255, 255, 0.05); height:38px; font-size:0.85rem; cursor:pointer; min-width: 180px;">
          @foreach($logFiles as $file)
            <option value="{{ $file }}" {{ $file === 'qr-scan.log' ? 'selected' : '' }} style="background:#1a1a2e;">{{ $file }}</option>
          @endforeach
        </select>
        <button type="button" class="btn das-btn --info m-0" id="btnRefresh" style="height:38px; display: inline-flex; align-items: center;">
          <i class="ti tabler-refresh me-1"></i> Refresh
        </button>
      </div>
    </div>
  </div>

  {{-- TERMINAL BODY --}}
  <div class="das-panel">
    <div class="das-panel__header border-bottom py-3 px-4 d-flex align-items-center justify-content-between"
      style="border-color:rgba(255,255,255,0.08) !important;">
      <h6 class="das-panel__title mb-0 d-flex align-items-center gap-2">
        <i class="ti tabler-terminal text-info"></i> Console Log: <span id="terminalTitle" class="text-white-50">qr-scan.log</span>
      </h6>
      <div>
        <button type="button" class="btn das-btn --danger btn-sm" id="btnClearLog">
          <i class="ti tabler-trash me-1"></i> Clear Log
        </button>
      </div>
    </div>
    
    <div class="das-panel__body p-0">
      <div class="terminal-container" style="border:none; border-radius:0; height:550px;">
        <div class="terminal-body" id="terminalBody">Memuat data log...</div>
      </div>
    </div>
  </div>
@endsection

@section('vendor-script')
  @vite(['resources/assets/vendor/libs/sweetalert2/sweetalert2.js'])
@endsection

@section('page-script')
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      const logFileSelect = document.getElementById('logFileSelect');
      const btnRefresh = document.getElementById('btnRefresh');
      const btnClearLog = document.getElementById('btnClearLog');
      const terminalBody = document.getElementById('terminalBody');
      const terminalTitle = document.getElementById('terminalTitle');

      // Fungsi mengambil isi Log
      function loadLogData() {
        const selectedFile = logFileSelect.value;
        terminalTitle.textContent = selectedFile;
        terminalBody.textContent = 'Memuat log dari server...';

        fetch(`{{ route('admin.system-logs.data') }}?file=${selectedFile}`)
          .then(response => response.json())
          .then(data => {
            if (data.success) {
              terminalBody.textContent = data.content;
              // Auto-scroll ke paling bawah agar log terbaru langsung terlihat
              terminalBody.scrollTop = terminalBody.scrollHeight;
            } else {
              terminalBody.textContent = `[Gagal mengambil log: ${data.message}]`;
            }
          })
          .catch(error => {
            terminalBody.textContent = `[Error: Gagal terhubung ke server]`;
            console.error(error);
          });
      }

      // Event Listeners
      logFileSelect.addEventListener('change', loadLogData);
      btnRefresh.addEventListener('click', loadLogData);

      // Event Listener Clear Log dengan SweetAlert2
      btnClearLog.addEventListener('click', function () {
        const selectedFile = logFileSelect.value;

        Swal.fire({
          title: 'Apakah Anda Yakin?',
          text: `Aksi ini akan menghapus semua isi file log "${selectedFile}" secara permanen dari server!`,
          icon: 'warning',
          showCancelButton: true,
          confirmButtonText: 'Ya, Hapus Log!',
          cancelButtonText: 'Batal',
          customClass: {
            popup: 'das-swal-popup',
            title: 'das-swal-title',
            htmlContainer: 'das-swal-html',
            confirmButton: 'das-swal-confirm btn btn-danger',
            cancelButton: 'das-swal-cancel btn btn-secondary',
            icon: 'das-swal-icon'
          },
          buttonsStyling: false
        }).then((result) => {
          if (result.isConfirmed) {
            terminalBody.textContent = 'Membersihkan file log...';
            
            fetch(`{{ route('admin.system-logs.clear') }}`, {
              method: 'POST',
              headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
              },
              body: JSON.stringify({ file: selectedFile })
            })
            .then(response => {
              if (!response.ok) {
                return response.json().then(err => { throw new Error(err.message || 'Terjadi kesalahan pada server.') });
              }
              return response.json();
            })
            .then(data => {
              if (data.success) {
                Swal.fire({
                  title: 'Log Dibersihkan!',
                  text: data.message,
                  icon: 'success',
                  customClass: {
                    popup: 'das-swal-popup',
                    title: 'das-swal-title',
                    htmlContainer: 'das-swal-html',
                    confirmButton: 'das-swal-confirm btn btn-success',
                    icon: 'das-swal-icon'
                  },
                  buttonsStyling: false
                });
                loadLogData();
              } else {
                Swal.fire({
                  title: 'Gagal!',
                  text: data.message,
                  icon: 'error',
                  customClass: {
                    popup: 'das-swal-popup',
                    title: 'das-swal-title',
                    htmlContainer: 'das-swal-html',
                    confirmButton: 'das-swal-confirm btn btn-primary',
                    icon: 'das-swal-icon'
                  },
                  buttonsStyling: false
                });
                loadLogData();
              }
            })
            .catch(error => {
              Swal.fire({
                title: 'Error!',
                text: error.message || 'Terjadi kesalahan sistem saat menghubungi backend.',
                icon: 'error',
                customClass: {
                  popup: 'das-swal-popup',
                  title: 'das-swal-title',
                  htmlContainer: 'das-swal-html',
                  confirmButton: 'das-swal-confirm btn btn-primary',
                  icon: 'das-swal-icon'
                },
                buttonsStyling: false
              });
              loadLogData();
              console.error(error);
            });
          }
        });
      });

      // Load data log pertama kali
      loadLogData();
    });
  </script>
@endsection
