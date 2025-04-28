@extends('admin.layouts.app')
@section('title', isset($tipe) ? 'Edit Tipe' : 'Tambah Tipe')
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
                    <a href="{{ route('tipe.index') }}">Tipe</a>
                </li>
                <li class="separator">
                    <i class="icon-arrow-right"></i>
                </li>
                <li class="nav-item">
                    <a
                        href="{{ isset($tipe) ? route('tipe.edit', $tipe->kode_tipe) : route('tipe.create') }}">@yield('title')</a>
                </li>
            </ul>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title mb-0">{{ isset($tipe) ? 'Edit Tipe' : 'Tambah Tipe' }}</h4>
                    </div>
                    <div class="card-body">
                        <form action="{{ isset($tipe) ? route('tipe.update', $tipe->kode_tipe) : route('tipe.store') }}"
                            method="POST">
                            @csrf
                            @if (isset($tipe))
                                @method('PUT')
                            @endif

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="nama_tipe" class="form-label">Nama Tipe</label>
                                        <input type="text" class="form-control @error('nama_tipe') is-invalid @enderror"
                                            id="nama_tipe" name="nama_tipe"
                                            value="{{ old('nama_tipe', $tipe->nama_tipe ?? '') }}" required maxlength="50">
                                        @error('nama_tipe')
                                            <div class="invalid-feedback">
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="kode_brand" class="form-label">Brand</label>
                                        <select class="form-select form-control @error('kode_brand') is-invalid @enderror"
                                            id="kode_brand" name="kode_brand" required>
                                            <option value="" readonly>Pilih Brand</option>
                                            @foreach ($brands as $brand)
                                                <option value="{{ $brand->kode_brand }}"
                                                    {{ old('kode_brand', $tipe->kode_brand ?? '') == $brand->kode_brand ? 'selected' : '' }}>
                                                    {{ $brand->nama_brand }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('kode_brand')
                                            <div class="invalid-feedback">
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>
                                </div>
                            </div>


                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="potongan_harga" class="form-label">Potongan Harga</label>
                                    <div class="input-group">
                                        <span class="input-group-text">Rp</span>
                                        <input type="number"
                                            class="form-control @error('potongan_harga') is-invalid @enderror"
                                            id="potongan_harga" name="potongan_harga"
                                            value="{{ old('potongan_harga', $tipe->potongan_harga ?? '') }}" min="0"
                                            max="100000000">
                                    </div>
                                    @error('potongan_harga')
                                        <div class="invalid-feedback d-block">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            </div>

                            <div class="d-flex justify-content-between">
                                <a href="{{ route('tipe.index') }}" class="btn btn-secondary">Kembali</a>
                                <button type="submit" class="btn btn-primary">
                                    {{ isset($tipe) ? 'Update' : 'Simpan' }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
