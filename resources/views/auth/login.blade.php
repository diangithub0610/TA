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


            <form action="{{ route('login') }}" method="POST" class="space-y-6">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Email atau Username</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-500">
                            <i class="fas fa-user"></i>
                        </span>
                        <input type="text" name="login" required value="{{ old('login') }}"
                            class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md focus:ring-custom focus:border-custom"
                            placeholder="Masukkan email atau username">
                    </div>
                    @error('login')
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

                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <input type="checkbox" id="remember" name="remember"
                            class="h-4 w-4 text-custom focus:ring-custom border-gray-300 rounded">
                        <label for="remember" class="ml-2 block text-sm text-gray-700">Ingat saya</label>
                    </div>
                    <a href="{{ route('forgot.password') }}"
                        class="text-sm font-medium text-custom hover:text-custom-dark">
                        Lupa kata sandi?
                    </a>
                </div>

                <button type="submit"
                    class="w-full !rounded-button bg-custom text-white py-2 px-4 hover:bg-custom/90 focus:outline-none focus:ring-2 focus:ring-custom focus:ring-offset-2">
                    Masuk
                </button>
            </form>

            <p class="mt-8 text-center text-sm text-gray-600">
                Belum punya akun?
                <a href="{{ route('register') }}" class="font-medium text-custom hover:text-custom-dark">Daftar
                    sekarang</a>
            </p>
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
            const input = button.parentElement.querySelector('input');
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
    </script>
</body>

</html>
