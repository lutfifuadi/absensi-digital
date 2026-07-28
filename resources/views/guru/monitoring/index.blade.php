@extends('layouts/layoutMaster')

@section('title', 'Kehadiran Mengajar Saya')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-bold">Kehadiran Mengajar Saya</h5>
                </div>
                <div class="card-body">
                    <!-- Nanti akan diisi data tabel oleh Backend / Dika -->
                    <div class="text-center py-5 text-muted">
                        <i class="ti ti-user-check fs-1 mb-3"></i>
                        <p>Catatan kehadiran mengajar Anda akan ditampilkan di sini.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection