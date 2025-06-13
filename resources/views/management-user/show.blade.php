{{-- resources/views/management-user/show.blade.php --}}
@extends('admin.layouts.app')

@section('title', 'Detail User')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Detail User</h3>
                    <div class="card-tools">
                        <a href="{{ route('management-user.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                        <a href="{{ route('management-user.edit', $user->id_admin) }}" class="btn btn-warning">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 text-center">
                            <div class="profile-photo mb-3">
                                @if($user->foto_profil)
                                    <img src="{{ asset('storage/profile_photos/' . $user->foto_profil) }}" 
                                         alt="Foto Profil" 
                                         class="img-fluid rounded-circle border" 
                                         style="width: 200px; height: 200px; object-fit: cover;">
                                @else
                                    <div class="border rounded-circle d-inline-flex align-items-center justify-content-center text-muted"
                                         style="width: 200px; height: 200px; font-size: 4rem;">
                                        <i class="fas fa-user-circle"></i>
                                    </div>
                                @endif
                            </div>
                            <h4>{{ $user->nama_admin }}</h4>
                            <p class="text-muted">{{ $user->email }}</p>
                            <span class="badge badge-{{ $user->role == 'gudang' ? 'info' : 'warning' }} badge-lg">
                                {{ ucfirst($user->role) }}
                            </span>
                            <br><br>
                            <span class="badge badge-{{ ($user->status ?? 'aktif') == 'aktif' ? 'success' : 'danger' }} badge-lg">
                                {{ ucfirst($user->status ?? 'aktif') }}
                            </span>
                        </div>
                        <div class="col-md-8">
                            <div class="table-responsive">
                                <table class="table table-borderless">
                                    <tbody>
                                        <tr>
                                            <td width="200"><strong>ID Admin</strong></td>
                                            <td>:</td>
                                            <td>{{ $user->id_admin }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Nama Lengkap</strong></td>
                                            <td>:</td>
                                            <td>{{ $user->nama_admin }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Email</strong></td>
                                            <td>:</td>
                                            <td>{{ $user->email }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Username</strong></td>
                                            <td>:</td>
                                            <td>{{ $user->username }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>No. HP</strong></td>
                                            <td>:</td>
                                            <td>{{ $user->no_hp }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Role</strong></td>
                                            <td>:</td>
                                            <td>
                                                <span class="badge badge-{{ $user->role == 'gudang' ? 'info' : 'warning' }}">
                                                    {{ ucfirst($user->role) }}
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>Status</strong></td>
                                            <td>:</td>
                                            <td>
                                                <span class="badge badge-{{ ($user->status ?? 'aktif') == 'aktif' ? 'success' : 'danger' }}">
                                                    {{ ucfirst($user->status ?? 'aktif') }}
                                                </span>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            
                            <div class="mt-4">
                                <h5>Aksi</h5>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('management-user.edit', $user->id_admin) }}" 
                                       class="btn btn-warning">
                                        <i class="fas fa-edit"></i> Edit User
                                    </a>
                                    <button type="button" 
                                            class="btn btn-secondary" 
                                            onclick="resetPassword('{{ $user->id_admin }}')">
                                        <i class="fas fa-key"></i> Reset Password
                                    </button>
                                    <button type="button" 
                                            class="btn btn-{{ ($user->status ?? 'aktif') == 'aktif' ? 'danger' : 'success' }}" 
                                            onclick="toggleStatus('{{ $user->id_admin }}')">
                                        <i class="fas fa-{{ ($user->status ?? 'aktif') == 'aktif' ? 'times' : 'check' }}"></i> 
                                        {{ ($user->status ?? 'aktif') == 'aktif' ? 'Nonaktifkan' : 'Aktifkan' }}
                                    </button>
                                    <button type="button" 
                                            class="btn btn-danger" 
                                            onclick="deleteUser('{{ $user->id_admin }}')">
                                        <i class="fas fa-trash"></i> Hapus User
                                    </button>
                                </div>
                            </div>
                        </div>
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
                <h5 class="modal-title">Reset Password - {{ $user->nama_admin }}</h5>
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

@push('styles')
<style>
.badge-lg {
    font-size: 1rem;
    padding: 0.5rem 1rem;
}
</style>
@endpush

@push('scripts')
<script>
function toggleStatus(userId) {
    const currentStatus = '{{ $user->status ?? "aktif" }}';
    const newStatus = currentStatus === 'aktif' ? 'nonaktif' : 'aktif';
    
    if (confirm(`Apakah Anda yakin ingin ${newStatus === 'aktif' ? 'mengaktifkan' : 'menonaktifkan'} user ini?`)) {
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
    $('#resetPasswordModal').modal('show');
}

$('#resetPasswordForm').on('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    $.ajax({
        url: `/management-user/{{ $user->id_admin }}/reset-password`,
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