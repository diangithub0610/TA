@extends('admin.layouts.app')
@section('title', 'Pengaturan Toko')
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
                    <a href="{{ route('admin.toko.index') }}">@yield('title')</a>
                </li>
            </ul>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title mb-0"></h4>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('admin.toko.save') }}" method="POST">
                            @csrf

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="nama_toko">Nama Toko</label>
                                        <input type="text" class="form-control" id="nama_toko" name="nama_toko"
                                            value="{{ $toko->nama_toko ?? old('nama_toko') }}" required>
                                    </div>

                                    <div class="form-group">
                                        <label for="alamat">Alamat</label>
                                        <textarea class="form-control" id="alamat" name="alamat" rows="3" required>{{ $toko->alamat ?? old('alamat') }}</textarea>
                                    </div>

                                    <div class="form-group">
                                        <label for="search_location">Cari Lokasi (Kota/Kecamatan/Kelurahan/Kode Pos)</label>
                                        <div class="input-group">
                                            <input type="text" class="form-control" id="search_location"
                                                placeholder="Ketik minimal 3 huruf...">
                                            <div class="input-group-append">
                                                <button class="btn btn-primary" type="button" id="btn_search_location">
                                                    <i class="fas fa-search"></i> Cari
                                                </button>
                                            </div>
                                        </div>
                                        <div class="mt-2" id="search_results" style="display:none;">
                                            <select class="form-control" id="location_results" size="5">
                                                <!-- Hasil pencarian akan ditampilkan di sini -->
                                            </select>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label for="provinsi">Provinsi</label>
                                        <input type="text" class="form-control" id="provinsi" name="provinsi"
                                            value="{{ $toko->provinsi ?? old('provinsi') }}" required readonly>
                                    </div>

                                    <div class="form-group">
                                        <label for="kota">Kota/Kabupaten</label>
                                        <input type="text" class="form-control" id="kota" name="kota"
                                            value="{{ $toko->kota ?? old('kota') }}" required readonly>
                                    </div>

                                    <div class="form-group">
                                        <label for="kecamatan">Kecamatan</label>
                                        <input type="text" class="form-control" id="kecamatan" name="kecamatan"
                                            value="{{ $toko->kecamatan ?? old('kecamatan') }}" readonly>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="kelurahan">Kelurahan/Desa</label>
                                        <input type="text" class="form-control" id="kelurahan" name="kelurahan"
                                            value="{{ $toko->kelurahan ?? old('kelurahan') }}" readonly>
                                    </div>

                                    <div class="form-group">
                                        <label for="kode_pos">Kode Pos</label>
                                        <input type="text" class="form-control" id="kode_pos" name="kode_pos"
                                            value="{{ $toko->kode_pos ?? old('kode_pos') }}" required readonly>
                                    </div>

                                    <div class="form-group">
                                        <label for="rajaongkir_id">ID Raja Ongkir</label>
                                        <input type="text" class="form-control" id="rajaongkir_id" name="rajaongkir_id"
                                            value="{{ $toko->rajaongkir_id ?? old('rajaongkir_id') }}" required readonly>
                                        <small class="form-text text-muted">ID ini akan terisi otomatis saat memilih
                                            lokasi</small>
                                    </div>

                                    <div class="form-group">
                                        <label for="no_telp">Nomor Telepon</label>
                                        <input type="text" class="form-control" id="no_telp" name="no_telp"
                                            value="{{ $toko->no_telp ?? old('no_telp') }}" required>
                                    </div>

                                    <div class="form-group">
                                        <label for="email">Email</label>
                                        <input type="email" class="form-control" id="email" name="email"
                                            value="{{ $toko->email ?? old('email') }}" required>
                                    </div>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary">Simpan Pengaturan</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('search_location');
            const searchButton = document.getElementById('btn_search_location');
            const searchResults = document.getElementById('search_results');
            const locationResults = document.getElementById('location_results');

            // Input fields
            const inputFields = {
                provinsi: document.getElementById('provinsi'),
                kota: document.getElementById('kota'),
                kecamatan: document.getElementById('kecamatan'),
                kelurahan: document.getElementById('kelurahan'),
                kodePos: document.getElementById('kode_pos'),
                rajaOngkirId: document.getElementById('rajaongkir_id')
            };

            // Search location when button is clicked
            searchButton.addEventListener('click', searchLocation);

            // Also search when Enter key is pressed
            searchInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    searchLocation();
                }
            });

            // Show results and populate form when a location is selected
            locationResults.addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];

                if (!selectedOption || !selectedOption.dataset.location) {
                    return;
                }

                try {
                    const locationData = JSON.parse(selectedOption.dataset.location);

                    // Safely populate form fields
                    inputFields.provinsi.value = locationData.province_name || '';
                    inputFields.kota.value = locationData.city_name || '';
                    inputFields.kecamatan.value = locationData.district_name || '';
                    inputFields.kelurahan.value = locationData.subdistrict_name || '';
                    inputFields.kodePos.value = locationData.zip_code || '';
                    inputFields.rajaOngkirId.value = locationData.id || '';
                } catch (error) {
                    console.error('Error parsing location data:', error);
                    alert('Terjadi kesalahan saat memilih lokasi');
                }
            });

            function searchLocation() {
                const keyword = searchInput.value.trim();

                if (keyword.length < 3) {
                    alert('Silakan masukkan minimal 3 karakter untuk pencarian');
                    return;
                }

                // Show loading indicator
                locationResults.innerHTML = '<option>Mencari...</option>';
                searchResults.style.display = 'block';

                // Fetch results from Raja Ongkir
                fetch(`/admin/toko/search-destination?keyword=${encodeURIComponent(keyword)}`, {
                        method: 'GET',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.json();
                    })
                    .then(data => {
                        locationResults.innerHTML = '';

                        if (!data || data.length === 0) {
                            locationResults.innerHTML = '<option>Tidak ditemukan hasil</option>';
                            return;
                        }

                        // Populate results
                        data.forEach(location => {
                            const option = document.createElement('option');

                            // Construct display text with fallback values
                            const displayParts = [
                                location.subdistrict_name,
                                location.district_name,
                                location.city_name,
                                location.province_name,
                                location.zip_code ? `(${location.zip_code})` : ''
                            ].filter(Boolean);

                            option.textContent = displayParts.join(', ');

                            // Store full location data for later use
                            option.dataset.location = JSON.stringify(location);
                            locationResults.appendChild(option);
                        });

                        // Automatically show results if there are any
                        if (data.length > 0) {
                            locationResults.selectedIndex = 0;
                            locationResults.dispatchEvent(new Event('change'));
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        locationResults.innerHTML = '<option>Error saat mengambil data</option>';
                        alert('Gagal mengambil data lokasi. Silakan coba lagi.');
                    });
            }
        });
    </script>
@endpush
