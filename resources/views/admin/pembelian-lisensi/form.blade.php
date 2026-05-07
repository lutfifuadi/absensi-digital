@extends('layouts/layoutMaster')

@section('title', $isEdit ? 'Edit Pembelian Lisensi' : 'Tambah Pembelian Lisensi')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">Admin / <a href="{{ route('admin.pembelian-lisensi.index') }}">Pembelian Lisensi</a> /</span>
        {{ $isEdit ? 'Edit' : 'Tambah' }}
    </h4>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        {{ $isEdit ? 'Edit Data Pembelian' : 'Tambah Pembelian Baru' }}
                    </h5>
                </div>
                <div class="card-body">
                    <form method="POST"
                          action="{{ $isEdit ? route('admin.pembelian-lisensi.update', $pembelian) : route('admin.pembelian-lisensi.store') }}">
                        @csrf
                        @if($isEdit) @method('PUT') @endif

                        <div class="mb-3">
                            <label class="form-label">Nama Klien / Sekolah <span class="text-danger">*</span></label>
                            <input type="text" name="nama_klien" class="form-control @error('nama_klien') is-invalid @enderror"
                                   value="{{ old('nama_klien', $pembelian->nama_klien) }}"
                                   placeholder="Contoh: SMA Negeri 1 Bandung" required>
                            @error('nama_klien')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Email Klien <span class="text-danger">*</span></label>
                            <input type="email" name="email_klien" class="form-control @error('email_klien') is-invalid @enderror"
                                   value="{{ old('email_klien', $pembelian->email_klien) }}"
                                   placeholder="email@sekolah.sch.id" required>
                            @error('email_klien')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            <div class="form-text">Email ini akan menerima license key dan link download.</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Domain (opsional)</label>
                            <input type="text" name="domain" class="form-control @error('domain') is-invalid @enderror"
                                   value="{{ old('domain', $pembelian->domain) }}"
                                   placeholder="absensi.sekolah.sch.id">
                            @error('domain')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            <div class="form-text">Domain yang akan digunakan klien untuk instalasi. Bisa dikosongkan dulu (akan otomatis terisi saat aktivasi pertama).</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Status Pembayaran <span class="text-danger">*</span></label>
                            <select name="payment_status" class="form-select @error('payment_status') is-invalid @enderror" required>
                                <option value="menunggu" {{ old('payment_status', $pembelian->payment_status) == 'menunggu' ? 'selected' : '' }}>Menunggu Pembayaran</option>
                                <option value="lunas"    {{ old('payment_status', $pembelian->payment_status) == 'lunas'    ? 'selected' : '' }}>Lunas (Generate & Kirim Lisensi)</option>
                            </select>
                            @error('payment_status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            <div class="form-text">Pilih "Lunas" untuk otomatis generate license key dan kirim email ke klien.</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Berlaku Hingga (kosongkan = seumur hidup)</label>
                            <input type="date" name="expires_at" class="form-control @error('expires_at') is-invalid @enderror"
                                   value="{{ old('expires_at', $pembelian->expires_at?->format('Y-m-d')) }}">
                            @error('expires_at')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Catatan Internal</label>
                            <textarea name="catatan" class="form-control @error('catatan') is-invalid @enderror"
                                      rows="3" placeholder="Catatan tambahan untuk admin...">{{ old('catatan', $pembelian->catatan) }}</textarea>
                            @error('catatan')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="ti ti-device-floppy me-1"></i>
                                {{ $isEdit ? 'Simpan Perubahan' : 'Simpan & Proses' }}
                            </button>
                            <a href="{{ route('admin.pembelian-lisensi.index') }}" class="btn btn-outline-secondary">
                                Batal
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-info">
                <div class="card-body">
                    <h6 class="text-info mb-3"><i class="ti ti-info-circle me-1"></i>Panduan</h6>
                    <ul class="small text-muted ps-3">
                        <li class="mb-2">Isi data klien dan email yang valid untuk menerima lisensi.</li>
                        <li class="mb-2">Jika pembayaran sudah <strong>Lunas</strong>, sistem akan otomatis:
                            <ul class="mt-1">
                                <li>Generate license key unik</li>
                                <li>Kirim email berisi license key + link download</li>
                            </ul>
                        </li>
                        <li class="mb-2">Domain bisa dikosongkan — akan otomatis terdaftar saat klien pertama kali aktivasi.</li>
                        <li>Jika email gagal terkirim, gunakan tombol <strong>"Kirim Ulang Email"</strong> di halaman detail.</li>
                    </ul>
                </div>
            </div>

            @if($isEdit && $pembelian->license_key)
            <div class="card mt-3 border-success">
                <div class="card-body">
                    <h6 class="text-success mb-2"><i class="ti ti-key me-1"></i>License Key</h6>
                    <code class="d-block p-2 bg-light rounded user-select-all">{{ $pembelian->license_key }}</code>
                    <div class="mt-2 small text-muted">Status: <span class="badge badge-status-{{ $pembelian->status }}">{{ ucfirst($pembelian->status) }}</span></div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
