@extends('pelanggan.layouts.app')
@section('title', 'Ubah Password')

@section('content')
<div class="flex min-h-screen">
    @include('profil.sidebar')
    <main class="flex-1 p-8">
        <div class="max-w-3xl mx-auto">
            <!-- Header -->
            <div class="mb-6">
                <h1 class="text-xl font-semibold text-gray-900 mb-2">Ubah Password</h1>
                <p class="text-sm text-gray-600">
                    Untuk keamanan akun Anda, mohon untuk tidak menyebarkan password Anda ke orang lain.
                </p>
            </div>

            <!-- Success / Error -->
            @if (session('success'))
                <div class="mb-4 px-4 py-3 rounded-lg bg-green-100 border border-green-400 text-green-700 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span>{{ session('success') }}</span>
                    </div>
                    <button onclick="this.parentElement.remove()" class="text-green-700 hover:text-green-900">&times;</button>
                </div>
            @endif

            @if (session('error'))
                <div class="mb-4 px-4 py-3 rounded-lg bg-red-100 border border-red-400 text-red-700 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                        <span>{{ session('error') }}</span>
                    </div>
                    <button onclick="this.parentElement.remove()" class="text-red-700 hover:text-red-900">&times;</button>
                </div>
            @endif

            <!-- Form -->
            <form class="space-y-6" action="{{ route('change-password.update') }}" method="POST">
                @csrf
            
                <!-- Password Lama -->
                <div>
                    <label for="current_password" class="block text-sm font-medium text-gray-700 mb-2">Password Lama</label>
                    <div class="relative">
                        <input type="password" id="current_password" name="current_password"
                            class="w-full px-3 py-2 pr-10 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            placeholder="Masukkan password lama">
                        <button type="button" class="absolute inset-y-0 right-0 px-3 flex items-center"
                            onclick="togglePassword('current_password')">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                        </button>
                    </div>
                    @error('current_password')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            
                <!-- Password Baru -->
                <div>
                    <label for="new_password" class="block text-sm font-medium text-gray-700 mb-2">Password Baru</label>
                    <div class="relative">
                        <input type="password" id="new_password" name="new_password"
                            class="w-full px-3 py-2 pr-10 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            placeholder="Masukkan password baru">
                        <button type="button" class="absolute inset-y-0 right-0 px-3 flex items-center"
                            onclick="togglePassword('new_password')">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                        </button>
                    </div>
                    @error('new_password')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            
                <!-- Konfirmasi Password -->
                <div>
                    <label for="confirm_password" class="block text-sm font-medium text-gray-700 mb-2">Konfirmasi Password</label>
                    <div class="relative">
                        <input type="password" id="confirm_password" name="confirm_password"
                            class="w-full px-3 py-2 pr-10 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            placeholder="Konfirmasi password baru">
                        <button type="button" class="absolute inset-y-0 right-0 px-3 flex items-center"
                            onclick="togglePassword('confirm_password')">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                        </button>
                    </div>
                    @error('confirm_password')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            
                <!-- Tombol Submit -->
                <div class="pt-2">
                    <button type="submit"
                        class="w-full bg-blue-500 hover:bg-blue-600 text-white font-medium py-2 px-4 rounded-md transition duration-200 ease-in-out">
                        Konfirmasi
                    </button>
                </div>
            </form>
            
        </div>
    </main>
</div>
@endsection

@push('scripts')
<script>
    function togglePassword(inputId) {
        const input = document.getElementById(inputId);
        const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
        input.setAttribute('type', type);
    }
</script>
@endpush
