<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Kategori;

class KategoriController extends Controller
{
    public function index()
    {
        $kategori = Kategori::latest()->get();
        return view('backend.kategori.index', compact('kategori'));
    }

    public function create()
    {
        return view('backend.kategori.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_kategori' => 'required'
        ]);

        Kategori::create($request->all());

        return redirect()->route('kategori.index')
            ->with('success', 'Kategori berhasil ditambahkan');
    }

    public function show(string $id)
    {
        $kategori = Kategori::findOrFail($id);
        return view('backend.kategori.show', compact('kategori'));
    }

    public function edit(string $id)
    {
        $kategori = Kategori::findOrFail($id);
        return view('backend.kategori.edit', compact('kategori'));
    }

    public function update(Request $request, string $id)
    {
        $kategori = Kategori::findOrFail($id);

        $request->validate([
            'nama_kategori' => 'required'
        ]);

        $kategori->update($request->all());

        return redirect()->route('kategori.index')
            ->with('success', 'Kategori berhasil diupdate');
    }

    public function destroy(string $id)
    {
        Kategori::destroy($id);

        return redirect()->route('kategori.index')
            ->with('success', 'Kategori berhasil dihapus');
    }

    public function archive()
    {
        $kategori = Kategori::onlyTrashed()->get();
        return view('backend.kategori.archive', compact('kategori'));
    }

    public function restore($id)
    {
        $kategori = Kategori::withTrashed()->findOrFail($id);
        $kategori->restore();
        return redirect()->route('kategori.archive')->with('success', 'Kategori berhasil dipulihkan.');
    }
}