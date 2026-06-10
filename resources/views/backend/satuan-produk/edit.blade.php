@extends('layouts.app')

@section('content')

<div class="card">
    <div class="card-body">

        <h3>Edit Satuan Produk</h3>

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('satuan-produk.update', $data->id_satuan) }}" method="POST">
            @csrf
            @method('PUT')

            <!-- PRODUK -->
            <div class="mb-3">
                <label>Produk</label>
                <select name="id_produk" class="form-control" required>
                    @foreach($produk as $p)
                        <option value="{{ $p->id_produk }}"
                            {{ $data->id_produk == $p->id_produk ? 'selected' : '' }}>
                            {{ $p->nama_produk }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- NAMA SATUAN -->
            <div class="mb-3">
                <label>Nama Satuan</label>
                <input type="text" name="nama_satuan"
                    value="{{ $data->nama_satuan }}"
                    class="form-control" required>
            </div>

            <!-- KUANTITI PER SATUAN -->
            <div class="mb-3">
                <label>Kuantiti Per Satuan</label>
                <input type="number" name="kuantiti_per_satuan" class="form-control" required value="{{ $data->kuantiti_per_satuan ?? 1 }}">
            </div>
            
            <!-- HARGA BELI -->
            <div class="mb-3">
                <label>Harga Beli</label>
                <input type="number" name="harga_beli"
                    value="{{ $data->harga_beli }}"
                    class="form-control" required>
            </div>

            <!-- HARGA JUAL -->
            <div class="mb-3">
                <label>Harga Jual</label>
                <input type="number" name="harga_jual"
                    value="{{ $data->harga_jual }}"
                    class="form-control" required>
            </div>


            

            <button type="submit" class="btn btn-success">Update</button>
            <a href="{{ route('satuan-produk.index') }}" class="btn btn-secondary">Kembali</a>

        </form>

    </div>
</div>

@endsection