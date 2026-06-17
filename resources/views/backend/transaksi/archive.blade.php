@extends('layouts.app')

@section('content')

    <div class="card shadow-sm mb-4">
        <div class="card-body">

            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4 class="mb-0 text-secondary"><i class="fas fa-archive"></i> Arsip Transaksi</h4>
                <a href="{{ route('transaksi.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
            </div>

            <div class="table-responsive">
                <table class="table table-hover table-bordered custom-datatable" data-nosort="7">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Kode Transaksi</th>
                            <th>Kode Produk</th>
                            <th>Total</th>
                            <th>Bayar</th>
                            <th>Kembalian</th>
                            <th>Dihapus Pada</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($transaksi as $t)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>
                                    <strong>{{ $t->kode_transaksi }}</strong>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border">
                                        {{ $t->detailTransaksi->first()->satuanProduk->produk->kode_produk ?? '-' }}
                                    </span>
                                </td>
                                <td class="font-weight-bold">Rp {{ number_format($t->total_tagihan, 0, ',', '.') }}</td>
                                <td class="text-success font-weight-bold">Rp {{ number_format($t->jumlah_bayar, 0, ',', '.') }}
                                </td>
                                <td class="text-primary font-weight-bold">Rp {{ number_format($t->kembalian, 0, ',', '.') }}
                                </td>
                                <td>
                                    <span
                                        style="display:none;">{{ \Carbon\Carbon::parse($t->deleted_at)->format('Y-m-d') }}</span>
                                    {{ \Carbon\Carbon::parse($t->deleted_at)->format('d/m/Y') }}
                                </td>
                                <td class="text-center">
                                    <div class="d-flex justify-content-center">
                                        @if(session('user_role') === 'admin')
                                            <form action="{{ route('transaksi.restore', $t->id_transaksi) }}" method="POST"
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
                        @endforeach
                    </tbody>
                </table>
            </div>

        </div>
    </div>

@endsection
