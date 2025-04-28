@extends('admin.layouts.app')
@section('title', isset($warna) ? 'Edit Warna' : 'Tambah Warna')
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
                    <a href="{{ route('warna.index') }}">Warna</a>
                </li>
                <li class="separator">
                    <i class="icon-arrow-right"></i>
                </li>
                <li class="nav-item">
                    <a
                        href="{{ isset($warna) ? route('warna.edit', $warna->kode_warna) : route('warna.create') }}">@yield('title')</a>
                </li>
            </ul>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                       <h4 class="card-title mb-0"> {{ isset($warna) ? 'Edit Warna' : 'Tambah Warna' }} </h4>
                    </div>
                    <div class="card-body">
                        <form
                            action="{{ isset($warna) ? route('warna.update', $warna->kode_warna) : route('warna.store') }}"
                            method="POST">
                            @csrf
                            @if (isset($warna))
                                @method('PUT')
                            @endif

                            <div class="mb-3">
                                <label for="warna" class="form-label">Nama Warna</label>
                                <input type="text" class="form-control @error('warna') is-invalid @enderror"
                                    id="warna" name="warna" value="{{ old('warna', $warna->warna ?? '') }}" required
                                    maxlength="30">
                                @error('warna')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="kode_hex" class="form-label">Kode Warna</label>
                                <div class="input-group">
                                    <span class="input-group-text">#</span>
                                    <input type="text" class="form-control @error('kode_hex') is-invalid @enderror"
                                        id="kode_hex" name="kode_hex" value="{{ old('kode_hex', $warna->kode_hex ?? '') }}"
                                        required maxlength="6" pattern="[0-9A-Fa-f]{6}">
                                    <input type="color" class="form-control form-control-color" id="color_picker"
                                        value="#{{ old('kode_hex', $warna->kode_hex ?? '000000') }}" title="Pilih Warna">
                                </div>
                                @error('kode_hex')
                                    <div class="invalid-feedback d-block">
                                        {{ $message }}
                                    </div>
                                @enderror
                                <small class="form-text text-muted">
                                    Masukkan kode hex warna (contoh: FF0000 untuk merah)
                                </small>
                            </div>

                            <div class="d-flex justify-content-between">
                                <a href="{{ route('warna.index') }}" class="btn btn-secondary">Kembali</a>
                                <button type="submit" class="btn btn-primary">
                                    {{ isset($warna) ? 'Update' : 'Simpan' }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            // Sinkronisasi antara color picker dan input kode hex
            $('#color_picker').on('input', function() {
                // Hapus # dari value color picker
                let hexColor = $(this).val().replace('#', '');
                $('#kode_hex').val(hexColor);
            });

            // Sinkronisasi input manual dengan color picker
            $('#kode_hex').on('input', function() {
                let hexColor = $(this).val();
                // Pastikan hanya 6 karakter
                hexColor = hexColor.substring(0, 6);
                $('#color_picker').val('#' + hexColor);
            });
        });
    </script>
@endpush
