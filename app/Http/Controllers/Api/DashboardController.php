<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Produk;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');

        $produk = Produk::when($search, function($query) use ($search) {
                        return $query->where('nama_produk', 'like', "%{$search}%");
                    })
                    ->with('satuanProduk') 
                    ->paginate(12)
                    ->withQueryString();

        $produklatest = Produk::orderBy('created_at', 'desc')->take(5)->get(); 

        return response()->json([
            'success' => true,
            'data' => [
                'produk' => $produk,
                'produklatest' => $produklatest
            ]
        ]);
    }
}
