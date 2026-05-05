@extends('layouts/layoutMaster')

@section('title', 'Dashboard')

@section('content')
    <div class="row mb-4">
      <div class="col-12">
        <div class="card">
          <div class="card-body">
            <h5 class="card-title">Selamat datang, {{ $user->name }}!</h5>
            <p class="card-text">Peran: <strong>{{ ucfirst(str_replace('_', ' ', $user->role)) }}</strong></p>
            <p class="card-text text-muted">Gunakan menu di sebelah kiri untuk mengakses fitur yang tersedia.</p>
          </div>
        </div>
      </div>
    </div>
@endsection
