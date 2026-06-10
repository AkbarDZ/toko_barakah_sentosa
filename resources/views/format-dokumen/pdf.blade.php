<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Detail Pergerakan Stok - {{ $pergerakan->kode_pergerakan }}</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #333;
            font-size: 13px;
        }
        /* Header Title */
        .header-title {
            font-size: 24px;
            color: #2c3e50;
            margin-bottom: 25px;
        }
        .text-purple {
            color: #6f42c1;
            font-weight: bold;
        }
        
        /* Summary Card Box */
        .summary-box {
            background-color: #f8f9fa;
            border-top: 2px solid #eaeaea;
            border-bottom: 2px solid #eaeaea;
            padding: 15px 20px;
            margin-bottom: 30px;
        }
        .summary-table {
            width: 100%;
            border-collapse: collapse;
        }
        .summary-table td {
            width: 33.33%;
            vertical-align: top;
        }
        .label {
            color: #a0aab5;
            font-size: 12px;
            margin-bottom: 5px;
            display: block;
        }
        .value {
            font-size: 14px;
            font-weight: bold;
            color: #2c3e50;
        }

        /* Badges */
        .badge {
            padding: 4px 8px;
            border-radius: 4px;
            color: #fff;
            font-size: 11px;
            font-weight: bold;
            display: inline-block;
        }
        .badge-masuk { background-color: #20c997; }
        .badge-keluar { background-color: #dc3545; }
        .badge-penyesuaian { background-color: #ffc107; color: #333; }
        .badge-kode { background-color: #6c757d; font-size: 10px; border-radius: 3px; padding: 2px 4px; margin-bottom: 4px;}

        /* Main Table */
        .section-title {
            font-size: 15px;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 10px;
        }
        .item-table {
            width: 100%;
            border-collapse: collapse;
        }
        .item-table th {
            background-color: #343a40;
            color: #ffffff;
            padding: 12px;
            text-align: left;
            font-size: 13px;
        }
        .item-table td {
            padding: 12px;
            border-bottom: 1px solid #f0f0f0;
            vertical-align: middle;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        
        /* Mutasi Colors */
        .mutasi-plus { color: #20c997; font-weight: bold; font-size: 15px; }
        .mutasi-minus { color: #dc3545; font-weight: bold; font-size: 15px; }
        .mutasi-adj { color: #0d6efd; font-weight: bold; font-size: 15px; }
        .mutasi-note { font-size: 10px; color: #adb5bd; margin-top: 3px;}
    </style>
</head>
<body>

    {{-- DEKLARASI VARIABEL GLOBAL UNTUK VIEW INI --}}
    @php
        $tipe = strtolower($pergerakan->tipe_pergerakan);
        
        // Logika Operator dan Warna
        $operator = ($tipe == 'keluar') ? '-' : '+';
        $mutasiClass = ($tipe == 'keluar') ? 'mutasi-minus' : 'mutasi-plus';
        
        if($tipe == 'penyesuaian') { 
            $operator = '~'; 
            $mutasiClass = 'mutasi-adj'; 
        }
    @endphp

    <div class="header-title">
        Detail Pergerakan Stok: <span class="text-purple">{{ $pergerakan->kode_pergerakan }}</span>
    </div>

    <div class="summary-box">
        <table class="summary-table">
            <tr>
                <td>
                    <span class="label">Tanggal Pergerakan</span>
                    <span class="value">{{ \Carbon\Carbon::parse($pergerakan->tanggal_pergerakan)->format('d F Y') }}</span>
                </td>
                <td>
                    <span class="label">Tipe Pergerakan</span>
                    @if($tipe == 'masuk')
                        <span class="badge badge-masuk">Barang Masuk</span>
                    @elseif($tipe == 'keluar')
                        <span class="badge badge-keluar">Barang Keluar</span>
                    @else
                        <span class="badge badge-penyesuaian">Penyesuaian</span>
                    @endif
                </td>
                <td>
                    <span class="label">Catatan</span>
                    <span class="value">{{ $pergerakan->catatan ?? '-' }}</span>
                </td>
            </tr>
        </table>
    </div>

    <div class="section-title">Daftar Item Barang & Mutasi Stok</div>
    
    <table class="item-table">
        <thead>
            <tr>
                <th width="5%" class="text-center">No</th>
                <th width="45%">Produk</th>
                <th width="25%" class="text-center">Kuantiti Input</th>
                <th width="25%" class="text-right">Mutasi Stok (Terkecil)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($pergerakan->detail as $index => $item)
                @php
                    $pengali = $item->satuanProduk ? $item->satuanProduk->kuantiti_per_satuan : 1;
                    $mutasiTerkecil = $item->kuantiti * $pengali;
                @endphp
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>
                    <div class="badge badge-kode">{{ $item->snapshot_kode_produk }}</div><br>
                    <strong>{{ $item->snapshot_nama_produk }}</strong>
                </td>
                <td>
                    <strong>{{ $item->kuantiti }}</strong> {{ $item->snapshot_nama_satuan }}
                </td>
                <td>
                    <div class="{{ $mutasiClass }}">
                        {{ $operator }} {{ number_format($mutasiTerkecil, 0, ',', '.') }}
                    </div>
                    <div class="mutasi-note">(Nilai Konversi: x{{ $pengali }})</div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="4" class="text-center">Tidak ada detail barang.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

</body>
</html>