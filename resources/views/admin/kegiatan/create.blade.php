@extends('layouts/layoutMaster')

@section('title', 'Tambah Kegiatan Khusus')

@section('page-style')
<style>
    .glass-card {
        background: rgba(255, 255, 255, 0.04) !important;
        border: 1px solid rgba(255, 255, 255, 0.08) !important;
        backdrop-filter: blur(10px);
    }
</style>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card glass-card border-0 shadow-none">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-start mb-4">
                    <div>
                        <h4 class="mb-1 text-white fw-bold">Tambah Kegiatan Baru</h4>
                        <p class="text-white-50 mb-0">Masukkan detail kegiatan untuk role operator.</p>
                    </div>
                    <a href="{{ route('admin.kegiatan.index') }}" class="btn btn-outline-secondary">Kembali ke Daftar</a>
                </div>

                @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                <form action="{{ route('admin.kegiatan.store') }}" method="POST">
                    @csrf
                    <div class="row gy-3">
                        <div class="col-md-6">
                            <label class="form-label text-white-50 small fw-bold">NAMA KEGIATAN</label>
                            <input type="text" name="nama_kegiatan" value="{{ old('nama_kegiatan') }}" class="form-control bg-dark border-secondary text-white" style="border-radius: 8px;" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-white-50 small fw-bold">JENIS</label>
                            <select name="jenis" class="form-select bg-dark border-secondary text-white" style="border-radius: 8px;" required>
                                <option value="EKSTRAKURIKULER" {{ old('jenis') === 'EKSTRAKURIKULER' ? 'selected' : '' }}>Ekstrakurikuler</option>
                                <option value="UJIAN" {{ old('jenis') === 'UJIAN' ? 'selected' : '' }}>Ujian</option>
                                <option value="RAPAT" {{ old('jenis') === 'RAPAT' ? 'selected' : '' }}>Rapat</option>
                                <option value="LAINNYA" {{ old('jenis') === 'LAINNYA' ? 'selected' : '' }}>Lainnya</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-white-50 small fw-bold">TANGGAL</label>
                            <input type="date" name="tanggal_pelaksanaan" value="{{ old('tanggal_pelaksanaan', date('Y-m-d')) }}" class="form-control bg-dark border-secondary text-white" style="border-radius: 8px;" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-white-50 small fw-bold">WAKTU MULAI</label>
                            <input type="time" name="waktu_mulai" value="{{ old('waktu_mulai') }}" class="form-control bg-dark border-secondary text-white" style="border-radius: 8px;" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-white-50 small fw-bold">WAKTU SELESAI</label>
                            <input type="time" name="waktu_selesai" value="{{ old('waktu_selesai') }}" class="form-control bg-dark border-secondary text-white" style="border-radius: 8px;" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label text-white-50 small fw-bold">LOKASI</label>
                            <input type="text" name="lokasi" value="{{ old('lokasi') }}" class="form-control bg-dark border-secondary text-white" style="border-radius: 8px;" placeholder="Nama Aula/Lapangan">
                        </div>
                        <div class="col-12">
                            <label class="form-label text-white-50 small fw-bold">DESKRIPSI KEGIATAN</label>
                            <textarea name="keterangan" class="form-control bg-dark border-secondary text-white" style="border-radius: 8px;" rows="3" placeholder="Tuliskan deskripsi singkat kegiatan">{{ old('keterangan') }}</textarea>
                        </div>
                        <div class="col-12 text-end">
                            <button type="submit" class="btn btn-primary px-4 fw-bold" style="border-radius: 8px;">Simpan Agenda</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
