@extends('admin.layouts.app')
@section('title', isset($barang) ? 'Edit Barang' : 'Tambah Barang')
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
                    <a href="{{ route('barang.index') }}">Barang</a>
                </li>
                <li class="separator">
                    <i class="icon-arrow-right"></i>
                </li>
                <li class="nav-item">
                    <a
                        href="{{ isset($barang) ? route('barang.edit', $barang->kode_barang) : route('barang.create') }}">@yield('title')</a>
                </li>
            </ul>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title mb-0">{{ isset($barang) ? 'Edit Barang' : 'Tambah Barang' }} </h4>
                    </div>
                    <div class="card-body">
                        <form
                            action="{{ isset($barang) ? route('barang.update', $barang->kode_barang) : route('barang.store') }}"
                            method="POST" enctype="multipart/form-data" id="barangForm">
                            @csrf
                            @if (isset($barang))
                                @method('PUT')
                            @endif

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="nama_barang" class="form-label">Nama Barang</label>
                                        <input type="text"
                                            class="form-control @error('nama_barang') is-invalid @enderror" id="nama_barang"
                                            name="nama_barang" value="{{ old('nama_barang', $barang->nama_barang ?? '') }}"
                                            required maxlength="100">
                                        @error('nama_barang')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="kode_tipe" class="form-label">Tipe</label>
                                        <select class="form-select form-control @error('kode_tipe') is-invalid @enderror"
                                            id="kode_tipe" name="kode_tipe" required>
                                            <option value="">Pilih Tipe</option>
                                            @foreach ($tipes as $tipe)
                                                <option value="{{ $tipe->kode_tipe }}"
                                                    {{ old('kode_tipe', $barang->kode_tipe ?? '') == $tipe->kode_tipe ? 'selected' : '' }}>
                                                    {{ $tipe->brand->nama_brand }} - {{ $tipe->nama_tipe }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('kode_tipe')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="berat" class="form-label">Berat (gram)</label>
                                        <input type="number" class="form-control @error('berat') is-invalid @enderror"
                                            id="berat" name="berat" value="{{ old('berat', $barang->berat ?? '') }}"
                                            required min="1">
                                        @error('berat')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="harga_normal" class="form-label">Harga Normal</label>
                                        <div class="input-group">
                                            <span class="input-group-text">Rp</span>
                                            <input type="number"
                                                class="form-control @error('harga_normal') is-invalid @enderror"
                                                id="harga_normal" name="harga_normal"
                                                value="{{ old('harga_normal', $barang->harga_normal ?? '') }}" required
                                                min="1000">
                                        </div>
                                        @error('harga_normal')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="deskripsi" class="form-label">Deskripsi</label>
                                <textarea class="form-control" id="deskripsi" name="deskripsi" rows="4">{{ old('deskripsi', $barang->deskripsi ?? '') }}</textarea>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="gambar" class="form-label">Gambar</label>
                                        <input type="file" class="form-control @error('gambar') is-invalid @enderror"
                                            id="gambar" name="gambar" accept="image/*" onchange="previewImage(event)">
                                        @if (isset($barang) && $barang->gambar)
                                            <input type="hidden" name="existing_gambar" value="{{ $barang->gambar }}">
                                        @endif
                                        @error('gambar')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror

                                        <div class="mt-2">
                                            <img id="image-preview"
                                                src="{{ isset($barang) && $barang->gambar ? Storage::url($barang->gambar) : '' }}"
                                                class="img-fluid"
                                                style="max-height: 200px; display: {{ isset($barang) && $barang->gambar ? 'block' : 'none' }};">
                                        </div>
                                    </div>

                                    <!-- Modal untuk Cropper -->
                                    <div class="modal fade" id="cropperModal" tabindex="-1">
                                        <div class="modal-dialog modal-lg">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Crop Gambar</h5>
                                                    <button type="button" class="btn-close"
                                                        data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="img-container">
                                                        <img id="crop-image" src="" alt="Crop Image">
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary"
                                                        data-bs-dismiss="modal">Batal</button>
                                                    <button type="button" class="btn btn-primary"
                                                        id="crop-button">Crop</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>


                            <hr>

                            <div class="row">
                                <div class="col-md-12">
                                    <h5>Detail Barang
                                        <button type="button" class="btn btn-success btn-sm ms-2"
                                            id="tambah-detail-btn">
                                            <i class="fas fa-plus"></i> Tambah Detail
                                        </button>
                                    </h5>
                                </div>
                            </div>

                            <div id="detail-container">
                                @if (isset($barang) && $barang->detailBarangs->count() > 0)
                                    @foreach ($barang->detailBarangs as $index => $detail)
                                        <div class="detail-row row mb-3">
                                            <div class="col-md-3">
                                                <select class="form-select warna-select"
                                                    name="detail_warnas[{{ $index }}][kode_warna]">
                                                    <option value="">Pilih Warna</option>
                                                    @foreach ($warnas as $warna)
                                                        <option value="{{ $warna->kode_warna }}"
                                                            {{ $detail->kode_warna == $warna->kode_warna ? 'selected' : '' }}>
                                                            {{ $warna->warna }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-3">
                                                <input type="text" class="form-control ukuran-input"
                                                    name="detail_warnas[{{ $index }}][ukuran]"
                                                    placeholder="Ukuran (mis: 42, 41.5)" value="{{ $detail->ukuran }}"
                                                    pattern="^\d+(\.\d+)?$">
                                            </div>
                                            <div class="col-md-3">
                                                <input type="number" class="form-control stok-input"
                                                    name="detail_warnas[{{ $index }}][stok]" placeholder="Stok"
                                                    value="{{ $detail->stok }}" min="0">
                                            </div>
                                            <div class="col-md-3 d-flex align-items-end">
                                                <button type="button" class="btn btn-danger remove-detail-btn">
                                                    <i class="fas fa-trash"></i> Hapus
                                                </button>
                                            </div>
                                        </div>
                                    @endforeach
                                @endif
                            </div>


                            <div class="d-flex justify-content-between mt-3">
                                <a href="{{ route('barang.index') }}" class="btn btn-secondary">Kembali</a>
                                <button type="submit" class="btn btn-primary">
                                    {{ isset($barang) ? 'Update' : 'Simpan' }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.css">
@endpush

@push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.js"></script>
    <script>
        $(document).ready(function() {
            // Tambah baris detail barang
            $('#tambah-detail-btn').on('click', function() {
                const detailIndex = $('#detail-container .detail-row').length;
                const detailRow = `
            <div class="detail-row row mb-3">
                <div class="col-md-3">
                    <select 
                        class="form-select warna-select" 
                        name="detail_warnas[${detailIndex}][kode_warna]" 
                    >
                        <option value="">Pilih Warna</option>
                        @foreach ($warnas as $warna)
                            <option 
                                value="{{ $warna->kode_warna }}" 
                                data-hex="{{ $warna->kode_hex }}"
                            >
                                {{ $warna->warna }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <input 
                        type="text" 
                        class="form-control ukuran-input" 
                        name="detail_warnas[${detailIndex}][ukuran]" 
                        placeholder="Ukuran (mis: 42, 41.5)"
                        pattern="^\\d+(\\.\\d+)?$"
                    >
                </div>
      
                <div class="col-md-3 d-flex align-items-end">
                    <button type="button" class="btn btn-danger remove-detail-btn">
                        <i class="fas fa-trash"></i> Hapus
                    </button>
                </div>
            </div>
        `;

                $('#detail-container').append(detailRow);
            });

            // Hapus baris detail barang
            $(document).on('click', '.remove-detail-btn', function() {
                $(this).closest('.detail-row').remove();
            });
        });

        let cropper;
        let imageFile;

        function previewImage(event) {
            const file = event.target.files[0];
            const reader = new FileReader();

            reader.onload = function(e) {
                const preview = document.getElementById('image-preview');
                preview.src = e.target.result;
                preview.style.display = 'block';

                // Tampilkan modal cropper
                imageFile = file;
                $('#crop-image').attr('src', e.target.result);
                $('#cropperModal').modal('show');
            }

            reader.readAsDataURL(file);
        }

        $('#cropperModal').on('shown.bs.modal', function() {
            const image = document.getElementById('crop-image');
            cropper = new Cropper(image, {
                aspectRatio: 1,
                viewMode: 1,
                dragMode: 'move'
            });
        }).on('hidden.bs.modal', function() {
            cropper.destroy();
        });

        $('#crop-button').on('click', function() {
            const croppedCanvas = cropper.getCroppedCanvas({
                width: 500,
                height: 500
            });

            croppedCanvas.toBlob(function(blob) {
                // Buat file baru dari cropped blob
                const croppedFile = new File([blob], imageFile.name, {
                    type: imageFile.type
                });

                // Buat DataTransfer untuk mengganti file input
                const dataTransfer = new DataTransfer();
                dataTransfer.items.add(croppedFile);

                // Update input file
                $('#gambar')[0].files = dataTransfer.files;

                // Update preview
                $('#image-preview').attr('src', croppedCanvas.toDataURL());

                // Tutup modal
                $('#cropperModal').modal('hide');
            });
        });
    </script>
@endpush
