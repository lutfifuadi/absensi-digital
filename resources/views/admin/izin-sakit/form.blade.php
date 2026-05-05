@extends('layouts/layoutMaster')

@section('title', isset($izinSakit) ? 'Ubah Pengajuan Izin/Sakit' : 'Tambah Pengajuan Izin/Sakit')

@section('content')
  <div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4">
      <span class="text-muted fw-light">Admin / <a href="{{ route('admin.izin-sakit.index') }}">Izin & Sakit</a> /</span>
      {{ isset($izinSakit) ? 'Edit Pengajuan' : 'Tambah Pengajuan' }}
    </h4>

    @if ($errors->any())
      <div class="alert alert-danger mb-4">
        <ul class="mb-0">
          @foreach ($errors->all() as $e)
            <li>{{ $e }}</li>
          @endforeach
        </ul>
      </div>
    @endif

    <div class="card">
      <div class="card-body">
        <form
          action="{{ isset($izinSakit) ? route('admin.izin-sakit.update', $izinSakit) : route('admin.izin-sakit.store') }}"
          method="POST" enctype="multipart/form-data">
          @csrf
          @if (isset($izinSakit))
            @method('PUT')
          @endif

          <div class="row gy-3">
            <div class="col-md-4">
              <label class="form-label">Tipe <span class="text-danger">*</span></label>
              <select name="tipe" class="form-select @error('tipe') is-invalid @enderror" id="tipePengaju" required>
                <option value="">-- Pilih Tipe --</option>
                @foreach (['siswa', 'guru', 'staff'] as $t)
                  <option value="{{ $t }}" @selected(old('tipe', $izinSakit->tipe ?? '') === $t)>{{ ucfirst($t) }}</option>
                @endforeach
              </select>
              @error('tipe')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <div class="col-md-8">
              <label class="form-label">Nama <span class="text-danger">*</span></label>
              <select name="reference_id" class="form-select @error('reference_id') is-invalid @enderror" required>
                <option value="">-- Pilih Nama --</option>
                <optgroup label="Siswa">
                  @foreach ($siswaOptions as $s)
                    <option value="{{ $s->id }}" data-tipe="siswa" @selected(old('reference_id', $izinSakit->reference_id ?? '') == $s->id && old('tipe', $izinSakit->tipe ?? '') === 'siswa')>
                      {{ $s->nama_lengkap }}
                    </option>
                  @endforeach
                </optgroup>
                <optgroup label="Guru">
                  @foreach ($guruOptions as $g)
                    <option value="{{ $g->id }}" data-tipe="guru" @selected(old('reference_id', $izinSakit->reference_id ?? '') == $g->id && old('tipe', $izinSakit->tipe ?? '') === 'guru')>
                      {{ $g->nama_lengkap }}
                    </option>
                  @endforeach
                </optgroup>
                <optgroup label="Staff TU">
                  @foreach ($staffOptions as $st)
                    <option value="{{ $st->id }}" data-tipe="staff" @selected(old('reference_id', $izinSakit->reference_id ?? '') == $st->id && old('tipe', $izinSakit->tipe ?? '') === 'staff')>
                      {{ $st->nama_lengkap }}
                    </option>
                  @endforeach
                </optgroup>
              </select>
              @error('reference_id')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <div class="col-md-3">
              <label class="form-label">Jenis <span class="text-danger">*</span></label>
              <select name="jenis" class="form-select @error('jenis') is-invalid @enderror" required>
                <option value="">-- Pilih Jenis --</option>
                @foreach (['sakit', 'izin'] as $j)
                  <option value="{{ $j }}" @selected(old('jenis', $izinSakit->jenis ?? '') === $j)>{{ ucfirst($j) }}</option>
                @endforeach
              </select>
              @error('jenis')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <div class="col-md-3">
              <label class="form-label">Tanggal Mulai <span class="text-danger">*</span></label>
              <input type="date" name="tanggal_mulai" class="form-control @error('tanggal_mulai') is-invalid @enderror"
                value="{{ old('tanggal_mulai', isset($izinSakit) ? $izinSakit->tanggal_mulai->format('Y-m-d') : '') }}"
                required>
              @error('tanggal_mulai')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <div class="col-md-3">
              <label class="form-label">Tanggal Selesai <span class="text-danger">*</span></label>
              <input type="date" name="tanggal_selesai"
                class="form-control @error('tanggal_selesai') is-invalid @enderror"
                value="{{ old('tanggal_selesai', isset($izinSakit) ? $izinSakit->tanggal_selesai->format('Y-m-d') : '') }}"
                required>
              @error('tanggal_selesai')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            @if (isset($izinSakit))
              <div class="col-md-3">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                  @foreach (['pending', 'disetujui', 'ditolak'] as $s)
                    <option value="{{ $s }}" @selected(old('status', $izinSakit->status) === $s)>{{ ucfirst($s) }}</option>
                  @endforeach
                </select>
              </div>
            @endif

            <div class="col-md-12">
              <label class="form-label">Keterangan</label>
              <textarea name="keterangan" class="form-control" rows="3">{{ old('keterangan', $izinSakit->keterangan ?? '') }}</textarea>
            </div>

            <div class="col-md-6">
              <label class="form-label">Lampiran Surat (maks. 100KB — JPG/PNG/PDF)</label>
              @if (isset($izinSakit) && $izinSakit->lampiran)
                <div class="mb-2">
                  <a href="{{ Storage::url($izinSakit->lampiran) }}" target="_blank" class="btn btn-sm btn-info">Lihat
                    Lampiran Lama</a>
                </div>
              @endif
              <input type="file" name="lampiran" class="form-control @error('lampiran') is-invalid @enderror"
                accept=".jpg,.jpeg,.png,.pdf">
              <small class="text-muted">Upload file baru untuk mengganti lampiran lama.</small>
              @error('lampiran')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
          </div>

          <div class="mt-4 d-flex gap-2">
            <button type="submit" class="btn btn-primary">{{ isset($izinSakit) ? 'Perbarui' : 'Simpan' }}</button>
            <a href="{{ route('admin.izin-sakit.index') }}" class="btn btn-secondary">Batal</a>
          </div>
        </form>
      </div>
    </div>
  </div>
@endsection
