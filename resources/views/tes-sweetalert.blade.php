@extends('pelanggan.layouts.app')
@section('title', 'Dashboard')
@section('content')

    <h1 class="text-2xl font-bold mb-6 mt-20">Test SweetAlert di Laravel</h1>

    <form action="{{ route('trigger.sweetalert') }}" method="POST">
        @csrf
        <button class="bg-custom text-white px-4 py-2 !rounded-button hover:bg-custom/90" type="submit">Tes
            SweetAlert</button>
    </form>

    @if (session('success'))
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script>
            Swal.fire({
                title: 'Berhasil!',
                text: '{{ session('success') }}',
                icon: 'success',
                timer: 2000,
                showConfirmButton: false
            });
        </script>
    @endif
@endsection
