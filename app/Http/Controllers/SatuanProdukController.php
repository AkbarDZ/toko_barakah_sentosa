<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SatuanProduk;
use App\Models\Produk;

class SatuanProdukController extends Controller
{
    public function index()
    {
        $data = SatuanProduk::with('produk')->latest()->get();
        return view('backend.satuan-produk.index', compact('data'));
    }

    public function create()
    {
        $produk = Produk::all();
        return view('backend.satuan-produk.create', compact('produk'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_produk' => 'required',
            'nama_satuan' => 'required',
            'kuantiti_per_satuan' => 'required|numeric',
            'harga_beli' => 'required|numeric',
            'harga_jual' => 'required|numeric',
        ]);

        SatuanProduk::create([
            'id_produk' => $request->id_produk,
            'nama_satuan' => $request->nama_satuan,
            'kuantiti_per_satuan' => $request->kuantiti_per_satuan,
            'harga_beli' => $request->harga_beli,
            'harga_jual' => $request->harga_jual,
        ]);

        return redirect()->route('satuan-produk.index')
            ->with('success', 'Data berhasil ditambahkan');
    }

    public function edit($id)
    {
        $data = SatuanProduk::findOrFail($id);
        $produk = Produk::all();

        return view('backend.satuan-produk.edit', compact('data', 'produk'));
    }

    public function update(Request $request, $id)
    {
        $data = SatuanProduk::findOrFail($id);

        $request->validate([
            'id_produk' => 'required',
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

        return redirect()->route('satuan-produk.index')
            ->with('success', 'Data berhasil diupdate');
    }

    public function destroy($id)
    {
        $data = SatuanProduk::findOrFail($id);
        $data->delete();

        return redirect()->route('satuan-produk.index')
            ->with('success', 'Data berhasil dihapus');
    }

    public function archive()
    {
        $data = SatuanProduk::with('produk')->onlyTrashed()->get();
        return view('backend.satuan-produk.archive', compact('data'));
    }

    public function restore($id)
    {
        $data = SatuanProduk::withTrashed()->findOrFail($id);
        $data->restore();
        return redirect()->route('satuan-produk.archive')->with('success', 'Satuan Produk berhasil dipulihkan.');
    }
}