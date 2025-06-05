<?php

use App\Models\persediaan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TipeController;
use App\Http\Controllers\TokoController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\KasirController;
use App\Http\Controllers\WarnaController;
use App\Http\Controllers\AlamatController;
use App\Http\Controllers\BarangController;
use App\Http\Controllers\OngkirController;
use App\Http\Controllers\ProfilController;
use App\Http\Controllers\UlasanController;
use App\Http\Controllers\PesananController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\KeranjangController;
use App\Http\Controllers\PelangganController;
use App\Http\Controllers\TransaksiController;
use App\Http\Controllers\PembayaranController;
use App\Http\Controllers\PemusnahanController;
use App\Http\Controllers\PersediaanController;
use App\Http\Controllers\RegistrasiController;
use App\Http\Controllers\BarangMasukController;
use App\Http\Controllers\AdminPesananController;
use App\Http\Controllers\CustomerOrderController;
use App\Http\Controllers\PemusnahanBarangController;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/
// Tombol uji coba
Route::get('/tes-sweetalert', function () {
    return view('tes-sweetalert');
})->name('tes.sweetalert');

// Aksi untuk trigger notifikasi
Route::post('/trigger-sweetalert', function () {
    return redirect()->route('tes.sweetalert')->with('success', 'Notifikasi berhasil ditampilkan!');
})->name('trigger.sweetalert');


Route::prefix('ongkir')->group(function () {
    Route::get('/', [OngkirController::class, 'index'])->name('ongkir.index');
    Route::get('/search-destination', [OngkirController::class, 'searchDestination']);
    Route::post('/calculate-cost', [OngkirController::class, 'calculateCost']);

    Route::post('/use-saved-address', [OngkirController::class, 'useSavedAddress'])
        ->name('ongkir.use-saved-address');
});

Route::resource('alamat', AlamatController::class)
    ->except(['show'])
    ->names([
        'index' => 'alamat.index',
        'create' => 'alamat.create',
        'store' => 'alamat.store',
        'edit' => 'alamat.edit',
        'update' => 'alamat.update',
        'destroy' => 'alamat.destroy'
    ]);

Route::get('/search-location', [AlamatController::class, 'searchLocation'])
    ->name('alamat.search-location');

// Optional: Route for getting saved addresses for shipping cost calculation
Route::get('/get-saved-addresses', [AlamatController::class, 'getSavedAddresses'])
    ->name('alamat.saved');

// Route::post('/cek-ongkir', [CheckoutController::class, 'cekOngkir'])->name('checkout.cek-ongkir');
// Route::get('/search-destination', [CheckoutController::class, 'searchDestination'])->name('checkout.search-destination');

// Login
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.attempt');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Forgot Password
Route::get('/forgot-password', [AuthController::class, 'showForgotPasswordForm'])->name('forgot.password');
Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])->name('password.email');

// Register
Route::get('/register', [AuthController::class, 'register'])->name('register');
Route::post('/register', [AuthController::class, 'doRegister'])->name('do.register');


Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

//barang masuk -- jika belum ada 
Route::get('/barang-masuk/get-produk-by-brand', [BarangMasukController::class, 'getProdukByBrand'])
    ->name('barang-masuk.get-produk-by-brand');

Route::get('/barang-masuk/get-barang-by-brand', [BarangMasukController::class, 'getBarangByBrand'])
    ->name('barang-masuk.get-barang-by-brand');

// Route untuk mendapatkan tipe berdasarkan brand  
Route::get('/barang-masuk/get-tipe-by-brand', [BarangMasukController::class, 'getTipeByBrand'])
    ->name('barang-masuk.get-tipe-by-brand');

// Route untuk menyimpan barang baru dari form barang masuk
Route::post('/barang-masuk/store-barang-baru', [BarangMasukController::class, 'storeBarangBaru'])
    ->name('barang-masuk.store-barang-baru');

Route::get('/barang-masuk/get-detail-by-produk', [BarangMasukController::class, 'getDetailByProduk'])
    ->name('barang-masuk.get-detail-by-produk');

Route::middleware(['auth:admin', 'role:shopkeeper'])->prefix('admin')->name('admin-')->group(function () {
    // Route untuk transaksi
    Route::get('/transaksi', [AdminPesananController::class, 'index'])->name('transaksi.index');
    Route::get('/transaksi/{kode_transaksi}', [AdminPesananController::class, 'show'])->name('admin-transaksi.show');
    Route::post('/transaksi/{kode_transaksi}/terima', [AdminPesananController::class, 'terima'])->name('transaksi.terima');
    Route::post('/transaksi/{kode_transaksi}/tolak', [AdminPesananController::class, 'tolak'])->name('transaksi.tolak');
    Route::post('/transaksi/{kode_transaksi}/update-status', [AdminPesananController::class, 'updateStatus'])->name('transaksi.update-status');
    Route::get('/transaksi/{kode_transaksi}/invoice', [AdminPesananController::class, 'invoice'])->name('admin-transaksi.invoice');
    Route::post('/transaksi/bulk-update-status', [AdminPesananController::class, 'bulkUpdateStatus'])->name('admin-transaksi.bulk-update-status');
    Route::get('/transaksi-statistics', [AdminPesananController::class, 'statistics'])->name('admin-transaksi.statistics');
});

Route::prefix('kasir')->name('kasir.')->group(function () {
    // Main kasir page
    Route::get('/', [KasirController::class, 'index'])->name('index');

    // Get products with filters
    Route::get('/barang', [KasirController::class, 'getBarang'])->name('barang');

    // Get product details/variants
    Route::get('/barang/{kode_barang}/detail', [KasirController::class, 'getDetailBarang'])->name('barang.detail');

    // Store transaction
    Route::post('/transaksi', [KasirController::class, 'store'])->name('store');

    // Print receipt
    Route::get('/print/{kode_transaksi}', [KasirController::class, 'print'])->name('print');
    /////

    Route::get('/transaksi', [KasirController::class, 'riwayat'])->name('riwayat');

    // Route untuk detail transaksi
    Route::get('/transaksi/{kode_transaksi}', [KasirController::class, 'show'])->name('admin.shopkeeper.detail');
});



Route::middleware(['auth:admin', 'role:gudang,owner'])->group(function () {

    // Master Data
    Route::resource('brand', BrandController::class)->only('index', 'store', 'update', 'destroy');
    Route::resource('warna', WarnaController::class);
    Route::resource('tipe', TipeController::class);
    Route::resource('barang', BarangController::class);

    // Barang Masuk
    Route::resource('barang-masuk', BarangMasukController::class);


    //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    // Persediaan
    // Route::resource('persediaan', PersediaanController::class);

    // Pemusnahan
    Route::get('/pemusnahan-barang', [PemusnahanBarangController::class, 'index'])->name('pemusnahan-barang.index');
    Route::get('/daftar-barang', [PemusnahanBarangController::class, 'detail'])->name('pemusnahan-barang.daftar-barang');
    Route::get('/detail-barang/{id}', [PemusnahanBarangController::class, 'show'])->name('pemusnahan-barang.detail-barang');
    Route::post('/pemusnahan-barang/store', [PemusnahanBarangController::class, 'store'])->name('pemusnahan-barang.store');
    Route::put('/pemusnahan-barang/persetujuan/{kode_pemusnahan}', [PemusnahanBarangController::class, 'persetujuan'])->name('pemusnahan-barang.persetujuan');


    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('/toko', [TokoController::class, 'index'])->name('toko.index');
        Route::post('/toko', [TokoController::class, 'save'])->name('toko.save');
        Route::get('/toko/search-destination', [TokoController::class, 'searchDestination'])->name('toko.search-destination');
    });
});

Route::prefix('pelanggan')->group(function () {
    Route::get('/beranda', [PelangganController::class, 'beranda'])->name('pelanggan.beranda');
    Route::get('/barang', [PelangganController::class, 'barang'])->name('pelanggan.produk');
    Route::get('/barang/{id}', [PelangganController::class, 'detailBarang'])->name('pelanggan.detailBarang');
});

Route::middleware(['auth:pelanggan'])->group(function () {
    // Keranjang
    Route::get('/keranjang', [KeranjangController::class, 'index'])->name('keranjang.index');
    Route::post('/keranjang/tambah', [KeranjangController::class, 'tambah'])->name('keranjang.tambah');
    Route::post('/keranjang/update', [KeranjangController::class, 'update'])->name('keranjang.update');
    Route::delete('/keranjang/hapus/{kodeDetail}', [KeranjangController::class, 'hapus'])->name('keranjang.hapus');
    Route::get('/keranjang/kosongkan', [KeranjangController::class, 'kosongkan'])->name('keranjang.kosongkan');
    Route::get('/keranjang/jumlah', [KeranjangController::class, 'getJumlahKeranjang'])->name('keranjang.jumlah');
    Route::post('/keranjang/check-stok', [KeranjangController::class, 'checkStok'])->name('keranjang.check-stok');

    // Checkout
    Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
    Route::post('/checkout/beli-langsung', [CheckoutController::class, 'beliLangsung'])->name('checkout.beli-langsung');
    Route::get('/checkout/get-kota', [CheckoutController::class, 'getKota'])->name('checkout.get-kota');
    Route::get('/checkout/get-kecamatan', [CheckoutController::class, 'getKecamatan'])->name('checkout.get-kecamatan');
    // Route::post('/checkout/cek-ongkir', [CheckoutController::class, 'cekOngkir'])->name('checkout.cek-ongkir');
    Route::post('/checkout/simpan-alamat', [CheckoutController::class, 'simpanAlamat'])->name('checkout.simpan-alamat');
    Route::post('/checkout/proses', [CheckoutController::class, 'proses'])->name('checkout.proses');

    Route::post('/cek-ongkir', [CheckoutController::class, 'calculateShippingCost'])
        ->name('checkout.cek-ongkir');

    Route::get('/search-destination', [CheckoutController::class, 'searchDestination'])
        ->name('checkout.search-destination');

    // Route::prefix('checkout')->name('checkout.')->middleware(['auth:pelanggan'])->group(function () {
    //     Route::get('/', [CheckoutController::class, 'index'])->name('index');
    //     Route::post('/cek-ongkir', [CheckoutController::class, 'cekOngkir'])->name('cek-ongkir');
    //     Route::post('/tambah-alamat', [CheckoutController::class, 'tambahAlamat'])->name('tambah-alamat');
    //     Route::post('/proses-pembayaran', [CheckoutController::class, 'prosesPembayaran'])->name('proses-pembayaran');
    // });

    // Routes untuk Raja Ongkir (versi baru)
    // Route::get('/checkout/search-destination', [CheckoutController::class, 'searchDestination'])->name('checkout.search-destination');


    // Pembayaran
    Route::get('/pembayaran/{kodeTransaksi}', [PembayaranController::class, 'show'])->name('pembayaran.show');

    // Di routes/api.php
    Route::post('midtrans/notification', [PembayaranController::class, 'notificationCallback']);

    // Di routes/web.php
    Route::get('pembayaran/finish', [PembayaranController::class, 'finish'])->name('pembayaran.finish');
    Route::get('pembayaran/unfinish', [PembayaranController::class, 'unfinish'])->name('pembayaran.unfinish');
    Route::get('pembayaran/error', [PembayaranController::class, 'error'])->name('pembayaran.error');
    // Route::get('pembayaran/{kodeTransaksi}', [PembayaranController::class, 'show'])->name('pembayaran.show');

    Route::get('/pembayaran/finish', [PembayaranController::class, 'finish'])->name('pembayaran.finish');
    Route::get('/pembayaran/unfinish', [PembayaranController::class, 'unfinish'])->name('pembayaran.unfinish');
    Route::get('/pembayaran/error', [PembayaranController::class, 'error'])->name('pembayaran.error');

    // Transaksi
    Route::get('/transaksi', [TransaksiController::class, 'index'])->name('transaksi.index');
    Route::get('/transaksi/{kodeTransaksi}', [TransaksiController::class, 'detail'])->name('transaksi.detail');
    Route::get('/transaksi/{kodeTransaksi}/terima', [TransaksiController::class, 'terimaBarang'])->name('transaksi.terima');
    Route::get('/transaksi/{kodeTransaksi}/batalkan', [TransaksiController::class, 'batalkan'])->name('transaksi.batalkan');

    // Daftar pesanan
    // Route::get('/pesanan', [PesananController::class, 'index'])->name('pesanan.index');


    // Alamat
    // Route::get('/alamat', [AlamatController::class, 'index'])->name('alamat.index');
    // Route::post('/alamat/simpan', [AlamatController::class, 'simpan'])->name('alamat.simpan');
    // Route::post('/alamat/{idAlamat}/update', [AlamatController::class, 'update'])->name('alamat.update');
    // Route::get('/alamat/{idAlamat}/hapus', [AlamatController::class, 'hapus'])->name('alamat.hapus');
    // Route::get('/alamat/{idAlamat}/utama', [AlamatController::class, 'jadikanUtama'])->name('alamat.utama');

    // Profil
    Route::get('/profil', [ProfilController::class, 'index'])->name('profil.index');
    Route::put('/profil', [ProfilController::class, 'update'])->name('profil.update');
    Route::post('/profil/foto', [ProfilController::class, 'updateFoto'])->name('profil.update.foto');

    //
    Route::get('/profil-alamat', [ProfilController::class, 'alamat'])->name('profil.alamat');
    Route::get('/change-password', [ProfilController::class, 'showChangePasswordForm'])->name('change-password');
    Route::post('/change-password', [ProfilController::class, 'changePassword'])->name('change-password.update');


    //pendaftaran
    // Route untuk pendaftaran

});

Route::get('/register', [App\Http\Controllers\RegistrasiController::class, 'index'])->name('register');
Route::post('/register', [App\Http\Controllers\RegistrasiController::class, 'store'])->name('register.store');
Route::get('/pembayaran-pendaftaran/{id}', [App\Http\Controllers\RegistrasiController::class, 'showPembayaran'])->name('pembayaran-pendaftaran.show');




//persediaan
// Route::get('/persediaan', [PersediaanController::class, 'index'])->name('persediaan.index');
// Routes untuk persediaan
Route::prefix('admin/persediaan')->group(function () {
    Route::get('/', [PersediaanController::class, 'index'])->name('persediaan.index');
    Route::get('/export-pdf', [PersediaanController::class, 'exportPdf'])->name('persediaan.export.pdf');
});

//produk-pelanggan
Route::prefix('pelanggan')->name('pelanggan.')->group(function () {
    // Route untuk halaman produk dengan filter dan sorting
    Route::get('/barang', [PelangganController::class, 'barang'])->name('barang');

    // Route untuk halaman produk dengan filter dan sorting
    // Route::get('/barang/cari', [BarangController::class, 'barang'])->name('cariBarang');

    // Route untuk filter berdasarkan brand (redirect ke halaman produk dengan parameter brand)
    Route::get('/produk/brand/{brandId}', [BarangController::class, 'byBrand'])->name('produk.byBrand');
});


// Route::middleware(['auth:pelanggan'])->group(function () {
// Routes untuk Ulasan
// Route::get('/ulasan/barang/{kode_barang}', [UlasanController::class, 'getUlasanByBarang'])->name('ulasan.by-barang');
// Route::get('/ulasan/create/{kode_barang}', [UlasanController::class, 'create'])->name('ulasan.create');
// Route::post('/ulasan', [UlasanController::class, 'store'])->name('ulasan.store');
Route::get('/ulasan/create/{kode_transaksi}', [UlasanController::class, 'create'])->name('ulasan.create');
Route::post('/ulasan/store', [UlasanController::class, 'store'])->name('ulasan.store');
// });

// Route publik untuk melihat ulasan
Route::get('/ulasan/{kode_barang}', [UlasanController::class, 'index'])->name('ulasan.index');


// routes/web.php


Route::get('/pesanan', [CustomerOrderController::class, 'index'])->name('customer.orders.index');
Route::get('/pesanan-ku/{kode_transaksi}', [CustomerOrderController::class, 'show'])->name('customer.orders.show');
Route::get('/pesanan/{kode_transaksi}/invoice', [CustomerOrderController::class, 'downloadInvoice'])->name('customer.orders.invoice');
