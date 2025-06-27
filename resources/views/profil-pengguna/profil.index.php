@extends('admin.layouts.app')

@section('title', 'Profil Pengguna')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <h4 class="page-title">Profil Pengguna</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Profil</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-4 col-xl-3">
            <!-- Profile Card -->
            <div class="card">
                <div class="card-body">
                    <div class="text-center">
                        <div class="position-relative mb-3">
                            @if($pengguna->foto_profil)
                                <img src="{{ asset('storage/profil/' . $pengguna->foto_profil) }}" 
                                     alt="Foto Profil" 
                                     class="rounded-circle img-thumbnail" 
                                     style="width: 150px; height: 150px; object-fit: cover;">
                            @else
                                <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center mx-auto" 
                                     style="width: 150px; height: 150px;">
                                    <i class="mdi mdi-account" style="font-size: 60px; color: white;"></i>
                                </div>
                            @endif
                        </div>
                        
                        <h4 class="mb-1">{{ $pengguna->nama_admin }}</h4>
                        <p class="text-muted mb-2">
                            <span class="badge badge-soft-{{ $pengguna->role == 'owner' ? 'danger' : ($pengguna->role == 'shopkeeper' ? 'success' : 'info') }}">
                                {{ ucfirst($pengguna->role) }}
                            </span>
                        </p>
                        <p class="text-muted">{{ $pengguna->email }}</p>
                        
                        <div class="mt-3">
                            <a href="{{ route('profile.edit') }}" class="btn btn-primary btn-sm me-2">
                                <i class="mdi mdi-pencil"></i> Edit Profil
                            </a>
                            @if($pengguna->foto_profil)
                                <form action="{{ route('profile.delete-foto') }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger btn-sm" 
                                            onclick="return confirm('Hapus foto profil?')">
                                        <i class="mdi mdi-delete"></i> Hapus Foto
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-8 col-xl-9">
            <!-- Profile Information -->
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h4 class="card-title">Informasi Profil</h4>
                        <a href="{{ route('profile.edit') }}" class="btn btn-outline-primary btn-sm">
                            <i class="mdi mdi-pencil"></i> Edit
                        </a>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Nama Lengkap</label>
                                <p class="form-control-plaintext">{{ $pengguna->nama_admin }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Username</label>
                                <p class="form-control-plaintext">{{ $pengguna->username }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Email</label>
                                <p class="form-control-plaintext">{{ $pengguna->email }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">No. HP</label>
                                <p class="form-control-plaintext">{{ $pengguna->no_hp }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Role</label>
                                <p class="form-control-plaintext">
                                    <span class="badge badge-soft-{{ $pengguna->role == 'owner' ? 'danger' : ($pengguna->role == 'shopkeeper' ? 'success' : 'info') }}">
                                        {{ ucfirst($pengguna->role) }}
                                    </span>
                                </p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Status</label>
                                <p class="form-control-plaintext">
                                    <span class="badge badge-soft-success">{{ ucfirst($pengguna->status) }}</span>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Change Password Card -->
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title mb-3">Ubah Password</h4>
                    
                    <form action="{{ route('profile.update-password') }}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label for="kata_sandi_lama" class="form-label">Password Lama</label>
                                    <input type="password" class="form-control @error('kata_sandi_lama') is-invalid @enderror" 
                                           id="kata_sandi_lama" name="kata_sandi_lama" required>
                                    @error('kata_sandi_lama')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="kata_sandi_baru" class="form-label">Password Baru</label>
                                    <input type="password" class="form-control @error('kata_sandi_baru') is-invalid @enderror" 
                                           id="kata_sandi_baru" name="kata_sandi_baru" required>
                                    @error('kata_sandi_baru')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="kata_sandi_baru_confirmation" class="form-label">Konfirmasi Password Baru</label>
                                    <input type="password" class="form-control" 
                                           id="kata_sandi_baru_confirmation" name="kata_sandi_baru_confirmation" required>
                                </div>
                            </div>
                        </div>

                        <div class="text-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="mdi mdi-content-save"></i> Ubah Password
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@if(session('success'))
    <div class="position-fixed top-0 end-0 p-3" style="z-index: 1050;">
        <div class="toast show" role="alert">
            <div class="toast-header">
                <i class="mdi mdi-check-circle text-success me-2"></i>
                <strong class="me-auto">Berhasil</strong>
                <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
            </div>
            <div class="toast-body">
                {{ session('success') }}
            </div>
        </div>
    </div>
@endif
@endsection