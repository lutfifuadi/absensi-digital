@extends('layouts/layoutMaster')

@section('title', 'Summary Monitoring')

@section('content')
<div class="container-fluid py-4 max-w-lg mx-auto">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h5 class="mb-0 fw-bold">Summary Monitoring</h5>
            <small class="text-muted">{{ \Carbon\Carbon::now()->isoFormat('dddd, D MMMM YYYY') }}</small>
        </div>
        <a href="{{ route('piket.monitoring.index') }}" class="btn btn-outline-primary btn-sm">
            Kembali
        </a>
    </div>

    <!-- Konten disederhanakan karena summary sudah masuk di sticky footer list utama. -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-4 text-center">
            <h1 class="display-1 text-success mb-3"><i class="ti ti-check"></i></h1>
            <h4>Selesai Memonitoring?</h4>
            <p class="text-muted">Pastikan semua kelas pada jam yang aktif sudah dicatat kehadirannya.</p>
        </div>
    </div>
</div>
@endsection