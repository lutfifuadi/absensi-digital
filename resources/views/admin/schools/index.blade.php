@extends('layouts/layoutMaster')

@section('title', 'Manajemen Sekolah — SaaS')

@section('content')
<div class="das-hero mb-4">
  <div class="das-hero__bg"></div>
  <div class="das-hero__glass"></div>
  <div class="das-hero__grid-lines"></div>

  <div class="das-hero__inner">
    <div class="das-hero__identity">
      <div class="das-hero__logo-wrapper">
        <div class="das-hero__logo-placeholder">
          <i class="ti tabler-building-community"></i>
        </div>
        <div class="das-hero__logo-glow"></div>
      </div>
      <div class="das-hero__meta">
        <div class="das-hero__badge">
          <span class="pulse-dot"></span>
          Multi-Tenant System
        </div>
        <h4 class="das-hero__title text-gradient-gold">Manajemen Sekolah</h4>
        <p class="das-hero__subtitle">Kelola seluruh tenant dan instansi sekolah dalam satu dashboard.</p>
      </div>
    </div>

    <div class="das-hero__actions">
      <a href="{{ route('admin.schools.create') }}" class="das-btn das-btn--primary shadow-lg">
        <i class="ti tabler-plus"></i> Tambah Sekolah Baru
      </a>
    </div>
  </div>
</div>

@if(session('success'))
  <div class="alert alert-success d-flex align-items-center bg-transparent border-success text-success mb-4" role="alert">
    <span class="alert-icon me-2">
      <i class="ti ti-check ti-xs"></i>
    </span>
    {{ session('success') }}
  </div>
@endif

<div class="das-panel">
  <div class="das-panel__head">
    <div class="das-panel__title">
      <span class="das-panel__icon-dot --primary"></span>
      Daftar Tenant Sekolah
    </div>
    <div class="d-flex align-items-center gap-2">
      <span class="das-chip --info">{{ $schools->count() }} Total Sekolah</span>
    </div>
  </div>
  <div class="table-responsive">
    <table class="das-table">
      <thead>
        <tr>
          <th>NAMA SEKOLAH</th>
          <th>KODE UNIK</th>
          <th>SUBDOMAIN</th>
          <th>STATUS</th>
          <th class="text-center">AKSI</th>
        </tr>
      </thead>
      <tbody>
        @forelse($schools as $school)
        <tr>
          <td>
            <div class="d-flex align-items-center gap-3">
              <div class="avatar avatar-sm">
                <span class="avatar-initial rounded bg-label-primary">{{ substr($school->name, 0, 1) }}</span>
              </div>
              <div class="d-flex flex-column">
                <span class="fw-bold text-white">{{ $school->name }}</span>
                <small class="text-muted">{{ $school->email ?? 'No email' }}</small>
              </div>
            </div>
          </td>
          <td>
            <span class="badge bg-label-secondary font-monospace">{{ $school->unique_code }}</span>
          </td>
          <td>
            <a href="http://{{ $school->subdomain }}.{{ explode('.', request()->getHost(), 2)[1] ?? request()->getHost() }}" target="_blank" class="text-info fw-semibold">
              <i class="ti tabler-external-link me-1"></i>{{ $school->subdomain }}
            </a>
          </td>
          <td>
            @if($school->status === 'active')
              <span class="das-chip --success">Aktif</span>
            @else
              <span class="das-chip --danger">Nonaktif</span>
            @endif
          </td>
          <td>
            <div class="d-flex justify-content-center align-items-center gap-2">
              <a href="{{ route('admin.schools.edit', $school->id) }}" class="das-btn das-btn--ghost-sm text-info" title="Edit">
                <i class="ti tabler-edit fs-5"></i>
              </a>
              <form action="{{ route('admin.schools.toggle-status', $school->id) }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="das-btn das-btn--ghost-sm {{ $school->status === 'active' ? 'text-warning' : 'text-success' }}" title="{{ $school->status === 'active' ? 'Nonaktifkan' : 'Aktifkan' }}">
                  <i class="ti {{ $school->status === 'active' ? 'tabler-player-pause' : 'tabler-player-play' }} fs-5"></i>
                </button>
              </form>
            </div>
          </td>
        </tr>
        @empty
        <tr>
          <td colspan="5" class="text-center py-5 text-muted">
            <i class="ti tabler-database-off fs-1 d-block mb-2"></i>
            Belum ada data sekolah.
          </td>
        </tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>
@endsection
