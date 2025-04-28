@extends('admin.layouts.app')
@section('title', 'Detail Barang')
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
                    <a href="{{ route('barang.show', $barang->kode_barang) }}">@yield('title')</a>
                </li>
            </ul>
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="card-title mb-0">Detail Produk</h4>
                        <a href="{{ route('barang.edit', $barang->kode_barang) }}" class="btn btn-sm btn-warning me-2">
                            <i class="fas fa-edit text-white"></i>
                        </a>
                    </div>
                    <div class="card-body">
                        @if ($barang->gambar)
                            <img src="{{ Storage::url($barang->gambar) }}" alt="{{ $barang->nama_barang }}"
                                class="img-fluid rounded mb-3">
                        @endif

                        <h4 class="card-title">{{ $barang->nama_barang }}</h4>
                        <table class="table">
                            <tr>
                                <th>Kode Barang</th>
                                <td>{{ $barang->kode_barang }}</td>
                            </tr>
                            <tr>
                                <th>Tipe</th>
                                <td>{{ $barang->tipe->nama_tipe }}</td>
                            </tr>
                            <tr>
                                <th>Berat</th>
                                <td>{{ $barang->berat }} gram</td>
                            </tr>
                            <tr>
                                <th>Harga</th>
                                <td>{{ $barang->formatted_harga }}</td>
                            </tr>
                        </table>

                        @if ($barang->deskripsi)
                            <h5>Deskripsi</h5>
                            <p>{{ $barang->deskripsi }}</p>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title mb-0">Stok Detail Barang</h4>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th>Warna</th>
                                        <th>Ukuran</th>
                                        <th>Stok</th>
                                        @if (Route::currentRouteName() == 'pemusnahan-barang.detail-barang')
                                            <th>Aksi</th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($barang->detailBarangs as $detail)
                                        <tr>
                                            <td>
                                                <div class="d-inline-block me-2"
                                                    style="width: 20px; height: 20px; background-color: #{{ $detail->warna->kode_hex }}; border: 1px solid #000;">
                                                </div>
                                                {{ $detail->warna->warna }}
                                            </td>
                                            <td>{{ $detail->ukuran }}</td>
                                            <td>{{ $detail->stok }}</td>
                                            @if (Route::currentRouteName() == 'pemusnahan-barang.detail-barang')
                                                <td>
                                                    <button class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#hapusModal{{ $detail->kode_detail }}">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                            
                                                    {{-- Modal --}}
                                                    <div class="modal fade" id="hapusModal{{ $detail->kode_detail }}" tabindex="-1" aria-labelledby="hapusModalLabel" aria-hidden="true">
                                                        <div class="modal-dialog">
                                                            <form action="{{ route('pemusnahan-barang.store') }}" method="POST" enctype="multipart/form-data">
                                                                @csrf
                                                                <div class="modal-content">
                                                                    <div class="modal-header">
                                                                        <h5 class="modal-title" id="hapusModalLabel">Pemusnahan Barang</h5>
                                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                    </div>
                                                                    <div class="modal-body">
                                                                        <input type="hidden" name="kode_detail" value="{{ $detail->kode_detail }}">
                            
                                                                        <div class="mb-3">
                                                                            <label>Nama Barang</label>
                                                                            <input type="text" class="form-control" value="{{ $barang->nama_barang }}" readonly>
                                                                        </div>
                            
                                                                        <div class="mb-3">
                                                                            <label>Detail Barang</label>
                                                                            <input type="text" class="form-control" value="Warna: {{ $detail->warna->warna }}, Ukuran: {{ $detail->ukuran }}" readonly>
                                                                        </div>
                            
                                                                        <div class="mb-3">
                                                                            <label>Jumlah</label>
                                                                            <input type="number" name="jumlah" class="form-control" required min="1" max="{{ $detail->stok }}">
                                                                        </div>
                            
                                                                        <div class="mb-3">
                                                                            <label>Upload Gambar / Kamera</label>
                                                                            <input type="file" name="bukti_gambar" accept="image/*" capture="environment" class="form-control" required>
                                                                        </div>
                            
                                                                        <div class="mb-3">
                                                                            <label>Alasan Pemusnahan</label>
                                                                            <textarea name="alasan" class="form-control" required></textarea>
                                                                        </div>
                                                                    </div>
                                                                    <div class="modal-footer">
                                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                                                                        <button type="submit" class="btn btn-danger">Ajukan Pemusnahan</button>
                                                                    </div>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </td>
                                            @endif
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-3 text-center">
            <a href="{{ route('barang.index') }}" class="btn btn-secondary">Kembali</a>
        </div>
    </div>
@endsection
