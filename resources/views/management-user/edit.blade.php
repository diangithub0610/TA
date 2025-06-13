{{-- resources/views/management-user/edit.blade.php --}}
@extends('admin.layouts.app')

@section('title', 'Edit User')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Edit User: {{ $user->nama_admin }}</h3>
                    <div class="card-tools">
                        <a href="{{ route('management-user.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                    </div>
                </div>
                <form action="{{ route('management-user.update', $user->id_admin) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="card-body">
                        <div class="row">
                                                        <div class="col-md-6">
                                <div class="form-group">
                                    <label for="nama_admin">Nama Lengkap <span class="text-danger">*</span></label>
                                    <input type="text" 
                                           class="form-control @error('nama_admin') is-invalid @enderror" 
                                           id="nama_admin" 
                                           name="nama_admin" 
                                           value="{{ old('nama_admin', $user->nama_admin) }}" 
                                           required>
                                    @error('nama_admin')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="email">Email <span class="text-danger">*</span></label>
                                    <input type="email" 
                                           class="form-control @error('email') is-invalid @enderror" 
                                           id="email" 
                                           name="email" 
                                           value="{{ old('email', $user->email) }}" 
                                           required>
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="username">Username <span class="text-danger">*</span></label>
                                    <input type="text" 
                                           class="form-control @error('username') is-invalid @enderror" 
                                           id="username" 
                                           name="username" 
                                           value="{{ old('username', $user->username) }}" 
                                           required>
                                    @error('username')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="no_hp">No. HP <span class="text-danger">*</span></label>
                                    <input type="text" 
                                           class="form-control @error('no_hp') is-invalid @enderror" 
                                           id="no_hp" 
                                           name="no_hp" 
                                           value="{{ old('no_hp', $user->no_hp) }}" 
                                           required>
                                    @error('no_hp')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="role">Role <span class="text-danger">*</span></label>
                                    <select class="form-control @error('role') is-invalid @enderror" 
                                            id="role" 
                                            name="role" 
                                            required>
                                        <option value="">Pilih Role</option>
                                        <option value="gudang" {{ old('role', $user->role) == 'gudang' ? 'selected' : '' }}>Gudang</option>
                                        <option value="shopkeeper" {{ old('role', $user->role) == 'shopkeeper' ? 'selected' : '' }}>Shopkeeper</option>
                                    </select>
                                    @error('role')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>ID Admin</label>
                                    <input type="text" class="form-control" value="{{ $user->id_admin }}" readonly>
                                    <small class="form-text text-muted">ID Admin tidak dapat diubah</small>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="foto_profil">Foto Profil</label>
                                    <input type="file" 
                                           class="form-control-file @error('foto_profil') is-invalid @enderror" 
                                           id="foto_profil" 
                                           name="foto_profil" 
                                           accept="image/*">
                                    <small class="form-text text-muted">
                                        Format: JPG, JPEG, PNG, GIF. Maksimal 2MB. Kosongkan jika tidak ingin mengubah foto.
                                    </small>
                                    @error('foto_profil')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Foto Saat Ini / Preview</label>
                                    <div class="text-center">
                                        @if($user->foto_profil)
                                            <img id="current-photo" 
                                                 src="{{ asset('storage/profile_photos/' . $user->foto_profil) }}" 
                                                 alt="Foto Profil" 
                                                 class="img-thumbnail" 
                                                 width="150">
                                        @else
                                            <div id="current-photo" class="border rounded p-4 text-muted">
                                                <i class="fas fa-user-circle fa-5x d-block mb-2"></i>
                                                No photo
                                            </div>
                                        @endif
                                        <img id="preview" src="#" alt="Preview" class="img-thumbnail d-none" width="150">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            <strong>Catatan:</strong> Password tidak dapat diubah melalui form ini. Gunakan fitur "Reset Password" di halaman daftar user untuk mengubah password.
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Update
                        </button>
                        <a href="{{ route('management-user.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Batal
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Preview foto baru
    $('#foto_profil').on('change', function() {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                $('#preview').attr('src', e.target.result).removeClass('d-none');
                $('#current-photo').addClass('d-none');
            }
            reader.readAsDataURL(file);
        } else {
            $('#preview').addClass('d-none');
            $('#current-photo').removeClass('d-none');
        }
    });
});
</script>
@endpush