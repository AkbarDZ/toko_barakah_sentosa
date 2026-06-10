<?php

namespace App\Http\Controllers;

use App\Models\Produk; // Pastikan model Produk sudah di-import di atas
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request) // 1. PASTIKAN ada (Request $request) di sini
    {
        // 2. MASUKKAN KODE PENCARIAN & PAGINATION DI SINI
        $search = $request->get('search');

        $produk = Produk::when($search, function($query) use ($search) {
                        return $query->where('nama_produk', 'like', "%{$search}%");
                    })
                    ->with('satuanProduk') // Opsional: Eager loading agar query lebih cepat & ringan
                    ->paginate(12)
                    ->withQueryString();

        // 3. Ambil data untuk tabel sebelah kanan (Daftar Harga Terkini)
        // Sesuaikan dengan logic lama Anda, contoh mengambil 5 produk terbaru:
        $produklatest = Produk::orderBy('created_at', 'desc')->take(5)->get(); 

        // 4. Kirim variabel $produk dan $produklatest ke view
        
        return view('backend.dashboard', compact('produk', 'produklatest')); 
        // Gantilah 'dashboard' di atas dengan nama file blade Anda (misal: 'warung.index' jika filenya di warung/index.blade.php)
    }
}