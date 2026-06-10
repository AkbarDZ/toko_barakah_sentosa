@extends('layouts.app')

@section('content')

<div class="card">
    <div class="card-body">

        <h3>Edit Produk</h3>

        <form action="{{ route('produk.update', $produk->id_produk) }}" method="POST"
            enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="form-group mb-2">
                <label>Nama Produk</label>
                <input type="text" name="nama_produk" class="form-control" value="{{ $produk->nama_produk }}"
                    required>
            </div>

            <div class="form-group mb-2">
                <label>Kategori</label>
                <select name="id_kategori" class="form-control">
                    @foreach($kategori as $k)
                        <option value="{{ $k->id_kategori }}"
                            {{ $produk->id_kategori == $k->id_kategori ? 'selected' : '' }}>
                            {{ $k->nama_kategori }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group mb-2">
                <label>Deskripsi</label>
                <textarea name="deskripsi" class="form-control">{{ $produk->deskripsi }}</textarea>
            </div>

            <div class="form-group mb-2">
                <label>Gambar Produk</label>
                <input type="file" name="direktori_gambar" class="form-control">
                <input type="hidden" name="old_direktori_gambar" value="{{ $produk->direktori_gambar }}">
                @if($produk->direktori_gambar)
                    <small class="text-muted">Gambar saat ini: {{ $produk->direktori_gambar }}</small>
                @endif
            </div>
            <div class="mt-2">
                @if($produk->direktori_gambar)
                    <div class="mb-2">
                        <p class="mb-1">Preview Gambar Lama:</p>
                        <img src="{{ asset('storage/' . $produk->direktori_gambar) }}"
                            alt="Old Image" style="max-width: 200px; border: 1px solid #ddd; padding: 5px;">
                    </div>
                @endif
                <div id="new-preview-container" style="display: none;">
                    <p class="mb-1">Preview Gambar Baru:</p>
                    <img id="new-preview" src="#" alt="New Image Preview"
                        style="max-width: 200px; border: 1px solid #ddd; padding: 5px;">
                </div>
            </div>

            <button type="submit" class="btn btn-success mt-3">Update</button>
            <a href="{{ route('produk.index') }}" class="btn btn-secondary mt-3">Kembali</a>

        </form>

    </div>
</div>

@endsection
