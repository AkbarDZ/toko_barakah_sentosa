@extends('layouts.app')

@section('content')

    <div class="card">
        <div class="card-body">

            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3 class="mb-0 fw-bold text-dark">Daftar Satuan Produk</h3>
                @if(session('user_role') === 'admin')
                    <a href="{{ route('satuan-produk.create') }}" class="btn btn-primary shadow-sm">
                        <i class="fas fa-plus"></i> Tambah Satuan Produk
                    </a>
                @endif
            </div>


            <div class="table-responsive">
                <table class="table table-hover table-bordered align-middle custom-datatable">

                    <thead class="bg-light">
                        <tr>
                            <th style="width:50px;">No</th>
                            <th>Produk</th>
                            <th>Nama Satuan</th>
                            <th>Harga Beli</th>
                            <th>Harga Jual</th>
                            <th style="width:170px;" class="text-center">Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($data as $d)
                            <tr>
                                <td>{{ $loop->iteration }}</td>

                                <td>
                                    <b>{{ $d->produk->nama_produk ?? '-' }}</b>
                                </td>

                                <td>
                                    <span class="badge bg-info text-light">
                                        {{ $d->nama_satuan }}
                                    </span>
                                </td>

                                <td>
                                    Rp {{ number_format($d->harga_beli) }}
                                </td>

                                <td>
                                    <b class="text-success">
                                        Rp {{ number_format($d->harga_jual) }}
                                    </b>
                                </td>

                                <td class="text-center">
                                    <div class="d-flex justify-content-center">
                                        @if(session('user_role') === 'admin')
                                            <a href="{{ route('satuan-produk.edit', $d->id_satuan) }}"
                                                class="btn btn-sm btn-warning text-white mr-1">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>

                                            <form action="{{ route('satuan-produk.destroy', $d->id_satuan) }}" method="POST"
                                                onsubmit="return confirm('Yakin ingin menghapus data ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger">
                                                    <i class="fas fa-trash-alt"></i> Hapus
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center">
                                    Data belum ada
                                </td>
                            </tr>
                        @endforelse
                    </tbody>

                </table>
            </div>

        </div>
    </div>

@endsection