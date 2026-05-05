@extends('layouts/layoutMaster')

@section('title', isset($absensiGuru) ? 'Ubah Absensi Guru' : 'Tambah Absensi Guru')

@section('content')
    <div class="row mb-3">
      <div class="col-12 d-flex justify-content-between align-items-center">
        <h1 class="mb-0">{{ isset($absensiGuru) ? 'Ubah Absensi Guru' : 'Tambah Absensi Guru' }}</h1>
        <a href="{{ route('admin.absensi-guru.index') }}" class="btn btn-secondary">Kembali</a>
      </div>
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

    <div class="card">
      <div class="card-body">
        <form
          action="{{ isset($absensiGuru) ? route('admin.absensi-guru.update', $absensiGuru) : route('admin.absensi-guru.store') }}"
          method="POST">
          @csrf
          @if (isset($absensiGuru))
            @method('PUT')
          @endif

          <div class="mb-3">
            <label class="form-label">Guru</label>
            <select name="guru_id" class="form-select" required>
              <option value="">Pilih guru</option>
              @foreach ($guruOptions as $guru)
                <option value="{{ $guru->id }}"
                  {{ old('guru_id', $absensiGuru->guru_id ?? '') == $guru->id ? 'selected' : '' }}>
                  {{ $guru->nama_lengkap }}</option>
              @endforeach
            </select>
          </div>

          <div class="row">
            <div class="col-md-4 mb-3">
              <label class="form-label">Tanggal</label>
              <input type="date" name="tanggal" class="form-control"
                value="{{ old('tanggal', isset($absensiGuru) ? $absensiGuru->tanggal->format('Y-m-d') : '') }}" required>
            </div>
            <div class="col-md-4 mb-3">
              <label class="form-label">Jam Masuk</label>
              <input type="time" name="jam_masuk" class="form-control"
                value="{{ old('jam_masuk', $absensiGuru->jam_masuk ?? '') }}">
            </div>
            <div class="col-md-4 mb-3">
              <label class="form-label">Jam Pulang</label>
              <input type="time" name="jam_pulang" class="form-control"
                value="{{ old('jam_pulang', $absensiGuru->jam_pulang ?? '') }}">
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label">Status</label>
            <select name="status" class="form-select" required>
              @foreach (['hadir', 'sakit', 'izin', 'alpha', 'terlambat'] as $status)
                <option value="{{ $status }}"
                  {{ old('status', $absensiGuru->status ?? '') === $status ? 'selected' : '' }}>{{ ucfirst($status) }}
                </option>
              @endforeach
            </select>
          </div>

          <div class="mb-3">
            <label class="form-label">Metode</label>
            <select name="metode" class="form-select" required>
              @foreach (['manual', 'qr', 'rfid'] as $metode)
                <option value="{{ $metode }}"
                  {{ old('metode', $absensiGuru->metode ?? '') === $metode ? 'selected' : '' }}>
                  {{ strtoupper($metode) }}</option>
              @endforeach
            </select>
          </div>

          <div class="mb-3">
            <label class="form-label">Keterangan</label>
            <textarea name="keterangan" class="form-control" rows="3">{{ old('keterangan', $absensiGuru->keterangan ?? '') }}</textarea>
          </div>

          <button type="submit" class="btn btn-primary">Simpan</button>
        </form>
      </div>
    </div>
@endsection
