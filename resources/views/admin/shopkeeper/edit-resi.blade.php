@extends('admin.layouts.app')

@section('title', 'Update Nomor Resi - ' . $pengiriman->kode_transaksi)

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
                    <a href="#">@yield('title')</a>
                </li>
            </ul>
        </div>
            <div class="row justify-content-center">
                <div class="col-lg-12">
                    <div class="card shadow-sm">
                        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><i class="fas fa-edit me-2"></i>Update Nomor Resi</h5>
                            <a href="{{ route('admin.pengiriman.show', $pengiriman->id_pengiriman) }}"
                                class="btn btn-light btn-sm">
                                <i class="fas fa-arrow-left me-1"></i>Kembali
                            </a>
                        </div>

                        <div class="card-body">
                            <!-- Info Pengiriman -->
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <h6 class="text-muted mb-3">INFORMASI PENGIRIMAN</h6>
                                    <table class="table table-borderless table-sm">
                                        <tr>
                                            <td class="fw-bold">Kode Transaksi:</td>
                                            <td>{{ $pengiriman->kode_transaksi }}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">Ekspedisi:</td>
                                            <td>
                                                <strong>{{ strtoupper($pengiriman->ekspedisi) }}</strong>
                                                <br>
                                                <small class="text-muted">{{ $pengiriman->layanan_ekspedisi }}</small>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">Status:</td>
                                            <td>
                                                <span
                                                    class="badge bg-secondary">{{ $pengiriman->status_pengiriman_text }}</span>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <h6 class="text-muted mb-3">INFORMASI PELANGGAN</h6>
                                    <table class="table table-borderless table-sm">
                                        <tr>
                                            <td class="fw-bold">Nama:</td>
                                            <td>{{ $pengiriman->transaksi->pelanggan->nama_pelanggan ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">No. HP:</td>
                                            <td>{{ $pengiriman->transaksi->pelanggan->no_hp ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">Alamat:</td>
                                            <td>
                                                @if($pengiriman->transaksi->alamat)
                                                    {{ $pengiriman->transaksi->alamat->kota }},
                                                    {{ $pengiriman->transaksi->alamat->provinsi }}
                                                @else
                                                    N/A
                                                @endif
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>

                            <hr>

                            <!-- Form Update Resi -->
                            <form action="{{ route('admin.pengiriman.update-resi', $pengiriman->id_pengiriman) }}"
                                method="POST">
                                @csrf
                                @method('PUT')

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="nomor_resi" class="form-label fw-bold">
                                                <i class="fas fa-barcode me-1"></i>Nomor Resi *
                                            </label>
                                            <input type="text" class="form-control @error('nomor_resi') is-invalid @enderror"
                                                id="nomor_resi" name="nomor_resi"
                                                value="{{ old('nomor_resi', $pengiriman->nomor_resi) }}"
                                                placeholder="Masukkan nomor resi" required>
                                            @error('nomor_resi')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            <div class="form-text">
                                                <i class="fas fa-info-circle me-1"></i>
                                                Pastikan nomor resi sudah benar sebelum menyimpan
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="ekspedisi_info" class="form-label fw-bold">Ekspedisi</label>
                                            <input type="text" class="form-control" id="ekspedisi_info"
                                                value="{{ strtoupper($pengiriman->ekspedisi) }} - {{ $pengiriman->layanan_ekspedisi }}"
                                                readonly>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="catatan_pengiriman" class="form-label fw-bold">
                                        <i class="fas fa-sticky-note me-1"></i>Catatan Pengiriman
                                    </label>
                                    <textarea class="form-control @error('catatan_pengiriman') is-invalid @enderror"
                                        id="catatan_pengiriman" name="catatan_pengiriman" rows="3"
                                        placeholder="Tambahkan catatan jika diperlukan">{{ old('catatan_pengiriman', $pengiriman->catatan_pengiriman) }}</textarea>
                                    @error('catatan_pengiriman')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Warning Info -->
                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    <strong>Perhatian:</strong>
                                    <ul class="mb-0 mt-2">
                                        <li>Setelah nomor resi disimpan, sistem akan otomatis mulai tracking pengiriman</li>
                                        <li>Pastikan nomor resi sudah benar dan sesuai dengan ekspedisi yang dipilih</li>
                                        {{-- <li>Status pengiriman akan berubah menjadi
                                             "Sedang Dikemas"</li> --}}
                                    </ul>
                                </div>

                                <!-- Buttons -->
                                <div class="d-flex justify-content-between">
                                    <a href="{{ route('admin.pengiriman.show', $pengiriman->id_pengiriman) }}"
                                        class="btn btn-secondary">
                                        <i class="fas fa-times me-1"></i>Batal
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-1"></i>Simpan & Mulai Tracking
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Tips Card -->
                    <div class="card shadow-sm mt-3">
                        <div class="card-header bg-info text-white">
                            <h6 class="mb-0"><i class="fas fa-lightbulb me-2"></i>Tips Input Nomor Resi</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <h6 class="text-primary">Format Nomor Resi:</h6>
                                    <ul class="small mb-0">
                                        <li><strong>JNE:</strong> 8912345678901234</li>
                                        <li><strong>POS:</strong> EE123456789ID</li>
                                        <li><strong>TIKI:</strong> 030123456789</li>
                                        <li><strong>J&T:</strong> JP1234567890</li>
                                    </ul>
                                </div>
                                {{-- <div class="col-md-6">
                                    <h6 class="text-primary">Cara Mendapatkan:</h6>
                                    <ul class="small mb-0">
                                        <li>Lihat pada struk pickup dari kurir</li>
                                        <li>Cek email konfirmasi dari ekspedisi</li>
                                        <li>Login ke sistem ekspedisi (jika ada)</li>
                                        <li>Hubungi customer service ekspedisi</li>
                                    </ul>
                                </div> --}}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
@endsection

@push('scripts')
    <script>
        // Auto format nomor resi berdasarkan ekspedisi
        document.getElementById('nomor_resi').addEventListener('input', function (e) {
            let value = e.target.value.toUpperCase();
            e.target.value = value;
        });

        // Validasi sebelum submit
        document.querySelector('form').addEventListener('submit', function (e) {
            const nomorResi = document.getElementById('nomor_resi').value;

            if (nomorResi.length < 8) {
                e.preventDefault();
                alert('Nomor resi minimal 8 karakter!');
                return false;
            }

            if (!confirm('Yakin ingin menyimpan nomor resi ini? Setelah disimpan, sistem akan mulai tracking otomatis.')) {
                e.preventDefault();
                return false;
            }
        });
    </script>
@endpush