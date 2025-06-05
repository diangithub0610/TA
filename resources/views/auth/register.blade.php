<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://ai-public.creatie.ai/gen_page/tailwind-custom.css" rel="stylesheet">
    <script
        src="https://cdn.tailwindcss.com/3.4.5?plugins=forms@0.5.7,typography@0.5.13,aspect-ratio@0.4.2,container-queries@0.1.1">
    </script>
    <script src="https://ai-public.creatie.ai/gen_page/tailwind-config.min.js" data-color="#FE5900"
        data-border-radius='medium'></script>
</head>

<body class="min-h-screen bg-custom">
    <div class="min-h-screen flex items-center justify-center p-4">
        <div class="bg-white rounded-xl shadow-lg w-full max-w-md p-8">
            <div class="flex justify-center mb-8">
                <img src="{{ asset('img/logo/logo-wf-panjang.png') }}" alt="navbar brand" class="h-10 w-auto object-contain" />
            </div>

            <h2 class="text-2xl font-bold text-center text-gray-800 mb-8">Masuk ke Akun Anda</h2>


            <h2 class="text-2xl font-bold text-center text-gray-800 mb-6">Daftar Akun</h2>

            <div class="mb-6">
                <div class="border-b border-gray-200">
                    <div class="flex justify-center -mb-px">
                        <button type="button" data-type="pelanggan"
                            class="account-type-btn py-4 px-6 text-sm font-medium text-center border-b-2 border-custom text-gray-900 rounded-t-lg active">
                            Pelanggan
                        </button>
                        <button type="button" data-type="reseller"
                            class="account-type-btn py-4 px-6 text-sm font-medium text-center border-b-2 border-transparent text-gray-500 hover:text-gray-700 rounded-t-lg">
                            Reseller
                        </button>
                    </div>
                </div>
            </div>

            <div id="pelanggan-info" class="bg-blue-50 text-blue-800 p-4 rounded-md mb-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-info-circle text-blue-600"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium">
                            Pendaftaran pelanggan <span class="font-bold">GRATIS</span>
                        </p>
                    </div>
                </div>
            </div>

            @php
                $toko = \App\Models\Toko::first();
                $biayaReseller = $toko ? number_format($toko->biaya_pendaftaran_reseller, 0, ',', '.') : '100.000';
            @endphp

            <div id="reseller-info" class="bg-purple-50 text-purple-800 p-4 rounded-md mb-6 hidden">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-info-circle text-purple-600"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium">
                            Pendaftaran reseller dikenakan biaya Rp {{ $biayaReseller }},-
                        </p>
                    </div>
                </div>
            </div>

            <form action="{{ route('register.store') }}" method="POST" class="space-y-6">
                @csrf
                <input type="hidden" name="tipe_akun" id="tipe_akun" value="pelanggan">

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Nama Lengkap</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-500">
                            <i class="fas fa-user"></i>
                        </span>
                        <input type="text" name="nama_pelanggan" required value="{{ old('nama_pelanggan') }}"
                            class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md focus:ring-custom focus:border-custom"
                            placeholder="Masukkan nama lengkap">
                    </div>
                    @error('nama_pelanggan')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Nomor HP</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-500">
                            <i class="fas fa-phone"></i>
                        </span>
                        <input type="text" name="no_hp" required value="{{ old('no_hp') }}"
                            class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md focus:ring-custom focus:border-custom"
                            placeholder="Masukkan nomor HP">
                    </div>
                    @error('no_hp')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-500">
                            <i class="fas fa-envelope"></i>
                        </span>
                        <input type="email" name="email" required value="{{ old('email') }}"
                            class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md focus:ring-custom focus:border-custom"
                            placeholder="Masukkan email">
                    </div>
                    @error('email')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Username</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-500">
                            <i class="fas fa-at"></i>
                        </span>
                        <input type="text" name="username" required value="{{ old('username') }}"
                            class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md focus:ring-custom focus:border-custom"
                            placeholder="Masukkan username">
                    </div>
                    @error('username')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Alamat</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-500">
                            <i class="fas fa-map-marker-alt"></i>
                        </span>
                        <textarea name="alamat_pengguna" required
                            class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md focus:ring-custom focus:border-custom"
                            placeholder="Masukkan alamat lengkap" rows="3">{{ old('alamat_pengguna') }}</textarea>
                    </div>
                    @error('alamat_pengguna')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Kata Sandi</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-500">
                            <i class="fas fa-lock"></i>
                        </span>
                        <input type="password" name="password" required
                            class="block w-full pl-10 pr-10 py-2 border border-gray-300 rounded-md focus:ring-custom focus:border-custom"
                            placeholder="Masukkan kata sandi">
                        <button type="button"
                            class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-500 hover:text-gray-700"
                            onclick="togglePassword(this)">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    @error('password')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Konfirmasi Kata Sandi</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-500">
                            <i class="fas fa-lock"></i>
                        </span>
                        <input type="password" name="password_confirmation" required
                            class="block w-full pl-10 pr-10 py-2 border border-gray-300 rounded-md focus:ring-custom focus:border-custom"
                            placeholder="Konfirmasi kata sandi">
                        <button type="button"
                            class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-500 hover:text-gray-700"
                            onclick="togglePassword(this)">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>

                <button type="submit"
                    class="w-full !rounded-button bg-custom text-white py-2 px-4 hover:bg-custom/90 focus:outline-none focus:ring-2 focus:ring-custom focus:ring-offset-2">
                    Daftar
                </button>

                <div class="text-center mt-4">
                    <p class="text-sm text-gray-600">
                        Sudah punya akun?
                        <a href="{{ route('login') }}" class="text-custom hover:text-custom-dark font-medium">
                            Masuk
                        </a>
                    </p>
                </div>
            </form>

        </div>
    </div>

    <footer class="bottom-0 w-full py-4 text-center text-white text-sm">
        <p>© 2024 Semua hak dilindungi</p>
        <div class="mt-2">
            <a href="#" class="hover:underline mx-2">Syarat & Ketentuan</a> <a href="#"
                class="hover:underline mx-2">Kebijakan Privasi</a>
        </div>
    </footer>

    <script>
        function togglePassword(button) {
            const input = button.previousElementSibling;
            const icon = button.querySelector('i');

            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            const buttons = document.querySelectorAll('.account-type-btn');
            const tipeAkunInput = document.getElementById('tipe_akun');
            const pelangganInfo = document.getElementById('pelanggan-info');
            const resellerInfo = document.getElementById('reseller-info');

            buttons.forEach(button => {
                button.addEventListener('click', function() {
                    // Remove active class from all buttons
                    buttons.forEach(btn => {
                        btn.classList.remove('active', 'text-gray-900', 'border-custom');
                        btn.classList.add('text-gray-500', 'border-transparent');
                    });

                    // Add active class to clicked button
                    this.classList.add('active', 'text-gray-900', 'border-custom');
                    this.classList.remove('text-gray-500', 'border-transparent');

                    // Update hidden input
                    const type = this.getAttribute('data-type');
                    tipeAkunInput.value = type;

                    // Toggle info boxes
                    if (type === 'pelanggan') {
                        pelangganInfo.classList.remove('hidden');
                        resellerInfo.classList.add('hidden');
                    } else {
                        pelangganInfo.classList.add('hidden');
                        resellerInfo.classList.remove('hidden');
                    }
                });
            });
        });
    </script>
</body>

</html>
