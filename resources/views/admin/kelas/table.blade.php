<div class="table-responsive">
  <table class="table table-hover align-middle mb-0" style="color:inherit;">
    <thead
      style="background:rgba(255,255,255,0.04);font-size:0.75rem;text-transform:uppercase;letter-spacing:0.8px;opacity:0.7;">
      <tr>
        <th class="ps-4 py-3" style="width:46px;">#</th>
        <th class="py-3">Nama Kelas</th>
        <th class="py-3 text-center">Tingkat</th>
        <th class="py-3">Jurusan</th>
        <th class="py-3 text-center">Jumlah Siswa</th>
        <th class="py-3">Wali Kelas</th>
        <th class="py-3">Tahun Akademik</th>
        <th class="py-3 pe-4 text-end">Aksi</th>
      </tr>
    </thead>
    <tbody>
      @forelse($kelas as $item)
        <tr class="kelas-row-hover">
          <td class="ps-4 text-white-50">{{ $kelas->firstItem() + $loop->index }}</td>
          <td>
            <div class="d-flex align-items-center gap-2">
              <div class="avatar avatar-xs">
                <span class="avatar-initial rounded bg-label-info">
                  <i class="ti tabler-door" style="font-size:0.8rem;"></i>
                </span>
              </div>
              <div>
                <span class="fw-semibold">{{ $item->nama }}</span>
                @if ($item->kustomisasi_jam)
                  <span class="badge bg-label-warning ms-1" style="font-size:0.6rem;" title="Jam Khusus: {{ $item->jam_masuk ? \Carbon\Carbon::parse($item->jam_masuk)->format('H:i') : '-' }} - {{ $item->jam_pulang ? \Carbon\Carbon::parse($item->jam_pulang)->format('H:i') : '-' }}">
                    <i class="ti tabler-clock-edit"></i>
                  </span>
                @endif
                @if (!$item->is_aktif_absensi)
                  <span class="badge bg-label-secondary ms-1" style="font-size:0.6rem;" title="Absensi Nonaktif">
                    <i class="ti tabler-player-pause"></i>
                  </span>
                @endif
              </div>
            </div>
          </td>
          <td class="text-center">
            @php
              $tingkatColor = match ($item->tingkat) {
                  'X' => 'primary',
                  'XI' => 'warning',
                  'XII' => 'danger',
                  default => 'secondary',
              };
            @endphp
            <span class="badge bg-label-{{ $tingkatColor }} tingkat-badge">{{ $item->tingkat }}</span>
          </td>
          <td class="text-white-50 small">{{ $item->jurusan }}</td>
          <td class="text-center">
            <span class="badge bg-label-primary px-2">{{ $item->siswa_count }}</span>
          </td>
          <td>
            @if ($item->waliKelas)
              <div class="d-flex align-items-start gap-3">
                <div class="avatar avatar-xs mt-1">
                  <span class="avatar-initial rounded-circle bg-label-success" style="font-size:0.65rem;">
                    {{ strtoupper(substr($item->waliKelas->nama_lengkap, 0, 1)) }}
                  </span>
                </div>
                <div class="w-100">
                  <div class="small mb-1">{{ $item->waliKelas->nama_lengkap }}</div>
                </div>
              </div>
            @else
              <span class="text-white-50 small">— Belum ditentukan</span>
            @endif
          </td>
          <td>
            @if ($item->tahunAkademik)
              <span class="badge bg-label-warning px-2">{{ $item->tahunAkademik->nama }}</span>
              <span class="text-white-50" style="font-size:0.72rem;">
                {{ ucfirst($item->tahunAkademik->semester) }}</span>
            @else
              <span class="text-white-50 small">—</span>
            @endif
          </td>
          <td class="pe-4 text-end">
            <a href="{{ route('admin.kelas.show', $item) }}"
              class="action-btn bg-label-success text-success me-1">
              <i class="ti tabler-users"></i> Detail Siswa
            </a>
            <button type="button" class="action-btn bg-label-info text-info me-1"
              onclick="openEditKelas({
                id: {{ $item->id }},
                nama: '{{ addslashes($item->nama) }}',
                tingkat: '{{ $item->tingkat }}',
                jurusan: '{{ addslashes($item->jurusan) }}',
                tahun_akademik_id: {{ $item->tahun_akademik_id ?? 'null' }},
                wali_kelas_id: {{ $item->wali_kelas_id ?? 'null' }},
                is_aktif_absensi: {{ $item->is_aktif_absensi ? 'true' : 'false' }},
                kustomisasi_jam: {{ $item->kustomisasi_jam ? 'true' : 'false' }},
                jam_masuk: '{{ $item->jam_masuk ? \Carbon\Carbon::parse($item->jam_masuk)->format('H:i') : '' }}',
                jam_pulang: '{{ $item->jam_pulang ? \Carbon\Carbon::parse($item->jam_pulang)->format('H:i') : '' }}'
              })">
              <i class="ti tabler-pencil"></i> Ubah
            </button>
            <button type="button" class="action-btn bg-label-danger text-danger"
              onclick="openHapusKelas({{ $item->id }}, '{{ addslashes($item->nama) }}')">
              <i class="ti tabler-trash"></i> Hapus
            </button>
          </td>
        </tr>
      @empty
        <tr>
          <td colspan="8" class="text-center py-5">
            <div class="d-flex flex-column align-items-center gap-2 opacity-50">
              <i class="ti tabler-door-off" style="font-size:2.5rem;"></i>
              <span class="small">Belum ada data kelas.</span>
              <button type="button" class="btn btn-sm btn-label-info mt-1" onclick="openTambahKelas()">
                <i class="ti tabler-plus me-1"></i> Tambah Sekarang
              </button>
            </div>
          </td>
        </tr>
      @endforelse
    </tbody>
  </table>
</div>

@if ($kelas->hasPages())
  <div class="px-4 py-3 border-top" style="border-color: var(--das-border) !important;">
    {{ $kelas->links('vendor.pagination.users') }}
  </div>
@endif
