@extends('layouts.app')

@section('content')

<div class="card">
    <div class="card-body">

        <h3>Detail Produk</h3>

        <table class="table">
            <tr>
                <th>Nama Produk</th>
                <td>{{ $produk->nama_produk }}</td>
            </tr>

            <tr>
                <th>Kategori</th>
                <td>{{ $produk->kategori->nama_kategori ?? '-' }}</td>
            </tr>

            <tr>
                <th>Deskripsi</th>
                <td>{{ $produk->deskripsi ?? '-' }}</td>
            </tr>
        </table>

        <a href="{{ route('produk.index') }}" class="btn btn-secondary">Kembali</a>

    </div>
</div>

@endsection