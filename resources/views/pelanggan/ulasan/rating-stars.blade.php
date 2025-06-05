{{-- 
Komponen untuk menampilkan rating bintang
Usage: @include('components.rating-stars', ['rating' => $rating, 'size' => 'sm'])

Parameters:
- $rating: nilai rating (1-5)
- $size: ukuran bintang ('sm', 'md', 'lg') - default 'md'
- $showText: tampilkan text rating (true/false) - default true
- $totalReviews: jumlah total review (opsional)
--}}

@php
    $rating = $rating ?? 0;
    $size = $size ?? 'md';
    $showText = $showText ?? true;
    $totalReviews = $totalReviews ?? null;
    
    $sizeClasses = [
        'sm' => 'star-sm',
        'md' => 'star-md', 
        'lg' => 'star-lg'
    ];
    
    $sizeClass = $sizeClasses[$size] ?? 'star-md';
@endphp

<div class="rating-display d-inline-flex align-items-center">
    <div class="stars {{ $sizeClass }}">
        @for($i = 1; $i <= 5; $i++)
            @if($i <= floor($rating))
                <span class="star filled">★</span>
            @elseif($i == ceil($rating) && $rating - floor($rating) >= 0.5)
                <span class="star half-filled">★</span>
            @else
                <span class="star empty">★</span>
            @endif
        @endfor
    </div>
    
    @if($showText)
        <span class="rating-text ms-2">
            <span class="rating-value">{{ number_format($rating, 1) }}</span>
            @if($totalReviews !== null)
                <span class="text-muted">({{ $totalReviews }} {{ $totalReviews == 1 ? 'ulasan' : 'ulasan' }})</span>
            @endif
        </span>
    @endif
</div>

<style>
.rating-display .stars {
    display: inline-flex;
    gap: 1px;
}

.rating-display .star {
    color: #e4e5e9;
    transition: color 0.2s ease;
}

.rating-display .star.filled {
    color: #ffc107;
}

.rating-display .star.half-filled {
    background: linear-gradient(90deg, #ffc107 50%, #e4e5e9 50%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.rating-display .star.empty {
    color: #e4e5e9;
}

/* Size variations */
.stars.star-sm .star {
    font-size: 0.875rem;
}

.stars.star-md .star {
    font-size: 1.125rem;
}

.stars.star-lg .star {
    font-size: 1.5rem;
}

.rating-text {
    font-size: 0.9rem;
}

.rating-value {
    font-weight: 600;
    color: #495057;
}
</style>