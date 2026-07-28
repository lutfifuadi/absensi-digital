@extends('layouts/layoutMaster')

@section('title', 'Detail Monitoring')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12 col-md-8 mx-auto">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold">Detail Monitoring</h5>
                    <a href="{{ route('admin.monitoring.index') }}" class="btn btn-sm btn-outline-secondary">Kembali</a>
                </div>
                <div class="card-body">
                     <!-- Nanti akan diisi data detail oleh Backend / Dika -->
                    <div class="text-center py-5 text-muted">
                        <i class="ti ti-file-description fs-1 mb-3"></i>
                        <p>Detail spesifik entri monitoring akan ditampilkan di sini.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection