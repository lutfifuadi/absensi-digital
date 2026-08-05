<!DOCTYPE html>
<html lang="id" class="h-full overflow-x-hidden">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard Absensi Cepat Publik</title>
  <link rel="stylesheet" href="{{ asset('assets/css/local-fonts.css') }}">
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
  <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
  <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
  <style>
    html, body {
      font-family: 'Product Sans', 'Inter', sans-serif;
      overflow-x: hidden !important;
      max-width: 100vw !important;
      width: 100% !important;
      margin: 0 !important;
      padding: 0 !important;
    }
    #qr-reader { width: 100% !important; border: none !important; }
    #qr-reader video { width: 100% !important; height: 100% !important; object-fit: cover !important; border-radius: 5px !important; }
    #qr-reader__scan_region { display: flex; justify-content: center; }
    
    /* Custom Scrollbar */
    ::-webkit-scrollbar { width: 6px; height: 6px; }
    ::-webkit-scrollbar-track { background: #020617; }
    ::-webkit-scrollbar-thumb { background: #334155; border-radius: 5px; }
    ::-webkit-scrollbar-thumb:hover { background: #475569; }

    /* Strict 5px radius styling for SweetAlert modals */
    .swal2-popup { border-radius: 5px !important; border: 1px solid #334155 !important; }
    .swal2-confirm, .swal2-cancel, .swal2-styled { border-radius: 5px !important; }

    /* Select2 Dark Kios Custom Theme with max 5px radius */
    .select2-container { width: 100% !important; }
    .select2-container--open { z-index: 99999 !important; }
    .select2-container--default .select2-selection--single {
      background-color: #020617 !important; /* slate-950 */
      border: 1px solid #1e293b !important; /* slate-800 */
      border-radius: 5px !important;
      height: 40px !important;
      display: flex !important;
      align-items: center !important;
      transition: all 0.2s ease;
    }
    .select2-container--default.select2-container--focus .select2-selection--single,
    .select2-container--default.select2-container--open .select2-selection--single {
      border-color: #6366f1 !important; /* indigo-500 */
      box-shadow: 0 0 0 1px #6366f1 !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
      color: #f8fafc !important; /* slate-100 */
      font-size: 0.75rem !important;
      font-weight: 700 !important;
      padding-left: 1rem !important;
      padding-right: 2rem !important;
      line-height: 38px !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__placeholder {
      color: #64748b !important; /* slate-500 */
      font-weight: 600 !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__clear {
      display: none !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
      height: 38px !important;
      right: 10px !important;
      display: flex !important;
      align-items: center !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow b {
      border-color: #818cf8 transparent transparent transparent !important;
      border-width: 5px 4px 0 4px !important;
    }
    .select2-container--default.select2-container--open .select2-selection--single .select2-selection__arrow b {
      border-color: transparent transparent #818cf8 transparent !important;
      border-width: 0 4px 5px 4px !important;
    }
    .select2-dropdown {
      background-color: #0f172a !important; /* slate-900 */
      border: 1px solid #334155 !important; /* slate-700 */
      border-radius: 5px !important;
      box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.7) !important;
      overflow: hidden !important;
      z-index: 99999 !important;
    }
    .select2-container--default .select2-search--dropdown {
      padding: 6px !important;
      background-color: #0f172a !important;
    }
    .select2-container--default .select2-search--dropdown .select2-search__field {
      background-color: #020617 !important;
      border: 1px solid #1e293b !important;
      border-radius: 5px !important;
      color: #f8fafc !important;
      font-size: 0.75rem !important;
      padding: 6px 10px !important;
      outline: none !important;
      width: 100% !important;
      box-sizing: border-box !important;
    }
    .select2-container--default .select2-search--dropdown .select2-search__field:focus {
      border-color: #6366f1 !important;
    }
    .select2-container--default .select2-results__option {
      color: #cbd5e1 !important;
      font-size: 0.75rem !important;
      font-weight: 600 !important;
      padding: 8px 12px !important;
    }
    .select2-container--default .select2-results__option--highlighted[aria-selected] {
      background-color: #4f46e5 !important; /* indigo-600 */
      color: #ffffff !important;
    }
    .select2-container--default .select2-results__option[aria-selected=true] {
      background-color: #312e81 !important; /* indigo-900 */
      color: #a5b4fc !important;
    }
  </style>
</head>
<body class="bg-slate-950 text-slate-100 min-h-screen flex flex-col overflow-x-hidden w-full relative"
      x-data="{
        activeTab: 'bulk',
        timeString: '',
        dateString: '',
        updateClock() {
          const now = new Date();
          this.timeString = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' }) + ' WIB';
          this.dateString = now.toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });
        }
      }"
      x-init="updateClock(); setInterval(() => updateClock(), 1000)">

  <!-- Top Accent Bar -->
  <div class="h-1 bg-gradient-to-r from-indigo-600 via-indigo-400 to-indigo-600 w-full z-20"></div>

  <div class="fixed inset-0 pointer-events-none bg-[radial-gradient(ellipse_at_top,_var(--tw-gradient-stops))] from-indigo-900/15 via-slate-950 to-slate-950"></div>

  <!-- Header -->
  <header class="relative border-b border-slate-800 bg-slate-900/80 backdrop-blur-md px-6 py-3 flex flex-col md:flex-row md:items-center justify-between gap-4 z-10 shadow-md">
    <div class="flex items-center gap-3">
      @php
        $logoVal = setting('logo_url') ?: setting('logo_sekolah');
        $logoSrc = null;
        if (!empty($logoVal)) {
          if (filter_var($logoVal, FILTER_VALIDATE_URL) || str_starts_with($logoVal, 'http://') || str_starts_with($logoVal, 'https://')) {
            $logoSrc = $logoVal;
          } else {
            $logoSrc = asset('uploads/logo/' . $logoVal);
          }
        }
      @endphp
      @if($logoSrc)
        <img src="{{ $logoSrc }}" alt="School Logo" class="h-9 w-auto object-contain rounded-[5px]">
      @else
        <div class="w-9 h-9 rounded-[5px] bg-indigo-600 flex items-center justify-center text-white font-extrabold shadow-md border border-indigo-500/30 text-xs">AC</div>
      @endif
      <div>
        <h1 class="text-sm font-extrabold text-white tracking-tight leading-tight flex items-center gap-2">
          Absensi Cepat Publik
          <span class="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 bg-indigo-500/10 border border-indigo-500/20 text-indigo-400 rounded-[5px]">Kios Mode</span>
        </h1>
        <p class="text-[11px] text-slate-400 font-medium mt-0.5">{{ setting('nama_sekolah', 'Absensi Sekolah') }}</p>
      </div>
    </div>

    <div class="flex items-center gap-4">
      <div class="text-right hidden md:block bg-slate-950/60 border border-slate-800 px-3 py-1 rounded-[5px]">
        <p class="text-xs font-bold text-indigo-300 tracking-wide" x-text="timeString"></p>
        <p class="text-[10px] text-slate-400" x-text="dateString"></p>
      </div>

      <form action="{{ route('public.absensi-cepat.logout') }}" method="POST" class="inline">
        @csrf
        <button type="submit" class="h-9 px-3.5 bg-rose-600/10 border border-rose-500/20 hover:bg-rose-600/20 text-rose-400 font-bold text-xs rounded-[5px] transition duration-150 flex items-center gap-2">
          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
          Selesai Sesi
        </button>
      </form>
    </div>
  </header>

  <!-- Main Container -->
  <div class="relative max-w-7xl w-full mx-auto px-6 py-5 z-10 flex-1 flex flex-col">
    <!-- Navigation Tabs -->
    <div class="flex justify-center mb-5">
      <div class="inline-flex p-1 bg-slate-900 border border-slate-800 rounded-[5px] shadow-md">
        <button @click="activeTab = 'bulk'"
                :class="activeTab === 'bulk' ? 'bg-indigo-600 text-white shadow-sm font-bold' : 'text-slate-400 hover:text-slate-200 font-semibold'"
                class="px-5 py-1.5 rounded-[5px] text-xs tracking-wide transition duration-150 flex items-center gap-2">
          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
          Absensi Cepat (Bulk / Pencarian)
        </button>
        <button @click="activeTab = 'scan'"
                :class="activeTab === 'scan' ? 'bg-indigo-600 text-white shadow-sm font-bold' : 'text-slate-400 hover:text-slate-200 font-semibold'"
                class="px-5 py-1.5 rounded-[5px] text-xs tracking-wide transition duration-150 flex items-center gap-2">
          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h.01M16 20h2M12 8V7m0 1v3m4 9v-3m0 0h.01M20 12h.01M8 12h.01M8 8h.01M8 16h.01M4 8h.01M4 12h.01M4 16h.01M8 20h.01M12 20h.01M16 8h.01M20 8h.01M4 20h.01"/></svg>
          Scan QR Code
        </button>
      </div>
    </div>

    <!-- Tab Contents -->
    <div class="flex-1 flex flex-col">
      
      <!-- Tab 1: SCAN QR CODE -->
      <div x-show="activeTab === 'scan'" class="flex-1 grid grid-cols-1 lg:grid-cols-2 gap-5"
           x-data="{
             manualInput: '',
             loading: false,
             result: null,
             html5QrCode: null,
             isScanning: false,
             cameraError: null,
             lastScannedCode: '',
             lastScannedTime: 0,
             initScanner() {
               if (this.isScanning) return;
               this.cameraError = null;

               const startCameraStream = () => {
                 this.html5QrCode = new Html5Qrcode('qr-reader');
                 const config = { fps: 15, qrbox: { width: 250, height: 250 }, aspectRatio: 1.0 };
                 
                 Html5Qrcode.getCameras().then(cameras => {
                   if (!cameras || cameras.length === 0) {
                     this.cameraError = 'Kamera tidak ditemukan di perangkat ini.';
                     this.isScanning = false;
                     return;
                   }
                   const backCam = cameras.find(c => 
                     c.label.toLowerCase().includes('back') || 
                     c.label.toLowerCase().includes('belakang') || 
                     c.label.toLowerCase().includes('rear') || 
                     c.label.toLowerCase().includes('environment')
                   );
                   const cameraId = backCam ? backCam.id : cameras[0].id;
                   
                   this.html5QrCode.start(
                     cameraId,
                     config,
                     (decodedText) => {
                       const now = Date.now();
                       if (decodedText === this.lastScannedCode && (now - this.lastScannedTime) < 2500) {
                         return;
                       }
                       this.lastScannedCode = decodedText;
                       this.lastScannedTime = now;
                       this.processScan(decodedText);
                     },
                     (errorMessage) => {}
                   ).then(() => {
                     this.isScanning = true;
                     this.cameraError = null;
                   }).catch(err => {
                     console.error('Camera start error:', err);
                     this.cameraError = 'Gagal mengakses kamera: ' + (err.message || 'Pastikan izin kamera aktif.');
                     this.isScanning = false;
                   });
                 }).catch(err => {
                   console.error('Get cameras error:', err);
                   this.cameraError = 'Akses kamera ditolak. Berikan izin di browser Anda.';
                   this.isScanning = false;
                 });
               };

               if (this.html5QrCode && this.isScanning) {
                 this.html5QrCode.stop().then(() => {
                   this.html5QrCode.clear();
                   startCameraStream();
                 }).catch(() => startCameraStream());
               } else {
                 startCameraStream();
               }
             },
             stopScanner() {
               if (this.html5QrCode && this.isScanning) {
                 this.html5QrCode.stop().then(() => {
                   this.html5QrCode.clear();
                   this.html5QrCode = null;
                   this.isScanning = false;
                 }).catch(e => console.error(e));
               }
             },
             playBeep(success = true) {
               try {
                 const audioCtx = new (window.AudioContext || window.webkitAudioContext)();
                 const oscillator = audioCtx.createOscillator();
                 const gainNode = audioCtx.createGain();
                 oscillator.connect(gainNode);
                 gainNode.connect(audioCtx.destination);
                 if (success) {
                   oscillator.frequency.setValueAtTime(880, audioCtx.currentTime); // A5
                   gainNode.gain.setValueAtTime(0.1, audioCtx.currentTime);
                   oscillator.start();
                   oscillator.stop(audioCtx.currentTime + 0.15);
                 } else {
                   oscillator.frequency.setValueAtTime(220, audioCtx.currentTime); // A3
                   gainNode.gain.setValueAtTime(0.1, audioCtx.currentTime);
                   oscillator.start();
                   oscillator.stop(audioCtx.currentTime + 0.3);
                 }
               } catch (e) {
                 console.error('Audio feedback error', e);
               }
             },
             processScan(code) {
               if (!code || this.loading) return;
               this.loading = true;
               fetch('{{ route('public.absensi-cepat.scan') }}', {
                 method: 'POST',
                 headers: {
                   'Content-Type': 'application/json',
                   'X-CSRF-TOKEN': '{{ csrf_token() }}'
                 },
                 body: JSON.stringify({ qr_code: code })
               })
               .then(async res => {
                 const data = await res.json();
                 if (res.ok && data.success) {
                   this.result = data;
                   this.playBeep(true);
                   this.manualInput = '';
                   Swal.fire({
                     icon: 'success',
                     title: data.name,
                     text: data.message,
                     timer: 2000,
                     showConfirmButton: false,
                     background: '#0f172a',
                     color: '#f8fafc'
                   });
                 } else {
                   this.playBeep(false);
                   Swal.fire({
                     icon: 'error',
                     title: 'Gagal',
                     text: data.message || 'Data tidak ditemukan.',
                     background: '#0f172a',
                     color: '#f8fafc',
                     confirmButtonColor: '#6366f1'
                   });
                 }
               })
               .catch(err => {
                 this.playBeep(false);
                 console.error(err);
                 Swal.fire({
                   icon: 'error',
                   title: 'Error',
                   text: 'Terjadi kesalahan server.',
                   background: '#0f172a',
                   color: '#f8fafc',
                   confirmButtonColor: '#6366f1'
                 });
               })
               .finally(() => {
                 this.loading = false;
               });
             }
           }"
           x-init="setTimeout(() => initScanner(), 300); $watch('activeTab', tab => { if(tab === 'scan') initScanner(); else stopScanner(); })"
           @destroy="stopScanner()">
        
        <!-- Scanner Pane -->
        <div class="bg-slate-900/90 border border-slate-800 rounded-[5px] p-4 flex flex-col shadow-lg">
          <h2 class="text-xs font-bold text-white mb-3 flex items-center justify-between">
            <span class="flex items-center gap-2">
              <span class="w-2 h-2 rounded-[2px]" :class="isScanning ? 'bg-emerald-500 animate-ping' : 'bg-rose-500'"></span>
              <span x-text="isScanning ? 'Kamera Scanner Active' : 'Kamera Nonaktif / Menunggu Izin'"></span>
            </span>
            <template x-if="cameraError">
              <button @click="initScanner()" class="text-xs bg-indigo-600 hover:bg-indigo-500 text-white px-2.5 py-1 rounded-[5px] transition font-bold">Coba Lagi</button>
            </template>
          </h2>
          
          <div class="relative overflow-hidden rounded-[5px] border border-slate-800 bg-slate-950 flex-1 flex items-center justify-center min-h-[290px] mb-4">
            <div id="qr-reader" class="w-full"></div>

            <template x-if="cameraError">
              <div class="absolute inset-0 bg-slate-950/90 flex flex-col items-center justify-center p-5 text-center z-10">
                <div class="w-10 h-10 rounded-[5px] bg-rose-500/10 border border-rose-500/20 flex items-center justify-center text-rose-400 mb-2.5">
                  <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
                <p class="text-xs font-semibold text-rose-300" x-text="cameraError"></p>
                <button @click="initScanner()" class="mt-3 px-3.5 py-1.5 bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold rounded-[5px] transition shadow-md">
                  Izinkan / Aktifkan Kamera
                </button>
              </div>
            </template>
          </div>

          <!-- Fallback Manual Input -->
          <div>
            <label class="block text-[11px] font-bold text-slate-300 uppercase tracking-wider mb-1.5 flex items-center gap-1.5">
              <svg class="w-3.5 h-3.5 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
              Input Manual (NIS / NIP / NAMA)
            </label>
            <div class="flex flex-col sm:flex-row gap-2.5">
              <input type="text" x-model="manualInput" @keydown.enter.prevent="processScan(manualInput)"
                     placeholder="Masukkan NIS, NIP atau Nama..."
                     class="flex-1 h-10 bg-slate-950 border border-slate-800 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 rounded-[5px] px-4 text-xs text-slate-100 placeholder-slate-500 outline-none transition">
              <button @click="processScan(manualInput)"
                      class="h-10 px-5 bg-indigo-600 hover:bg-indigo-500 active:bg-indigo-700 text-white font-bold text-xs rounded-[5px] transition duration-150 shadow-md shadow-indigo-600/20 flex items-center justify-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7"/></svg>
                Kirim
              </button>
            </div>
          </div>
        </div>

        <!-- 3-Way Result Card -->
        <div class="bg-slate-900/90 border border-slate-800 rounded-[5px] p-5 flex flex-col items-center justify-center min-h-[350px] shadow-lg">
          <template x-if="!result">
            <div class="text-center py-8">
              <div class="w-14 h-14 rounded-[5px] bg-slate-950 border border-slate-800 flex items-center justify-center mx-auto mb-3 text-slate-500">
                <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
              </div>
              <p class="text-xs font-bold text-slate-300">Menunggu scan kartu atau input manual...</p>
              <p class="text-[11px] text-slate-500 mt-1">Arahkan barcode / QR ke kamera atau ketik di input manual</p>
            </div>
          </template>

          <template x-if="result">
            <div class="w-full flex flex-col items-center text-center">
              <div class="relative mb-4">
                <img :src="result.foto" alt="Foto Profil" class="w-24 h-24 rounded-[5px] object-cover border-2 border-indigo-500/40 bg-slate-950 shadow-md">
                <span class="absolute bottom-1 right-1 px-2 py-0.5 text-[9px] font-black text-white uppercase rounded-[5px] tracking-wider shadow-sm"
                      :class="{
                        'bg-indigo-600': result.role === 'Siswa',
                        'bg-emerald-600': result.role === 'Guru',
                        'bg-amber-600': result.role === 'Staff TU'
                      }"
                      x-text="result.role"></span>
              </div>

              <h3 class="text-base font-extrabold text-white tracking-tight" x-text="result.name"></h3>
              <p class="text-xs text-indigo-300/90 font-semibold mt-1 px-3 py-0.5 bg-indigo-950/40 border border-indigo-900/40 rounded-[5px]" x-text="result.sub_info"></p>

              <div class="w-full max-w-sm mt-4 grid grid-cols-2 gap-3 border-t border-slate-800 pt-4">
                <div class="bg-slate-950/80 rounded-[5px] p-2.5 border border-slate-800/80 text-center">
                  <span class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider">Status Absen</span>
                  <span class="block text-xs font-black mt-0.5"
                        :class="result.status_code === 'H' ? 'text-emerald-400' : 'text-amber-400'"
                        x-text="result.status"></span>
                </div>
                <div class="bg-slate-950/80 rounded-[5px] p-2.5 border border-slate-800/80 text-center">
                  <span class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider">Jam Masuk</span>
                  <span class="block text-xs font-black text-indigo-400 mt-0.5" x-text="result.jam"></span>
                </div>
              </div>
            </div>
          </template>
        </div>
      </div>

      <!-- Tab 2: ABSENSI BULK / PENCARIAN -->
      <div x-show="activeTab === 'bulk'" class="flex-1 flex flex-col"
           x-data="{
             selectedRole: '',
             selectedKelas: '',
             searchQuery: '',
             students: [],
             loading: false,
             submitting: false,
             getInitials(name) {
               if (!name) return '??';
               const parts = name.trim().split(/\s+/);
               if (parts.length >= 2) {
                 return (parts[0][0] + parts[1][0]).toUpperCase();
               }
               return parts[0].substring(0, 2).toUpperCase();
             },
             initSelect2() {
               this.$nextTick(() => {
                 if (typeof jQuery !== 'undefined' && typeof jQuery.fn.select2 !== 'undefined') {
                   const $select = $(this.$refs.selectKelas);
                   if ($select.length) {
                     if ($select.hasClass('select2-hidden-accessible')) {
                       $select.select2('destroy');
                     }
                     $select.select2({
                       placeholder: '-- Semua Kelas --',
                       allowClear: false,
                       width: '100%'
                     });

                     if (this.selectedKelas) {
                       $select.val(this.selectedKelas).trigger('change.select2');
                     }

                     $select.off('change.select2-alpine').on('change.select2-alpine', (e) => {
                       const val = e.target.value;
                       if (this.selectedKelas !== val) {
                         this.selectedKelas = val;
                         this.loadData();
                       }
                     });
                   }
                 }
               });
             },
             loadData() {
               this.loading = true;
               const params = new URLSearchParams();
               if (this.selectedRole) params.append('role', this.selectedRole);
               if (this.searchQuery) params.append('q', this.searchQuery);

               fetch(`{{ route('public.absensi-cepat.search-people') }}?${params.toString()}`)
                 .then(res => res.json())
                 .then(res => {
                   if (res.success) {
                     this.students = res.data;
                   } else {
                     this.students = [];
                   }
                 })
                 .catch(err => console.error(err))
                 .finally(() => this.loading = false);
             },
              markAllHadir() {
                this.students.forEach(s => {
                  s.status = 'H';
                  this.autoSaveSingle(s);
                });
                Swal.fire({
                  toast: true,
                  position: 'top-end',
                  icon: 'success',
                  title: 'Semua ditandai Hadir & Tersimpan',
                  showConfirmButton: false,
                  timer: 1500,
                  background: '#0f172a',
                  color: '#f8fafc'
                });
              },
              autoSaveSingle(student) {
                if (!student || !student.id) return;
                fetch('{{ route('public.absensi-cepat.store-single') }}', {
                  method: 'POST',
                  headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                  },
                  body: JSON.stringify({
                    id: student.id,
                    type: student.type || 'siswa',
                    status: student.status,
                    keterangan: student.keterangan || '',
                    kelas_id: this.selectedKelas || null
                  })
                })
                .then(res => res.json())
                .then(res => {
                  if (res.success) {
                    Swal.fire({
                      toast: true,
                      position: 'top-end',
                      icon: 'success',
                      title: `${student.nama_lengkap}: ${student.status} tersimpan`,
                      showConfirmButton: false,
                      timer: 1500,
                      background: '#0f172a',
                      color: '#f8fafc'
                    });
                  } else {
                    Swal.fire({
                      toast: true,
                      position: 'top-end',
                      icon: 'error',
                      title: res.message || 'Gagal auto-save',
                      showConfirmButton: false,
                      timer: 2000,
                      background: '#0f172a',
                      color: '#f8fafc'
                    });
                  }
                })
                .catch(err => console.error('Public AutoSave Error:', err));
              },
             submitBulk() {
               if (this.students.length === 0) return;
               this.submitting = true;
               fetch('{{ route('public.absensi-cepat.bulk') }}', {
                 method: 'POST',
                 headers: {
                   'Content-Type': 'application/json',
                   'X-CSRF-TOKEN': '{{ csrf_token() }}'
                 },
                 body: JSON.stringify({
                   kelas_id: this.selectedKelas || null,
                   absensi: this.students.map(s => ({
                     id: s.id,
                     type: s.type || 'siswa',
                     status: s.status,
                     keterangan: s.keterangan
                   }))
                 })
               })
               .then(async res => {
                 const data = await res.json();
                 if (res.ok && data.success) {
                   Swal.fire({
                     icon: 'success',
                     title: 'Berhasil Disimpan',
                     html: `
                       <div class='text-left text-xs mt-3 space-y-1.5 font-medium'>
                         <p>Total Diproses: <b>${data.stats.total}</b></p>
                         <p class='text-emerald-400'>Hadir: <b>${data.stats.hadir}</b></p>
                         <p class='text-amber-400'>Terlambat: <b>${data.stats.terlambat}</b></p>
                         <p class='text-blue-400'>Sakit: <b>${data.stats.sakit}</b></p>
                         <p class='text-yellow-400'>Izin: <b>${data.stats.izin}</b></p>
                         <p class='text-rose-400'>Alpha: <b>${data.stats.alpha}</b></p>
                       </div>
                     `,
                     background: '#0f172a',
                     color: '#f8fafc',
                     confirmButtonColor: '#6366f1'
                   });
                 } else {
                   Swal.fire({
                     icon: 'error',
                     title: 'Gagal',
                     text: data.message || 'Terjadi kesalahan.',
                     background: '#0f172a',
                     color: '#f8fafc'
                   });
                 }
               })
               .catch(err => {
                 console.error(err);
                 Swal.fire({
                   icon: 'error',
                   title: 'Error',
                   text: 'Gagal menyimpan absensi.',
                   background: '#0f172a',
                   color: '#f8fafc'
                 });
               })
               .finally(() => {
                 this.submitting = false;
               });
             }
           }"
           x-init="initSelect2(); $watch('activeTab', tab => { if(tab === 'bulk') initSelect2(); })">
        
        <!-- Controls Header: Pegawai Filter (Guru & Staff TU) -->
        <div class="bg-slate-900/90 border border-slate-800 rounded-[5px] p-4 md:p-5 mb-4 flex flex-col lg:flex-row lg:items-end justify-between gap-4 shadow-lg relative z-20">
          <div class="flex flex-col sm:flex-row items-stretch sm:items-end gap-3 flex-1">
            
            <!-- 1. Select Kategori Pegawai -->
            <div class="w-full sm:w-64 md:w-72">
              <label class="block text-[11px] font-bold text-slate-300 uppercase tracking-wider mb-1.5 flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                Pilih Kategori Pegawai
              </label>
              <div class="relative w-full">
                <select x-model="selectedRole" @change="loadData()"
                        class="w-full h-10 bg-slate-950 border border-slate-800 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 rounded-[5px] text-xs font-semibold text-slate-100 outline-none px-4 pr-10 appearance-none cursor-pointer transition">
                  <option value="">-- Pilih Kategori --</option>
                  <option value="guru">👨‍🏫 Semua Guru</option>
                  <option value="staff">💼 Semua Staff TU</option>
                  <option value="all">👥 Semua Pegawai (Guru & Staff)</option>
                </select>
                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-slate-400">
                  <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </div>
              </div>
            </div>

            <!-- 2. Search Nama / NIP -->
            <div class="flex-1 w-full min-w-[200px]">
              <label class="block text-[11px] font-bold text-slate-300 uppercase tracking-wider mb-1.5 flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                Cari Nama / NIP
              </label>
              <div class="relative w-full">
                <input type="text" x-model="searchQuery" @input.debounce.300ms="loadData()"
                       placeholder="Ketik nama atau NIP..."
                       class="w-full h-10 bg-slate-950 border border-slate-800 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 rounded-[5px] pl-3.5 pr-8 text-xs font-semibold text-slate-100 placeholder-slate-500 outline-none transition">
                <button x-show="searchQuery" @click="searchQuery = ''; loadData()" class="absolute right-2.5 top-2.5 text-slate-500 hover:text-slate-300 text-xs font-bold">✕</button>
              </div>
            </div>
          </div>

          <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 w-full lg:w-auto">
            <button @click="markAllHadir()" :disabled="students.length === 0"
                    class="h-10 px-4 bg-emerald-500/10 border border-emerald-500/25 hover:bg-emerald-500/20 disabled:opacity-40 disabled:pointer-events-none text-emerald-400 font-bold text-xs rounded-[5px] transition duration-150 flex items-center justify-center gap-2">
              <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
              Tandai Semua Hadir
            </button>
            <span class="h-10 px-3.5 bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 font-bold text-xs rounded-[5px] flex items-center justify-center gap-1.5">
              <svg class="w-4 h-4 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
              Simpan Otomatis ⚡
            </span>
          </div>
        </div>

        <!-- Student & Person List Container -->
        <div class="bg-slate-900/90 border border-slate-800 rounded-[5px] overflow-hidden flex-1 flex flex-col shadow-lg relative z-10">
          
          <!-- Loading State -->
          <div x-show="loading" class="flex-1 flex flex-col items-center justify-center py-12">
            <svg class="animate-spin h-7 w-7 text-indigo-500 mb-2.5" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
            <p class="text-xs font-semibold text-slate-400">Mencari data...</p>
          </div>

          <!-- Empty State -->
          <div x-show="!loading && students.length === 0" class="flex-1 flex flex-col items-center justify-center py-12">
            <div class="w-12 h-12 rounded-[5px] bg-slate-950 border border-slate-800 flex items-center justify-center mb-2.5 text-slate-600">
              <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </div>
            <p class="text-xs font-semibold text-slate-400">Tidak ada data untuk ditampilkan.</p>
            <p class="text-[11px] text-slate-600 mt-0.5">Silakan pilih kategori pegawai atau ketik nama/NIP pada kolom pencarian.</p>
          </div>

          <!-- Table Person List (Siswa, Guru, Staff TU) -->
          <div x-show="!loading && students.length > 0" class="overflow-x-auto flex-1">
            <table class="w-full text-left border-collapse">
              <thead>
                <tr class="bg-slate-950 border-b border-slate-800 text-[10px] font-extrabold text-slate-400 uppercase tracking-wider">
                  <th class="px-4 py-3 w-12 text-center">No</th>
                  <th class="px-4 py-3 w-32">NIS / NIP</th>
                  <th class="px-4 py-3">Nama & Peran</th>
                  <th class="px-4 py-3 w-72 text-center">Status Absensi</th>
                  <th class="px-4 py-3">Keterangan</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-800/60">
                <template x-for="(student, index) in students" :key="student.type + '_' + student.id">
                  <tr class="hover:bg-slate-850/40 transition">
                    <td class="px-4 py-2.5 text-xs text-slate-400 font-medium text-center" x-text="index + 1"></td>
                    <td class="px-4 py-2.5 text-xs text-slate-300 font-bold" x-text="student.nis"></td>
                    <td class="px-4 py-2.5">
                      <div class="flex items-center gap-2.5">
                        <div class="w-7 h-7 rounded-[5px] bg-indigo-500/10 border border-indigo-500/25 flex items-center justify-center text-[10px] font-black text-indigo-400 shrink-0 uppercase tracking-tight shadow-sm"
                             x-text="getInitials(student.nama_lengkap)">
                        </div>
                        <div>
                          <div class="flex items-center gap-1.5">
                            <span class="text-xs font-bold text-white tracking-tight" x-text="student.nama_lengkap"></span>
                            <template x-if="student.type === 'guru'">
                              <span class="px-1.5 py-0.2 text-[9px] font-black bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 rounded-[3px] uppercase">GURU</span>
                            </template>
                            <template x-if="student.type === 'staff'">
                              <span class="px-1.5 py-0.2 text-[9px] font-black bg-amber-500/10 border border-amber-500/20 text-amber-400 rounded-[3px] uppercase">STAFF TU</span>
                            </template>
                            <template x-if="student.type === 'siswa'">
                              <span class="px-1.5 py-0.2 text-[9px] font-black bg-indigo-500/10 border border-indigo-500/20 text-indigo-400 rounded-[3px] uppercase">SISWA</span>
                            </template>
                          </div>
                          <p class="text-[10px] text-slate-400 font-medium" x-text="student.sub_info"></p>
                        </div>
                      </div>
                    </td>
                    <td class="px-4 py-2.5">
                      <div class="flex items-center justify-center gap-1">
                        <!-- Radio buttons for H, S, I, A, T -->
                        <label class="cursor-pointer">
                          <input type="radio" :name="'status_' + student.type + '_' + student.id" value="H" x-model="student.status" @change="autoSaveSingle(student)" class="sr-only peer">
                          <span class="w-8 h-7 rounded-[5px] text-xs font-black border border-slate-800 flex items-center justify-center peer-checked:border-emerald-500 peer-checked:bg-emerald-500/10 peer-checked:text-emerald-400 text-slate-400 hover:text-slate-200 transition">H</span>
                        </label>
                        <label class="cursor-pointer">
                          <input type="radio" :name="'status_' + student.type + '_' + student.id" value="S" x-model="student.status" @change="autoSaveSingle(student)" class="sr-only peer">
                          <span class="w-8 h-7 rounded-[5px] text-xs font-black border border-slate-800 flex items-center justify-center peer-checked:border-blue-500 peer-checked:bg-blue-500/10 peer-checked:text-blue-400 text-slate-400 hover:text-slate-200 transition">S</span>
                        </label>
                        <label class="cursor-pointer">
                          <input type="radio" :name="'status_' + student.type + '_' + student.id" value="I" x-model="student.status" @change="autoSaveSingle(student)" class="sr-only peer">
                          <span class="w-8 h-7 rounded-[5px] text-xs font-black border border-slate-800 flex items-center justify-center peer-checked:border-yellow-500 peer-checked:bg-yellow-500/10 peer-checked:text-yellow-400 text-slate-400 hover:text-slate-200 transition">I</span>
                        </label>
                        <label class="cursor-pointer">
                          <input type="radio" :name="'status_' + student.type + '_' + student.id" value="A" x-model="student.status" @change="autoSaveSingle(student)" class="sr-only peer">
                          <span class="w-8 h-7 rounded-[5px] text-xs font-black border border-slate-800 flex items-center justify-center peer-checked:border-rose-500 peer-checked:bg-rose-500/10 peer-checked:text-rose-400 text-slate-400 hover:text-slate-200 transition">A</span>
                        </label>
                        <label class="cursor-pointer">
                          <input type="radio" :name="'status_' + student.type + '_' + student.id" value="T" x-model="student.status" @change="autoSaveSingle(student)" class="sr-only peer">
                          <span class="w-8 h-7 rounded-[5px] text-xs font-black border border-slate-800 flex items-center justify-center peer-checked:border-amber-500 peer-checked:bg-amber-500/10 peer-checked:text-amber-400 text-slate-400 hover:text-slate-200 transition">T</span>
                        </label>
                      </div>
                    </td>
                    <td class="px-4 py-2.5">
                      <input type="text" x-model="student.keterangan" @blur="autoSaveSingle(student)" placeholder="Keterangan..."
                             class="w-full h-8 bg-slate-950 border border-slate-800 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 rounded-[5px] px-3 text-xs text-slate-200 placeholder-slate-600 outline-none transition">
                    </td>
                  </tr>
                </template>
              </tbody>
            </table>
          </div>

        </div>
      </div>

    </div>
  </div>

</body>
</html>
