@extends('layouts/layoutMaster')

@section('title', $role->exists ? 'Edit Role' : 'Tambah Role')

@section('content')
  <div class="mb-4">
    <div class="d-flex align-items-center justify-content-between gap-3 flex-wrap">
      <div>
        <h4 class="mb-1 text-white">{{ $role->exists ? 'Edit Role' : 'Tambah Role' }}</h4>
        <p class="text-muted mb-0">{{ $role->exists ? 'Perbarui informasi role.' : 'Tambahkan role baru ke dalam sistem.' }}</p>
      </div>
      <a href="{{ route('admin.role.index') }}" class="das-btn das-btn--ghost">Kembali ke Daftar Role</a>
    </div>
  </div>

  @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
  @endif
  @if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
  @endif
  @if($errors->any())
    <div class="alert alert-danger">
      <ul class="mb-0">
        @foreach($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <div class="das-panel">
    <div class="das-panel__head">
      <div class="das-panel__title"><span class="das-panel__icon-dot"></span> Form Role</div>
    </div>
    <div class="p-4">
      <form method="POST" action="{{ $role->exists ? route('admin.role.update', $role) : route('admin.role.store') }}">
        @csrf
        @if($role->exists)
          @method('PUT')
        @endif

        <div class="mb-3">
          <label class="form-label">Nama Role</label>
          <input type="text" name="name" class="form-control" value="{{ old('name', $role->name) }}" required maxlength="255">
        </div>

        <div class="mb-3">
          <label class="form-label">Deskripsi</label>
          <textarea name="description" rows="4" class="form-control">{{ old('description', $role->description) }}</textarea>
        </div>

        @if($role->exists)
          <div class="mb-3 text-muted small">
            <strong>Slug role:</strong> {{ $role->slug }}<br>
            Slug role tidak berubah agar relasi user tetap aman.
          </div>
        @endif

        <div class="d-flex gap-2 flex-wrap">
          <button type="submit" class="das-btn das-btn--primary">{{ $role->exists ? 'Simpan Perubahan' : 'Tambahkan Role' }}</button>
          <a href="{{ route('admin.role.index') }}" class="das-btn das-btn--ghost">Batal</a>
        </div>
      </form>
    </div>
  </div>
@endsection
