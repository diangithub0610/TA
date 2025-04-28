<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cek Ongkir dari Toko</title>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>

<body class="bg-gray-100 p-8">
    <div class="max-w-2xl mx-auto bg-white p-8 rounded-lg shadow-md">
        <h1 class="text-2xl font-bold mb-6 text-center">Cek Ongkos Kirim dari Toko</h1>

        <div class="mb-4">
            <label class="block mb-2">Lokasi Toko Asal</label>
            <input type="text" value="{{ $toko->nama_toko }} - {{ $toko->alamat }}"
                class="w-full p-2 border bg-gray-100" readonly>
        </div>

        @if ($savedAddresses->count() > 0)
            <div class="mb-4">
                <label class="block mb-2">Pilih Alamat Tujuan</label>
                <select id="savedAddressSelect" class="w-full p-2 border">
                    <option value="">Pilih Alamat Tersimpan</option>
                    @foreach ($savedAddresses as $address)
                        <option value="{{ $address->id_alamat }}" data-rajaongkir-id="{{ $address->rajaongkir_id }}"
                            data-full-address="{{ $address->full_address }}">
                            {{ $address->nama_penerima }} - {{ $address->alamat_lengkap }}, {{ $address->kota }}
                        </option>
                    @endforeach
                </select>
            </div>
        @endif

        <div class="mb-4">
            <label class="block mb-2">Tujuan</label>
            <div class="flex">
                <input type="text" id="destinationSearch" placeholder="Cari lokasi tujuan"
                    class="w-full p-2 border rounded-l" readonly>
                <button id="destinationSearchBtn" class="bg-blue-500 text-white px-4 rounded-r">
                    {{ $savedAddresses->count() > 0 ? 'Ganti' : 'Cari' }}
                </button>
            </div>
            <select id="destinationResults" class="w-full p-2 border mt-2" size="5"
                style="display:none;"></select>
        </div>

        <div class="mb-4">
            <label class="block mb-2">Berat (gram)</label>
            <input type="number" id="weight" placeholder="Masukkan berat" class="w-full p-2 border" value="1000">
        </div>

        <button id="calculateBtn" class="w-full bg-green-500 text-white p-2 rounded">
            Hitung Ongkos Kirim
        </button>

        <div id="resultContainer" class="mt-6 hidden">
            <h2 class="text-xl font-bold mb-4">Hasil Perhitungan</h2>
            <table id="resultTable" class="w-full border">
                <thead>
                    <tr class="bg-gray-200">
                        <th class="p-2 border">Kurir</th>
                        <th class="p-2 border">Service</th>
                        <th class="p-2 border">Deskripsi</th>
                        <th class="p-2 border">Biaya</th>
                        <th class="p-2 border">Estimasi</th>
                    </tr>
                </thead>
                <tbody id="resultBody"></tbody>
            </table>
        </div>

    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const savedAddressSelect = document.getElementById('savedAddressSelect');
            const destinationSearch = document.getElementById('destinationSearch');
            const destinationSearchBtn = document.getElementById('destinationSearchBtn');
            const destinationResults = document.getElementById('destinationResults');

            const weightInput = document.getElementById('weight');
            const calculateBtn = document.getElementById('calculateBtn');

            const resultContainer = document.getElementById('resultContainer');
            const resultBody = document.getElementById('resultBody');

            let selectedDestination = null;

            // Handling saved address selection
            if (savedAddressSelect) {
                savedAddressSelect.addEventListener('change', function() {
                    const selectedOption = this.options[this.selectedIndex];

                    if (selectedOption.value) {
                        const rajaongkirId = selectedOption.dataset.rajaongkirId;
                        const fullAddress = selectedOption.dataset.fullAddress;

                        // Set destination search input
                        destinationSearch.value = fullAddress;
                        selectedDestination = rajaongkirId;

                        // Hide location search results
                        destinationResults.style.display = 'none';
                    }
                });
            }

            // Location search functionality
            function searchDestination() {
                destinationResults.innerHTML = '';
                destinationResults.style.display = 'block';

                axios.get('/ongkir/search-destination', {
                        params: {
                            keyword: ''
                        }
                    })
                    .then(response => {
                        response.data.forEach(location => {
                            const option = document.createElement('option');
                            option.value = location.id;
                            option.textContent =
                                `${location.subdistrict_name}, ${location.district_name}, ${location.city_name}, ${location.province_name} (${location.zip_code})`;
                            option.dataset.location = JSON.stringify(location);
                            destinationResults.appendChild(option);
                        });
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Gagal mencari lokasi');
                    });
            }

            destinationSearchBtn.addEventListener('click', searchDestination);

            destinationResults.addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];
                selectedDestination = selectedOption.value;
                destinationSearch.value = selectedOption.textContent;

                // Hide results after selection
                this.style.display = 'none';
            });

            calculateBtn.addEventListener('click', function() {
                if (!selectedDestination) {
                    alert('Pilih lokasi tujuan terlebih dahulu');
                    return;
                }

                const weight = weightInput.value;

                axios.post('/ongkir/calculate-cost', {
                        destination: selectedDestination,
                        weight: weight
                    })
                    .then(response => {
                        resultBody.innerHTML = '';
                        if (response.data.length > 0) {
                            response.data.forEach(service => {
                                const row = document.createElement('tr');
                                row.innerHTML = `
                        <td class="p-2 border">${service.name}</td>
                        <td class="p-2 border">${service.code}</td>
                        <td class="p-2 border">${service.description}</td>
                        <td class="p-2 border">Rp ${service.cost.toLocaleString()}</td>
                        <td class="p-2 border">${service.etd} hari</td>
                    `;
                                resultBody.appendChild(row);
                            });
                            resultContainer.classList.remove('hidden');
                        } else {
                            alert('Tidak ada layanan pengiriman tersedia');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Gagal menghitung ongkos kirim');
                    });
            });
        });
    </script>

</body>

</html>
