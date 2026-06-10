@extends('layouts.app')

@section('content')

<div class="card">
    <div class="card-body">

        <h3>Tambah Kategori</h3>

        <form action="{{ route('kategori.store') }}" method="POST">
            @csrf

            <div class="form-group mb-2">
                <label>Nama Kategori</label>
                <input type="text" name="nama_kategori" class="form-control" required>
            </div>

            <div class="form-group mb-2">
                <label>Deskripsi</label>
                <textarea name="deskripsi" class="form-control"></textarea>
            </div>

            <button type="submit" class="btn btn-success mt-3">Simpan</button>
            <a href="{{ route('kategori.index') }}" class="btn btn-secondary mt-3">Kembali</a>
        </form>

    </div>
</div>

@endsection