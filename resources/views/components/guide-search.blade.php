@props([
    'placeholder' => 'Cari panduan...',
])

<div x-data="searchComponent()"
     @click.outside="showDropdown = false"
     class="relative w-full">

    {{-- Search Input --}}
    <div class="relative">
        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
        </svg>
        <input type="text"
               x-model="query"
               @input.debounce.300ms="search()"
               @focus="if(query.length >= 2) showDropdown = true"
               @keydown.escape="showDropdown = false"
               @keydown.enter="if(results.length > 0) navigate(results[0].url)"
               placeholder="{{ $placeholder }}"
               class="w-full pl-10 pr-4 py-2.5 text-sm bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500 dark:focus:border-brand-500 placeholder-slate-400 dark:placeholder-slate-500 text-slate-900 dark:text-slate-100 transition-all duration-200">

        {{-- Clear button --}}
        <button x-show="query.length > 0"
                @click="query = ''; results = []; showDropdown = false"
                class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-300">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>

    {{-- Search Results Dropdown --}}
    <div x-show="showDropdown && results.length > 0"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 -translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 -translate-y-2"
         class="absolute top-full left-0 right-0 mt-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-xl z-50 max-h-80 overflow-y-auto">

        {{-- Results header --}}
        <div class="px-4 py-2 text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500 border-b border-slate-100 dark:border-slate-700">
            <span x-text="results.length + ' hasil ditemukan'"></span>
        </div>

        {{-- Result items --}}
        <template x-for="(result, index) in results" :key="index">
            <a :href="result.url"
               @click="showDropdown = false"
               class="flex items-start gap-3 px-4 py-3 hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors duration-150 border-b border-slate-50 dark:border-slate-700/50 last:border-0">
                <span class="iconify w-4 h-4 mt-0.5 text-brand-500 flex-shrink-0" :data-icon="result.icon || 'tabler:file-text'"></span>
                <div class="min-w-0 flex-1">
                    <p class="text-sm font-medium text-slate-900 dark:text-white truncate" x-text="result.title"></p>
                    <p class="text-xs text-slate-500 dark:text-slate-400 truncate" x-text="result.excerpt"></p>
                    <p class="text-xs text-brand-500 mt-0.5" x-text="result.category"></p>
                </div>
            </a>
        </template>
    </div>

    {{-- No results --}}
    <div x-show="showDropdown && query.length >= 2 && results.length === 0"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 -translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         class="absolute top-full left-0 right-0 mt-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-xl z-50 p-6 text-center">
        <span class="iconify w-8 h-8 text-slate-300 dark:text-slate-600 mx-auto mb-2" data-icon="tabler:search-off"></span>
        <p class="text-sm text-slate-500 dark:text-slate-400">Tidak ditemukan hasil untuk "<span x-text="query" class="font-medium"></span>"</p>
        <p class="text-xs text-slate-400 dark:text-slate-500 mt-1">Coba gunakan kata kunci lain</p>
    </div>
</div>

<script>
    function searchComponent() {
        return {
            query: '',
            results: [],
            showDropdown: false,

            // Built-in search data (can be overridden by passing JSON from controller)
            searchData: [
                { title: 'Login Pertama Kali', excerpt: 'Panduan login untuk pengguna baru', category: 'Pengaturan Akun', url: '/guide/pengaturan-akun/login-pertama-kali', icon: 'tabler:login' },
                { title: 'Ganti Password', excerpt: 'Cara mengganti password akun', category: 'Pengaturan Akun', url: '/guide/pengaturan-akun/ganti-password', icon: 'tabler:key' },
                { title: 'Profil Pengguna', excerpt: 'Mengelola data profil pengguna', category: 'Pengaturan Akun', url: '/guide/pengaturan-akun/profil-pengguna', icon: 'tabler:user' },
                { title: 'Cara Scan QR Code', excerpt: 'Panduan melakukan scan QR untuk absensi', category: 'Absensi Siswa', url: '/guide/absensi-siswa/cara-scan-qr', icon: 'tabler:qrcode-scan' },
                { title: 'Absensi Mandiri', excerpt: 'Melakukan absensi mandiri dari smartphone', category: 'Absensi Siswa', url: '/guide/absensi-siswa/absensi-mandiri', icon: 'tabler:device-mobile' },
                { title: 'Riwayat Kehadiran', excerpt: 'Melihat riwayat kehadiran siswa', category: 'Absensi Siswa', url: '/guide/absensi-siswa/riwayat-kehadiran', icon: 'tabler:history' },
                { title: 'Absensi Guru via QR', excerpt: 'Panduan absensi guru dengan QR Code', category: 'Absensi Guru', url: '/guide/absensi-guru/absensi-guru-qr', icon: 'tabler:chalkboard' },
                { title: 'Pengajuan Izin Guru', excerpt: 'Mengajukan izin sebagai guru', category: 'Absensi Guru', url: '/guide/absensi-guru/izin-guru', icon: 'tabler:file-text' },
                { title: 'Cetak Laporan Bulanan', excerpt: 'Cetak laporan absensi bulanan', category: 'Laporan', url: '/guide/laporan/cetak-laporan', icon: 'tabler:printer' },
                { title: 'Export Data Excel', excerpt: 'Mengexport data absensi ke Excel', category: 'Laporan', url: '/guide/laporan/export-data', icon: 'tabler:file-spreadsheet' },
                { title: 'Konfigurasi WA Gateway', excerpt: 'Menyiapkan gateway WhatsApp', category: 'Notifikasi', url: '/guide/notifikasi/konfigurasi-wa', icon: 'tabler:brand-whatsapp' },
                { title: 'Notifikasi Otomatis', excerpt: 'Mengatur notifikasi otomatis', category: 'Notifikasi', url: '/guide/notifikasi/notifikasi-otomatis', icon: 'tabler:bell' },
                { title: 'Tahun Akademik', excerpt: 'Mengelola tahun akademik', category: 'Pengaturan Sistem', url: '/guide/pengaturan/tahun-akademik', icon: 'tabler:calendar' },
                { title: 'Manajemen User', excerpt: 'Mengelola pengguna sistem', category: 'Pengaturan Sistem', url: '/guide/pengaturan/manajemen-user', icon: 'tabler:users' },
            ],

            search() {
                const q = this.query.toLowerCase().trim();
                if (q.length < 2) {
                    this.results = [];
                    this.showDropdown = false;
                    return;
                }

                // Filter search data
                this.results = this.searchData.filter(item => {
                    return item.title.toLowerCase().includes(q) ||
                           item.excerpt.toLowerCase().includes(q) ||
                           item.category.toLowerCase().includes(q);
                });

                this.showDropdown = true;
            },

            navigate(url) {
                window.location.href = url;
            }
        };
    }
</script>
