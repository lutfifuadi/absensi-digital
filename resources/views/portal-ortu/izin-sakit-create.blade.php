@extends('layouts/layoutMaster')

@section('title', 'Ajukan Izin/Sakit Anak')

@section('content')
<div class="row">
    <div class="col-12">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('ortu.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('ortu.izin-sakit.index') }}">Daftar Izin</a></li>
                <li class="breadcrumb-item active">Ajukan</li>
            </ol>
        </nav>
    </div>
</div>

<div class="row">
    <div class="col-md-8 col-12 mx-auto">
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Form Pengajuan Izin/Sakit</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('ortu.izin-sakit.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    
                    <div class="mb-3">
                        <label class="form-label">Pilih Anak</label>
                        <select name="siswa_id" class="form-select @error('siswa_id') is-invalid @enderror">
                            <option value="">-- Pilih Anak --</option>
                            @foreach($anakList as $anak)
                                <option value="{{ $anak->id }}" {{ (request('siswa_id') == $anak->id || old('siswa_id') == $anak->id) ? 'selected' : '' }}>
                                    {{ $anak->nama_lengkap }} ({{ $anak->kelas->nama ?? '-' }})
                                </option>
                            @endforeach
                        </select>
                        @error('siswa_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Jenis Pengajuan</label>
                        <div class="d-flex gap-3">
                            <div class="form-check">
                                <input name="jenis" class="form-check-input" type="radio" value="sakit" id="radioSakit" {{ old('jenis') == 'sakit' ? 'checked' : '' }} required>
                                <label class="form-check-label" for="radioSakit"> Sakit </label>
                            </div>
                            <div class="form-check">
                                <input name="jenis" class="form-check-input" type="radio" value="izin" id="radioIzin" {{ old('jenis') == 'izin' ? 'checked' : '' }}>
                                <label class="form-check-label" for="radioIzin"> Izin </label>
                            </div>
                        </div>
                        @error('jenis') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6 col-12 mb-3 mb-md-0">
                            <label class="form-label">Tanggal Mulai</label>
                            <input type="date" name="tanggal_mulai" class="form-control @error('tanggal_mulai') is-invalid @enderror" value="{{ old('tanggal_mulai', date('Y-m-d')) }}" required>
                            @error('tanggal_mulai') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6 col-12">
                            <label class="form-label">Tanggal Selesai</label>
                            <input type="date" name="tanggal_selesai" class="form-control @error('tanggal_selesai') is-invalid @enderror" value="{{ old('tanggal_selesai', date('Y-m-d')) }}" required>
                            @error('tanggal_selesai') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Keterangan</label>
                        <textarea name="keterangan" class="form-control @error('keterangan') is-invalid @enderror" rows="3" placeholder="Contoh: Mengikuti acara keluarga / Demam tinggi" required>{{ old('keterangan') }}</textarea>
                        @error('keterangan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Lampiran Bukti (Opsional)</label>
                        <input type="file" name="lampiran" class="form-control @error('lampiran') is-invalid @enderror" accept=".jpg,.jpeg,.png,.pdf">
                        <small class="text-muted">Maksimal 2MB. Format: JPG, PNG, PDF.</small>
                        @error('lampiran') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">Kirim Pengajuan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
