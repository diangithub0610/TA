<!-- Tab Content untuk Ulasan -->
<div id="ulasanContent" class="hidden">
    <div class="bg-white rounded-lg shadow-sm p-6">
        <!-- Header Ulasan -->
        <div class="flex justify-between items-center mb-6">
            <div>
                <h3 class="text-lg font-semibold text-gray-900">Ulasan Pelanggan</h3>
                <div class="flex items-center mt-2">
                    <div class="flex items-center">
                        <span id="averageRating" class="text-2xl font-bold text-gray-900">0.0</span>
                        <div id="averageStars" class="flex ml-2">
                            <!-- Stars will be populated by JavaScript -->
                        </div>
                    </div>
                    <span id="totalUlasan" class="ml-3 text-sm text-gray-500">(0 ulasan)</span>
                </div>
            </div>
            
            <!-- Tombol Tulis Ulasan -->
            <div id="reviewButtonContainer">
                <!-- Button will be populated by JavaScript based on user status -->
            </div>
        </div>

        <!-- Daftar Ulasan -->
        <div id="ulasanList" class="space-y-6">
            <!-- Ulasan akan dimuat di sini -->
        </div>

        <!-- Empty State -->
        <div id="emptyUlasan" class="text-center py-12 hidden">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-3.582 8-8 8a8.955 8.955 0 01-4.126-.98L3 20l1.98-5.126A8.955 8.955 0 013 12c0-4.418 3.582-8 8-8s8 3.582 8 8z" />
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">Belum ada ulasan</h3>
            <p class="mt-1 text-sm text-gray-500">Jadilah yang pertama memberikan ulasan untuk produk ini.</p>
        </div>
    </div>
</div>

<!-- Modal Form Ulasan -->
<div id="ulasanModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium text-gray-900">Tulis Ulasan</h3>
                <button id="closeModal" class="text-gray-400 hover:text-gray-600">
                    <span class="sr-only">Close</span>
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            
            <form id="ulasanForm" method="POST">
                @csrf
                <input type="hidden" name="kode_barang" id="kodeBarangInput">
                <input type="hidden" name="transaksi_id" id="transaksiIdInput">
                
                <!-- Rating -->
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Rating</label>
                    <div class="flex items-center space-x-1">
                        <div id="starRating" class="flex">
                            <button type="button" class="star-btn text-gray-300 hover:text-yellow-400 focus:outline-none" data-rating="1">
                                <svg class="h-8 w-8 fill-current" viewBox="0 0 24 24">
                                    <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                                </svg>
                            </button>
                            <button type="button" class="star-btn text-gray-300 hover:text-yellow-400 focus:outline-none" data-rating="2">
                                <svg class="h-8 w-8 fill-current" viewBox="0 0 24 24">
                                    <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                                </svg>
                            </button>
                            <button type="button" class="star-btn text-gray-300 hover:text-yellow-400 focus:outline-none" data-rating="3">
                                <svg class="h-8 w-8 fill-current" viewBox="0 0 24 24">
                                    <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                                </svg>
                            </button>
                            <button type="button" class="star-btn text-gray-300 hover:text-yellow-400 focus:outline-none" data-rating="4">
                                <svg class="h-8 w-8 fill-current" viewBox="0 0 24 24">
                                    <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                                </svg>
                            </button>
                            <button type="button" class="star-btn text-gray-300 hover:text-yellow-400 focus:outline-none" data-rating="5">
                                <svg class="h-8 w-8 fill-current" viewBox="0 0 24 24">
                                    <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                                </svg>
                            </button>
                        </div>
                        <span id="ratingText" class="ml-2 text-sm text-gray-600">Pilih rating</span>
                    </div>
                    <input type="hidden" name="rating" id="ratingInput" required>
                </div>

                <!-- Komentar -->
                <div class="mb-6">
                    <label for="komentar" class="block text-sm font-medium text-gray-700 mb-2">Komentar</label>
                    <textarea 
                        name="komentar" 
                        id="komentar" 
                        rows="4" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                        placeholder="Bagikan pengalaman Anda dengan produk ini..."
                    ></textarea>
                </div>

                <!-- Buttons -->
                <div class="flex justify-end space-x-3">
                    <button type="button" id="cancelButton" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Kirim Ulasan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const tabUlasan = document.getElementById('tabUlasan');
    const ulasanContent = document.getElementById('ulasanContent');
    const ulasanModal = document.getElementById('ulasanModal');
    const ulasanForm = document.getElementById('ulasanForm');
    
    // Ambil kode_barang dari halaman (sesuaikan dengan implementasi Anda)
    const kodeBarang = '{{ $barang->kode_barang ?? "" }}';
    
    // Tab click handler
    tabUlasan.addEventListener('click', function(e) {
        e.preventDefault();
        
        // Update active tab styling
        document.querySelectorAll('[id^="tab"]').forEach(tab => {
            tab.classList.remove('border-blue-500', 'text-blue-600');
            tab.classList.add('border-transparent', 'text-gray-500');
        });
        
        tabUlasan.classList.remove('border-transparent', 'text-gray-500');
        tabUlasan.classList.add('border-blue-500', 'text-blue-600');
        
        // Hide all content, show ulasan content
        document.querySelectorAll('[id$="Content"]').forEach(content => {
            content.classList.add('hidden');
        });
        
        ulasanContent.classList.remove('hidden');
        
        // Load ulasan data
        loadUlasanData();
    });
    
    // Load ulasan data
    function loadUlasanData() {
        if (!kodeBarang) return;
        
        fetch(`/ulasan/barang/${kodeBarang}`)
            .then(response => response.json())
            .then(data => {
                updateUlasanDisplay(data);
            })
            .catch(error => {
                console.error('Error loading ulasan:', error);
            });
    }
    
    // Update ulasan display
    function updateUlasanDisplay(data) {
        const { ulasan, average_rating, total_ulasan } = data;
        
        // Update header stats
        document.getElementById('averageRating').textContent = average_rating || '0.0';
        document.getElementById('totalUlasan').textContent = `(${total_ulasan} ulasan)`;
        
        // Update average stars
        updateStarDisplay('averageStars', average_rating);
        
        // Update ulasan list
        const ulasanList = document.getElementById('ulasanList');
        const emptyUlasan = document.getElementById('emptyUlasan');
        
        if (ulasan.length === 0) {
            ulasanList.innerHTML = '';
            emptyUlasan.classList.remove('hidden');
        } else {
            emptyUlasan.classList.add('hidden');
            ulasanList.innerHTML = ulasan.map(review => createUlasanHTML(review)).join('');
        }
        
        // Update review button
        updateReviewButton();
    }
    
    // Create ulasan HTML
    function createUlasanHTML(review) {
        const tanggal = new Date(review.tanggal_review).toLocaleDateString('id-ID', {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });
        
        return `
            <div class="border-b border-gray-200 pb-6">
                <div class="flex items-start space-x-4">
                    <div class="flex-shrink-0">
                        <div class="h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center">
                            <span class="text-sm font-medium text-gray-700">${review.nama_reviewer.charAt(0).toUpperCase()}</span>
                        </div>
                    </div>
                    <div class="flex-1">
                        <div class="flex items-center justify-between">
                            <h4 class="text-sm font-medium text-gray-900">${review.nama_reviewer}</h4>
                            <span class="text-sm text-gray-500">${tanggal}</span>
                        </div>
                        <div class="flex items-center mt-1">
                            ${createStarsHTML(review.rating)}
                        </div>
                        ${review.komentar ? `<p class="mt-2 text-sm text-gray-600">${review.komentar}</p>` : ''}
                    </div>
                </div>
            </div>
        `;
    }
    
    // Create stars HTML
    function createStarsHTML(rating) {
        let starsHTML = '';
        for (let i = 1; i <= 5; i++) {
            const filled = i <= rating;
            starsHTML += `
                <svg class="h-4 w-4 ${filled ? 'text-yellow-400' : 'text-gray-300'} fill-current" viewBox="0 0 24 24">
                    <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                </svg>
            `;
        }
        return starsHTML;
    }
    
    // Update star display
    function updateStarDisplay(containerId, rating) {
        const container = document.getElementById(containerId);
        container.innerHTML = createStarsHTML(Math.round(rating));
    }
    
    // Update review button based on user status
    function updateReviewButton() {
        const container = document.getElementById('reviewButtonContainer');
        // This would need to be populated based on server-side data about user's purchase/review status
        @auth
            container.innerHTML = `
                <button id="writeReviewBtn" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                    Tulis Ulasan
                </button>
            `;
            
            // Add event listener for write review button
            document.getElementById('writeReviewBtn')?.addEventListener('click', function() {
                openUlasanModal();
            });
        @else
            container.innerHTML = `
                <a href="/login" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-md text-sm font-medium">
                    Login untuk Ulasan
                </a>
            `;
        @endauth
    }
    
    // Modal functions
    function openUlasanModal() {
        document.getElementById('kodeBarangInput').value = kodeBarang;
        ulasanModal.classList.remove('hidden');
    }
    
    function closeUlasanModal() {
        ulasanModal.classList.add('hidden');
        ulasanForm.reset();
        resetStarRating();
    }
    
    // Event listeners for modal
    document.getElementById('closeModal').addEventListener('click', closeUlasanModal);
    document.getElementById('cancelButton').addEventListener('click', closeUlasanModal);
    
    // Star rating functionality
    const starButtons = document.querySelectorAll('.star-btn');
    const ratingInput = document.getElementById('ratingInput');
    const ratingText = document.getElementById('ratingText');
    
    starButtons.forEach(button => {
        button.addEventListener('click', function() {
            const rating = parseInt(this.dataset.rating);
            setStarRating(rating);
        });
    });
    
    function setStarRating(rating) {
        ratingInput.value = rating;
        
        starButtons.forEach((button, index) => {
            if (index < rating) {
                button.classList.remove('text-gray-300');
                button.classList.add('text-yellow-400');
            } else {
                button.classList.remove('text-yellow-400');
                button.classList.add('text-gray-300');
            }
        });
        
        const ratingTexts = ['', 'Sangat Buruk', 'Buruk', 'Biasa', 'Baik', 'Sangat Baik'];
        ratingText.textContent = ratingTexts[rating];
    }
    
    function resetStarRating() {
        ratingInput.value = '';
        starButtons.forEach(button => {
            button.classList.remove('text-yellow-400');
            button.classList.add('text-gray-300');
        });
        ratingText.textContent = 'Pilih rating';
    }
    
    // Form submission
    ulasanForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        if (!ratingInput.value) {
            alert('Silakan pilih rating terlebih dahulu');
            return;
        }
        
        const formData = new FormData(this);
        
        fetch('/ulasan', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                closeUlasanModal();
                loadUlasanData(); // Reload ulasan data
                alert('Ulasan berhasil ditambahkan!');
            } else {
                alert(data.message || 'Terjadi kesalahan saat menyimpan ulasan');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Terjadi kesalahan saat menyimpan ulasan');
        });
    });
    
    // Close modal when clicking outside
    ulasanModal.addEventListener('click', function(e) {
        if (e.target === ulasanModal) {
            closeUlasanModal();
        }
    });
});
</script>