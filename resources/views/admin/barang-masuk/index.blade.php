@extends('admin.layouts.app')
@section('title', 'Barang Masuk')
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
                    <a href="{{ route('barang-masuk.index') }}">@yield('title')</a>
                </li>
            </ul>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="card-title mb-0">Data @yield('title')</h4>
                        <a href="{{ route('barang-masuk.create') }}" class="btn btn-black">
                            <i class="fas fa-plus"></i> Tambah Barang Masuk
                        </a>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="basic-datatables" class="display table table-striped table-hover">
                                <thead>
                                    <tr class="text-center">
                                        <th>No</th>
                                        <th>Kode Pembelian</th>
                                        <th>Tanggal Masuk</th>
                                        <th>Admin</th>
                                        <th>Bukti Pembelian</th>
                                        {{-- <th>Total Barang</th> --}}
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($barangMasuk as $index => $item)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>{{ $item->kode_pembelian }}</td>
                                            <td>{{ \Carbon\Carbon::parse($item->tanggal_masuk)->format('d/m/Y') }}</td>
                                            <td>{{ $item->admin->nama_admin ?? 'Unknown' }}</td>
                                            <td>
                                                @if (!empty($item->bukti_pembelian) && Storage::disk('public')->exists($item->bukti_pembelian))
                                                    <a href="{{ asset('storage/' . $item->bukti_pembelian) }}"
                                                        target="_blank" class="btn btn-sm btn-outline-primary">
                                                        <i class="fas fa-file-alt"></i> Lihat
                                                    </a>
                                                @else
                                                    <span class="badge badge-secondary">Tidak Ada</span>
                                                @endif

                                            </td>
                                            {{-- <td>
                                                <span class="badge badge-primary">
                                                    {{ $item->detail->sum('jumlah') }} Item
                                                </span>
                                            </td> --}}
                                            <td>
                                                <div class="d-flex">
                                                    <a href="{{ route('detail-barang-masuk.show', $item->kode_pembelian) }}"
                                                        class="btn btn-sm btn-info me-2" title="Detail">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="{{ route('barang-masuk.edit', $item->kode_pembelian) }}"
                                                        class="btn btn-sm btn-warning me-2" title="Edit">
                                                        <i class="fas fa-edit text-white"></i>
                                                    </a>
                                                    <form
                                                        action="{{ route('barang-masuk.destroy', $item->kode_pembelian) }}"
                                                        method="POST" class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-danger delete-btn"
                                                            title="Hapus">
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

    <!-- Modal Detail (Optional) -->
    <div class="modal fade" id="detailModal" tabindex="-1" aria-labelledby="detailModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="detailModalLabel">Detail Barang Masuk</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="modalContent">
                        <!-- Content will be loaded here -->
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {


            // Delete confirmation
            $('.delete-btn').on('click', function(e) {
                e.preventDefault();
                const form = $(this).closest('form');

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
                        form.submit();
                    }
                });
            });

            // Optional: Quick view detail in modal
            $('.quick-detail').on('click', function(e) {
                e.preventDefault();
                const kodePembelian = $(this).data('kode');

                // Load detail via AJAX (optional)
                $.get(`/barang-masuk/${kodePembelian}/quick-detail`, function(data) {
                    $('#modalContent').html(data);
                    $('#detailModal').modal('show');
                });
            });
        });
    </script>
@endpush
