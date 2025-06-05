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

        {{-- Button Tabs --}}
        <div class="mb-3">
            @php $statusAktif = request('status', 'diajukan'); @endphp
            @foreach (['diajukan', 'disetujui', 'ditolak'] as $index => $status)
                <button class="btn {{ $statusAktif === $status ? 'btn-dark' : 'btn-light' }} btn-md {{ $index < 2 ? 'me-2' : '' }}"
                    onclick="window.location.href='{{ route('pemusnahan-barang.index', ['status' => $status]) }}'">
                    {{ ucfirst($status) }}
                </button>
            @endforeach
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="card-title mb-0">Daftar @yield('title')</h4>
                        @php
                            $user = Auth::guard('admin')->user();
                            $role = $user?->role;
                        @endphp
                        @if ($role === 'gudang')
                            <a href="{{ route('pemusnahan-barang.daftar-barang') }}" class="btn btn-black mb-3">
                                <i class="fas fa-plus"></i> Pemusnahan Baru
                            </a>
                        @endif
                    </div>

                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="basic-datatables" class="display table table-striped table-hover">
                                <thead>
                                    <tr class="text-center">
                                        <th>Kode Pemusnahan</th>
                                        <th>Nama Barang</th>
                                        <th>Admin</th>
                                        <th>Tanggal Pemusnahan</th>
                                        <th>Alasan</th>
                                        <th>Jumlah Diajukan</th>
                                        <th>Jumlah Disetujui</th>
                                        <th>Bukti Gambar</th>
                                        <th>Status</th>
                                        @if (auth()->user()->role == 'owner')
                                            <th>Perlu Tindakan</th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($pemusnahanBarangs as $pemusnahan)
                                        @if ($pemusnahan->status === $statusAktif)
                                            <tr>
                                                <td>{{ $pemusnahan->kode_pemusnahan }}</td>
                                                <td>{{ $pemusnahan->detailBarang->barang->nama_barang }}</td>
                                                <td>{{ $pemusnahan->admin->nama_admin }}</td>
                                                <td>{{ $pemusnahan->tanggal_pemusnahan }}</td>
                                                <td>{{ $pemusnahan->alasan }}</td>
                                                <td>{{ $pemusnahan->jumlah_diajukan }}</td>
                                                <td>{{ $pemusnahan->disetujui }}</td>
                                                <td class="text-center">
                                                    <a href="#" class="btn btn-info btn-sm" data-bs-toggle="modal"
                                                        data-bs-target="#modalBukti{{ $pemusnahan->kode_pemusnahan }}">
                                                        <i class="icon-eye"></i>
                                                    </a>
                                                </td>

                                                <!-- Modal Bukti -->
                                                <div class="modal fade" id="modalBukti{{ $pemusnahan->kode_pemusnahan }}"
                                                    tabindex="-1"
                                                    aria-labelledby="buktiLabel{{ $pemusnahan->kode_pemusnahan }}"
                                                    aria-hidden="true">
                                                    <div class="modal-dialog modal-dialog-centered">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title"
                                                                    id="buktiLabel{{ $pemusnahan->kode_pemusnahan }}">Bukti
                                                                    Pemusnahan</h5>
                                                                <button type="button" class="btn-close"
                                                                    data-bs-dismiss="modal" aria-label="Tutup"></button>
                                                            </div>
                                                            <div class="modal-body text-center">
                                                                @if ($pemusnahan->bukti_gambar)
                                                                    <img src="{{ asset('storage/' . $pemusnahan->bukti_gambar) }}"
                                                                        alt="Bukti Gambar" class="img-fluid rounded">
                                                                @else
                                                                    <p class="text-muted">Tidak ada bukti gambar.</p>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <td>{{ ucfirst($pemusnahan->status) }}</td>

                                                @if (auth()->user()->role == 'owner')
                                                    <td class="text-center">
                                                        @if ($pemusnahan->status == 'diajukan')
                                                            <div class="d-flex gap-2">
                                                                <button type="button" class="btn btn-success btn-sm"
                                                                    onclick="setujui('{{ $pemusnahan->kode_pemusnahan }}', {{ $pemusnahan->jumlah_diajukan }})">Setujui</button>
                                                                <button type="button" class="btn btn-danger btn-sm"
                                                                    onclick="tolak('{{ $pemusnahan->kode_pemusnahan }}')">Tolak</button>
                                                            </div>


                                                            <!-- Hidden Form -->
                                                            <form id="form-persetujuan" method="POST"
                                                                style="display: none;">
                                                                @csrf
                                                                @method('PUT')
                                                                <input type="hidden" name="aksi" id="aksi-input">
                                                                <input type="hidden" name="jumlah" id="jumlah-input">
                                                            </form>
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                @endif
                                            </tr>
                                        @endif
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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
       function setujui(kode, jumlahDiajukan) {
    Swal.fire({
        title: 'Setujui Pemusnahan',
        text: 'Masukkan jumlah yang disetujui:',
        input: 'number',
        inputAttributes: {
            min: 1,
            max: jumlahDiajukan
        },
        inputValue: jumlahDiajukan, // default value dari jumlah diajukan
        inputValidator: (value) => {
            if (!value || value <= 0) {
                return 'Jumlah harus diisi dan lebih dari 0';
            }
            if (value > jumlahDiajukan) {
                return `Jumlah tidak boleh melebihi jumlah pengajuan (${jumlahDiajukan})`;
            }
        },
        showCancelButton: true,
        confirmButtonText: 'Setujui',
        cancelButtonText: 'Batal',
        preConfirm: (jumlah) => {
            document.getElementById('form-persetujuan').action =
                `/pemusnahan-barang/persetujuan/${kode}`;
            document.getElementById('aksi-input').value = 'disetujui';
            document.getElementById('jumlah-input').value = jumlah;
            document.getElementById('form-persetujuan').submit();
        }
    });
}


        function tolak(kode) {
            Swal.fire({
                title: 'Tolak Pemusnahan?',
                text: "Apakah Anda yakin ingin menolak permintaan ini?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, Tolak',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('form-persetujuan').action = `/pemusnahan-barang/persetujuan/${kode}`;
                    document.getElementById('aksi-input').value = 'ditolak';
                    document.getElementById('jumlah-input').value = '';
                    document.getElementById('form-persetujuan').submit();
                }
            });
            
        }
    </script>
@endpush
