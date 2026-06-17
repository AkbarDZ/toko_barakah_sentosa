<?php

use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProdukController;
use App\Http\Controllers\KategoriController;
use App\Http\Controllers\TransaksiController;
use App\Http\Controllers\PergerakanStokController;
use App\Http\Controllers\SatuanProdukController;
use App\Http\Controllers\PenggunaController;

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
// HANYA ADMIN
// =========================
Route::middleware(['auth.custom', 'role:admin'])->group(function () {
    
    // MASTER DATA PRODUK
    Route::get('/produk/archive', [ProdukController::class, 'archive'])->name('produk.archive');
    Route::post('/produk/{id}/restore', [ProdukController::class, 'restore'])->name('produk.restore');
    Route::get('/produk', [ProdukController::class, 'index'])->name('produk.index');
    Route::get('/produk/create', [ProdukController::class, 'create'])->name('produk.create');
    Route::post('/produk/store', [ProdukController::class, 'store'])->name('produk.store');
    Route::get('/produk/{id}', [ProdukController::class, 'show'])->name('produk.show');
    Route::get('/produk/{id}/edit', [ProdukController::class, 'edit'])->name('produk.edit');
    Route::put('/produk/{id}/update', [ProdukController::class, 'update'])->name('produk.update');
    Route::delete('/produk/{id}/delete', [ProdukController::class, 'destroy'])->name('produk.destroy');

    // MASTER DATA KATEGORI
    Route::get('/kategori/archive', [KategoriController::class, 'archive'])->name('kategori.archive');
    Route::post('/kategori/{id}/restore', [KategoriController::class, 'restore'])->name('kategori.restore');
    Route::get('/kategori', [KategoriController::class, 'index'])->name('kategori.index');
    Route::get('/kategori/create', [KategoriController::class, 'create'])->name('kategori.create');
    Route::post('/kategori/store', [KategoriController::class, 'store'])->name('kategori.store');
    Route::get('/kategori/{id}', [KategoriController::class, 'show'])->name('kategori.show');
    Route::get('/kategori/{id}/edit', [KategoriController::class, 'edit'])->name('kategori.edit');
    Route::put('/kategori/{id}/update', [KategoriController::class, 'update'])->name('kategori.update');
    Route::delete('/kategori/{id}/delete', [KategoriController::class, 'destroy'])->name('kategori.destroy');

    // MASTER DATA SATUAN PRODUK
    Route::get('/satuan-produk/archive', [SatuanProdukController::class, 'archive'])->name('satuan-produk.archive');
    Route::post('/satuan-produk/{id}/restore', [SatuanProdukController::class, 'restore'])->name('satuan-produk.restore');
    Route::get('/satuan-produk', [SatuanProdukController::class, 'index'])->name('satuan-produk.index');
    Route::get('/satuan-produk/create', [SatuanProdukController::class, 'create'])->name('satuan-produk.create');
    Route::post('/satuan-produk/store', [SatuanProdukController::class, 'store'])->name('satuan-produk.store');
    Route::get('/satuan-produk/{id}/edit', [SatuanProdukController::class, 'edit'])->name('satuan-produk.edit');
    Route::put('/satuan-produk/{id}/update', [SatuanProdukController::class, 'update'])->name('satuan-produk.update');
    Route::delete('/satuan-produk/{id}/delete', [SatuanProdukController::class, 'destroy'])->name('satuan-produk.destroy');

    // MANAJEMEN PENGGUNA
    Route::get('/pengguna', [PenggunaController::class, 'index'])->name('pengguna.index');
    Route::get('/pengguna/create', [PenggunaController::class, 'create'])->name('pengguna.create');
    Route::post('/pengguna', [PenggunaController::class, 'store'])->name('pengguna.store');
    Route::get('/pengguna/{id}/edit', [PenggunaController::class, 'edit'])->name('pengguna.edit');
    Route::put('/pengguna/{id}', [PenggunaController::class, 'update'])->name('pengguna.update');
    Route::delete('/pengguna/{id}', [PenggunaController::class, 'destroy'])->name('pengguna.destroy');

    // AKSI TRANSAKSI & STOK (ADMIN ONLY)
    Route::get('/transaksi/archive', [TransaksiController::class, 'archive'])->name('transaksi.archive');
    Route::post('/transaksi/{id}/restore', [TransaksiController::class, 'restore'])->name('transaksi.restore');
    Route::get('/transaksi/export', [TransaksiController::class, 'exportExcel'])->name('transaksi.export');
    Route::delete('/transaksi/{id}/delete', [TransaksiController::class, 'destroy'])->name('transaksi.destroy');
    Route::get('/stok/archive', [PergerakanStokController::class, 'archive'])->name('stok.archive');
    Route::post('/stok/{id}/restore', [PergerakanStokController::class, 'restore'])->name('stok.restore');
    Route::delete('/stok/{id}/delete', [PergerakanStokController::class, 'destroy'])->name('stok.destroy');
    Route::get('stok-export', [PergerakanStokController::class, 'exportExcel'])->name('stok.export');
});

// =========================
// AKSES UMUM (ADMIN & KASIR)
// =========================
Route::middleware('auth.custom')->group(function () {
    // =========================
    // DASHBOARD
    // =========================
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // =========================
    // TRANSAKSI
    // =========================
    Route::get('/transaksi', [TransaksiController::class, 'index'])->name('transaksi.index');
    Route::get('/transaksi/create', [TransaksiController::class, 'create'])->name('transaksi.create');
    Route::post('/transaksi/store', [TransaksiController::class, 'store'])->name('transaksi.store');
    Route::get('/transaksi/{id}', [TransaksiController::class, 'show'])->name('transaksi.show');
    
    // =========================
    // STOK (PERGERAKAN)
    // =========================
    Route::get('/stok', [PergerakanStokController::class, 'index'])->name('stok.index');
    Route::get('/stok/create', [PergerakanStokController::class, 'create'])->name('stok.create'); 
    Route::post('/stok', [PergerakanStokController::class, 'store'])->name('stok.store'); 
    Route::get('/stok/{stok}', [PergerakanStokController::class, 'show'])->name('stok.show');
    Route::get('stok/{id}/cetak', [PergerakanStokController::class, 'cetak'])->name('stok.cetak');
    
    // =========================
    // PROFIL / PENGATURAN AKUN
    // =========================
    Route::get('/profil', [\App\Http\Controllers\ProfilController::class, 'edit'])->name('profil.edit');
    Route::put('/profil', [\App\Http\Controllers\ProfilController::class, 'update'])->name('profil.update');

    // =========================
    // KASIR (ALIAS)
    // =========================
    Route::get('/kasir', [TransaksiController::class, 'create'])->name('kasir');
    Route::post('/kasir/store', [TransaksiController::class, 'store'])->name('kasir.store');
});