<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Absensi Cepat Publik - Access Security</title>
  <link rel="stylesheet" href="{{ asset('assets/css/local-fonts.css') }}">
  <script src="https://cdn.tailwindcss.com"></script>
  <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <style>
    body { font-family: 'Product Sans', 'Inter', sans-serif; }
  </style>
</head>
<body class="bg-slate-950 text-slate-100 min-h-screen flex items-center justify-center p-4">

  <div class="fixed inset-0 pointer-events-none bg-[radial-gradient(ellipse_at_top,_var(--tw-gradient-stops))] from-indigo-900/30 via-slate-950 to-slate-950"></div>

  <div class="relative w-full max-w-md bg-slate-900/90 backdrop-blur-xl border border-slate-800 rounded-2xl shadow-2xl p-8" x-data="{ showPassword: false, loading: false }">
    <div class="flex flex-col items-center text-center mb-8">
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
        <img src="{{ $logoSrc }}" alt="School Logo" class="h-16 w-auto mb-3 object-contain drop-shadow-md">
      @else
        <div class="w-16 h-16 rounded-2xl bg-gradient-to-tr from-indigo-500 to-purple-600 flex items-center justify-center text-white font-extrabold text-2xl mb-3 shadow-lg shadow-indigo-500/30">
          AC
        </div>
      @endif
      
      <h1 class="text-2xl font-bold text-white tracking-tight">{{ setting('nama_sekolah', 'Absensi Cepat Publik') }}</h1>
      <p class="text-xs text-slate-400 mt-1">Portal Absensi Cepat Guru Piket & Pengawas</p>
    </div>

    @if(session('error'))
      <div class="mb-6 p-4 rounded-xl bg-rose-500/10 border border-rose-500/20 text-rose-400 text-sm flex items-center gap-3">
        <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <span>{{ session('error') }}</span>
      </div>
    @endif

    @if($errors->any())
      <script>
        document.addEventListener('DOMContentLoaded', function() {
          Swal.fire({
            icon: 'error',
            title: 'Akses Ditolak',
            text: '{{ $errors->first() }}',
            background: '#0f172a',
            color: '#f8fafc',
            confirmButtonColor: '#6366f1',
          });
        });
      </script>
    @endif

    <form action="{{ route('public.absensi-cepat.auth') }}" method="POST" @submit="loading = true">
      @csrf
      
      <div class="mb-6">
        <label for="password" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">Password Akses Scanner</label>
        <div class="relative">
          <input :type="showPassword ? 'text' : 'password'" id="password" name="password" required autofocus
                 placeholder="Masukkan password..."
                 class="w-full bg-slate-950/80 border border-slate-800 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 rounded-xl px-4 py-3.5 text-sm text-slate-100 placeholder-slate-500 outline-none transition duration-200">
          
          <button type="button" @click="showPassword = !showPassword"
                  class="absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-200 transition">
            <template x-if="!showPassword">
              <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
            </template>
            <template x-if="showPassword">
              <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858-5.908a8.959 8.959 0 012.122-.387c4.478 0 8.268 2.943 9.542 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21M3 3l18 18"/></svg>
            </template>
          </button>
        </div>
      </div>

      <button type="submit" :disabled="loading"
              class="w-full py-3.5 px-4 bg-gradient-to-r from-indigo-500 to-purple-600 hover:from-indigo-600 hover:to-purple-700 text-white font-semibold text-sm rounded-xl shadow-lg shadow-indigo-500/25 transition duration-200 flex items-center justify-center gap-2">
        <span x-show="!loading">Buka Absensi Cepat</span>
        <span x-show="loading" class="flex items-center gap-2">
          <svg class="animate-spin h-5 w-5 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
          Memverifikasi...
        </span>
      </button>
    </form>

    <div class="mt-6 text-center">
      <p class="text-xs text-slate-500">&copy; {{ date('Y') }} {{ setting('nama_sekolah', 'Absensi Sekolah') }}. Hak Cipta Dilindungi.</p>
    </div>
  </div>

</body>
</html>
