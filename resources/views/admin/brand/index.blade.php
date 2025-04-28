@extends('admin.layouts.app')
@section('title', 'Brand')
@section('content')
    <div class="page-inner">
        <div class="page-header">
            <h3 class="fw-bold mb-3">@yield('title')</h3>
            <ul class="breadcrumbs mb-3">
                <li class="nav-home">
                    <a href="{{ route('dashboard') }}">
                        <i class="icon-home"></i>
                    </a>
                </li>
                <li class="separator">
                    <i class="icon-arrow-right"></i>
                </li>
                <li class="nav-item">
                    <a href="{{ route('brand.index') }}">@yield('title')</a>
                </li>
            </ul>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="card-title mb-0">Daftar @yield('title')</h4>
                        <button class="btn btn-black mb-3" data-bs-toggle="modal" data-bs-target="#modalTambah"><i
                                class="fas fa-plus"></i> Tambah
                            Brand</button>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="basic-datatables" class="display table table-striped table-hover">
                                <thead>
                                    <tr class="text-center">
                                        <th>No</th>
                                        <th>Kode</th>
                                        <th>Logo</th>
                                        <th>Nama</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($brands as $index => $brand)
                                        <tr>
                                            <td class="text-center">{{ $index + 1 }}</td>
                                            <td>{{ $brand->kode_brand }}</td>
                                            <td class="text-center">
                                                <div class="avatar">
                                                    <img src="{{ asset('storage/' . $brand->logo) }}" alt="{{ $brand->nama_brand }}"
                                                        class="avatar-img rounded">
                                                </div>
                                            </td>
                                            <td>{{ $brand->nama_brand }}</td>
                                            <td class="text-center">
                                                <button class="btn btn-warning btn-sm btn-edit"
                                                    data-id="{{ $brand->kode_brand }}" data-nama="{{ $brand->nama_brand }}"
                                                    data-bs-toggle="modal" data-bs-target="#modalEdit" title="Edit">
                                                    <i class="fas fa-edit text-white"></i>
                                                </button>

                                                <form action="{{ route('brand.destroy', $brand->kode_brand) }}"
                                                    method="POST" style="display:inline;">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger btn-sm delete-btn"
                                                        data-route="{{ route('brand.destroy', $brand->kode_brand) }}"
                                                        title="Hapus">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Tambah -->
    <div class="modal fade" id="modalTambah" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Brand</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('brand.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Nama Brand</label>
                            <input type="text" name="nama_brand" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Logo Brand</label>
                            <input type="file" name="logo" class="form-control" accept="image/*"
                                onchange="previewLogo(event, 'tambahLogoPreview')">
                            <div class="mt-2">
                                <img id="tambahLogoPreview" src="" class="img-fluid"
                                    style="max-height: 200px; display: none;">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Edit -->
    <div class="modal fade" id="modalEdit" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Brand</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="formEdit" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Nama Brand</label>
                            <input type="text" name="nama_brand" id="editNama" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Logo Brand</label>
                            <input type="file" name="logo" class="form-control" accept="image/*"
                                onchange="previewLogo(event, 'editLogoPreview')">
                            <div class="mt-2">
                                <input type="hidden" name="existing_logo" id="editExistingLogo">
                                <img id="editLogoPreview" src="" class="img-fluid"
                                    style="max-height: 200px; display: none;">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>



@endsection

@push('scripts')
    <script>
        function previewLogo(event, previewId) {
            const input = event.target;
            const preview = document.getElementById(previewId);

            if (input.files && input.files[0]) {
                const reader = new FileReader();

                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                }

                reader.readAsDataURL(input.files[0]);
            } else {
                preview.src = '';
                preview.style.display = 'none';
            }
        }

        document.querySelectorAll('.btn-edit').forEach(btn => {
            btn.addEventListener('click', function() {
                document.getElementById('editNama').value = this.dataset.nama;
                document.getElementById('formEdit').action = `/brand/${this.dataset.id}`;

                // Set existing logo
                const existingLogo = this.dataset.logo;
                const logoPreview = document.getElementById('editLogoPreview');
                const existingLogoInput = document.getElementById('editExistingLogo');

                if (existingLogo) {
                    logoPreview.src = existingLogo;
                    logoPreview.style.display = 'block';
                    existingLogoInput.value = existingLogo;
                } else {
                    logoPreview.src = '';
                    logoPreview.style.display = 'none';
                    existingLogoInput.value = '';
                }
            });
        });
    </script>
@endpush
