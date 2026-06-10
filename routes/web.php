<?php

use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProdukController;
use App\Http\Controllers\KategoriController;
use App\Http\Controllers\TransaksiController;
use App\Http\Controllers\PergerakanStokController;
use App\Http\Controllers\SatuanProdukController;

/*
|--------------------------------------------------------------------------
| WEB ROUTES
|--------------------------------------------------------------------------
*/

// Authentication routes
use App\Http\Controllers\AuthController;

// 1. HALAMAN PERTAMA DIBUKA (Akan otomatis melempar ke /login)
// Mengarahkan halaman utama langsung ke fungsi showLoginForm tanpa lewat root()
Route::redirect('/', '/login');

// Panggil showLogin di sini
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
// =========================
// DASHBOARD
// =========================
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard')->middleware('auth.custom');


// =========================
// PRODUK
// =========================
Route::get('/produk', [ProdukController::class, 'index'])->name('produk.index')->middleware('auth.custom');
Route::get('/produk/create', [ProdukController::class, 'create'])->name('produk.create')->middleware('auth.custom');
Route::post('/produk/store', [ProdukController::class, 'store'])->name('produk.store')->middleware('auth.custom');
Route::get('/produk/{id}', [ProdukController::class, 'show'])->name('produk.show')->middleware('auth.custom');
Route::get('/produk/{id}/edit', [ProdukController::class, 'edit'])->name('produk.edit')->middleware('auth.custom');
Route::put('/produk/{id}/update', [ProdukController::class, 'update'])->name('produk.update')->middleware('auth.custom');
Route::delete('/produk/{id}/delete', [ProdukController::class, 'destroy'])->name('produk.destroy')->middleware('auth.custom');


// =========================
// KATEGORI
// =========================
Route::get('/kategori', [KategoriController::class, 'index'])->name('kategori.index')->middleware('auth.custom');
Route::get('/kategori/create', [KategoriController::class, 'create'])->name('kategori.create')->middleware('auth.custom');
Route::post('/kategori/store', [KategoriController::class, 'store'])->name('kategori.store')->middleware('auth.custom');
Route::get('/kategori/{id}', [KategoriController::class, 'show'])->name('kategori.show')->middleware('auth.custom');
Route::get('/kategori/{id}/edit', [KategoriController::class, 'edit'])->name('kategori.edit')->middleware('auth.custom');
Route::put('/kategori/{id}/update', [KategoriController::class, 'update'])->name('kategori.update')->middleware('auth.custom');
Route::delete('/kategori/{id}/delete', [KategoriController::class, 'destroy'])->name('kategori.destroy')->middleware('auth.custom');


// SATUAN PRODUK
Route::get('/satuan-produk', [SatuanProdukController::class, 'index'])->name('satuan-produk.index')->middleware('auth.custom');
Route::get('/satuan-produk/create', [SatuanProdukController::class, 'create'])->name('satuan-produk.create')->middleware('auth.custom');
Route::post('/satuan-produk/store', [SatuanProdukController::class, 'store'])->name('satuan-produk.store')->middleware('auth.custom');
Route::get('/satuan-produk/{id}/edit', [SatuanProdukController::class, 'edit'])->name('satuan-produk.edit')->middleware('auth.custom');
Route::put('/satuan-produk/{id}/update', [SatuanProdukController::class, 'update'])->name('satuan-produk.update')->middleware('auth.custom');
Route::delete('/satuan-produk/{id}/delete', [SatuanProdukController::class, 'destroy'])->name('satuan-produk.destroy')->middleware('auth.custom');


// TRANSAKSI
// =========================
Route::get('/transaksi', [TransaksiController::class, 'index'])->name('transaksi.index')->middleware('auth.custom');
Route::get('/transaksi/create', [TransaksiController::class, 'create'])->name('transaksi.create')->middleware('auth.custom');
Route::post('/transaksi/store', [TransaksiController::class, 'store'])->name('transaksi.store')->middleware('auth.custom');

// 🔥 PINDAHKAN KESINI (Di atas route yang memakai parameter {id})
Route::get('/transaksi/export', [TransaksiController::class, 'exportExcel'])->name('transaksi.export')->middleware('auth.custom');

// Route yang menggunakan parameter {id} wajib ditaruh di paling bawah kelompoknya
Route::get('/transaksi/{id}', [TransaksiController::class, 'show'])->name('transaksi.show')->middleware('auth.custom');
Route::delete('/transaksi/{id}/delete', [TransaksiController::class, 'destroy'])->name('transaksi.destroy')->middleware('auth.custom');


// =========================
// STOK (PERGERAKAN)
// =========================
Route::get('/stok', [PergerakanStokController::class, 'index'])->name('stok.index')->middleware('auth.custom');
Route::get('/stok/create', [PergerakanStokController::class, 'create'])->name('stok.create')->middleware('auth.custom'); 
Route::post('/stok', [PergerakanStokController::class, 'store'])->name('stok.store')->middleware('auth.custom'); 
Route::get('/stok/{stok}', [PergerakanStokController::class, 'show'])->name('stok.show')->middleware('auth.custom');
Route::get('stok/{id}/cetak', [PergerakanStokController::class, 'cetak'])->name('stok.cetak')->middleware('auth.custom');
Route::get('stok-export', [PergerakanStokController::class, 'exportExcel'])->name('stok.export')->middleware('auth.custom');


// =========================
// KASIR (ALIAS)
Route::get('/kasir', [TransaksiController::class, 'create'])->name('kasir')->middleware('auth.custom');
Route::post('/kasir/store', [TransaksiController::class, 'store'])->name('kasir.store')->middleware('auth.custom');