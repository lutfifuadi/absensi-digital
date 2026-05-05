@extends('layouts/layoutMaster')

@section('title', 'Manajemen Template ID Card')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Daftar Template Smart Card</h5>
        <a href="{{ route('admin.id-card-templates.create') }}" class="btn btn-primary">
            <i class="ti tabler-plus me-1"></i> Buat Template Baru
        </a>
    </div>
    <div class="table-responsive text-nowrap">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Nama Template</th>
                    <th>Tipe</th>
                    <th>Status</th>
                    <th>Terakhir Diupdate</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody class="table-border-bottom-0">
                @forelse($templates as $template)
                <tr>
                    <td><strong>{{ $template->name }}</strong></td>
                    <td>
                        <span class="badge bg-label-info">{{ ucfirst($template->type) }}</span>
                    </td>
                    <td>
                        @if($template->is_active)
                            <span class="badge bg-success">Aktif</span>
                        @else
                            <span class="badge bg-secondary">Draft</span>
                        @endif
                    </td>
                    <td>{{ $template->updated_at->diffForHumans() }}</td>
                    <td>
                        <div class="d-flex gap-2">
                            <a href="{{ route('admin.id-card-templates.edit', $template->id) }}" class="btn btn-icon btn-sm btn-outline-primary">
                                <i class="ti tabler-edit"></i>
                            </a>
                            <form action="{{ route('admin.id-card-templates.destroy', $template->id) }}" method="POST" onsubmit="return confirm('Hapus template ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-icon btn-sm btn-outline-danger">
                                    <i class="ti tabler-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center py-5 text-muted">Belum ada template. Silakan buat template baru.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer">
        {{ $templates->links() }}
    </div>
</div>
@endsection
