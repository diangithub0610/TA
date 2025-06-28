@extends('admin.layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            {{-- {{ dd($pengguna) }} --}}
            <!-- Profile Header Card -->
            <div class="card shadow-sm mb-4">
                <div class="card-body text-center py-5">
                    <div class="mb-4">
                        @if($pengguna && $pengguna->foto_profil && file_exists(public_path('uploads/profile/' . $pengguna->foto_profil)))
                        <img src="{{ asset('uploads/profile/' . $pengguna->foto_profil) }}" 
                             alt="Profile Picture" 
                             class="rounded-circle border border-3 border-light shadow"
                             style="width: 120px; height: 120px; object-fit: cover;">
                    @else
                        <div class="rounded-circle bg-primary d-inline-flex align-items-center justify-content-center shadow"
                             style="width: 120px; height: 120px;">
                            <i class="fas fa-user text-white" style="font-size: 3rem;"></i>
                        </div>
                    @endif
                    
                    </div>
                    
                    <h3 class="fw-bold mb-2">{{ $pengguna->nama_admin }}</h3>
                    
                    <div class="mb-3">
                        @php
                            $roleClass = match($pengguna->role) {
                                'owner' => 'bg-danger',
                                'shopkeeper' => 'bg-success',
                                'gudang' => 'bg-warning text-dark',
                                default => 'bg-secondary'
                            };
                        @endphp
                        <span class="badge {{ $roleClass }} px-3 py-2 fs-6 rounded-pill">
                            <i class="fas fa-user-tag me-2"></i>
                            {{ ucfirst($pengguna->role) }}
                        </span>
                    </div>

                    <div class="mb-3">
                        @if($pengguna->status == 'aktif')
                            <span class="badge bg-success px-3 py-2 fs-6 rounded-pill">
                                <i class="fas fa-check-circle me-2"></i>Aktif
                            </span>
                        @else
                            <span class="badge bg-danger px-3 py-2 fs-6 rounded-pill">
                                <i class="fas fa-times-circle me-2"></i>Nonaktif
                            </span>
                        @endif
                    </div>

                    <p class="text-muted mb-0">ID Admin: <strong>{{ $pengguna->id_admin }}</strong></p>
                </div>
            </div>

            <!-- Profile Details Card -->
            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-info-circle me-2"></i>Informasi Detail
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <!-- Contact Information -->
                        <div class="col-md-6">
                            <div class="d-flex align-items-center mb-3">
                                <div class="flex-shrink-0">
                                    <div class="bg-primary bg-opacity-10 rounded-circle p-3">
                                        <i class="fas fa-envelope text-white"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h6 class="mb-1">Email</h6>
                                    <p class="mb-0 text-muted">
                                        {{ $pengguna->email ?: 'Tidak tersedia' }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="d-flex align-items-center mb-3">
                                <div class="flex-shrink-0">
                                    <div class="bg-success bg-opacity-10 rounded-circle p-3">
                                        <i class="fas fa-phone text-white"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h6 class="mb-1">No. HP</h6>
                                    <p class="mb-0 text-muted">
                                        {{ $pengguna->no_hp ?: 'Tidak tersedia' }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="d-flex align-items-center mb-3">
                                <div class="flex-shrink-0">
                                    <div class="bg-info bg-opacity-10 rounded-circle p-3">
                                        <i class="fas fa-user text-white"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h6 class="mb-1">Username</h6>
                                    <p class="mb-0 text-muted">
                                        {{ $pengguna->username ?: 'Tidak tersedia' }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="d-flex align-items-center mb-3">
                                <div class="flex-shrink-0">
                                    <div class="bg-warning bg-opacity-10 rounded-circle p-3">
                                        <i class="fas fa-briefcase text-white"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h6 class="mb-1">Peran</h6>
                                    <p class="mb-0 text-muted">
                                        @switch($pengguna->role)
                                            @case('owner')
                                                Pemilik
                                                @break
                                            @case('shopkeeper')
                                                Penjaga Toko
                                                @break
                                            @case('gudang')
                                                Staff Gudang
                                                @break
                                            @default
                                                {{ ucfirst($pengguna->role) }}
                                        @endswitch
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                                <button type="button" class="btn btn-primary px-4" data-bs-toggle="modal" data-bs-target="#editProfileModal">
                                    <i class="fas fa-edit me-2"></i>Edit Profil
                                </button>
                                <button type="button" class="btn btn-outline-secondary px-4" data-bs-toggle="modal" data-bs-target="#changePasswordModal">
                                    <i class="fas fa-key me-2"></i>Ubah Password
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Profile Modal -->
<div class="modal fade" id="editProfileModal" tabindex="-1" aria-labelledby="editProfileModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editProfileModalLabel">
                    <i class="fas fa-edit me-2"></i>Edit Profil
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12 text-center mb-4">
                            <div class="position-relative d-inline-block">
                                @if($pengguna->foto_profil && file_exists(public_path('uploads/profile/' . $pengguna->foto_profil)))
                                    <img src="{{ asset('uploads/profile/' . $pengguna->foto_profil) }}" 
                                         alt="Profile Picture" 
                                         class="rounded-circle border"
                                         style="width: 100px; height: 100px; object-fit: cover;"
                                         id="previewImage">
                                @else
                                    <div class="rounded-circle bg-primary d-inline-flex align-items-center justify-content-center"
                                         style="width: 100px; height: 100px;" id="previewPlaceholder">
                                        <i class="fas fa-user text-white" style="font-size: 2rem;"></i>
                                    </div>
                                @endif
                                <label for="foto_profil" class="position-absolute bottom-0 end-0 bg-primary text-white rounded-circle p-2 cursor-pointer border border-2 border-white" style="cursor: pointer;">
                                    <i class="fas fa-camera" style="font-size: 0.8rem;"></i>
                                </label>
                                <input type="file" id="foto_profil" name="foto_profil" class="d-none" accept="image/*">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label for="nama_admin" class="form-label">Nama Lengkap</label>
                            <input type="text" class="form-control" id="nama_admin" name="nama_admin" value="{{ $pengguna->nama_admin }}" required>
                        </div>

                        <div class="col-md-6">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="username" name="username" value="{{ $pengguna->username }}" required>
                        </div>

                        <div class="col-md-6">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" value="{{ $pengguna->email }}">
                        </div>

                        <div class="col-md-6">
                            <label for="no_hp" class="form-label">No. HP</label>
                            <input type="text" class="form-control" id="no_hp" name="no_hp" value="{{ $pengguna->no_hp }}">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Change Password Modal -->
<div class="modal fade" id="changePasswordModal" tabindex="-1" aria-labelledby="changePasswordModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="changePasswordModalLabel">
                    <i class="fas fa-key me-2"></i>Ubah Password
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('profile.update-password') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="kata_sandi_lama" class="form-label">Password Saat Ini</label>
                        <input type="password" class="form-control" id="kata_sandi_lama" name="kata_sandi_lama" required>
                    </div>
                    <div class="mb-3">
                        <label for="kata_sandi_baru" class="form-label">Password Baru</label>
                        <input type="password" class="form-control" id="kata_sandi_baru" name="kata_sandi_baru" required>
                    </div>
                    <div class="mb-3">
                        <label for="kata_sandi_baru_confirmation" class="form-label">Konfirmasi Password Baru</label>
                        <input type="password" class="form-control" id="kata_sandi_baru_confirmation" name="kata_sandi_baru_confirmation" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Ubah Password
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Image preview functionality
    const fotoProfilInput = document.getElementById('foto_profil');
    const previewImage = document.getElementById('previewImage');
    const previewPlaceholder = document.getElementById('previewPlaceholder');
    
    if (fotoProfilInput) {
        fotoProfilInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    if (previewImage) {
                        previewImage.src = e.target.result;
                    } else if (previewPlaceholder) {
                        previewPlaceholder.innerHTML = `<img src="${e.target.result}" class="rounded-circle" style="width: 100px; height: 100px; object-fit: cover;">`;
                    }
                };
                reader.readAsDataURL(file);
            }
        });
    }
});
</script>

@endsection