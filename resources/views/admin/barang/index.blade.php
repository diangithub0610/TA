@extends('admin.layouts.app')
@section('title', 'Barang')
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
                    <a href="{{ route('barang.index') }}">@yield('title')</a>
                </li>
            </ul>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    @if (Route::currentRouteName() == 'barang.index')
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h4 class="card-title mb-0">Daftar @yield('title')</h4>
                            <a href="{{ route('barang.create') }}" class="btn btn-black">
                                <i class="fas fa-plus"></i> Tambah Barang
                            </a>
                        </div>
                    @endif
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="basic-datatables" class="display table table-striped table-hover">
                                <thead>
                                    <tr class="text-center">
                                        <th>No</th>
                                        <th>Kode Barang</th>
                                        <th>Nama Barang</th>
                                        <th>Tipe</th>
                                        <th>Harga</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($barangs as $index => $barang)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>{{ $barang->kode_barang }}</td>
                                            <td>{{ $barang->nama_barang }}</td>
                                            <td>{{ $barang->tipe->nama_tipe }}</td>
                                            <td>{{ $barang->formatted_harga }}</td>
                                            <td>
                                                @if (Route::currentRouteName() == 'barang.index')
                                                    <div class="d-flex">
                                                        <a href="{{ route('barang.show', $barang->kode_barang) }}"
                                                            class="btn btn-sm btn-info me-2">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                        <a href="{{ route('barang.edit', $barang->kode_barang) }}"
                                                            class="btn btn-sm btn-warning me-2">
                                                            <i class="fas fa-edit text-white"></i>
                                                        </a>
                                                        <form action="{{ route('barang.destroy', $barang->kode_barang) }}"
                                                            method="POST" class="d-inline">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-sm btn-danger delete-btn">
                                                                <i class="fas fa-trash-alt"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                @else
                                                <a href="{{ route('pemusnahan-barang.detail-barang', $barang->kode_barang) }}"
                                                    class="btn btn-sm btn-info me-2">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                @endif
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
@endsection
