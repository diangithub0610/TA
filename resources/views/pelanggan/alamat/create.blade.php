@extends('pelanggan.layouts.app')
@section('content')
    <div class="container mx-auto p-6">
        <h1 class="text-2xl font-bold mb-4">Tambah Alamat Baru</h1>
        <form id="alamatForm" action="{{ route('alamat.store') }}" method="POST"
            class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4">
            @csrf

            <input type="hidden" name="rajaongkir_id" id="rajaongkir_id">

            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2">
                    Cari Lokasi
                </label>
                <div class="flex">
                    <input type="text" id="locationSearch" placeholder="Cari kecamatan, kota, atau provinsi"
                        class="shadow appearance-none border rounded-l w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    <button type="button" id="searchLocationBtn" class="bg-blue-500 text-white px-4 rounded-r">
                        Cari
                    </button>
                </div>
                <select id="locationResults" class="w-full p-2 border mt-2" size="5"></select>
            </div>

            <div class="grid md:grid-cols-2 gap-4">
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="provinsi">
                        Provinsi
                    </label>
                    <input
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                        id="provinsi" name="provinsi" type="text" readonly>
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="kota">
                        Kota/Kabupaten
                    </label>
                    <input
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                        id="kota" name="kota" type="text" readonly>
                </div>
            </div>

            <div class="grid md:grid-cols-2 gap-4">
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="kecamatan">
                        Kecamatan
                    </label>
                    <input
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                        id="kecamatan" name="kecamatan" type="text" readonly>
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="kecamatan">
                        Kecamatan
                    </label>
                    <input
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                        id="kelurahan" name="kelurahan" type="text" readonly>
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="kode_pos">
                        Kode Pos
                    </label>
                    <input
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                        id="kode_pos" name="kode_pos" type="text" readonly>
                </div>
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="alamat_lengkap">
                    Alamat Lengkap
                </label>
                <textarea
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                    id="alamat_lengkap" name="alamat_lengkap" required></textarea>
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="nama_penerima">
                    Nama Penerima
                </label>
                <input
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                    id="nama_penerima" name="nama_penerima" type="text" required>
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="no_hp_penerima">
                    Nomor HP
                </label>
                <input
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                    id="no_hp_penerima" name="no_hp_penerima" type="tel" required>
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="jenis">
                    Jenis Alamat
                </label>
                <select
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                    id="jenis" name="jenis">
                    <option value="rumah">Rumah</option>
                    <option value="kantor">Kantor</option>
                    <option value="lainnya">Lainnya</option>
                </select>
            </div>

            <div class="mb-4">
                <label class="inline-flex items-center">
                    <input type="checkbox" name="is_utama" class="form-checkbox">
                    <span class="ml-2">Jadikan Alamat Utama</span>
                </label>
            </div>

            @if ($errors->any())
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="flex items-center justify-between">
                <button
                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline"
                    type="submit">
                    Simpan Alamat
                </button>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const locationSearch = document.getElementById('locationSearch');
            const searchLocationBtn = document.getElementById('searchLocationBtn');
            const locationResults = document.getElementById('locationResults');

            const provinsiInput = document.getElementById('provinsi');
            const kotaInput = document.getElementById('kota');
            const kecamatanInput = document.getElementById('kecamatan');
            const kelurahanInput = document.getElementById('kelurahan');
            const kodeposInput = document.getElementById('kode_pos');
            const rajaongkirIdInput = document.getElementById('rajaongkir_id');

            function searchLocation() {
                const keyword = locationSearch.value.trim();
                if (keyword.length < 3) {
                    alert('Masukkan minimal 3 karakter');
                    return;
                }

                axios.get('{{ route('alamat.search-location') }}', {
                        params: {
                            keyword: keyword
                        }
                    })
                    .then(response => {
                        locationResults.innerHTML = '';
                        response.data.forEach(location => {
                            const option = document.createElement('option');
                            option.value = location.id;
                            option.textContent = location.full_address;
                            option.dataset.location = JSON.stringify(location);
                            locationResults.appendChild(option);
                        });
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Gagal mencari lokasi');
                    });
            }

            searchLocationBtn.addEventListener('click', searchLocation);

            locationResults.addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];
                const location = JSON.parse(selectedOption.dataset.location);

                // Isi input tersembunyi
                rajaongkirIdInput.value = location.id;

                // Isi input readonly
                provinsiInput.value = location.province;
                kotaInput.value = location.city;
                kecamatanInput.value = location.district;
                kelurahanInput.value = location.subdistrict;
                kodeposInput.value = location.zip_code;

                // Atur lokasi pencarian
                locationSearch.value = location.full_address;
            });
        });
    </script>
@endpush
