<!-- MODAL PINDAH KELAS -->
<div class="modal fade" id="modalPindahKelas" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content das-modal">
      <form method="POST" action="{{ route('admin.siswa.pindah-kelas', $siswa->id) }}">
        @csrf
        <div class="das-modal-head">
          <h5 class="das-modal-title"><i class="ti tabler-arrows-exchange me-2 text-warning"></i>Pindah Kelas</h5>
        </div>
        <div class="das-modal-body">
          <div class="alert alert-info py-2 small mb-3 bg-transparent border-info text-info">
            Memindahkan siswa ke kelas lain dalam tahun ajaran yang sama.
          </div>
          <div class="mb-3">
            <label class="form-label text-white-50 small fw-bold">KELAS SAAT INI</label>
            <input type="text" class="form-control bg-dark border-0 text-white" value="{{ $siswa->kelas->nama ?? '-' }}" readonly>
          </div>
          <div class="mb-3">
            <label class="form-label text-white-50 small fw-bold">KELAS TUJUAN</label>
            <select class="form-select bg-dark border-0 text-white" name="kelas_id" required>
              <option value="">— Pilih Kelas Tujuan —</option>
              @foreach($kelasOptions as $kelas)
                @if($kelas->tahun_akademik_id === $siswa->tahun_akademik_id && $kelas->id !== $siswa->kelas_id)
                  <option value="{{ $kelas->id }}">{{ $kelas->nama }}</option>
                @endif
              @endforeach
            </select>
          </div>
        </div>
        <div class="d-flex gap-2 p-4 pt-0">
          <button type="button" class="das-btn das-btn--secondary w-100" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="das-btn das-btn--warning w-100">Pindahkan</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- MODAL NAIK KELAS -->
<div class="modal fade" id="modalNaikKelas" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content das-modal">
      <form method="POST" action="{{ route('admin.siswa.naik-kelas', $siswa->id) }}">
        @csrf
        <div class="das-modal-head">
          <h5 class="das-modal-title"><i class="ti tabler-trending-up me-2 text-success"></i>Naik Kelas</h5>
        </div>
        <div class="das-modal-body">
          <div class="alert alert-warning py-2 small mb-3 bg-transparent border-warning text-warning">
            Menghubungkan siswa ke kelas di tahun ajaran baru.
          </div>
          <div class="mb-3">
            <label class="form-label text-white-50 small fw-bold">TA TUJUAN</label>
            <select class="form-select bg-dark border-0 text-white" name="tahun_akademik_id" id="naik_tahun_akademik_id" required>
              <option value="">— Pilih Tahun Akademik —</option>
              @foreach($tahunAkademikOptions as $ta)
                @if($ta->id !== $siswa->tahun_akadem_id)
                  <option value="{{ $ta->id }}">{{ $ta->nama }}</option>
                @endif
              @endforeach
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label text-white-50 small fw-bold">KELAS TUJUAN</label>
            <select class="form-select bg-dark border-0 text-white" name="kelas_id" id="naik_kelas_id" required disabled>
              <option value="">— Pilih TA Dulu —</option>
            </select>
          </div>
        </div>
        <div class="d-flex gap-2 p-4 pt-0">
          <button type="button" class="das-btn das-btn--secondary w-100" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="das-btn das-btn--success w-100">Proses Naik Kelas</button>
        </div>
      </form>
    </div>
  </div>
</div>
