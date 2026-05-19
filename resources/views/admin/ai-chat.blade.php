@extends('layouts/layoutMaster')

@section('title', 'Asisten AI')

@section('content')

<div class="set-hero mb-5">
  <div class="set-hero__bg"></div>
  <div class="set-hero__glass"></div>
  <div class="set-hero__grid"></div>
  <div class="set-hero__inner">
    <div class="set-hero__identity">
      <div class="set-hero__icon-wrap">
        <i class="ti tabler-message-chatbot"></i>
        <div class="set-hero__icon-glow"></div>
      </div>
      <div>
        <div class="set-hero__badge">
          <span class="pulse-dot"></span>
          Kecerdasan Buatan
        </div>
        <h4 class="set-hero__title text-gradient-gold">Asisten AI</h4>
        <p class="set-hero__sub">Tanya atau edit data dengan percakapan natural menggunakan Google Gemini AI.</p>
      </div>
    </div>
    <div class="set-hero__breadcrumb glass-card">
      <span class="text-muted small"><i class="ti tabler-home me-1"></i>Dashboard</span>
      <i class="ti tabler-chevron-right text-muted mx-1" style="font-size:0.7rem;"></i>
      <span class="small text-white fw-semibold">Asisten AI</span>
    </div>
  </div>
</div>

<div class="row">
    <div class="col-12">
        @livewire('admin.ai-chat')
    </div>
</div>

<div class="row mt-3">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">Contoh Perintah</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="border rounded p-3 h-100">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <i class="ti tabler-users text-primary" style="font-size: 1.2rem;"></i>
                                <strong>Data Siswa</strong>
                            </div>
                            <ul class="list-unstyled mb-0 small">
                                <li class="mb-1">"Cari siswa bernama Andi"</li>
                                <li class="mb-1">"Tampilkan data siswa NISN 0102039759"</li>
                                <li class="mb-1">"Edit alamat siswa ID 5 menjadi Jl. Merdeka No. 10"</li>
                                <li class="mb-0">"Ubah status siswa ID 3 menjadi nonaktif"</li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="border rounded p-3 h-100">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <i class="ti tabler-school text-success" style="font-size: 1.2rem;"></i>
                                <strong>Data Guru & Kelas</strong>
                            </div>
                            <ul class="list-unstyled mb-0 small">
                                <li class="mb-1">"Cari guru dengan NIP 197002021993011004"</li>
                                <li class="mb-1">"Tampilkan semua data guru"</li>
                                <li class="mb-1">"Edit jabatan guru ID 2 menjadi Kepala Sekolah"</li>
                                <li class="mb-0">"Cari kelas X IPA 1"</li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="border rounded p-3 h-100">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <i class="ti tabler-chart-bar text-warning" style="font-size: 1.2rem;"></i>
                                <strong>Statistik</strong>
                            </div>
                            <ul class="list-unstyled mb-0 small">
                                <li class="mb-1">"Berapa total siswa?"</li>
                                <li class="mb-1">"Tampilkan statistik data"</li>
                                <li class="mb-1">"Total guru dan kelas"</li>
                                <li class="mb-0">"Berapa jumlah user?"</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
