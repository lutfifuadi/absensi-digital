<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard Absensi Cepat Publik</title>
  <link rel="stylesheet" href="{{ asset('assets/css/local-fonts.css') }}">
  <script src="https://cdn.tailwindcss.com"></script>
  <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="https://cdn.jsdelivr.net/npm/html5-qrcode/html5-qrcode.min.js"></script>
  <style>
    body { font-family: 'Product Sans', 'Inter', sans-serif; }
  </style>
</head>
<body class="bg-slate-950 text-slate-100 min-h-screen flex flex-col"
      x-data="{
        activeTab: 'scan',
        timeString: '',
        dateString: '',
        updateClock() {
          const now = new Date();
          this.timeString = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' }) + ' WIB';
          this.dateString = now.toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });
        }
      }"
      x-init="updateClock(); setInterval(() => updateClock(), 1000)">

  <div class="fixed inset-0 pointer-events-none bg-[radial-gradient(ellipse_at_top,_var(--tw-gradient-stops))] from-indigo-900/10 via-slate-950 to-slate-950"></div>

  <!-- Header -->
  <header class="relative border-b border-slate-800 bg-slate-900/60 backdrop-blur-md px-6 py-4 flex flex-col md:flex-row md:items-center justify-between gap-4 z-10">
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
        <img src="{{ $logoSrc }}" alt="School Logo" class="h-10 w-auto object-contain">
      @else
        <div class="w-10 h-10 rounded-xl bg-indigo-600 flex items-center justify-center text-white font-extrabold shadow-md">AC</div>
      @endif
      <div>
        <h1 class="text-lg font-bold text-white leading-tight">Absensi Cepat Publik</h1>
        <p class="text-xs text-slate-400 font-medium">{{ setting('nama_sekolah', 'Absensi Sekolah') }}</p>
      </div>
    </div>

    <div class="flex items-center gap-6">
      <div class="text-right hidden md:block">
        <p class="text-sm font-semibold text-white tracking-wide" x-text="timeString"></p>
        <p class="text-xs text-slate-400" x-text="dateString"></p>
      </div>

      <form action="{{ route('public.absensi-cepat.logout') }}" method="POST" class="inline">
        @csrf
        <button type="submit" class="px-4 py-2 bg-rose-600/10 border border-rose-500/20 hover:bg-rose-600/20 text-rose-400 font-semibold text-xs rounded-xl transition duration-150 flex items-center gap-2">
          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
          Selesai Sesi
        </button>
      </form>
    </div>
  </header>

  <!-- Navigation Tabs -->
  <div class="relative max-w-7xl w-full mx-auto px-6 py-6 z-10 flex-1 flex flex-col">
    <div class="flex justify-center mb-8">
      <div class="inline-flex p-1 bg-slate-900 border border-slate-800 rounded-xl">
        <button @click="activeTab = 'scan'"
                :class="activeTab === 'scan' ? 'bg-indigo-600 text-white shadow-md' : 'text-slate-400 hover:text-slate-200'"
                class="px-6 py-2.5 rounded-lg text-sm font-semibold tracking-wide transition duration-150 flex items-center gap-2">
          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h.01M16 20h2M12 8V7m0 1v3m4 9v-3m0 0h.01M20 12h.01M8 12h.01M8 8h.01M8 16h.01M4 8h.01M4 12h.01M4 16h.01M8 20h.01M12 20h.01M16 8h.01M20 8h.01M4 20h.01"/></svg>
          Scan QR Code
        </button>
        <button @click="activeTab = 'bulk'"
                :class="activeTab === 'bulk' ? 'bg-indigo-600 text-white shadow-md' : 'text-slate-400 hover:text-slate-200'"
                class="px-6 py-2.5 rounded-lg text-sm font-semibold tracking-wide transition duration-150 flex items-center gap-2">
          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
          Absensi Cepat (Bulk Kelas)
        </button>
      </div>
    </div>

    <!-- Tab Contents -->
    <div class="flex-1 flex flex-col">
      
      <!-- Tab 1: SCAN QR CODE -->
      <div x-show="activeTab === 'scan'" class="flex-1 grid grid-cols-1 lg:grid-cols-2 gap-8"
           x-data="{
             manualInput: '',
             loading: false,
             result: null,
             html5QrcodeScanner: null,
             initScanner() {
               this.html5QrcodeScanner = new Html5QrcodeScanner('qr-reader', {
                 fps: 10,
                 qrbox: { width: 250, height: 250 }
               }, false);
               this.html5QrcodeScanner.render((decodedText) => {
                 this.processScan(decodedText);
               }, (errorMessage) => {
                 // Scan errors ignored
               });
             },
             stopScanner() {
               if (this.html5QrcodeScanner) {
                 this.html5QrcodeScanner.clear().catch(e => console.error(e));
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
           x-init="setTimeout(() => initScanner(), 500)"
           @destroy="stopScanner()">
        
        <!-- Scanner Pane -->
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 flex flex-col">
          <h2 class="text-base font-bold text-white mb-4 flex items-center gap-2">
            <span class="w-2.5 h-2.5 bg-green-500 rounded-full animate-ping"></span>
            Kamera Scanner Active
          </h2>
          
          <div class="relative overflow-hidden rounded-xl border border-slate-800 bg-slate-950 flex-1 flex items-center justify-center min-h-[300px] mb-6">
            <div id="qr-reader" class="w-full"></div>
          </div>

          <!-- Fallback Manual Input -->
          <div>
            <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Input Manual (NIS / NIP / NAMA)</label>
            <div class="flex gap-2">
              <input type="text" x-model="manualInput" @keydown.enter.prevent="processScan(manualInput)"
                     placeholder="Masukkan NIS, NIP atau Nama..."
                     class="flex-1 bg-slate-950 border border-slate-800 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 rounded-xl px-4 py-3 text-sm text-slate-100 placeholder-slate-500 outline-none transition">
              <button @click="processScan(manualInput)"
                      class="px-6 py-3 bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white font-semibold text-sm rounded-xl transition duration-150">
                Kirim
              </button>
            </div>
          </div>
        </div>

        <!-- 3-Way Result Card -->
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 flex flex-col items-center justify-center min-h-[350px]">
          <template x-if="!result">
            <div class="text-center py-12">
              <div class="w-20 h-20 rounded-full bg-slate-950 border border-slate-850 flex items-center justify-center mx-auto mb-4 text-slate-600">
                <svg class="w-10 h-10" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
              </div>
              <p class="text-sm font-semibold text-slate-400">Menunggu scan kartu atau input manual...</p>
              <p class="text-xs text-slate-600 mt-1">Arahkan barcode / QR ke kamera atau ketik di input manual</p>
            </div>
          </template>

          <template x-if="result">
            <div class="w-full flex flex-col items-center text-center">
              <div class="relative mb-6">
                <img :src="result.foto" alt="Foto Profil" class="w-32 h-32 rounded-full object-cover border-4 border-indigo-600/40 bg-slate-950">
                <span class="absolute bottom-1.5 right-1.5 px-3 py-1 text-[10px] font-bold text-white uppercase rounded-full tracking-wider shadow-md"
                      :class="{
                        'bg-indigo-600': result.role === 'Siswa',
                        'bg-emerald-600': result.role === 'Guru',
                        'bg-amber-600': result.role === 'Staff TU'
                      }"
                      x-text="result.role"></span>
              </div>

              <h3 class="text-xl font-bold text-white" x-text="result.name"></h3>
              <p class="text-sm text-slate-400 mt-1" x-text="result.sub_info"></p>

              <div class="w-full max-w-sm mt-6 grid grid-cols-2 gap-4 border-t border-slate-800 pt-6">
                <div class="bg-slate-950/60 rounded-xl p-3 border border-slate-850">
                  <span class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider">Status Absen</span>
                  <span class="block text-sm font-extrabold mt-1"
                        :class="result.status_code === 'H' ? 'text-green-400' : 'text-amber-400'"
                        x-text="result.status"></span>
                </div>
                <div class="bg-slate-950/60 rounded-xl p-3 border border-slate-850">
                  <span class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider">Jam Masuk</span>
                  <span class="block text-sm font-extrabold text-indigo-400 mt-1" x-text="result.jam"></span>
                </div>
              </div>
            </div>
          </template>
        </div>
      </div>

      <!-- Tab 2: ABSENSI BULK KELAS -->
      <div x-show="activeTab === 'bulk'" class="flex-1 flex flex-col"
           x-data="{
             selectedKelas: '',
             students: [],
             loading: false,
             submitting: false,
             getStudents() {
               if (!this.selectedKelas) {
                 this.students = [];
                 return;
               }
               this.loading = true;
               fetch(`/absensi-cepat/siswa/${this.selectedKelas}`)
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
               this.students.forEach(s => s.status = 'H');
               Swal.fire({
                 toast: true,
                 position: 'top-end',
                 icon: 'success',
                 title: 'Semua siswa ditandai Hadir',
                 showConfirmButton: false,
                 timer: 1500,
                 background: '#0f172a',
                 color: '#f8fafc'
               });
             },
             submitBulk() {
               if (!this.selectedKelas || this.students.length === 0) return;
               this.submitting = true;
               fetch('{{ route('public.absensi-cepat.bulk') }}', {
                 method: 'POST',
                 headers: {
                   'Content-Type': 'application/json',
                   'X-CSRF-TOKEN': '{{ csrf_token() }}'
                 },
                 body: JSON.stringify({
                   kelas_id: this.selectedKelas,
                   absensi: this.students.map(s => ({
                     siswa_id: s.id,
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
                       <div class='text-left text-sm mt-3 space-y-1.5'>
                         <p>Total Siswa: <b>${data.stats.total}</b></p>
                         <p class='text-green-400'>Hadir: <b>${data.stats.hadir}</b></p>
                         <p class='text-amber-400'>Terlambat: <b>${data.stats.terlambat}</b></p>
                         <p class='text-blue-400'>Sakit: <b>${data.stats.sakit}</b></p>
                         <p class='text-yellow-400'>Izin: <b>${data.stats.izin}</b></p>
                         <p class='text-red-400'>Alpha: <b>${data.stats.alpha}</b></p>
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
           }">
        
        <!-- Controls Header -->
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
          <div class="flex-1 max-w-sm">
            <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Pilih Kelas</label>
            <select x-model="selectedKelas" @change="getStudents()"
                    class="w-full bg-slate-950 border border-slate-800 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 rounded-xl px-4 py-3 text-sm text-slate-100 outline-none transition">
              <option value="">-- Pilih Kelas --</option>
              @foreach($kelasList as $kelas)
                <option value="{{ $kelas->id }}">{{ $kelas->nama }}</option>
              @endforeach
            </select>
          </div>

          <div class="flex items-center gap-3">
            <button @click="markAllHadir()" :disabled="students.length === 0"
                    class="px-5 py-3 bg-indigo-600/10 border border-indigo-500/20 hover:bg-indigo-600/20 disabled:opacity-40 disabled:pointer-events-none text-indigo-400 font-semibold text-xs rounded-xl transition duration-150">
              Tandai Semua Hadir
            </button>
            <button @click="submitBulk()" :disabled="students.length === 0 || submitting"
                    class="px-6 py-3 bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 disabled:opacity-40 disabled:pointer-events-none text-white font-semibold text-xs rounded-xl transition duration-150 flex items-center gap-2">
              <span x-show="submitting" class="animate-spin rounded-full h-4.5 w-4.5 border-2 border-white border-t-transparent"></span>
              Submit Absensi
            </button>
          </div>
        </div>

        <!-- Student List Container -->
        <div class="bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden flex-1 flex flex-col">
          
          <!-- Loading State -->
          <div x-show="loading" class="flex-1 flex flex-col items-center justify-center py-20">
            <svg class="animate-spin h-10 w-10 text-indigo-500 mb-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
            <p class="text-sm font-semibold text-slate-400">Memuat daftar siswa...</p>
          </div>

          <!-- Empty State -->
          <div x-show="!loading && students.length === 0" class="flex-1 flex flex-col items-center justify-center py-20">
            <div class="w-16 h-16 rounded-full bg-slate-950 border border-slate-850 flex items-center justify-center mb-4 text-slate-600">
              <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            </div>
            <p class="text-sm font-semibold text-slate-400">Tidak ada data siswa untuk ditampilkan.</p>
            <p class="text-xs text-slate-600 mt-1">Silakan pilih kelas terlebih dahulu.</p>
          </div>

          <!-- Table Student List -->
          <div x-show="!loading && students.length > 0" class="overflow-x-auto flex-1">
            <table class="w-full text-left border-collapse">
              <thead>
                <tr class="bg-slate-950 border-b border-slate-800 text-[10px] font-bold text-slate-400 uppercase tracking-wider">
                  <th class="px-6 py-4 w-16">No</th>
                  <th class="px-6 py-4">NIS</th>
                  <th class="px-6 py-4">Nama Lengkap</th>
                  <th class="px-6 py-4 w-96 text-center">Status Absensi (1-5)</th>
                  <th class="px-6 py-4">Keterangan</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-800/60">
                <template x-for="(student, index) in students" :key="student.id">
                  <tr class="hover:bg-slate-900/40 transition">
                    <td class="px-6 py-4.5 text-sm text-slate-400 font-medium" x-text="index + 1"></td>
                    <td class="px-6 py-4.5 text-sm text-slate-300 font-semibold" x-text="student.nis"></td>
                    <td class="px-6 py-4.5">
                      <div class="flex items-center gap-3">
                        <img :src="student.foto || '/assets/img/avatars/1.png'" alt="Avatar" class="w-9 h-9 rounded-full object-cover bg-slate-950">
                        <span class="text-sm font-bold text-white" x-text="student.nama_lengkap"></span>
                      </div>
                    </td>
                    <td class="px-6 py-4.5">
                      <div class="flex items-center justify-center gap-2">
                        <!-- Radio buttons for H, S, I, A, T -->
                        <label class="cursor-pointer">
                          <input type="radio" :name="'status_' + student.id" value="H" x-model="student.status" class="sr-only peer">
                          <span class="px-3.5 py-2 rounded-lg text-xs font-bold border border-slate-800 peer-checked:border-green-500 peer-checked:bg-green-500/10 peer-checked:text-green-400 text-slate-400 hover:text-slate-200 transition">H</span>
                        </label>
                        <label class="cursor-pointer">
                          <input type="radio" :name="'status_' + student.id" value="S" x-model="student.status" class="sr-only peer">
                          <span class="px-3.5 py-2 rounded-lg text-xs font-bold border border-slate-800 peer-checked:border-blue-500 peer-checked:bg-blue-500/10 peer-checked:text-blue-400 text-slate-400 hover:text-slate-200 transition">S</span>
                        </label>
                        <label class="cursor-pointer">
                          <input type="radio" :name="'status_' + student.id" value="I" x-model="student.status" class="sr-only peer">
                          <span class="px-3.5 py-2 rounded-lg text-xs font-bold border border-slate-800 peer-checked:border-yellow-500 peer-checked:bg-yellow-500/10 peer-checked:text-yellow-400 text-slate-400 hover:text-slate-200 transition">I</span>
                        </label>
                        <label class="cursor-pointer">
                          <input type="radio" :name="'status_' + student.id" value="A" x-model="student.status" class="sr-only peer">
                          <span class="px-3.5 py-2 rounded-lg text-xs font-bold border border-slate-800 peer-checked:border-red-500 peer-checked:bg-red-500/10 peer-checked:text-red-400 text-slate-400 hover:text-slate-200 transition">A</span>
                        </label>
                        <label class="cursor-pointer">
                          <input type="radio" :name="'status_' + student.id" value="T" x-model="student.status" class="sr-only peer">
                          <span class="px-3.5 py-2 rounded-lg text-xs font-bold border border-slate-800 peer-checked:border-amber-500 peer-checked:bg-amber-500/10 peer-checked:text-amber-400 text-slate-400 hover:text-slate-200 transition">T</span>
                        </label>
                      </div>
                    </td>
                    <td class="px-6 py-4.5">
                      <input type="text" x-model="student.keterangan" placeholder="Keterangan..."
                             class="w-full bg-slate-950 border border-slate-800 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 rounded-lg px-3 py-1.5 text-xs text-slate-200 placeholder-slate-600 outline-none transition">
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
