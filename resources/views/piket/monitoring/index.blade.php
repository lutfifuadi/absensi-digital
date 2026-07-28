@extends('layouts/layoutMaster')

@section('title', 'Monitoring Piket')

@section('content')
<div class="container-fluid py-4 max-w-lg mx-auto">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h5 class="mb-0 fw-bold">Monitoring Piket</h5>
            <small class="text-muted">{{ \Carbon\Carbon::now()->isoFormat('dddd, D MMMM YYYY') }}</small>
        </div>
    </div>
    
    @livewire('piket-monitoring-list')
    @livewire('monitoring-status-modal')
    @livewire('monitoring-form-tidak-hadir')
    @livewire('monitoring-form-terlambat')
</div>
@endsection