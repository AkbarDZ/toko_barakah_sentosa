@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-4">
    
    {{-- BARIS TOMBOL: Disembunyikan saat dicetak --}}
    <div class="d-flex justify-content-between align-items-center mb-4 d-print-none">
        <h3 class="mb-0 fw-bold text-dark" style="font-size: 1.4rem;">
            <i class="fas fa-file-invoice text-primary me-2"></i> Detail Transaksi
        </h3>
        <div>
            {{-- Mengubah fungsi onclick agar memanggil script custom kita --}}
            <button type="button" onclick="cetakNotaDenganNamaKode()" class="btn btn-success mr-2">
                <i class="fas fa-print me-2"></i> Cetak Nota
            </button>
            <a href="{{ route('transaksi.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i> Kembali
            </a>
        </div>
    </div>

    {{-- KONTEN NOTA UTAMA --}}
    <div class="row" id="nota-cetak">
        {{-- SISI KIRI: INFORMASI UTAMA NOTA --}}
        <div class="col-md-4 column-info">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4">
                    {{-- Judul Toko Tambahan Saat Print --}}
                    <div class="d-none d-print-block text-center mb-4">
                        <h4 class="fw-bold mb-1">NOTA PENJUALAN</h4>
                        <p class="text-muted small mb-0">Terima Kasih Telah Berbelanja</p>
                        <hr>
                    </div>

                    <h5 class="fw-bold text-secondary border-bottom pb-2 mb-3 judul-seksi">Informasi Nota</h5>
                    
                    <div class="mb-3">
                        <label class="text-muted small d-block">Kode Transaksi</label>
                        <span class="fw-bold text-dark fs-5">{{ $transaksi->kode_transaksi }}</span>
                    </div>

                    <div class="mb-3">
                        <label class="text-muted small d-block">Tanggal Transaksi</label>
                        <span class="text-dark fw-medium">{{ \Carbon\Carbon::parse($transaksi->created_at)->format('d F Y H:i') }}</span>
                    </div>

                    <hr>

                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Total Tagihan:</span>
                        <span class="fw-bold text-dark">Rp {{ number_format($transaksi->total_tagihan, 0, ',', '.') }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Jumlah Bayar:</span>
                        <span class="fw-bold text-success">Rp {{ number_format($transaksi->jumlah_bayar, 0, ',', '.') }}</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Kembalian:</span>
                        <span class="fw-bold text-primary">Rp {{ number_format($transaksi->kembalian, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- SISI KANAN: DAFTAR BARANG YANG DIBELI --}}
        <div class="col-md-8 column-tabel">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <h5 class="fw-bold text-secondary border-bottom pb-2 mb-3 judul-seksi">Daftar Produk Terbeli</h5>

                    <div class="table-responsive border rounded table-print">
                        <table class="table table-hover align-middle mb-0" style="font-size: 0.9rem;">
                            <thead class="table-light text-dark fw-semibold">
                                <tr>
                                    <th width="50" class="ps-3 py-3">No</th>
                                    <th class="py-3">Nama Produk</th>
                                    <th class="py-3">Satuan Jual</th>
                                    <th class="py-3 text-center">Qty</th>
                                    <th class="py-3 text-end">Harga Satuan</th>
                                    <th class="py-3 text-end pe-3">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($transaksi->detailTransaksi as $index => $detail)
                                    <tr class="border-bottom">
                                        <td class="ps-3 text-secondary">{{ $index + 1 }}</td>
                                        <td>
                                            <span class="fw-bold text-dark d-block">
                                                {{ $detail->satuanProduk->produk->nama_produk ?? 'Produk Tidak Diketahui' }}
                                            </span>
                                            <small class="text-muted d-print-none">{{ $detail->satuanProduk->produk->kode_produk ?? '-' }}</small>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark border">
                                                {{ $detail->satuanProduk->nama_satuan ?? '-' }}
                                            </span>
                                        </td>
                                        <td class="text-center fw-semibold">{{ $detail->kuantiti }}</td>
                                        <td class="text-end">Rp {{ number_format($detail->harga_jual, 0, ',', '.') }}</td>
                                        <td class="text-end fw-bold text-dark pe-3">Rp {{ number_format($detail->subtotal, 0, ',', '.') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-4">Tidak ada detail item pada transaksi ini.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>
    </div>

</div>

{{-- JAVASCRIPT: Mengubah nama file cetak PDF menjadi Kode Transaksi --}}
<script>
function cetakNotaDenganNamaKode() {
    // 1. Simpan judul halaman asli aplikasi ("Warung App") ke variabel sementara
    let judulAsliHalaman = document.title;
    
    // 2. Ubah judul halaman web saat ini menggunakan kode transaksi produk terkait
    document.title = "{{ $transaksi->kode_transaksi }}";
    
    // 3. Panggil jendela print cetak browser
    window.print();
    
    // 4. Kembalikan judul halaman web ke awal setelah jendela print ditutup / selesai
    setTimeout(function() {
        document.title = judulAsliHalaman;
    }, 10);
}
</script>

{{-- CSS KHUSUS PRINT --}}
<style>
@media print {
    body * {
        visibility: hidden;
    }
    #nota-cetak, #nota-cetak * {
        visibility: visible;
    }
    #nota-cetak {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
        margin: 0;
        padding: 0;
    }
    .column-info {
        width: 35% !important;
        float: left !important;
    }
    .column-tabel {
        width: 65% !important;
        float: left !important;
    }
    .card {
        border: none !important;
        box-shadow: none !important;
    }
    .table-print {
        border: 1px solid #dee2e6 !important;
    }
}
</style>
@endsection