<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PembayaranController;
use App\Http\Controllers\PengirimanController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();

});
// Route::middleware(['api'])
//     ->group(function () {
//         Route::post('/pembayaran/notification', [PembayaranController::class, 'notification'])
//             ->name('pembayaran.notification')
//             ->withoutMiddleware(['csrf']);
//     });

Route::patch('/admin/pengiriman/{id}/update-tracking', [PengirimanController::class, 'updateTracking'])->name('update-tracking');

Route::post('/debug/tracking-api', [PengirimanController::class, 'debugTrackingAPI'])
    ->name('debug.tracking-api');

// Route untuk test courier codes
Route::post('/debug/test-couriers', [PengirimanController::class, 'testCourierCodes'])
    ->name('debug.test-couriers');

Route::post('/midtrans/notification/pendaftaran', [App\Http\Controllers\RegistrasiController::class, 'notifikasi'])->name('midtrans.notification');
