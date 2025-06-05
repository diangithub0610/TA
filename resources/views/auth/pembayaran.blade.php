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
            <div class="max-w-md mx-auto bg-white rounded-lg shadow-md overflow-hidden">
                <div class="py-4 px-6">
                    <h2 class="text-2xl font-bold text-center text-gray-800 mb-6">Pembayaran Pendaftaran</h2>

                    <div class="bg-gray-50 rounded-lg p-6 mb-6">
                        <div class="mb-4">
                            <h3 class="text-lg font-medium text-gray-900">Detail Pendaftaran</h3>
                            <p class="text-sm text-gray-600">ID Pendaftaran: {{ $pendaftaran->id_pendaftaran }}</p>
                        </div>

                        <div class="mb-4">
                            <h4 class="text-base font-medium text-gray-800">Data Pelanggan</h4>
                            <p class="text-sm text-gray-600">Nama: {{ $pendaftaran->pelanggan->nama_pelanggan }}</p>
                            <p class="text-sm text-gray-600">Email: {{ $pendaftaran->pelanggan->email }}</p>
                        </div>

                        <div class="mb-4">
                            <h4 class="text-base font-medium text-gray-800">Detail Pembayaran</h4>
                            <p class="text-sm text-gray-600">Tipe Akun: {{ ucfirst($pendaftaran->tipe_akun) }}</p>
                            <p class="text-sm text-gray-600">Jumlah: Rp
                                {{ number_format($pendaftaran->biaya_pendaftaran, 0, ',', '.') }},-</p>
                            <p class="text-sm text-gray-600">Status:
                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                {{ $pendaftaran->status_pembayaran == 'pending'
                                    ? 'bg-yellow-100 text-yellow-800'
                                    : ($pendaftaran->status_pembayaran == 'berhasil'
                                        ? 'bg-green-100 text-green-800'
                                        : 'bg-red-100 text-red-800') }}">
                                    {{ ucfirst($pendaftaran->status_pembayaran) }}
                                </span>
                            </p>
                        </div>

                        <div class="border-t border-gray-200 pt-4">
                            @if ($pendaftaran->biaya_pendaftaran > 0)
                                <p class="text-sm text-gray-500 mb-2">Klik tombol di bawah untuk melanjutkan pembayaran
                                </p>
                                <button id="pay-button"
                                    class="w-full bg-custom text-white py-2 px-4 rounded-button hover:bg-custom/90 focus:outline-none focus:ring-2 focus:ring-custom focus:ring-offset-2">
                                    Bayar Sekarang
                                </button>
                            @else
                                <p class="text-sm text-gray-500 mb-2">Pendaftaran Anda telah berhasil!</p>
                                <a href="{{ route('login') }}"
                                    class="block w-full text-center bg-custom text-white py-2 px-4 rounded-button hover:bg-custom/90 focus:outline-none focus:ring-2 focus:ring-custom focus:ring-offset-2">
                                    Lanjut ke Login
                                </a>
                            @endif
                        </div>
                    </div>

                    <div class="text-center mt-4">
                        <p class="text-sm text-gray-600">
                            <i class="fas fa-shield-alt text-gray-500 mr-1"></i>
                            Pembayaran aman dengan Midtrans
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer class="bottom-0 w-full py-4 text-center text-white text-sm">
        <p>© 2024 Semua hak dilindungi</p>
        <div class="mt-2">
            <a href="#" class="hover:underline mx-2">Syarat & Ketentuan</a> <a href="#"
                class="hover:underline mx-2">Kebijakan Privasi</a>
        </div>
    </footer>

    <script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ config('midtrans.client_key') }}">
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const payButton = document.getElementById('pay-button');
            const snapToken = "{{ $pendaftaran->snap_token }}";

            payButton.addEventListener('click', function() {
                // Disable the button to prevent multiple clicks
                this.disabled = true;

                // Open Snap payment popup
                snap.pay(snapToken, {
                    onSuccess: function(result) {
                        window.location.href = "{{ route('login') }}?status=success";
                    },
                    onPending: function(result) {
                        window.location.href = "{{ route('login') }}?status=pending";
                    },
                    onError: function(result) {
                        window.location.href = "{{ route('login') }}?status=error";
                        payButton.disabled = false;
                    },
                    onClose: function() {
                        payButton.disabled = false;
                    }
                });
            });

            // If payment status is already successful, redirect to login
            @if ($pendaftaran->status_pembayaran == 'berhasil')
                window.location.href = "{{ route('login') }}?status=success";
            @endif
        });
    </script>

</body>

</html>
