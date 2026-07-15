@extends('layouts/layoutMaster')

@section('title', 'Ajukan Izin/Sakit Anak')

@section('page-style')
<style>
    /* Premium Border Radius Constraint (Max 5px) */
    .card, .btn, .badge, .rounded, .rounded-circle, .avatar, .avatar-initial, .form-select, .form-control {
        border-radius: 5px !important;
    }
    
    /* Elegant Form Card */
    .izin-create-card {
        background: linear-gradient(135deg, rgba(115, 103, 240, 0.03) 0%, rgba(30, 41, 59, 0.01) 100%);
        border-top: 4px solid #7367f0 !important;
    }
</style>
@endsection

@section('content')
<div class="row mb-4">
    <div class="col-12 text-white">
        <div class="card border-0 text-white overflow-hidden shadow-sm"
            style="background: linear-gradient(135deg, #7367f0 0%, #4338ca 100%); border-radius: 5px !important;">
            <div class="card-body p-4">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="d-flex align-items-center justify-content-center shadow-sm"
                            style="width:52px;height:52px;border-radius:5px !important;background:rgba(255,255,255,0.2);border:1px solid rgba(255,255,255,0.3);">
                            <i class="ti tabler-file-plus text-white fs-3"></i>
                        </div>
                        <div>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb mb-1" style="font-size:0.72rem;opacity:0.8;">
                                    <li class="breadcrumb-item"><a href="{{ route('ortu.dashboard') }}"
                                            class="text-white text-decoration-none">Dashboard</a></li>
                                    <li class="breadcrumb-item"><a href="{{ route('ortu.izin-sakit.index') }}"
                                            class="text-white text-decoration-none">Daftar Izin</a></li>
                                    <li class="breadcrumb-item active text-white" aria-current="page">Ajukan</li>
                                </ol>
                            </nav>
                            <h4 class="mb-0 text-white fw-bold" style="letter-spacing:-0.5px;">Ajukan Izin & Sakit</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card border-0 shadow-sm mb-4 izin-create-card">
            <div class="card-header border-bottom py-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold text-white"><i class="ti tabler-forms me-2 text-primary"></i>Form Pengajuan Izin/Sakit</h5>
            </div>
            <div class="card-body pt-4">
                <form action="{{ route('ortu.izin-sakit.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    
                    <div class="mb-4">
                        <label class="form-label fw-semibold small text-muted text-uppercase mb-1">Pilih Anak <span class="text-danger">*</span></label>
                        <select name="siswa_id" class="form-select @error('siswa_id') is-invalid @enderror py-2">
                            <option value="">-- Pilih Anak --</option>
                            @foreach($anakList as $anak)
                                <option value="{{ $anak->id }}" {{ (request('siswa_id') == $anak->id || old('siswa_id') == $anak->id) ? 'selected' : '' }}>
                                    {{ $anak->nama_lengkap }} ({{ $anak->kelas?->nama ?? '-' }})
                                </option>
                            @endforeach
                        </select>
                        @error('siswa_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold small text-muted text-uppercase mb-2 d-block">Jenis Pengajuan <span class="text-danger">*</span></label>
                        <div class="d-flex gap-4">
                            <div class="form-check">
                                <input name="jenis" class="form-check-input" type="radio" value="sakit" id="radioSakit" {{ old('jenis') == 'sakit' ? 'checked' : '' }} required>
                                <label class="form-check-label fw-semibold text-white" for="radioSakit"> Sakit </label>
                            </div>
                            <div class="form-check">
                                <input name="jenis" class="form-check-input" type="radio" value="izin" id="radioIzin" {{ old('jenis') == 'izin' ? 'checked' : '' }}>
                                <label class="form-check-label fw-semibold text-white" for="radioIzin"> Izin </label>
                            </div>
                        </div>
                        @error('jenis') <small class="text-danger mt-1.5 d-block">{{ $message }}</small> @enderror
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6 col-12 mb-3 mb-md-0">
                            <label class="form-label fw-semibold small text-muted text-uppercase mb-1">Tanggal Mulai <span class="text-danger">*</span></label>
                            <input type="date" name="tanggal_mulai" class="form-control @error('tanggal_mulai') is-invalid @enderror py-2" value="{{ old('tanggal_mulai', date('Y-m-d')) }}" required>
                            @error('tanggal_mulai') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6 col-12">
                            <label class="form-label fw-semibold small text-muted text-uppercase mb-1">Tanggal Selesai <span class="text-danger">*</span></label>
                            <input type="date" name="tanggal_selesai" class="form-control @error('tanggal_selesai') is-invalid @enderror py-2" value="{{ old('tanggal_selesai', date('Y-m-d')) }}" required>
                            @error('tanggal_selesai') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold small text-muted text-uppercase mb-1">Keterangan <span class="text-danger">*</span></label>
                        <textarea name="keterangan" class="form-control @error('keterangan') is-invalid @enderror" rows="3" placeholder="Contoh: Mengikuti acara keluarga / Demam tinggi" required>{{ old('keterangan') }}</textarea>
                        @error('keterangan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold small text-muted text-uppercase mb-1">Lampiran Bukti (Opsional)</label>
                        <input type="file" name="lampiran" class="form-control @error('lampiran') is-invalid @enderror" accept=".jpg,.jpeg,.png,.pdf">
                        <small class="text-muted mt-1.5 d-block">Maksimal 2MB. Format: JPG, PNG, PDF.</small>
                        @error('lampiran') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="d-grid mt-5">
                        <button type="submit" class="btn btn-primary py-2.5 fw-semibold"><i class="ti tabler-device-floppy me-1"></i>Kirim Pengajuan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
