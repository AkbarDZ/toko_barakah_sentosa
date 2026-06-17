<?php

namespace App\Http\Controllers;

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
        $kategori = Kategori::all();
        $produk = Produk::with('kategori')->latest()->get();
        return view('backend.produk.index', compact('produk', 'kategori'));
    }

    public function create()
    {
        $kategori = Kategori::all();
        return view('backend.produk.create', compact('kategori'));
    }

    public function store(Request $request)
    {
        // 1. Validasi Input (Sesuaikan rules dengan kebutuhan)
        $validated = $request->validate([
            'id_kategori'      => 'required|exists:kategori,id_kategori',
            'nama_produk'      => 'required|string|max:100',
            'deskripsi'        => 'nullable|string|max:255',
            'direktori_gambar' => 'nullable|image|mimes:jpeg,png,jpg|max:5120', // Maks 5MB
        ]);


        // logika gambar
        $pathGambar = null;

        if ($request->hasFile('direktori_gambar')) {
            $file = $request->file('direktori_gambar');
            $filename = Str::random(20) . '.' . $file->getClientOriginalExtension();
            $pathGambar = 'produk/' . $filename;

            // 1. Baca file gambar (Sintaks V2)
            $img = Image::make($file->getRealPath());
            
            // 2. Resize gambar maksimal lebar 600px, tinggi otomatis (menjaga rasio)
            $img->resize(600, null, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize(); // Cegah gambar kecil jadi pecah/nge-blur
            });

            // 3. Encode ke JPG dengan kualitas 75% lalu jadikan string
            $encoded = (string) $img->encode('jpg', 75);

            // 4. Simpan ke storage Laravel
            Storage::disk('public')->put($pathGambar, $encoded);
        }

        // buat automatic kode_produk
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

        // 6. Return Redirect
        return redirect()->route('produk.index')
            ->with('success', "Produk berhasil ditambah dengan kode: {$kodeProduk}");
    }

    /**
     * TAMPILAN EDIT
     */
    public function edit(string $id)
    {
        $produk = Produk::findOrFail($id);
        $kategori = Kategori::all();

        return view('backend.produk.edit', compact('produk', 'kategori'));
    }

    public function update(Request $request, string $id)
    {
        // 1. Validasi input tanpa menyertakan kode_produk
        $validated = $request->validate([
            'id_kategori'      => 'required|exists:kategori,id_kategori',
            'nama_produk'      => 'required|string|max:100',
            'deskripsi'        => 'nullable|string|max:255',
            'direktori_gambar' => 'nullable|image|mimes:jpeg,png,jpg|max:5120',
        ]);

        // 2. Pastikan produk eksis
        $produk = DB::table('produk')->where('id_produk', $id)->first();

        if (!$produk) {
            return redirect()->route('produk.index')
                ->with('error', 'Data produk tidak ditemukan.');
        }

        $pathGambar = $produk->direktori_gambar;

        // Cek apakah user mengupload gambar baru
        if ($request->hasFile('direktori_gambar')) {
            
            // Hapus gambar lama JIKA ada di storage
            if ($pathGambar && Storage::disk('public')->exists($pathGambar)) {
                Storage::disk('public')->delete($pathGambar);
            }

            // Upload gambar baru dengan logika yang sama seperti store
            $file = $request->file('direktori_gambar');
            $filename = Str::random(20) . '.' . $file->getClientOriginalExtension();
            $pathGambar = 'produk/' . $filename;

            // 1. Baca file gambar (Sintaks V2)
            $img = Image::make($file->getRealPath());
            
            // 2. Resize gambar maksimal lebar 600px, tinggi otomatis (menjaga rasio)
            $img->resize(600, null, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize(); // Cegah gambar kecil jadi pecah/nge-blur
            });

            // 3. Encode ke JPG dengan kualitas 75% lalu jadikan string
            $encoded = (string) $img->encode('jpg', 75);

            // 4. Simpan ke storage Laravel
            Storage::disk('public')->put($pathGambar, $encoded);
        }

        // 3. Update data (tanpa menyentuh kode_produk)
        DB::table('produk')->where('id_produk', $id)->update([
            'id_kategori'      => $validated['id_kategori'],
            'nama_produk'      => $validated['nama_produk'],
            'deskripsi'        => $validated['deskripsi'] ?? null,
            'direktori_gambar' => $pathGambar,
            'updated_at'       => now(),
        ]);

        return redirect()->route('produk.index')
            ->with('success', "Produk dengan kode {$produk->kode_produk} berhasil diupdate");
    }

    public function destroy(string $id)
    {
        $produk = Produk::findOrFail($id);

        // Jangan hapus gambar dari storage saat soft delete, 
        // hanya ketika hard delete (jika ada).
        $produk->delete();
        return redirect()->route('produk.index')->with('success', 'Produk dihapus');
    }

    public function archive()
    {
        $produk = Produk::with('kategori')->onlyTrashed()->get();
        return view('backend.produk.archive', compact('produk'));
    }

    public function restore($id)
    {
        $produk = Produk::withTrashed()->findOrFail($id);
        $produk->restore();
        return redirect()->route('produk.archive')->with('success', 'Produk berhasil dipulihkan.');
    }
}