@extends('admin.layouts.app')
@section('title', 'Tipe')
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
                    <a href="{{ route('tipe.index') }}">@yield('title')</a>
                </li>
            </ul>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="card-title mb-0">Daftar @yield('title')</h4>
                        <a href="{{ route('tipe.create') }}" class="btn btn-black">
                            <i class="fas fa-plus"></i> Tambah Tipe
                        </a>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="basic-datatables" class="display table table-striped table-hover">
                                <thead>
                                    <tr class="text-center">
                                        <th>No</th>
                                        <th>Kode Tipe</th>
                                        <th>Nama Tipe</th>
                                        <th>Brand</th>
                                        <th>Potongan Harga</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($tipes as $index => $tipe)
                                        <tr>
                                            <td class="text-center">{{ $index + 1 }}</td>
                                            <td>{{ $tipe->kode_tipe }}</td>
                                            <td>{{ $tipe->nama_tipe }}</td>
                                            <td>{{ $tipe->brand->nama_brand }}</td>
                                            <td>
                                                {{ $tipe->formatted_potongan_harga }}
                                            </td>
                                            <td class="text-center">
                                                <div class="d-flex">
                                                    <a href="{{ route('tipe.edit', $tipe->kode_tipe) }}"
                                                        class="btn btn-sm btn-warning me-2">
                                                        <i class="fas fa-edit text-white"></i>
                                                    </a>
                                                    <form action="{{ route('tipe.destroy', $tipe->kode_tipe) }}"
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
        </div>
    </div>
@endsection
@push('scripts')
@if ($errors->has('nama_tipe'))
<script>
    Swal.fire({
        icon: 'error',
        title: 'Gagal Menambahkan Tipe',
        text: '{{ $errors->first('nama_tipe') }}',
        confirmButtonColor: '#d33'
    });
</script>
@endif

    
@endpush
