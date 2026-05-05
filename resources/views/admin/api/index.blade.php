@extends('layouts/layoutMaster')

@section('title', 'Integrasi API')

@section('page-style')
  <style>
    .glass-card {
      background: rgba(255, 255, 255, 0.04) !important;
      border: 1px solid rgba(255, 255, 255, 0.08) !important;
      transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .glass-card:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2) !important;
    }

    .form-control,
    .form-select {
      background: rgba(255, 255, 255, 0.05) !important;
      border: 1px solid rgba(255, 255, 255, 0.1) !important;
      color: #fff !important;
      border-radius: 8px !important;
    }

    .form-control:focus {
      background: rgba(255, 255, 255, 0.08) !important;
      border-color: var(--bs-primary) !important;
      box-shadow: 0 0 0 0.25rem rgba(var(--bs-primary-rgb), 0.15) !important;
    }

    .action-btn {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      width: 32px;
      height: 32px;
      border-radius: 8px;
      transition: all 0.2s ease;
      background: rgba(255, 255, 255, 0.05);
      color: inherit;
    }

    .action-btn:hover {
      transform: translateY(-2px);
      background: rgba(255, 255, 255, 0.1);
    }
  </style>
@endsection

@section('content')

  {{-- HERO HEADER --}}
  <div class="row mb-4">
    <div class="col-12">
      <div class="card border-0 text-white overflow-hidden shadow-lg"
        style="background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%); border-radius: 12px;">
        <div class="card-body p-4">
          <div class="row align-items-center">
            <div class="col-md-7">
              <div class="d-flex align-items-center gap-3">
                <div class="rounded d-flex align-items-center justify-content-center shadow-sm"
                  style="width:52px;height:52px;border-radius:12px !important;background:rgba(0,207,232,0.2);border:1px solid rgba(0,207,232,0.4);">
                  <i class="ti tabler-api text-info fs-3"></i>
                </div>
                <div>
                  <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-1" style="font-size:0.72rem;opacity:0.6;">
                      <li class="breadcrumb-item"><span class="text-white opacity-50">Sistem</span></li>
                      <li class="breadcrumb-item active text-white">Integrasi API</li>
                    </ol>
                  </nav>
                  <h4 class="mb-0 text-white fw-bold" style="letter-spacing:-0.5px;">Integrasi API</h4>
                  <p class="mb-0 text-white opacity-60 small">Hubungkan sistem dengan aplikasi eksternal melalui REST API (mode Push).</p>
                </div>
              </div>
            </div>
            <div class="col-md-5 text-md-end mt-3 mt-md-0">
               <div class="badge bg-black bg-opacity-25 p-2 px-3 border border-white border-opacity-10 text-white">
                  Header Auth: <code class="text-info px-1">Bearer {token}</code>
               </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="row">
    {{-- CREATE TOKEN --}}
    <div class="col-md-5 mb-4">
      <div class="card glass-card h-100">
        <div class="card-header border-bottom py-3" style="background:transparent; border-color:rgba(255,255,255,0.08) !important;">
          <h6 class="card-title mb-0 d-flex align-items-center gap-2 text-white">
            <i class="ti tabler-key text-info"></i> Buat Token Baru
          </h6>
        </div>
        <div class="card-body py-4">
          <form action="{{ route('admin.api-integration.store') }}" method="POST">
            @csrf
            <div class="mb-4">
              <label for="token_name" class="form-label text-white-50 small fw-bold">Nama Aplikasi / Keterangan</label>
              <input type="text" class="form-control @error('token_name') is-invalid @enderror" id="token_name"
                name="token_name" placeholder="Contoh: Portal Siswa V2" required>
              @error('token_name')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
            <button type="submit" class="btn btn-info w-100 fw-bold shadow-sm">
              <i class="ti tabler-bolt me-2"></i> Generate API Token
            </button>
          </form>

          @if (session('success_token'))
            <div class="alert alert-warning mt-4 border-0 shadow-sm" role="alert" style="background: rgba(255,159,67,0.1); border-radius:10px;">
              <h6 class="alert-heading fw-bold mb-1 d-flex align-items-center gap-2 text-warning">
                <i class="ti tabler-alert-triangle fs-4"></i> SIMPAN TOKEN INI!
              </h6>
              <p class="mb-3 text-white-50" style="font-size: 0.8rem">Token ini hanya akan ditampilkan sekali demi alasan keamanan.</p>
              <div class="p-3 rounded text-break user-select-all bg-dark shadow-inset" style="font-family: 'Fira Code', monospace; color: #fff; border:1px solid rgba(255,255,255,0.1);">
                {{ session('success_token') }}
              </div>
            </div>
          @endif

          @if (session('success'))
            <div class="alert alert-info mt-4 border-0" style="background: rgba(0,207,232,0.1); border-radius:8px;">
              <i class="ti tabler-check me-1"></i> {{ session('success') }}
            </div>
          @endif
        </div>
      </div>
    </div>

    {{-- TOKEN LIST --}}
    <div class="col-md-7 mb-4">
      <div class="card glass-card h-100">
        <div class="card-header border-bottom py-3 d-flex align-items-center justify-content-between" style="background:transparent; border-color:rgba(255,255,255,0.08) !important;">
          <h6 class="card-title mb-0 d-flex align-items-center gap-2 text-white">
            <i class="ti tabler-shield-lock text-info"></i> Token Aktif
          </h6>
          <span class="badge bg-label-info">{{ count($tokens) }} Aktif</span>
        </div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="color:inherit;">
              <thead style="background:rgba(255,255,255,0.04); font-size:0.7rem; text-transform:uppercase; letter-spacing:0.8px; opacity:0.7;">
                <tr>
                  <th class="ps-4">Nama Token</th>
                  <th>Dibuat Oleh</th>
                  <th class="text-center">Terakhir Digunakan</th>
                  <th class="pe-4 text-end">Aksi</th>
                </tr>
              </thead>
              <tbody>
                @forelse($tokens as $token)
                  <tr>
                    <td class="ps-4">
                      <div class="fw-bold text-white">{{ $token->name }}</div>
                    </td>
                    <td>
                      <div class="small text-white-50">{{ $token->tokenable ? $token->tokenable->name : 'System' }}</div>
                    </td>
                    <td class="text-center">
                      @if ($token->last_used_at)
                        <span class="text-info small fw-medium">{{ \Carbon\Carbon::parse($token->last_used_at)->diffForHumans() }}</span>
                      @else
                        <span class="text-white-50 small">Belum dipakai</span>
                      @endif
                    </td>
                    <td class="pe-4 text-end">
                      <form action="{{ route('admin.api-integration.destroy', $token->id) }}" method="POST"
                        onsubmit="return confirm('Apakah Anda yakin ingin mencabut token ini? Aplikasi yang menggunakannya akan terputus.');">
                        @csrf
                        @method('DELETE')
                        <button class="action-btn text-danger" type="submit" title="Revoke Token">
                          <i class="ti tabler-trash fs-5"></i>
                        </button>
                      </form>
                    </td>
                  </tr>
                @empty
                  <tr>
                    <td colspan="4" class="text-center py-5 text-white-50 opacity-50">
                      <i class="ti tabler-cloud-off fs-1 d-block mb-2"></i>
                      Belum ada token API aktif.
                    </td>
                  </tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- ENDPOINTS --}}
  <div class="row">
    <div class="col-12">
      <div class="card glass-card">
        <div class="card-header border-bottom py-3" style="background:transparent; border-color:rgba(255,255,255,0.08) !important;">
          <h6 class="card-title mb-0 d-flex align-items-center gap-2 text-white">
            <i class="ti tabler-cloud-cog text-info"></i> Endpoint Tersedia
          </h6>
        </div>
        <div class="card-body">
          <p class="text-white-50 small mb-4">Aplikasi eksternal harus mengirimkan request <span class="badge bg-label-success">POST</span> (JSON) ke endpoint berikut untuk melakukan sinkronisasi data.</p>
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="color:inherit; border:1px solid rgba(255,255,255,0.08);">
              <thead style="background:rgba(255,255,255,0.04); font-size:0.7rem; text-transform:uppercase; letter-spacing:0.8px;">
                <tr>
                  <th class="ps-3 py-3">Entitas</th>
                  <th class="text-center py-3">Metode</th>
                  <th class="py-3">Endpoint URL</th>
                  <th class="py-3">Payload Minimal (Contoh)</th>
                </tr>
              </thead>
              <tbody>
                @php
                  $endpoints = [
                    ['icon'=>'tabler-users', 'name' => 'Siswa', 'url' => '/api/v1/sync/siswa', 'payload' => 'nisn, email, nama_lengkap'],
                    ['icon'=>'tabler-chalkboard-teacher', 'name' => 'Guru', 'url' => '/api/v1/sync/guru', 'payload' => 'nip, email, nama_lengkap'],
                    ['icon'=>'tabler-briefcase', 'name' => 'Staff TU', 'url' => '/api/v1/sync/staff', 'payload' => 'nip, email, nama_lengkap'],
                    ['icon'=>'tabler-door', 'name' => 'Kelas', 'url' => '/api/v1/sync/kelas', 'payload' => 'nama, tingkat, wali_kelas_nip'],
                    ['icon'=>'tabler-calendar-stats', 'name' => 'Tahun Akademik', 'url' => '/api/v1/sync/tahun-akademik', 'payload' => 'nama, semester'],
                  ];
                @endphp
                @foreach($endpoints as $ep)
                <tr>
                  <td class="ps-3">
                    <div class="d-flex align-items-center gap-2">
                       <i class="ti {{ $ep['icon'] }} text-info"></i>
                       <span class="fw-bold text-white">{{ $ep['name'] }}</span>
                    </div>
                  </td>
                  <td class="text-center"><span class="badge bg-label-success px-2">POST</span></td>
                  <td><code class="text-info font-monospace">{{ url($ep['url']) }}</code></td>
                  <td><small class="text-white-50">{{ $ep['payload'] }}</small></td>
                </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection
