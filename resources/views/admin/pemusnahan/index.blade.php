@extends('admin.layouts.app')
@section('title', 'Pemusnahan')
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
                    <a href="{{ route('pemusnahan-barang.index') }}">@yield('title')</a>
                </li>
            </ul>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="card-title mb-0">Daftar @yield('title')</h4>
                        <a href="{{ route('pemusnahan-barang.daftar-barang') }}" class="btn btn-black mb-3"><i
                                class="fas fa-plus"></i> Pemusnahan Baru</a>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="basic-datatables" class="display table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Kode Pemusnahan</th>
                                        <th>Kode Detail</th>
                                        <th>Admin</th>
                                        <th>Tanggal Pemusnahan</th>
                                        <th>Alasan</th>
                                        <th>Bukti Gambar</th>
                                        <th>Status</th>
                                        @if (auth()->user()->role == 'gudang')
                                            <th>Persetujuan</th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($pemusnahanBarangs as $pemusnahan)
                                        <tr>
                                            <td>{{ $pemusnahan->kode_pemusnahan }}</td>
                                            <td>{{ $pemusnahan->detailBarang->barang->nama_barang }}</td>
                                            <td>{{ $pemusnahan->detailBarang->ukuran }}</td>
                                            <td>{{ $pemusnahan->detailBarang->warna->warna ?? '-' }}</td>
                                            <td>{{ $pemusnahan->jumlah }}</td>
                                            <td>{{ $pemusnahan->alasan }}</td>
                                            <td>{{ ucfirst($pemusnahan->status) }}</td>

                                            @if (auth()->user()->role == 'gudang')
                                                <td>
                                                    @if ($pemusnahan->status == 'diajukan')
                                                        <form
                                                            action="{{ route('pemusnahan-barang.persetujuan', $pemusnahan->kode_pemusnahan) }}"
                                                            method="POST" class="d-inline">
                                                            @csrf
                                                            @method('PUT')
                                                            <button type="submit" name="aksi" value="disetujui"
                                                                class="btn btn-success btn-sm"
                                                                onclick="return confirm('Yakin ingin menyetujui pemusnahan ini?')">Setujui</button>
                                                        </form>

                                                        <form
                                                            action="{{ route('pemusnahan-barang.persetujuan', $pemusnahan->kode_pemusnahan) }}"
                                                            method="POST" class="d-inline">
                                                            @csrf
                                                            @method('PUT')
                                                            <button type="submit" name="aksi" value="ditolak"
                                                                class="btn btn-danger btn-sm"
                                                                onclick="return confirm('Yakin ingin menolak pemusnahan ini?')">Tolak</button>
                                                        </form>
                                                    @else
                                                        -
                                                    @endif
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
    </div>
@endsection
