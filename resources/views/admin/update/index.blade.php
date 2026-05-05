@extends('layouts/layoutMaster')

@section('title', 'Pembaruan Sistem')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Informasi Pembaruan Sistem</h5>
                <span class="badge bg-label-primary">Versi Saat Ini: {{ $currentVersion }}</span>
            </div>
            <div class="card-body text-center py-5">
                @if($updateInfo)
                    <div class="mb-4">
                        <div class="avatar avatar-xl bg-label-warning mx-auto mb-3">
                            <i class="ti tabler-cloud-download fs-1"></i>
                        </div>
                        <h4 class="mb-1">Versi {{ $updateInfo['latest_version'] }} Tersedia!</h4>
                        <p class="text-muted">Pembaruan baru telah tersedia untuk sistem Anda.</p>
                    </div>

                    <div class="row justify-content-center">
                        <div class="col-md-8 text-start">
                            <div class="bg-label-secondary p-4 rounded mb-4">
                                <h6 class="fw-bold mb-2">Changelog:</h6>
                                <div class="changelog-content" style="white-space: pre-line;">
                                    {{ $updateInfo['changelog'] }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-2">
                        <button type="button" id="btn-run-update" class="btn btn-primary btn-lg">
                            <i class="ti tabler-download me-2"></i> Mulai Perbarui Sekarang
                        </button>
                        <p class="text-muted small mt-3">
                            <i class="ti tabler-alert-triangle text-warning me-1"></i>
                            Pastikan Anda telah melakukan backup database sebelum melanjutkan.
                        </p>
                    </div>
                @else
                    <div class="mb-4">
                        <div class="avatar avatar-xl bg-label-success mx-auto mb-3">
                            <i class="ti tabler-check fs-1"></i>
                        </div>
                        <h4 class="mb-1">Sistem Sudah Mutakhir</h4>
                        <p class="text-muted">Anda sedang menggunakan versi terbaru dari {{ config('app.name') }}.</p>
                        <small class="text-muted">Terakhir diperiksa: {{ $updateInfo['last_check'] ?? 'Belum pernah' }}</small>
                    </div>
                    <button type="button" id="btn-check-update" class="btn btn-outline-primary">
                        <i class="ti tabler-refresh me-2"></i> Periksa Pembaruan
                    </button>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Modal Progress Update --}}
<div class="modal fade" id="modalProgressUpdate" data-bs-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center py-5">
                <div class="spinner-border text-primary mb-4" role="status" style="width: 3rem; height: 3rem;">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <h4 class="mb-2">Sedang Memproses Pembaruan...</h4>
                <p class="text-muted">Mohon jangan tutup halaman ini atau mematikan server selama proses berlangsung.</p>
                <div class="progress mt-4" style="height: 10px;">
                    <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%" id="update-progress"></div>
                </div>
                <p class="mt-2 small" id="update-status">Menyiapkan lingkungan...</p>
            </div>
        </div>
    </div>
</div>
@endsection

@section('page-script')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const btnCheckUpdate = document.getElementById('btn-check-update');
    const btnRunUpdate = document.getElementById('btn-run-update');
    const modalProgress = new bootstrap.Modal(document.getElementById('modalProgressUpdate'));
    const progressBar = document.getElementById('update-progress');
    const statusText = document.getElementById('update-status');

    if (btnCheckUpdate) {
        btnCheckUpdate.addEventListener('click', function() {
            btnCheckUpdate.disabled = true;
            btnCheckUpdate.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status"></span> Memeriksa...';
            
            fetch("{{ route('admin.update.check') }}", {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        title: data.update_available ? 'Update Tersedia!' : 'Sudah Terbaru',
                        text: data.update_available ? 'Versi baru ditemukan.' : 'Sistem Anda sudah menggunakan versi terbaru.',
                        icon: data.update_available ? 'info' : 'success',
                        confirmButtonText: 'OK'
                    }).then(() => {
                        window.location.reload();
                    });
                } else {
                    throw new Error(data.message || 'Gagal memeriksa update');
                }
            })
            .catch(error => {
                Swal.fire('Error', error.message, 'error');
                btnCheckUpdate.disabled = false;
                btnCheckUpdate.innerHTML = '<i class="ti tabler-refresh me-2"></i> Periksa Pembaruan';
            });
        });
    }

    if (btnRunUpdate) {
        btnRunUpdate.addEventListener('click', function() {
            Swal.fire({
                title: 'Konfirmasi Pembaruan',
                text: "Sistem akan diperbarui ke versi terbaru. Proses ini mungkin memakan waktu beberapa saat.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#7367f0',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, Perbarui!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    startUpdateProcess();
                }
            });
        });
    }

    function startUpdateProcess() {
        modalProgress.show();
        
        let progress = 0;
        const interval = setInterval(() => {
            if (progress < 90) {
                progress += Math.random() * 10;
                updateProgress(Math.min(progress, 90));
            }
        }, 1000);

        function updateProgress(p) {
            progressBar.style.width = p + '%';
            if (p < 30) statusText.innerText = 'Mengunduh paket pembaruan...';
            else if (p < 60) statusText.innerText = 'Mengekstrak file...';
            else if (p < 80) statusText.innerText = 'Menjalankan migrasi database...';
            else statusText.innerText = 'Membersihkan cache sistem...';
        }

        fetch("{{ route('admin.update.run') }}", {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            clearInterval(interval);
            updateProgress(100);
            statusText.innerText = 'Pembaruan Selesai!';
            
            if (data.success) {
                setTimeout(() => {
                    Swal.fire({
                        title: 'Berhasil!',
                        text: data.message,
                        icon: 'success',
                        confirmButtonText: 'Selesai'
                    }).then(() => {
                        window.location.href = "{{ route('admin.update.index') }}";
                    });
                }, 1000);
            } else {
                throw new Error(data.message || 'Gagal menjalankan update');
            }
        })
        .catch(error => {
            clearInterval(interval);
            modalProgress.hide();
            Swal.fire('Gagal', error.message, 'error');
        });
    }
});
</script>
@endsection
