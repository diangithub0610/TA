<div class="bg-white rounded-lg shadow-sm">
    @if($ulasan->count() > 0)
        <div class="divide-y divide-gray-200">
            @foreach($ulasan as $review)
                <div class="p-6">
                    <div class="flex items-start space-x-4">
                        <!-- Avatar -->
                        <div class="flex-shrink-0">
                            <div class="h-12 w-12 rounded-full bg-gray-300 flex items-center justify-center">
                                <span class="text-lg font-medium text-gray-700">
                                    {{ strtoupper(substr($review->pelanggan->nama_pelanggan, 0, 1)) }}
                                </span>
                            </div>
                        </div>

                        <!-- Content -->
                        <div class="flex-1">
                            <div class="flex items-center justify-between">
                                <h3 class="text-sm font-medium text-gray-900">{{ $review->pelanggan->nama_pelanggan }}</h3>
                                <span class="text-sm text-gray-500">
                                    {{ $review->tanggal_review->format('d F Y') }}
                                </span>
                            </div>

                            <!-- Rating -->
                            <div class="flex items-center mt-1">
                                @for($i = 1; $i <= 5; $i++)
                                    <svg class="h-4 w-4 {{ $i <= $review->rating ? 'text-yellow-400' : 'text-gray-300' }} fill-current" viewBox="0 0 24 24">
                                        <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                                    </svg>
                                @endfor
                                <span class="ml-2 text-sm text-gray-600">{{ $review->rating }}/5</span>
                            </div>

                            <!-- Comment -->
                            @if($review->komentar)
                                <p class="mt-3 text-sm text-gray-600">{{ $review->komentar }}</p>
                            @endif

                            <!-- Actions for own review -->
                            @if (Auth::guard('pelanggan')->check()) {
                                @if($review->user_id == auth()->id())
                                    <div class="flex items-center space-x-4 mt-3">
                                        <a href="{{ route('ulasan.edit', $review->id) }}" class="text-sm text-blue-600 hover:text-blue-800">
                                            Edit
                                        </a>
                                        <form action="{{ route('ulasan.destroy', $review->id) }}" method="POST" class="inline"
                                            onsubmit="return confirm('Apakah Anda yakin ingin menghapus ulasan ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-sm text-red-600 hover:text-red-800">
                                                Hapus
                                            </button>
                                        </form>
                                    </div>
                                @endif
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $ulasan->links() }}
        </div>
    @else
        <!-- Empty State -->
        <div class="text-center py-12">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-3.582 8-8 8a8.955 8.955 0 01-4.126-.98L3 20l1.98-5.126A8.955 8.955 0 013 12c0-4.418 3.582-8 8-8s8 3.582 8 8z" />
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">Belum ada ulasan</h3>
            <p class="mt-1 text-sm text-gray-500">Jadilah yang pertama memberikan ulasan untuk produk ini.</p>
            
            @auth
                @if($canReview)
                    <div class="mt-6">
                        <a href="{{ route('ulasan.create', $barang->kode_barang) }}" 
                           class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-md">
                            Tulis Ulasan Pertama
                        </a>
                    </div>
                @endif
            @endauth
        </div>
    @endif
</div>