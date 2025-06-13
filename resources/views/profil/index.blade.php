@extends('pelanggan.layouts.app')
@section('title', 'Checkout')
@section('content')
    <div class="flex min-h-screen">

        @include('profil.sidebar')

        <!-- Main Content -->
        <main class="flex-1 p-8 pt-0">
            <div class="max-w-3xl mx-auto pt-8">
                <h1 class="text-2xl font-bold mb-8">Profil Saya</h1>

                <div class="bg-white rounded-lg shadow-sm p-6">
                    {{-- resources/views/profil/index.blade.php --}}
                    <div class="flex flex-col items-center mb-8">
                        <div class="w-32 h-32 rounded-full overflow-hidden mb-4 relative group">
                            @if ($pelanggan->foto_profil)
                                <img src="{{ asset('storage/profil/' . $pelanggan->foto_profil) }}" alt="Profile"
                                    class="w-full h-full object-cover">
                            @else
                                <div
                                    class="w-full h-full bg-gray-200 flex items-center justify-center text-3xl font-bold text-gray-600">
                                    {{ strtoupper(substr($pelanggan->nama_pelanggan, 0, 1)) }}
                                </div>
                            @endif
                            <form method="POST" action="{{ route('profil.update.foto') }}" enctype="multipart/form-data"
                                class="absolute inset-0 bg-black/50 hidden group-hover:flex items-center justify-center cursor-pointer">
                                @csrf
                                <label class="cursor-pointer">
                                    <i class="ri-camera-line text-white text-xl"></i>
                                    <input type="file" name="foto_profil" class="hidden" onchange="this.form.submit()">
                                </label>
                            </form>
                        </div>
                        <h2 class="text-xl font-medium">{{ '(' . $pelanggan->id_pelanggan . ')'}}</h2>
                    </div>

                    <form method="POST" action="{{ route('profil.update') }}" class="space-y-6">
                        @csrf
                        @method('PUT')
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Nama -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Nama</label>
                                <input type="text" name="nama_pelanggan" value="{{ $pelanggan->nama_pelanggan }}"
                                    class="w-full px-4 py-2 border border-gray-200 rounded text-sm focus:outline-none focus:ring-1 focus:ring-primary">
                            </div>

                            <!-- Email -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                                <input type="email" name="email" value="{{ $pelanggan->email }}"
                                    class="w-full px-4 py-2 border border-gray-200 rounded text-sm focus:outline-none focus:ring-1 focus:ring-primary">
                            </div>

                            <!-- No HP -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Nomor Telepon</label>
                                <input type="text" name="no_hp" value="{{ $pelanggan->no_hp }}"
                                    class="w-full px-4 py-2 border border-gray-200 rounded text-sm focus:outline-none focus:ring-1 focus:ring-primary">
                            </div>


                        </div>

                        <div class="flex justify-end">
                            <button type="submit"
                                class="bg-primary text-white px-6 py-2 rounded-button font-medium hover:bg-primary/90 whitespace-nowrap">
                                Simpan Perubahan
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </main>

    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const form = document.querySelector("form");
            form.addEventListener("submit", function(e) {
                e.preventDefault();
                const notification = document.createElement("div");
                notification.className =
                    "fixed bottom-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg";
                notification.textContent = "Profil berhasil diperbarui";
                document.body.appendChild(notification);
                setTimeout(() => notification.remove(), 3000);
            });
        });
    </script>
@endsection
