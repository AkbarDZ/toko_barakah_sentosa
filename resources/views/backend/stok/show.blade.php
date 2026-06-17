@extends('layouts.app')

@section('content')

<div class="card">
    <div class="card-body">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3>Detail Pergerakan Stok: <span class="text-primary">{{ $pergerakan->kode_pergerakan }}</span></h3>
            <div>
                <a href="{{ route('stok.index') }}" class="btn btn-secondary">Kembali</a>
                <a href="{{ route('stok.cetak', $pergerakan->id_pergerakan) }}" target="_blank" class="btn btn-success">
                    <i class="fas fa-print"></i> Cetak
                </a>
                @if(session('user_role') === 'admin')
                <form action="{{ route('stok.destroy', $pergerakan->id_pergerakan) }}" method="POST" class="d-inline"
                    onsubmit="return confirm('Yakin ingin menghapus dokumen ini? Data akan masuk ke arsip.')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash-alt"></i> Hapus
                    </button>
                </form>
                @endif
            </div>
        </div>

        <div class="card bg-light mb-4">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <p class="mb-1 text-muted">Tanggal Pergerakan</p>
                        <h6 class="font-weight-bold">{{ \Carbon\Carbon::parse($pergerakan->tanggal_pergerakan)->format('d F Y') }}</h6>
                    </div>
                    <div class="col-md-3">
                        <p class="mb-1 text-muted">Tipe Pergerakan</p>
                        <h6>
                            @if(strtolower($pergerakan->tipe_pergerakan) == 'masuk')
                                <span class="badge bg-success text-white">Barang Masuk</span>
                            @elseif(strtolower($pergerakan->tipe_pergerakan) == 'keluar')
                                <span class="badge bg-danger text-white">Barang Keluar</span>
                            @else
                                <span class="badge bg-warning text-dark">Penyesuaian</span>
                            @endif
                        </h6>
                    </div>
                    <div class="col-md-3">
                        <p class="mb-1 text-muted">Dicatat Oleh</p>
                        <h6 class="font-weight-bold">
                            <i class="fas fa-user-circle text-secondary mr-1"></i>
                            {{ session('user_name') ?? 'N/A' }}
                            <small class="text-muted">({{ session('user_role') }})</small>
                        </h6>
                    </div>
                    <div class="col-md-3">
                        <p class="mb-1 text-muted">Catatan</p>
                        <h6>{{ $pergerakan->catatan ?? '-' }}</h6>
                    </div>
                </div>
            </div>
        </div>

        <h5>Daftar Item Barang & Mutasi Stok</h5>
        <div class="table-responsive mt-3">
            <table class="table table-bordered table-striped">
                <thead class="bg-dark text-white">
                    <tr>
                        <th width="50px" class="text-center">No</th>
                        <th>Produk</th>
                        <th class="text-center">Kuantiti Input</th>
                        <th class="text-right">Mutasi Stok (Terkecil)</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        // Menentukan visual penambahan atau pengurangan
                        $tipe = strtolower($pergerakan->tipe_pergerakan);
                        $operator = ($tipe == 'keluar') ? '-' : '+';
                        $textColor = ($tipe == 'keluar') ? 'text-danger' : 'text-success';
                        if($tipe == 'penyesuaian') $textColor = 'text-primary';
                    @endphp

                    @forelse($pergerakan->detail as $index => $item)
                        @php
                            // Mengambil angka pengali dari master data satuan. 
                            // Fallback ke 1 jika data master satuan kebetulan sudah terhapus.
                            $pengali = $item->satuanProduk ? $item->satuanProduk->kuantiti_per_satuan : 1;
                            $mutasiTerkecil = $item->kuantiti * $pengali;
                        @endphp
                    <tr>
                        <td class="text-center align-middle">{{ $index + 1 }}</td>
                        <td>
                            <span class="badge bg-secondary mb-1 text-white">{{ $item->snapshot_kode_produk }}</span><br>
                            <strong>{{ $item->snapshot_nama_produk }}</strong>
                        </td>
                        <td class="text-center align-middle">
                            <span class="font-weight-bold" style="font-size: 1.1em;">{{ $item->kuantiti }}</span> 
                            {{ $item->snapshot_nama_satuan }}
                        </td>
                        <td class="text-right align-middle">
                            <h5 class="{{ $textColor }} mb-0">
                                {{ $operator }} {{ number_format($mutasiTerkecil, 0, ',', '.') }}
                            </h5>
                            <small class="text-muted">(Nilai Konversi: x{{ $pengali }})</small>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center">Tidak ada detail barang untuk pergerakan ini.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>
</div>

@endsection