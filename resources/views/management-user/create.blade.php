{{-- resources/views/management-user/create.blade.php --}}
@extends('admin.layouts.app')

@section('title', 'Tambah User')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Tambah User Baru</h3>
                    <div class="card-tools">
                        <a href="{{ route('management-user.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                    </div>
                </div>
                <form action="{{ route('management-user.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="nama_admin">Nama Lengkap <span class="text-danger">*</span></label>
                                    <input type="text" 
                                           class="form-control @error('nama_admin') is-invalid @enderror" 
                                           id="nama_admin" 
                                           name="nama_admin" 
                                           value="{{ old('nama_admin') }}" 
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
                                           value="{{ old('email') }}" 
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
                                           value="{{ old('username') }}" 
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
                                           value="{{ old('no_hp') }}" 
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
                                        <option value="gudang" {{ old('role') == 'gudang' ? 'selected' : '' }}>Gudang</option>
                                        <option value="shopkeeper" {{ old('role') == 'shopkeeper' ? 'selected' : '' }}>Shopkeeper</option>
                                    </select>
                                    @error('role')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="kata_sandi">Password <span class="text-danger">*</span></label>
                                    <input type="password" 
                                           class="form-control @error('kata_sandi') is-invalid @enderror" 
                                           id="kata_sandi" 
                                           name="kata_sandi" 
                                           required>
                                    @error('kata_sandi')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
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
                                        Format: JPG, JPEG, PNG, GIF. Maksimal 2MB.
                                    </small>
                                    @error('foto_profil')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Preview Foto</label>
                                    <div class="text-center">
                                        <img id="preview" src="#" alt="Preview" class="img-thumbnail d-none" width="150">
                                        <div id="no-preview" class="border rounded p-4 text-muted">
                                            <i class="fas fa-image fa-3x d-block mb-2"></i>
                                            No image selected
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Simpan
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
    // Preview foto
    $('#foto_profil').on('change', function() {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                $('#preview').attr('src', e.target.result).removeClass('d-none');
                $('#no-preview').addClass('d-none');
            }
            reader.readAsDataURL(file);
        } else {
            $('#preview').addClass('d-none');
            $('#no-preview').removeClass('d-none');
        }
    });

    // Auto generate username dari email
    $('#email').on('input', function() {
        const email = $(this).val();
        if (email) {
            const username = email.split('@')[0];
            $('#username').val(username);
        }
    });
});
</script>
@endpush