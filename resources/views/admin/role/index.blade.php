@extends('layouts/layoutMaster')

@section('title', 'Manajemen Role')

@section('page-style')
  <style>
    .das-panel__actions {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 0.75rem;
      flex-wrap: wrap;
    }
    .das-table .badge-role-count {
      min-width: 3rem;
    }
  </style>
@endsection

@section('content')
  <div class="mb-4">
    <div class="d-flex align-items-center justify-content-between gap-3 flex-wrap">
      <div>
        <h4 class="mb-1 text-white">Manajemen Role</h4>
        <p class="text-muted mb-0">Kelola daftar role, lihat jumlah user per role, dan buka detail role dengan daftar user.</p>
      </div>
      <div class="d-flex gap-2 flex-wrap">
        <a href="{{ route('admin.role.create') }}" class="das-btn das-btn--primary">Tambah Role</a>
      </div>
    </div>
  </div>

  @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
  @endif
  @if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
  @endif
  @if($errors->any())
    <div class="alert alert-danger">
      <ul class="mb-0">
        @foreach($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <div class="das-panel">
    <div class="das-panel__head">
      <div class="das-panel__title"><span class="das-panel__icon-dot"></span> Daftar Role</div>
    </div>
    <div class="table-responsive">
      <table class="das-table">
        <thead>
          <tr>
            <th>#</th>
            <th>Nama Role</th>
            <th class="text-center">Jumlah User</th>
            <th class="text-end">Aksi</th>
          </tr>
        </thead>
        <tbody>
          @forelse($roles as $role)
            <tr>
              <td>{{ $loop->iteration }}</td>
              <td>{{ $role->name }}</td>
              <td class="text-center">
                <span class="badge bg-info badge-role-count">{{ $role->users_count }}</span>
              </td>
              <td class="text-end">
                <div class="d-flex justify-content-end gap-2 flex-wrap">
                  @php
                    $roleDetail = json_encode([
                      "name" => $role->name,
                      "slug" => $role->slug,
                      "description" => $role->description,
                    ]);
                  @endphp
                  <button type="button" class="das-btn das-btn--ghost btn-role-detail"
                    data-bs-toggle="modal"
                    data-bs-target="#roleDetailModal"
                    data-role='{{ $roleDetail }}'>
                    Detail
                  </button>

                  <a href="{{ route('admin.role.edit', $role) }}" class="das-btn das-btn--info">Edit</a>

                  <button type="button" class="das-btn das-btn--danger btn-role-delete"
                    data-bs-toggle="modal"
                    data-bs-target="#deleteRoleModal"
                    data-url="{{ route('admin.role.destroy', $role) }}"
                    data-name="{{ $role->name }}">
                    Hapus
                  </button>
                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="4" class="py-5 text-center">
                <div class="d-flex flex-column align-items-center gap-2 opacity-40">
                  <i class="ti tabler-folder-open" style="font-size:3rem;"></i>
                  <span>Belum ada role yang terdaftar.</span>
                </div>
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

  <div class="modal fade" id="roleDetailModal" tabindex="-1" aria-labelledby="roleDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
      <div class="modal-content bg-dark text-white border-0">
        <div class="modal-header border-bottom border-secondary">
          <h5 class="modal-title" id="roleDetailModalLabel">Detail Role</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="mb-4">
            <div class="fw-semibold">Role</div>
            <div id="detailRoleName" class="text-white"></div>
            <div class="text-muted small" id="detailRoleSlug"></div>
          </div>
          <div class="mb-4">
            <div class="fw-semibold">Deskripsi</div>
            <div id="detailRoleDescription" class="text-white"></div>
          </div>
          <div>
            <div class="fw-semibold mb-2">Daftar User</div>
            <div class="table-responsive">
              <table class="table table-borderless table-striped text-white-75">
                <thead>
                  <tr>
                    <th>Nama</th>
                    <th>Email</th>
                  </tr>
                </thead>
                <tbody id="detailRoleUsers">
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="deleteRoleModal" tabindex="-1" aria-labelledby="deleteRoleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content bg-dark text-white border-0">
        <div class="modal-header border-bottom border-secondary">
          <h5 class="modal-title" id="deleteRoleModalLabel">Hapus Role</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <p>Apakah Anda yakin ingin menghapus role <strong id="deleteRoleName"></strong>?</p>
          <p class="text-muted small">Role hanya dapat dihapus jika tidak ada user yang memilikinya.</p>
        </div>
        <div class="modal-footer border-top border-secondary">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
          <form id="deleteRoleForm" method="POST" action="">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger">Hapus</button>
          </form>
        </div>
      </div>
    </div>
  </div>
@endsection

@section('page-script')
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      const roleDetailModal = document.getElementById('roleDetailModal');
      const deleteRoleModal = document.getElementById('deleteRoleModal');
      const detailRoleName = document.getElementById('detailRoleName');
      const detailRoleSlug = document.getElementById('detailRoleSlug');
      const detailRoleDescription = document.getElementById('detailRoleDescription');
      const detailRoleUsers = document.getElementById('detailRoleUsers');
      const deleteRoleName = document.getElementById('deleteRoleName');
      const deleteRoleForm = document.getElementById('deleteRoleForm');

      document.querySelectorAll('.btn-role-detail').forEach(function (button) {
        button.addEventListener('click', function () {
          const role = JSON.parse(this.getAttribute('data-role'));
          detailRoleName.textContent = role.name;
          detailRoleSlug.textContent = role.slug;
          detailRoleDescription.textContent = role.description || 'Tidak ada deskripsi.';
          detailRoleUsers.innerHTML = '';

          if (Array.isArray(role.users) && role.users.length) {
            role.users.forEach(function (user) {
              const row = document.createElement('tr');
              row.innerHTML = `<td>${user.name}</td><td>${user.email}</td>`;
              detailRoleUsers.appendChild(row);
            });
          } else {
            detailRoleUsers.innerHTML = '<tr><td colspan="2" class="text-center text-muted">Belum ada user untuk role ini.</td></tr>';
          }
        });
      });

      document.querySelectorAll('.btn-role-delete').forEach(function (button) {
        button.addEventListener('click', function () {
          deleteRoleName.textContent = this.getAttribute('data-name');
          deleteRoleForm.action = this.getAttribute('data-url');
        });
      });
    });
  </script>
@endsection
