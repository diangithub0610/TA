@extends('pelanggan.layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-800 mb-2">Beri Ulasan</h1>
            <p class="text-gray-600">Transaksi: {{ $kode_transaksi }}</p>
        </div>

        <!-- Form Ulasan -->
        <form action="{{ route('ulasan.store') }}" method="POST" class="space-y-6">
            @csrf
            <input type="hidden" name="kode_transaksi" value="{{ $kode_transaksi }}">
            
            @foreach($barangList as $index => $barang)
                <div class="bg-white rounded-lg border border-gray-200 p-6 shadow-sm">
                    <!-- Informasi Barang -->
                    <div class="flex items-start space-x-4 mb-6">
                        <div class="flex-shrink-0">
                            <img src="{{ asset('storage/' . $barang->gambar) }}" 
                                 alt="{{ $barang->nama_barang }}"
                                 class="w-20 h-20 object-cover rounded-lg border">
                        </div>
                        <div class="flex-1">
                            <h3 class="text-lg font-semibold text-gray-800 mb-1">{{ $barang->nama_barang }}</h3>
                            <p class="text-sm text-gray-600">Kode: {{ $barang->kode_barang }}</p>
                        </div>
                    </div>

                    <!-- Cek apakah sudah diulas -->
                    @if(in_array($barang->kode_barang, $existingReviews))
                        <div class="text-center py-4">
                            <div class="inline-flex items-center px-4 py-2 bg-green-100 text-green-800 rounded-full text-sm font-medium">
                                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                                Sudah diulas
                            </div>
                        </div>
                    @else
                        <!-- Form Input Ulasan -->
                        <div class="space-y-4">
                            <input type="hidden" name="reviews[{{ $index }}][kode_barang]" value="{{ $barang->kode_barang }}">
                            
                            <!-- Rating Bintang -->
                            <div class="space-y-2">
                                <label class="block text-sm font-medium text-gray-700">Rating</label>
                                <div class="flex items-center space-x-1">
                                    @for($i = 1; $i <= 5; $i++)
                                        <label class="cursor-pointer">
                                            <input type="radio" 
                                                   name="reviews[{{ $index }}][rating]" 
                                                   value="{{ $i }}" 
                                                   class="sr-only rating-input"
                                                   data-group="{{ $index }}"
                                                   required>
                                            <svg class="w-8 h-8 star-icon text-gray-300 hover:text-yellow-400 transition-colors" 
                                                 fill="currentColor" 
                                                 viewBox="0 0 20 20">
                                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72C2.196 8.15 2.598 6.91 3.567 6.91h3.462a1 1 0 00.95-.69l1.07-3.292z"/>
                                            </svg>
                                        </label>
                                    @endfor
                                </div>
                            </div>

                            <!-- Komentar -->
                            <div class="space-y-2">
                                <label class="block text-sm font-medium text-gray-700">Komentar (Opsional)</label>
                                <textarea name="reviews[{{ $index }}][komentar]" 
                                          rows="3" 
                                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none"
                                          placeholder="Bagikan pengalaman Anda dengan produk ini..."></textarea>
                            </div>
                        </div>
                    @endif
                </div>
            @endforeach

            <!-- Submit Button -->
            @if($barangList->whereNotIn('kode_barang', $existingReviews)->count() > 0)
                <div class="flex justify-between items-center pt-6">
                    <a href="{{ url()->previous() }}" 
                       class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                        Kembali
                    </a>
                    <button type="submit" 
                            class="px-8 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium">
                        Kirim Ulasan
                    </button>
                </div>
            @else
                <div class="text-center pt-6">
                    <a href="{{ url()->previous() }}" 
                       class="px-6 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors">
                        Kembali
                    </a>
                </div>
            @endif
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle star rating
    const ratingInputs = document.querySelectorAll('.rating-input');
    
    ratingInputs.forEach(input => {
        input.addEventListener('change', function() {
            const group = this.dataset.group;
            const rating = parseInt(this.value);
            const stars = document.querySelectorAll(`input[data-group="${group}"]`);
            
            stars.forEach((star, index) => {
                const starIcon = star.nextElementSibling;
                if (index < rating) {
                    starIcon.classList.remove('text-gray-300');
                    starIcon.classList.add('text-yellow-400');
                } else {
                    starIcon.classList.remove('text-yellow-400');
                    starIcon.classList.add('text-gray-300');
                }
            });
        });
    });
    
    // Handle star hover effect
    const starIcons = document.querySelectorAll('.star-icon');
    
    starIcons.forEach((star, index) => {
        const input = star.previousElementSibling;
        const group = input.dataset.group;
        const rating = parseInt(input.value);
        
        star.addEventListener('mouseenter', function() {
            const groupStars = document.querySelectorAll(`input[data-group="${group}"]`);
            groupStars.forEach((groupStar, groupIndex) => {
                const groupStarIcon = groupStar.nextElementSibling;
                if (groupIndex <= index) {
                    groupStarIcon.classList.add('text-yellow-400');
                    groupStarIcon.classList.remove('text-gray-300');
                }
            });
        });
        
        star.addEventListener('mouseleave', function() {
            const groupStars = document.querySelectorAll(`input[data-group="${group}"]`);
            const checkedStar = document.querySelector(`input[data-group="${group}"]:checked`);
            const checkedRating = checkedStar ? parseInt(checkedStar.value) : 0;
            
            groupStars.forEach((groupStar, groupIndex) => {
                const groupStarIcon = groupStar.nextElementSibling;
                if (groupIndex < checkedRating) {
                    groupStarIcon.classList.add('text-yellow-400');
                    groupStarIcon.classList.remove('text-gray-300');
                } else {
                    groupStarIcon.classList.add('text-gray-300');
                    groupStarIcon.classList.remove('text-yellow-400');
                }
            });
        });
    });
});
</script>
@endsection