@extends('layouts.app')

@section('content')

<div class="card">
    <div class="card-body">

        <h3>Edit Kategori</h3>

        <form action="{{ route('kategori.update', $kategori->id_kategori) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="form-group mb-2">
                <label>Nama Kategori</label>
                <input type="text" name="nama_kategori" class="form-control"
                    value="{{ $kategori->nama_kategori }}" required>
            </div>

            <div class="form-group mb-2">
                <label>Deskripsi</label>
                <textarea name="deskripsi" class="form-control">{{ $kategori->deskripsi }}</textarea>
            </div>

            <button type="submit" class="btn btn-primary mt-3">Update</button>
            <a href="{{ route('kategori.index') }}" class="btn btn-secondary mt-3">Kembali</a>
        </form>

    </div>
</div>

@endsection