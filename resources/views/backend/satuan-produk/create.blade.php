@extends('layouts.app')

@section('content')
<div class="card">
    <div class="card-body">
        <h3 class="mb-3">Tambah Satuan Produk</h3>

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('satuan-produk.store') }}" method="POST">
            @csrf
            
            <div class="mb-3">
                <label>Pilih Produk</label>
                <select name="id_produk" class="form-control use-select2" required>
                    <option value="">-- Pilih Produk --</option>
                    @foreach($produk as $p)
                        <option value="{{ $p->id_produk }}">{{ $p->nama_produk }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label>Nama Satuan</label>
                <input type="text" name="nama_satuan" class="form-control" placeholder="Format: (nama produk) + (nama satuan), misal: Nutrisari Sachet/Renceng" required>
            </div>

            <div class="mb-3">
                <label>Kuantiti Per Satuan</label>
                <input type="number" name="kuantiti_per_satuan" placeholder="Contoh : 1 jika nama satuan sachet, 12 jika nama satuan renceng" class="form-control" required>
            </div>

            <div class="mb-3">
                <label>Harga Beli</label>
                <input type="number" name="harga_beli" class="form-control" required>
            </div>

            <div class="mb-3">
                <label>Harga Jual</label>
                <input type="number" name="harga_jual" class="form-control" required>
            </div>

            <button type="submit" class="btn btn-primary">Simpan</button>
            <a href="{{ route('satuan-produk.index') }}" class="btn btn-secondary">Batal</a>
        </form>

    </div>
</div>
@endsection