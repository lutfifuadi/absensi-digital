@extends('layouts/layoutMaster')

@section('title', 'Detail Orang Tua')

@section('content')

    {{-- HERO HEADER --}}
    <div class="das-hero mb-4">
        <div class="das-hero__bg"></div>
        <div class="das-hero__glass"></div>
        <div class="das-hero__grid-lines"></div>

        <div class="das-hero__inner">
            <div class="das-hero__identity">
                <div class="das-hero__logo-wrapper">
                    <div class="das-hero__logo-placeholder">
                        <i class="ti tabler-eye text-info"></i>
                    </div>
                    <div class="das-hero__logo-glow"></div>
                </div>

                <div class="das-hero__meta">
                    <div class="das-hero__badge">
                        <span class="pulse-dot"></span>
                        <a href="{{ route('admin.master-data') }}" class="text-white text-decoration-none">Master Data</a> / <a href="{{ route('admin.orang-tua.index') }}" class="text-white text-decoration-none">Orang Tua</a> / Detail
                    </div>
                    <h4 class="das-hero__title text-gradient-gold">Detail Data Orang Tua</h4>
                    <p class="das-hero__subtitle">Lihat informasi detail profil orang tua / wali murid beserta siswa yang dihubungkan.</p>
                </div>
            </div>

            <div class="das-hero__actions">
                <a href="{{ route('admin.orang-tua.edit', $orangTua) }}" class="btn das-btn --warning">
                    <i class="ti tabler-pencil me-1"></i> Ubah
                </a>
                <a href="{{ route('admin.orang-tua.index') }}" class="btn das-btn --secondary">
                    Kembali
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        {{-- Detail Informasi --}}
        <div class="col-md-6 mb-4">
            <div class="das-panel h-100">
                <div class="das-panel__header border-bottom py-3 px-4 d-flex align-items-center gap-2"
                    style="border-color:rgba(255,255,255,0.08) !important;background:transparent;">
                    <i class="ti tabler-user text-info"></i>
                    <h6 class="das-panel__title mb-0 text-white">Profil Orang Tua</h6>
                </div>
                <div class="das-panel__body p-4">
                    <table class="table table-borderless text-white mb-0">
                        <tbody>
                            <tr>
                                <td class="fw-semibold px-0 py-2 small text-white-50" style="width: 35%;">Nama Lengkap</td>
                                <td class="px-0 py-2 small fw-bold text-white">{{ $orangTua->name }}</td>
                            </tr>
                            <tr>
                                <td class="fw-semibold px-0 py-2 small text-white-50">Hubungan</td>
                                <td class="px-0 py-2 small">
                                    <span class="badge bg-label-info">{{ $orangTua->hubungan ?? 'Orang Tua' }}</span>
                                </td>
                            </tr>
                            <tr>
                                <td class="fw-semibold px-0 py-2 small text-white-50">No. WhatsApp / HP</td>
                                <td class="px-0 py-2 small text-white">{{ $orangTua->no_hp ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="fw-semibold px-0 py-2 small text-white-50">Status Akun</td>
                                <td class="px-0 py-2 small">
                                    <span class="badge bg-label-{{ $orangTua->status === 'aktif' ? 'success' : 'danger' }}">
                                        {{ ucfirst($orangTua->status) }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td class="fw-semibold px-0 py-2 small text-white-50">Username</td>
                                <td class="px-0 py-2 small text-white">{{ $orangTua->username }}</td>
                            </tr>
                            <tr>
                                <td class="fw-semibold px-0 py-2 small text-white-50">Email</td>
                                <td class="px-0 py-2 small text-white">{{ $orangTua->email }}</td>
                            </tr>
                            <tr>
                                <td class="fw-semibold px-0 py-2 small text-white-50">Dibuat Pada</td>
                                <td class="px-0 py-2 small text-white">{{ $orangTua->created_at ? $orangTua->created_at->translatedFormat('d F Y H:i') : '-' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Hubungan Siswa --}}
        <div class="col-md-6 mb-4">
            <div class="das-panel h-100">
                <div class="das-panel__header border-bottom py-3 px-4 d-flex align-items-center gap-2"
                    style="border-color:rgba(255,255,255,0.08) !important;background:transparent;">
                    <i class="ti tabler-users-group text-info"></i>
                    <h6 class="das-panel__title mb-0 text-white">Anak / Siswa yang Dihubungkan</h6>
                </div>
                <div class="das-panel__body p-4">
                    @if($orangTua->children->count() > 0)
                        <div class="list-group list-group-flush">
                            @foreach($orangTua->children as $child)
                                <div class="list-group-item bg-transparent border-white-10 px-0 py-3 d-flex align-items-center justify-content-between">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="avatar avatar-md">
                                            <span class="avatar-initial rounded-circle bg-label-info" style="font-size:0.85rem;">
                                                {{ strtoupper(substr($child->nama_lengkap, 0, 1)) }}{{ strtoupper(substr(strrchr($child->nama_lengkap, ' ') ?: $child->nama_lengkap, 1, 1)) }}
                                            </span>
                                        </div>
                                        <div>
                                            <h6 class="mb-0 text-white fw-bold">{{ $child->nama_lengkap }}</h6>
                                            <p class="mb-0 text-white-50 small" style="font-size:0.75rem;">NISN: {{ $child->nisn }} | NIS: {{ $child->nis ?? '-' }}</p>
                                        </div>
                                    </div>
                                    <div>
                                        <span class="badge bg-label-primary">{{ optional($child->kelas)->nama ?? '-' }}</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="d-flex flex-column align-items-center justify-content-center h-100 py-4 opacity-50 text-white">
                            <i class="ti tabler-users-minus" style="font-size:2.5rem;"></i>
                            <span class="small mt-2">Belum ada siswa yang dihubungkan ke akun Orang Tua ini.</span>
                            <a href="{{ route('admin.orang-tua.edit', $orangTua) }}" class="btn btn-sm btn-label-primary mt-2">
                                Hubungkan Sekarang
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

@endsection
