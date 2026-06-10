<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\KategoriController;
use App\Http\Controllers\Api\ProdukController;
use App\Http\Controllers\Api\SatuanProdukController;
use App\Http\Controllers\Api\TransaksiController;
use App\Http\Controllers\Api\PergerakanStokController;

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

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    // Auth routes
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index']);

    // Master Data
    Route::apiResource('kategori', KategoriController::class);
    Route::apiResource('produk', ProdukController::class);
    Route::apiResource('satuan-produk', SatuanProdukController::class);

    // Transactions & Stock
    Route::apiResource('transaksi', TransaksiController::class)->except(['update']);
    Route::apiResource('stok', PergerakanStokController::class)->except(['update', 'destroy']);
});
