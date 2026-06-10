@extends('layouts.app')

@section('content')

<div class="card">
    <div class="card-body">

        <h3>Tambah Produk</h3>

        <form action="{{ route('produk.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="form-group mb-2">
                <label>Nama Produk</label>
                <input type="text" name="nama_produk" class="form-control">
            </div>

            <div class="form-group mb-2">
                <label>Kategori</label>
                <select name="id_kategori" class="form-control use-select2">
                    @foreach($kategori as $k)
                        <option value="{{ $k->id_kategori }}">{{ $k->nama_kategori }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group mb-2">
                <label>Deskripsi</label>
                <textarea name="deskripsi" class="form-control"></textarea>
            </div>

            <div class="form-group mb-2">
                <label>Gambar Produk</label>
                <input type="file" name="direktori_gambar" class="form-control" accept="image/jpeg,image/png,image/jpg">
            </div>
            <div id="new-preview-container" style="display: none;" class="mt-2">
                <p class="mb-1">Preview Gambar:</p>
                <img id="new-preview" src="#" alt="New Image Preview"
                    style="max-width: 200px; border: 1px solid #ddd; padding: 5px;">
            </div>

            <button type="submit" class="btn btn-success mt-3">Simpan</button>
            <a href="{{ route('produk.index') }}" class="btn btn-secondary mt-3">Kembali</a>
        </form>

    </div>
</div>

@endsection

