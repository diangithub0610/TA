<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TipeController;
use App\Http\Controllers\TokoController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\WarnaController;
use App\Http\Controllers\AlamatController;
use App\Http\Controllers\BarangController;
use App\Http\Controllers\OngkirController;
use App\Http\Controllers\ProfilController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\KeranjangController;
use App\Http\Controllers\PelangganController;
use App\Http\Controllers\TransaksiController;
use App\Http\Controllers\PembayaranController;
use App\Http\Controllers\PemusnahanController;
use App\Http\Controllers\PersediaanController;
use App\Http\Controllers\BarangMasukController;
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
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login')->middleware('guest');
Route::post('/login', [AuthController::class, 'login'])->name('login.attempt');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Forgot Password
Route::get('/forgot-password', [AuthController::class, 'showForgotPasswordForm'])->name('forgot.password');
Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])->name('password.email');

// Register
Route::get('/register', [AuthController::class, 'register'])->name('register');
Route::post('/register', [AuthController::class, 'doRegister'])->name('do.register');


Route::get('/', [DashboardController::class, 'index']);


Route::middleware(['auth:admin', 'role:gudang'])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    // Route::prefix('pelanggan')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('gudang.dashboard');
    // });
    // Master Data
    Route::resource('brand', BrandController::class)->only('index', 'store', 'update', 'destroy');
    Route::resource('warna', WarnaController::class);
    Route::resource('tipe', TipeController::class);
    Route::resource('barang', BarangController::class);

    // Barang Masuk
    Route::resource('barang-masuk', BarangMasukController::class);

    // Persediaan
    Route::resource('persediaan', PersediaanController::class);

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

    // Alamat
    // Route::get('/alamat', [AlamatController::class, 'index'])->name('alamat.index');
    // Route::post('/alamat/simpan', [AlamatController::class, 'simpan'])->name('alamat.simpan');
    // Route::post('/alamat/{idAlamat}/update', [AlamatController::class, 'update'])->name('alamat.update');
    // Route::get('/alamat/{idAlamat}/hapus', [AlamatController::class, 'hapus'])->name('alamat.hapus');
    // Route::get('/alamat/{idAlamat}/utama', [AlamatController::class, 'jadikanUtama'])->name('alamat.utama');

    // Profil
    Route::get('/profil', [ProfilController::class, 'index'])->name('profil.index');
    Route::post('/profil/update', [ProfilController::class, 'update'])->name('profil.update');
    Route::get('/profil/password', [ProfilController::class, 'password'])->name('profil.password');
    Route::post('/profil/password', [ProfilController::class, 'updatePassword'])->name('profil.update-password');
});


