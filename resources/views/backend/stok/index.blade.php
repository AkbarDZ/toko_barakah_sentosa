@extends('layouts.app')

@section('content')

{{-- @if(session('success'))
    <div class="alert alert-success mb-3">{{ session('success') }}</div>
@endif --}}

{{-- BAGIAN ATAS: RIWAYAT DOKUMEN PERGERAKAN STOK --}}
<div class="card shadow-sm mb-4">
    <div class="card-body">
        
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="mb-0 text-secondary"><i class="fas fa-history"></i> Riwayat Pergerakan Stok</h4>
            <div>
                <button type="button" class="btn btn-success mr-2" data-toggle="modal" data-target="#modalExportExcel">
                    <i class="fas fa-file-excel"></i> Export Excel
                </button>
                <a href="{{ route('stok.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Catat Pergerakan Baru
                </a>
            </div>
        </div>

        <div id="filter-date-stok" style="display: none;">
            <label style="margin-right: 20px;">
                Tanggal:
                <input type="date" id="start-date" class="form-control form-control-sm" style="display:inline-block; width:auto; margin-left:5px;">
            </label>
            <label >
                Sampai:
                <input type="date" id="end-date" class="form-control form-control-sm" style="display:inline-block; width:auto; margin-left:5px;">
            </label>
        </div>

        <div class="table-responsive">
            <table class="table table-hover table-bordered custom-datatable" 
            data-nosort="4,5" 
            data-date-template="#filter-date-stok"
            data-date-start="#start-date"
            data-date-end="#end-date"
            data-date-col="2">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Kode Dokumen</th>
                        <th>Tanggal</th>
                        <th>Tipe</th>
                        <th>Catatan</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pergerakan as $item)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td><strong>{{ $item->kode_pergerakan }}</strong></td>
                        <td>
                            <span style="display:none;">{{ \Carbon\Carbon::parse($item->tanggal_pergerakan)->format('Y-m-d') }}</span>
                            {{ \Carbon\Carbon::parse($item->tanggal_pergerakan)->format('d/m/Y') }}
                        </td>
                        <td>
                            @if(strtolower($item->tipe_pergerakan) == 'masuk')
                                <span class="badge bg-success text-white">Masuk</span>
                            @elseif(strtolower($item->tipe_pergerakan) == 'keluar')
                                <span class="badge bg-danger text-white">Keluar</span>
                            @else
                                <span class="badge bg-warning text-dark">Penyesuaian</span>
                            @endif
                        </td>
                        <td>{{ $item->catatan ?? '-' }}</td>
                        <td class="text-center">
                            <div class="d-flex justify-content-center">
                                <a href="{{ route('stok.show', $item->id_pergerakan) }}" class="btn btn-sm btn-info text-white">
                                    <i class="fas fa-eye"></i> Detail
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

    </div>
</div>

{{-- BAGIAN BAWAH: LIVE STOK PRODUK FISIK --}}
<div class="card shadow-sm">
    <div class="card-body">
        
        <h4 class="mb-3 text-secondary"><i class="fas fa-boxes"></i> Live Stok Produk (Satuan Terkecil)</h4>
        
        <div class="table-responsive">
            <table class="table table-hover table-bordered custom-datatable">
                <thead>
                    <tr>
                        <th width="150px">Kode Produk</th>
                        <th>Nama Produk</th>
                        <th class="text-right">Total Stok (Fisik)</th>
                        <th>Aktivitas Terakhir</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($liveStok as $stok)
                    <tr>
                        <td class="align-middle"><span class="badge bg-light text-dark border">{{ $stok['kode_produk'] }}</span></td>
                        <td class="align-middle"><strong>{{ $stok['nama_produk'] }}</strong></td>
                        <td class="text-right align-middle font-weight-bold" style="font-size: 1.15em;">
                            {{ number_format($stok['stok_sekarang'], 0, ',', '.') }}
                        </td>
                        <td class="align-middle">
                            @if($stok['tanggal_terakhir'])
                                <span class="text-muted mr-2" style="display:inline-block; width: 85px;">
                                    {{ \Carbon\Carbon::parse($stok['tanggal_terakhir'])->format('d/m/Y') }}
                                </span>
                                
                                {{-- Indikator mutasi stok --}}
                                @if($stok['tipe_terakhir'] == 'masuk')
                                    <span class="badge bg-success text-white px-2 py-1">
                                        <i class="fas fa-arrow-up"></i> Masuk {{ $stok['jumlah_terakhir'] }}
                                    </span>
                                @elseif($stok['tipe_terakhir'] == 'keluar')
                                    <span class="badge bg-danger text-white px-2 py-1">
                                        <i class="fas fa-arrow-down"></i> Keluar {{ $stok['jumlah_terakhir'] }}
                                    </span>
                                @else
                                    <span class="badge bg-warning text-dark px-2 py-1">
                                        <i class="fas fa-sync"></i> Penyesuaian {{ $stok['jumlah_terakhir'] }}
                                    </span>
                                @endif
                            @else
                                <span class="text-muted" style="font-style: italic;">Belum ada aktivitas mutasi.</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

    </div>
</div>

{{-- modal Export Excel --}}

<div class="modal fade" id="modalExportExcel" tabindex="-1" aria-labelledby="modalExportExcelLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('stok.export') }}" method="GET">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="modalExportExcelLabel">
                        <i class="fas fa-file-excel"></i> Export Laporan Pergerakan Stok
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
                        <input type="date" name="end_date" class="form-control" required value="{{ date('Y-m-t') }}">
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