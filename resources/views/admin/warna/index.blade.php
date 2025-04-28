@extends('admin.layouts.app')
@section('title', 'Warna')
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
                    <a href="{{ route('warna.index') }}">@yield('title')</a>
                </li>
            </ul>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="card-title mb-0">Daftar @yield('title')</h4>
                        <a href="{{ route('warna.create') }}" class="btn btn-black">
                            <i class="fas fa-plus"></i> Tambah Warna
                        </a>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="basic-datatables" class="display table table-striped table-hover">
                                <thead>
                                    <tr class="text-center">
                                        <th>No</th>
                                        <th>Kode Warna</th>
                                        <th>Nama Warna</th>
                                        <th>Kode Hex</th>
                                        <th>Preview</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($warnas as $index => $warna)
                                        <tr>
                                            <td class="text-center">{{ $index + 1 }}</td>
                                            <td>{{ $warna->kode_warna }}</td>
                                            <td>{{ $warna->warna }}</td>
                                            <td class="text-center">#{{ $warna->kode_hex }}</td>
                                            <td class="text-center">
                                                <div style="width: 50px; align-items: center; height: 30px; background-color: #{{ $warna->kode_hex }};"
                                                    class="border"></div>
                                            </td>
                                            <td>
                                                <div class="d-flex">
                                                    <a href="{{ route('warna.edit', $warna->kode_warna) }}"
                                                        class="btn btn-sm btn-warning me-2">
                                                        <i class="fas fa-edit text-white"></i>
                                                    </a>
                                                    <form action="{{ route('warna.destroy', $warna->kode_warna) }}"
                                                        method="POST" class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-danger delete-btn">
                                                            <i class="fas fa-trash-alt"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        @endsection
