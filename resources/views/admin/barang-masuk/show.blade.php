@extends('admin.layouts.app')

@section('title', 'Detail Barang Masuk - ' . $barangMasuk->kode_pembelian)
@php use Carbon\Carbon; @endphp

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Info Barang Masuk -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-box"></i> Informasi Barang Masuk
                        </h5>
                        <a href="{{ route('detail-barang-masuk.index') }}" class="btn btn-light btn-sm">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Kode Pembelian:</strong></td>
                                    <td>{{ $barangMasuk->kode_pembelian }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Admin:</strong></td>
                                    <td>{{ $barangMasuk->admin->nama_admin ?? '-' }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Tanggal Masuk:</strong></td>
                                    <td>
                                        {{ $barangMasuk->tanggal_masuk ? \Carbon\Carbon::parse($barangMasuk->tanggal_masuk)->format('d F Y') : '-' }}
                                    </td>
                                    
                                </tr>
                                <tr>
                                    <td><strong>Bukti Pembelian:</strong></td>
                                    <td>{{ $barangMasuk->bukti_pembelian ?? '-' }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Detail Barang -->
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-list"></i> Detail Barang Masuk
                        </h5>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>No</th>
                                    <th>Kode Barang</th>
                                    <th>Nama Barang</th>
                                    <th>Jumlah</th>
                                    <th>Harga Satuan</th>
                                    <th>Total Harga</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($detailBarangMasuk as $index => $detail)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $detail->kode_barang }}</td>
                                    <td>{{ $detail->barang->nama_barang ?? '-' }}</td>
                                    <td>
                                        <span class="badge bg-secondary">{{ $detail->jumlah }}</span>
                                    </td>
                                    <td>{{ $detail->harga_barang_masuk }}</td>
                                    <td>
                                        <strong class="text-success">
                                            Rp {{ number_format($detail->jumlah * $detail->harga_barang_masuk, 0, ',', '.') }}
                                        </strong>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('detail-barang-masuk.edit', $detail->kode_pembelian) }}" 
                                               class="btn btn-sm btn-warning" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="{{ route('detail-barang-masuk.destroy', $detail->kode_pembelian) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button" class="btn btn-sm btn-danger" 
                                                        onclick="confirmDelete(this)" title="Hapus">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center">
                                        <div class="alert alert-info mb-0">
                                            <i class="fas fa-info-circle"></i> Belum ada detail barang untuk pembelian ini
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                            @if($detailBarangMasuk->count() > 0)
                            <tfoot class="table-secondary">
                                <tr>
                                    <th colspan="3">Total</th>
                                    <th>
                                        <span class="badge bg-primary">{{ $totalJumlah }}</span>
                                    </th>
                                    <th>-</th>
                                    <th>
                                        <strong class="text-success">
                                            Rp {{ number_format($totalHarga, 0, ',', '.') }}
                                        </strong>
                                    </th>
                                    <th>-</th>
                                </tr>
                            </tfoot>
                            @endif
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function confirmDelete(button) {
    Swal.fire({
        title: 'Apakah Anda yakin?',
        text: "Data yang dihapus tidak dapat dikembalikan!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            button.closest('form').submit();
        }
    });
}
</script>

@if(session('success'))
    <script>
        Swal.fire({
            title: 'Berhasil!',
            text: '{{ session('success') }}',
            icon: 'success',
            confirmButtonText: 'OK'
        });
    </script>
@endif

@if(session('error'))
    <script>
        Swal.fire({
            title: 'Gagal!',
            text: '{{ session('error') }}',
            icon: 'error',
            confirmButtonText: 'OK'
        });
    </script>
@endif
@endsection