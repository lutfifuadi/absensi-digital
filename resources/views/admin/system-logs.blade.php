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

    .btn-clear-log {
      background-color: rgba(234, 84, 85, 0.12);
      border: 1px solid rgba(234, 84, 85, 0.3);
      color: #ea5455;
      padding: 6px 14px;
      border-radius: 8px;
      font-family: inherit;
      font-size: 0.8rem;
      font-weight: 600;
      display: inline-flex;
      align-items: center;
      gap: 6px;
      cursor: pointer;
      transition: all 0.2s ease;
    }

    .btn-clear-log:hover {
      background-color: #ea5455;
      color: #ffffff;
      box-shadow: 0 4px 12px rgba(234, 84, 85, 0.3);
      transform: translateY(-1px);
    }

    /* Dropdown kustom premium */
    .custom-select-log {
      background-color: #242435 !important;
      border: 1px solid rgba(255, 255, 255, 0.15) !important;
      color: #ffffff !important;
      border-radius: 8px;
      padding: 7px 14px;
      font-size: 0.85rem;
      outline: none;
      transition: all 0.2s ease;
    }

    .custom-select-log:focus {
      border-color: rgba(115, 103, 240, 0.5) !important;
      box-shadow: 0 0 8px rgba(115, 103, 240, 0.2);
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
  <div class="row mb-4">
    <div class="col-12">
      <div class="card border-0 text-white overflow-hidden shadow-lg"
        style="background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%); border-radius: 12px;">
        <div class="card-body p-4">
          <div class="row align-items-center">
            <div class="col-md-7">
              <div class="d-flex align-items-center gap-3">
                <div class="rounded d-flex align-items-center justify-content-center shadow-sm"
                  style="width:52px;height:52px;border-radius:12px !important;background:rgba(0,207,232,0.2);border:1px solid rgba(0,207,232,0.4);">
                  <i class="ti tabler-terminal-2 text-info fs-3"></i>
                </div>
                <div>
                  <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-1" style="font-size:0.72rem;opacity:0.6;">
                      <li class="breadcrumb-item">
                        <span class="text-white opacity-50">Sistem</span>
                      </li>
                      <li class="breadcrumb-item active text-white">Log Sistem</li>
                    </ol>
                  </nav>
                  <h4 class="mb-0 text-white fw-bold" style="letter-spacing:-0.5px;">Log Sistem</h4>
                  <p class="mb-0 text-white opacity-60 small">Monitor aktivitas server dan scan QR Code presensi secara langsung.</p>
                </div>
              </div>
            </div>
            <div class="col-md-5 text-md-end mt-3 mt-md-0">
              <div class="d-inline-flex align-items-center gap-2">
                <select id="logFileSelect" class="custom-select-log">
                  @foreach($logFiles as $file)
                    <option value="{{ $file }}" {{ $file === 'qr-scan.log' ? 'selected' : '' }}>{{ $file }}</option>
                  @endforeach
                </select>
                <button type="button" class="btn btn-info d-flex align-items-center gap-1 shadow-sm" id="btnRefresh" style="border-radius: 8px; padding: 8px 16px;">
                  <i class="ti tabler-refresh fs-5"></i> Refresh
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- TERMINAL BODY --}}
  <div class="row">
    <div class="col-12">
      <div class="terminal-container">
        <div class="terminal-header">
          <div class="terminal-dots">
            <div class="terminal-dot terminal-dot-red"></div>
            <div class="terminal-dot terminal-dot-yellow"></div>
            <div class="terminal-dot terminal-dot-green"></div>
          </div>
          <div class="terminal-title" id="terminalTitle">terminal - qr-scan.log</div>
          <div>
            <button type="button" class="btn-clear-log" id="btnClearLog">
              <i class="ti tabler-trash fs-6"></i>
              Clear Log
            </button>
          </div>
        </div>
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
        terminalTitle.textContent = `terminal - ${selectedFile}`;
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
