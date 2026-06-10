@extends('layouts.app')

@section('content')

    {{-- @if(session('success'))
        <div class="alert alert-success mb-3">{{ session('success') }}</div>
    @endif --}}

    {{-- BAGIAN ATAS: RIWAYAT TRANSAKSI --}}
    <div class="card shadow-sm mb-4">
        <div class="card-body">

            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4 class="mb-0 text-secondary"><i class="fas fa-history"></i> Data Transaksi</h4>
                <div>
                    <button type="button" class="btn btn-success mr-2" data-toggle="modal" data-target="#modalExportExcel">
                        <i class="fas fa-file-excel"></i> Export Excel
                    </button>
                    <a href="{{ route('transaksi.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Tambah Transaksi Baru
                    </a>
                </div>
            </div>

            {{-- Template Filter Tanggal Otomatis untuk DataTables UI --}}
            <div id="filter-date-transaksi" style="display: none;">
                <label style="margin-right: 20px;">
                    Tanggal:
                    <input type="date" id="start-date" class="form-control form-control-sm"
                        style="display:inline-block; width:auto; margin-left:5px;">
                </label>
                <label>
                    Sampai:
                    <input type="date" id="end-date" class="form-control form-control-sm"
                        style="display:inline-block; width:auto; margin-left:5px;">
                </label>
            </div>

            <div class="table-responsive">
                <table class="table table-hover table-bordered custom-datatable" data-nosort="7"
                    data-date-template="#filter-date-transaksi" data-date-start="#start-date" data-date-end="#end-date"
                    data-date-col="6">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Kode Transaksi</th>
                            <th>Kode Produk</th>
                            <th>Total</th>
                            <th>Bayar</th>
                            <th>Kembalian</th>
                            <th>Tanggal</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($transaksi as $t)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>
                                    {{-- Menampilkan kode transaksi asli dari database (Urut Ascending dari Controller) --}}
                                    <strong>{{ $t->kode_transaksi }}</strong>
                                </td>
                                <td>
                                    {{-- Menampilkan kode produk asli lewat relasi detail transaksi pertama --}}
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
                                        style="display:none;">{{ \Carbon\Carbon::parse($t->created_at)->format('Y-m-d') }}</span>
                                    {{ \Carbon\Carbon::parse($t->created_at)->format('d/m/Y') }}
                                </td>
                                <td class="text-center">
                                    <div class="d-flex justify-content-center">
                                        <a href="{{ route('transaksi.show', $t->id_transaksi) }}"
                                            class="btn btn-sm btn-info text-white mr-1">
                                            <i class="fas fa-eye"></i> Detail
                                        </a>
                                        <div class="d-flex justify-content-center">
                                            @if(session('user_role') === 'admin')
                                                <form action="{{ route('transaksi.destroy', $t->id_transaksi) }}" method="POST"
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
                        @endforeach
                    </tbody>
                </table>
            </div>

        </div>
    </div>

    {{-- BAGIAN BAWAH: LIVE PENJUALAN PRODUK --}}
    <div class="card shadow-sm">
        <div class="card-body">

            <h4 class="mb-3 text-secondary">
                <i class="fas fa-chart-line"></i> Live Penjualan Produk
            </h4>

            <div class="table-responsive">
                <table class="table table-hover table-bordered custom-datatable">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th width="150px">Kode Produk</th>
                            <th>Nama Produk</th>
                            <th class="text-center">Total Terjual</th>
                            <th class="text-right">Total Omset</th>
                            <th class="text-right">Total Keuntungan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $grandTotalOmset = 0;
                            $grandTotalKeuntungan = 0;
                        @endphp

                        @foreach($livePenjualan ?? [] as $index => $p)
                            @php
                                $grandTotalOmset += $p['total_omset'];
                                $grandTotalKeuntungan += $p['total_keuntungan'];
                            @endphp
                            <tr>
                                <td class="align-middle">{{ $index + 1 }}</td>
                                <td class="align-middle"><span
                                        class="badge bg-light text-dark border">{{ $p['kode_produk'] }}</span></td>
                                <td class="align-middle"><strong>{{ $p['nama_produk'] }}</strong></td>
                                <td class="text-center align-middle">
                                    <span class="badge bg-info text-white px-3 py-1.5 font-weight-bold" style="font-size: 1em;">
                                        {{ number_format($p['total_terjual'], 0, ',', '.') }} {{ $p['satuan_jual'] }}
                                    </span>
                                </td>
                                <td class="text-right align-middle text-success font-weight-bold">
                                    Rp {{ number_format($p['total_omset'], 0, ',', '.') }}
                                </td>
                                <td class="text-right align-middle text-primary font-weight-bold">
                                    Rp {{ number_format($p['total_keuntungan'], 0, ',', '.') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>

                    @if(count($livePenjualan ?? []) > 0)
                        <tfoot class="bg-light font-weight-bold" style="background-color: #f8f9fa;">
                            <tr>
                                <td colspan="3" class="text-center align-middle text-dark" style="font-size: 1.05em;">TOTAL
                                    KESELURUHAN</td>
                                <td class="text-center align-middle text-muted small" style="font-style: italic;">
                                    (Terinci per satuan)
                                </td>
                                <td class="text-right align-middle text-success" style="font-size: 1.1em;">
                                    Rp {{ number_format($grandTotalOmset, 0, ',', '.') }}
                                </td>
                                <td class="text-right align-middle text-primary" style="font-size: 1.1em;">
                                    Rp {{ number_format($grandTotalKeuntungan, 0, ',', '.') }}
                                </td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>

        </div>
    </div>

    {{-- MODAL EXPORT EXCEL --}}
    <div class="modal fade" id="modalExportExcel" tabindex="-1" aria-labelledby="modalExportExcelLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('transaksi.export') }}" method="GET">
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title" id="modalExportExcelLabel">
                            <i class="fas fa-file-excel"></i> Export Laporan Transaksi
                        </h5>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p class="text-muted mb-3">Pilih rentang waktu data yang ingin Anda unduh.</p>

                        <div class="form-group mb-3">
                            <label>Dari Tanggal:</label>
                            <input type="date" name="start_date" class="form-control" required value="{{ date('Y-m-01') }}">
                        </div>

                        <div class="form-group mb-2">
                            <label>Sampai Tanggal:</label>
                            <input type="date" name="end_date" class="form-control" required value="{{ date('Y-m-d') }}">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-download"></i> Unduh Excel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection