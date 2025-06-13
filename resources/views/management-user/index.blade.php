{{-- resources/views/management-user/index.blade.php --}}
@extends('admin.layouts.app')

@section('title', 'Management User')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h3 class="card-title">Management User</h3>
                        <a href="{{ route('management-user.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Tambah User
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="close" data-dismiss="alert">
                                <span>&times;</span>
                            </button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="close" data-dismiss="alert">
                                <span>&times;</span>
                            </button>
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="userTable">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>ID Admin</th>
                                    <th>Foto</th>
                                    <th>Nama</th>
                                    <th>Email</th>
                                    <th>Username</th>
                                    <th>Role</th>
                                    <th>No HP</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($users as $index => $user)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $user->id_admin }}</td>
                                    <td class="text-center">
                                        @if($user->foto_profil)
                                            <img src="{{ asset('storage/profile_photos/' . $user->foto_profil) }}" 
                                                 alt="Foto Profil" class="img-thumbnail" width="50" height="50">
                                        @else
                                            <i class="fas fa-user-circle fa-2x text-secondary"></i>
                                        @endif
                                    </td>
                                    <td>{{ $user->nama_admin }}</td>
                                    <td>{{ $user->email }}</td>
                                    <td>{{ $user->username }}</td>
                                    <td>
                                        <span class="badge badge-{{ $user->role == 'gudang' ? 'info' : 'warning' }}">
                                            {{ ucfirst($user->role) }}
                                        </span>
                                    </td>
                                    <td>{{ $user->no_hp }}</td>
                                    <td>
                                        <span class="badge badge-{{ ($user->status ?? 'aktif') == 'aktif' ? 'success' : 'danger' }}">
                                            {{ ucfirst($user->status ?? 'aktif') }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('management-user.show', $user->id_admin) }}" 
                                               class="btn btn-info btn-sm" title="Detail">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('management-user.edit', $user->id_admin) }}" 
                                               class="btn btn-warning btn-sm" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button type="button" class="btn btn-secondary btn-sm" 
                                                    onclick="resetPassword('{{ $user->id_admin }}')" title="Reset Password">
                                                <i class="fas fa-key"></i>
                                            </button>
                                            <button type="button" 
                                                    class="btn btn-{{ ($user->status ?? 'aktif') == 'aktif' ? 'danger' : 'success' }} btn-sm" 
                                                    onclick="toggleStatus('{{ $user->id_admin }}')" 
                                                    title="{{ ($user->status ?? 'aktif') == 'aktif' ? 'Nonaktifkan' : 'Aktifkan' }}">
                                                <i class="fas fa-{{ ($user->status ?? 'aktif') == 'aktif' ? 'times' : 'check' }}"></i>
                                            </button>
                                            <button type="button" class="btn btn-danger btn-sm" 
                                                    onclick="deleteUser('{{ $user->id_admin }}')" title="Hapus">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="10" class="text-center">Tidak ada data user</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Reset Password -->
<div class="modal fade" id="resetPasswordModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Reset Password</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="resetPasswordForm">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="new_password">Password Baru</label>
                        <input type="password" class="form-control" id="new_password" name="new_password" required>
                    </div>
                    <div class="form-group">
                        <label for="new_password_confirmation">Konfirmasi Password</label>
                        <input type="password" class="form-control" id="new_password_confirmation" name="new_password_confirmation" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Reset Password</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Form Delete (Hidden) -->
<form id="deleteForm" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    $('#userTable').DataTable({
        responsive: true,
        language: {
            url: '//cdn.datatables.net/plug-ins/1.10.25/i18n/Indonesian.json'
        }
    });
});

let currentUserId = null;

function toggleStatus(userId) {
    if (confirm('Apakah Anda yakin ingin mengubah status user ini?')) {
        $.ajax({
            url: `/management-user/${userId}/toggle-status`,
            type: 'PATCH',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    location.reload();
                }
            },
            error: function(xhr) {
                alert('Terjadi kesalahan saat mengubah status');
            }
        });
    }
}

function resetPassword(userId) {
    currentUserId = userId;
    $('#resetPasswordModal').modal('show');
}

$('#resetPasswordForm').on('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    $.ajax({
        url: `/management-user/${currentUserId}/reset-password`,
        type: 'PATCH',
        data: formData,
        processData: false,
        contentType: false,
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            if (response.success) {
                $('#resetPasswordModal').modal('hide');
                $('#resetPasswordForm')[0].reset();
                alert('Password berhasil direset');
            }
        },
        error: function(xhr) {
            const errors = xhr.responseJSON.errors;
            let errorMessage = '';
            for (let field in errors) {
                errorMessage += errors[field].join('\n') + '\n';
            }
            alert(errorMessage || 'Terjadi kesalahan saat reset password');
        }
    });
});

function deleteUser(userId) {
    if (confirm('Apakah Anda yakin ingin menghapus user ini? Data yang sudah dihapus tidak dapat dikembalikan.')) {
        const form = document.getElementById('deleteForm');
        form.action = `/management-user/${userId}`;
        form.submit();
    }
}
</script>
@endpush