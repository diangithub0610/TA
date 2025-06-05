@extends('pelanggan.layouts.app')

@section('title', 'Tulis Ulasan - ' . $barang->nama_barang)

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Tulis Ulasan</h5>
                        <a href="{{ route('ulasan.index', $barang->kode_barang) }}" class="btn btn-sm btn-secondary">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <!-- Informasi Barang -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            @if($barang->foto_barang)
                                <img src="{{ asset('storage/' . $barang->foto_barang) }}" 
                                     alt="{{ $barang->nama_barang }}" 
                                     class="img-fluid rounded">
                            @else
                                <div class="bg-light rounded d-flex align-items-center justify-content-center" 
                                     style="height: 120px;">
                                    <i class="fas fa-image fa-3x text-muted"></i>
                                </div>
                            @endif
                        </div>
                        <div class="col-md-9">
                            <h6 class="text-primary">{{ $barang->nama_barang }}</h6>
                            <p class="text-muted mb-1">Kode: {{ $barang->kode_barang }}</p>
                            <p class="text-success font-weight-bold">
                                Rp {{ number_format($barang->harga, 0, ',', '.') }}
                            </p>
                        </div>
                    </div>

                    <hr>

                    <!-- Form Ulasan -->
                    <form action="{{ route('ulasan.store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="kode_barang" value="{{ $barang->kode_barang }}">
                        <input type="hidden" name="transaksi_id" value="{{ $transaksi->id }}">

                        <!-- Rating -->
                        <div class="form-group mb-4">
                            <label class="form-label">Rating <span class="text-danger">*</span></label>
                            <div class="rating-input">
                                <div class="star-rating">
                                    @for($i = 5; $i >= 1; $i--)
                                        <input type="radio" name="rating" value="{{ $i }}" id="star{{ $i }}" 
                                               {{ old('rating') == $i ? 'checked' : '' }}>
                                        <label for="star{{ $i }}" class="star">★</label>
                                    @endfor
                                </div>
                                <div class="rating-text mt-2">
                                    <span id="rating-description" class="text-muted">Pilih rating Anda</span>
                                </div>
                            </div>
                            @error('rating')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Komentar -->
                        <div class="form-group mb-4">
                            <label for="komentar" class="form-label">Komentar</label>
                            <textarea name="komentar" id="komentar" class="form-control @error('komentar') is-invalid @enderror" 
                                      rows="5" placeholder="Bagikan pengalaman Anda menggunakan produk ini...">{{ old('komentar') }}</textarea>
                            <div class="form-text">Opsional - Maksimal 1000 karakter</div>
                            @error('komentar')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Tombol Submit -->
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('ulasan.index', $barang->kode_barang) }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Batal
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-paper-plane"></i> Kirim Ulasan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.star-rating {
    direction: rtl;
    display: inline-block;
    font-size: 2rem;
}

.star-rating input[type="radio"] {
    display: none;
}

.star-rating label {
    color: #ddd;
    cursor: pointer;
    display: inline-block;
    font-size: 2rem;
    padding: 0 2px;
    transition: color 0.2s;
}

.star-rating label:hover,
.star-rating label:hover ~ label,
.star-rating input[type="radio"]:checked ~ label {
    color: #ffc107;
}

.star-rating label:hover {
    transform: scale(1.1);
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const ratingInputs = document.querySelectorAll('input[name="rating"]');
    const ratingDescription = document.getElementById('rating-description');
    
    const descriptions = {
        1: '⭐ Sangat Buruk',
        2: '⭐⭐ Buruk', 
        3: '⭐⭐⭐ Cukup',
        4: '⭐⭐⭐⭐ Baik',
        5: '⭐⭐⭐⭐⭐ Sangat Baik'
    };

    ratingInputs.forEach(input => {
        input.addEventListener('change', function() {
            ratingDescription.textContent = descriptions[this.value];
            ratingDescription.className = 'text-warning fw-bold';
        });
    });

    // Set initial description if there's an old value
    const checkedRating = document.querySelector('input[name="rating"]:checked');
    if (checkedRating) {
        ratingDescription.textContent = descriptions[checkedRating.value];
        ratingDescription.className = 'text-warning fw-bold';
    }
});
</script>
@endsection