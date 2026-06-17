@extends('layouts.app')

@section('content')

    <div class="card">
        <div class="card-body">

            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3 class="mb-0 fw-bold text-dark">Arsip Satuan Produk</h3>
                <a href="{{ route('satuan-produk.index') }}" class="btn btn-secondary shadow-sm">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
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
                                            <form action="{{ route('satuan-produk.restore', $d->id_satuan) }}" method="POST"
                                                onsubmit="return confirm('Yakin ingin memulihkan data ini?')">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-success">
                                                    <i class="fas fa-trash-restore"></i> Pulihkan
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center">
                                    Data arsip belum ada
                                </td>
                            </tr>
                        @endforelse
                    </tbody>

                </table>
            </div>

        </div>
    </div>

@endsection
