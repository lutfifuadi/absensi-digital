@extends('layouts/layoutMaster')

@section('title', 'Log Sistem')

@section('page-style')
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

    /* Custom style for Bootstrap Modal */
    #clearLogConfirmModal .modal-content,
    #alertModal .modal-content {
      background: #1e1e2d;
      border: 1px solid rgba(255,255,255,0.1) !important;
      border-radius: 5px !important;
      box-shadow: 0 15px 35px rgba(0, 0, 0, 0.4);
    }
    #clearLogConfirmModal .modal-header,
    #clearLogConfirmModal .modal-body,
    #clearLogConfirmModal .modal-footer,
    #clearLogConfirmModal button,
    #clearLogConfirmModal .btn-close,
    #alertModal .modal-header,
    #alertModal .modal-body,
    #alertModal .modal-footer,
    #alertModal button,
    #alertModal .btn-close {
      border-radius: 5px !important;
    }
    #clearLogConfirmModal .btn-close-white,
    #alertModal .btn-close-white {
      filter: invert(1) grayscale(1) brightness(2);
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

  <!-- Modal Konfirmasi Hapus Log -->
  <div class="modal fade" id="clearLogConfirmModal" tabindex="-1" aria-labelledby="clearLogConfirmModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header bg-danger bg-opacity-10 py-3 border-bottom border-secondary d-flex align-items-center">
          <h5 class="modal-title text-white d-flex align-items-center mb-0" id="clearLogConfirmModalLabel">
            <i class="ti tabler-alert-triangle text-warning me-2 fs-4"></i> Apakah Anda Yakin?
          </h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body text-white py-4">
          Aksi ini akan menghapus semua isi file log '<strong id="modalLogFileName" class="text-warning"></strong>' secara permanen dari server!
        </div>
        <div class="modal-footer border-top border-secondary py-3">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="button" class="btn btn-danger" id="btnConfirmClear">Ya, Hapus Log!</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal Alert/Notification -->
  <div class="modal fade" id="alertModal" tabindex="-1" aria-labelledby="alertModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header py-3 border-bottom border-secondary d-flex align-items-center" id="alertModalHeader">
          <h5 class="modal-title d-flex align-items-center mb-0" id="alertModalTitle">
            <i id="alertModalIcon" class=""></i> <span id="alertModalTitleText"></span>
          </h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body text-white py-4" id="alertModalMessage">
        </div>
        <div class="modal-footer border-top border-secondary py-3">
          <button type="button" class="btn btn-primary" data-bs-dismiss="modal">OK</button>
        </div>
      </div>
    </div>
  </div>
@endsection

@section('page-script')
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      const logFileSelect = document.getElementById('logFileSelect');
      const btnRefresh = document.getElementById('btnRefresh');
      const btnClearLog = document.getElementById('btnClearLog');
      const terminalBody = document.getElementById('terminalBody');
      const terminalTitle = document.getElementById('terminalTitle');

      // Modals
      const clearLogConfirmModal = new bootstrap.Modal(document.getElementById('clearLogConfirmModal'));
      const alertModal = new bootstrap.Modal(document.getElementById('alertModal'));
      const modalLogFileName = document.getElementById('modalLogFileName');
      const btnConfirmClear = document.getElementById('btnConfirmClear');
      const alertModalHeader = document.getElementById('alertModalHeader');
      const alertModalTitleText = document.getElementById('alertModalTitleText');
      const alertModalIcon = document.getElementById('alertModalIcon');
      const alertModalMessage = document.getElementById('alertModalMessage');

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

      // Event Listener Tampilkan Modal Konfirmasi
      btnClearLog.addEventListener('click', function () {
        const selectedFile = logFileSelect.value;
        modalLogFileName.textContent = selectedFile;
        clearLogConfirmModal.show();
      });

      // Event Listener Clear Log (Proses Hapus di Modal)
      btnConfirmClear.addEventListener('click', function () {
        const selectedFile = logFileSelect.value;
        clearLogConfirmModal.hide();
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
            alertModalHeader.className = 'modal-header bg-success bg-opacity-10 py-3 border-bottom border-secondary d-flex align-items-center text-success';
            alertModalTitleText.textContent = 'Sukses';
            alertModalIcon.className = 'ti tabler-circle-check fs-4 me-2';
            alertModalMessage.textContent = data.message;
            alertModal.show();
            loadLogData();
          } else {
            alertModalHeader.className = 'modal-header bg-danger bg-opacity-10 py-3 border-bottom border-secondary d-flex align-items-center text-danger';
            alertModalTitleText.textContent = 'Gagal';
            alertModalIcon.className = 'ti tabler-circle-x fs-4 me-2';
            alertModalMessage.textContent = data.message;
            alertModal.show();
            loadLogData();
          }
        })
        .catch(error => {
          alertModalHeader.className = 'modal-header bg-danger bg-opacity-10 py-3 border-bottom border-secondary d-flex align-items-center text-danger';
          alertModalTitleText.textContent = 'Gagal';
          alertModalIcon.className = 'ti tabler-circle-x fs-4 me-2';
          alertModalMessage.textContent = error.message || 'Terjadi kesalahan sistem saat menghubungi backend.';
          alertModal.show();
          loadLogData();
          console.error(error);
        });
      });

      // Load data log pertama kali
      loadLogData();
    });
  </script>
@endsection
