<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SatuanProduk;

class SatuanProdukController extends Controller
{
    public function index()
    {
        $data = SatuanProduk::with('produk')->latest()->get();
        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_produk' => 'required|exists:produk,id_produk',
            'nama_satuan' => 'required',
            'kuantiti_per_satuan' => 'required|numeric',
            'harga_beli' => 'required|numeric',
            'harga_jual' => 'required|numeric',
        ]);

        $satuan = SatuanProduk::create([
            'id_produk' => $request->id_produk,
            'nama_satuan' => $request->nama_satuan,
            'kuantiti_per_satuan' => $request->kuantiti_per_satuan,
            'harga_beli' => $request->harga_beli,
            'harga_jual' => $request->harga_jual,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Data berhasil ditambahkan',
            'data' => $satuan
        ], 201);
    }

    public function show($id)
    {
        $data = SatuanProduk::with('produk')->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    public function update(Request $request, $id)
    {
        $data = SatuanProduk::findOrFail($id);

        $request->validate([
            'id_produk' => 'required|exists:produk,id_produk',
            'nama_satuan' => 'required',
            'harga_beli' => 'required|numeric',
            'harga_jual' => 'required|numeric',
            'kuantiti_per_satuan' => 'required|numeric'
        ]);

        $data->update([
            'id_produk' => $request->id_produk,
            'nama_satuan' => $request->nama_satuan,
            'harga_beli' => $request->harga_beli,
            'harga_jual' => $request->harga_jual,
            'kuantiti_per_satuan' => $request->kuantiti_per_satuan,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Data berhasil diupdate',
            'data' => $data
        ]);
    }

    public function destroy($id)
    {
        $data = SatuanProduk::findOrFail($id);
        $data->delete();

        return response()->json([
            'success' => true,
            'message' => 'Data berhasil dihapus'
        ]);
    }
}
