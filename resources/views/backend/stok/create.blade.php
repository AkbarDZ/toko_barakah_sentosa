@extends('layouts.app')

@section('content')

@if(session('error'))
    <div class="alert alert-danger mb-3">
        <i class="fas fa-times-circle"></i> {{ session('error') }}
    </div>
@endif

<div class="card">
    <div class="card-body">

        <h3>Catat Pergerakan Stok Baru</h3>

        @if ($errors->any())
            <div class="alert alert-danger mb-3">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('stok.store') }}" method="POST">
            @csrf

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group mb-2">
                        <label>Tipe Pergerakan <span class="text-danger">*</span></label>
                        <select name="tipe_pergerakan" class="form-control" required>
                            <option value="">-- Pilih Tipe --</option>
                            <option value="masuk" {{ old('tipe_pergerakan') == 'masuk' ? 'selected' : '' }}>Barang Masuk</option>
                            <option value="keluar" {{ old('tipe_pergerakan') == 'keluar' ? 'selected' : '' }}>Barang Keluar</option>
                            <option value="penyesuaian" {{ old('tipe_pergerakan') == 'penyesuaian' ? 'selected' : '' }}>Penyesuaian / Opname</option>
                        </select>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="form-group mb-2">
                        <label>Tanggal Pergerakan <span class="text-danger">*</span></label>
                        <input type="date" name="tanggal_pergerakan" class="form-control" value="{{ old('tanggal_pergerakan', date('Y-m-d')) }}" required>
                    </div>
                </div>
            </div>

            <div class="form-group mb-4">
                <label>Catatan Keterangan</label>
                <textarea name="catatan" class="form-control" rows="2" placeholder="Cth: Koreksi input PO-123 atau Penerimaan barang baru">{{ old('catatan') }}</textarea>
            </div>

            <h5 class="mt-4 mb-2">Detail Barang</h5>
            <div class="table-responsive">
                <table class="table table-bordered" id="table-detail">
                    <thead class="bg-light">
                        <tr>
                            <th>Produk & Satuan <span class="text-danger">*</span></th>
                            <th width="150px">Kuantiti <span class="text-danger">*</span></th>
                            <th width="80px" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>
                                <select name="details[0][id_satuan]" class="form-control use-select2" required>
                                    <option value="">-- Cari Produk --</option>
                                    @foreach($satuanProduk as $sp)
                                        <option value="{{ $sp->id_satuan }}">
                                            [{{ $sp->produk->kode_barang }}] {{ $sp->produk->nama_produk }} - {{ $sp->nama_satuan }}
                                        </option>
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                <input type="number" name="details[0][kuantiti]" class="form-control" min="1" required>
                            </td>
                            <td class="text-center">
                                <button type="button" class="btn btn-sm btn-danger btn-remove" disabled>X</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <button type="button" class="btn btn-sm btn-info mb-4" id="btn-add-row">Tambah Baris Barang</button>

            <div class="alert alert-warning">
                <strong><i class="fas fa-exclamation-triangle"></i> Peringatan:</strong> Data pergerakan stok bersifat permanen (Immutable) dan tidak dapat diubah atau dihapus setelah disimpan. Pastikan data yang dimasukkan sudah benar.
            </div>

            <div class="mt-3">
                <button type="submit" class="btn btn-success">Simpan Permanen</button>
                <a href="{{ route('stok.index') }}" class="btn btn-secondary">Kembali</a>
            </div>
        </form>

    </div>
</div>

@endsection