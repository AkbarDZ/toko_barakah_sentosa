<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Produk;
use App\Models\Kategori;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\ImageManagerStatic as Image;

class ProdukController extends Controller
{
    public function index()
    {
        $produk = Produk::with('kategori')->latest()->get();
        return response()->json([
            'success' => true,
            'data' => $produk
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_kategori'      => 'required|exists:kategori,id_kategori',
            'nama_produk'      => 'required|string|max:100',
            'deskripsi'        => 'nullable|string|max:255',
            'direktori_gambar' => 'nullable|image|mimes:jpeg,png,jpg|max:5120',
        ]);

        $pathGambar = null;

        if ($request->hasFile('direktori_gambar')) {
            $file = $request->file('direktori_gambar');
            $filename = Str::random(20) . '.' . $file->getClientOriginalExtension();
            $pathGambar = 'produk/' . $filename;

            $img = Image::make($file->getRealPath());
            $img->resize(600, null, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize(); 
            });

            $encoded = (string) $img->encode('jpg', 75);
            Storage::disk('public')->put($pathGambar, $encoded);
        }

        $kategoriDigit = str_pad($request->id_kategori, 2, '0', STR_PAD_LEFT);
        $words = explode(' ', trim($request->nama_produk));
        $singkatan = '';
        
        foreach ($words as $word) {
            $singkatan .= strtoupper(substr($word, 0, 1));
            if (strlen($singkatan) >= 3) break;
        }
        
        if (strlen($singkatan) < 3) {
            $cleanName = preg_replace('/[^A-Za-z]/', '', $request->nama_produk);
            $singkatan = strtoupper(substr($cleanName, 0, 3));
        }
        
        $singkatan = str_pad($singkatan, 3, 'X');
        $prefix = $kategoriDigit . '-' . $singkatan . '-'; 

        $kodeProduk = DB::transaction(function () use ($pathGambar, $prefix, $validated) {
            
            $lastProduct = DB::table('produk')
                ->where('kode_produk', 'like', $prefix . '%')
                ->orderBy('kode_produk', 'desc')
                ->lockForUpdate()
                ->first();

            $sequence = 1;
            if ($lastProduct) {
                $lastSequence = (int) substr($lastProduct->kode_produk, -3);
                $sequence = $lastSequence + 1;
            }

            $urutDigit = str_pad($sequence, 3, '0', STR_PAD_LEFT);
            $finalKode = $prefix . $urutDigit;

            DB::table('produk')->insert([
                'kode_produk'         => $finalKode,
                'id_kategori'         => $validated['id_kategori'],
                'nama_produk'         => $validated['nama_produk'],
                'deskripsi'           => $validated['deskripsi'] ?? null,
                'direktori_gambar'    => $pathGambar,
                'total_stok_terkecil' => 0,
                'created_at'          => now(),
                'updated_at'          => now(),
            ]);

            return $finalKode;
        });

        $produk = Produk::where('kode_produk', $kodeProduk)->first();

        return response()->json([
            'success' => true,
            'message' => "Produk berhasil ditambah dengan kode: {$kodeProduk}",
            'data' => $produk
        ], 201);
    }

    public function show($id)
    {
        $produk = Produk::with('kategori')->findOrFail($id);
        
        return response()->json([
            'success' => true,
            'data' => $produk
        ]);
    }

    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'id_kategori'      => 'required|exists:kategori,id_kategori',
            'nama_produk'      => 'required|string|max:100',
            'deskripsi'        => 'nullable|string|max:255',
            'direktori_gambar' => 'nullable|image|mimes:jpeg,png,jpg|max:5120',
        ]);

        $produk = DB::table('produk')->where('id_produk', $id)->first();

        if (!$produk) {
            return response()->json([
                'success' => false,
                'message' => 'Data produk tidak ditemukan.'
            ], 404);
        }

        $pathGambar = $produk->direktori_gambar;

        if ($request->hasFile('direktori_gambar')) {
            if ($pathGambar && Storage::disk('public')->exists($pathGambar)) {
                Storage::disk('public')->delete($pathGambar);
            }

            $file = $request->file('direktori_gambar');
            $filename = Str::random(20) . '.' . $file->getClientOriginalExtension();
            $pathGambar = 'produk/' . $filename;

            $img = Image::make($file->getRealPath());
            
            $img->resize(600, null, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize(); 
            });

            $encoded = (string) $img->encode('jpg', 75);
            Storage::disk('public')->put($pathGambar, $encoded);
        }

        DB::table('produk')->where('id_produk', $id)->update([
            'id_kategori'      => $validated['id_kategori'],
            'nama_produk'      => $validated['nama_produk'],
            'deskripsi'        => $validated['deskripsi'] ?? null,
            'direktori_gambar' => $pathGambar,
            'updated_at'       => now(),
        ]);

        $updatedProduk = Produk::where('id_produk', $id)->first();

        return response()->json([
            'success' => true,
            'message' => "Produk dengan kode {$produk->kode_produk} berhasil diupdate",
            'data' => $updatedProduk
        ]);
    }

    public function destroy(string $id)
    {
        $produk = Produk::findOrFail($id);

        if ($produk->direktori_gambar && Storage::disk('public')->exists($produk->direktori_gambar)) {
            Storage::disk('public')->delete($produk->direktori_gambar);
        }

        $produk->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Produk dihapus'
        ]);
    }
}
