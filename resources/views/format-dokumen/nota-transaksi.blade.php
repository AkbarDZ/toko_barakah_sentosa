<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Nota {{ $transaksi->kode_transaksi }}</title>
    <style>
        @page {
            margin: 24px;
        }

        body {
            color: #172a46;
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            margin: 0;
        }

        .header {
            border-bottom: 2px solid #5b67f1;
            margin-bottom: 18px;
            padding-bottom: 13px;
            text-align: center;
        }

        .brand {
            color: #172a46;
            font-size: 19px;
            font-weight: bold;
            letter-spacing: .5px;
            margin: 0 0 3px;
        }

        .subtitle {
            color: #7f8da2;
            margin: 0;
        }

        .meta {
            background: #f5f6ff;
            border-radius: 8px;
            margin-bottom: 16px;
            padding: 12px 14px;
        }

        .meta-table,
        .items,
        .totals {
            border-collapse: collapse;
            width: 100%;
        }

        .meta-table td {
            padding: 3px 0;
        }

        .label {
            color: #8b96a8;
            width: 38%;
        }

        .value {
            font-weight: bold;
            text-align: right;
        }

        .items {
            margin-bottom: 14px;
        }

        .items th {
            background: #172a46;
            color: #fff;
            font-size: 9px;
            padding: 8px 6px;
            text-align: left;
        }

        .items td {
            border-bottom: 1px solid #e6e9f0;
            padding: 9px 6px;
            vertical-align: top;
        }

        .items .number {
            text-align: center;
            width: 6%;
        }

        .items .right {
            text-align: right;
        }

        .product-name {
            font-weight: bold;
            margin-bottom: 2px;
        }

        .product-code {
            color: #929cad;
            font-size: 8px;
        }

        .totals {
            margin-left: auto;
            width: 58%;
        }

        .totals td {
            padding: 4px 0;
        }

        .grand-total td {
            border-top: 1px solid #ccd2dd;
            font-size: 12px;
            font-weight: bold;
            padding-top: 8px;
        }

        .paid {
            color: #16a56a;
            font-weight: bold;
        }

        .change {
            color: #5b67f1;
            font-weight: bold;
        }

        .footer {
            border-top: 1px dashed #cdd3de;
            color: #8b96a8;
            margin-top: 22px;
            padding-top: 12px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="header">
        <p class="brand">TOKO BARAKAH</p>
        <p class="subtitle">Nota Penjualan</p>
    </div>

    <div class="meta">
        <table class="meta-table">
            <tr>
                <td class="label">Kode Transaksi</td>
                <td class="value">{{ $transaksi->kode_transaksi }}</td>
            </tr>
            <tr>
                <td class="label">Tanggal</td>
                <td class="value">
                    {{ \Carbon\Carbon::parse($transaksi->created_at)->format('d/m/Y H:i') }}
                </td>
            </tr>
        </table>
    </div>

    <table class="items">
        <thead>
            <tr>
                <th class="number">No</th>
                <th>Produk</th>
                <th class="right">Qty</th>
                <th class="right">Harga</th>
                <th class="right">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($transaksi->detailTransaksi as $index => $detail)
                <tr>
                    <td class="number">{{ $index + 1 }}</td>
                    <td>
                        <div class="product-name">
                            {{ $detail->satuanProduk->produk->nama_produk ?? 'Produk' }}
                        </div>
                        <div class="product-code">
                            {{ $detail->satuanProduk->produk->kode_produk ?? '-' }}
                            / {{ $detail->satuanProduk->nama_satuan ?? '-' }}
                        </div>
                    </td>
                    <td class="right">{{ number_format($detail->kuantiti, 0, ',', '.') }}</td>
                    <td class="right">Rp {{ number_format($detail->harga_jual, 0, ',', '.') }}</td>
                    <td class="right">Rp {{ number_format($detail->subtotal, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="totals">
        <tr class="grand-total">
            <td>Total Tagihan</td>
            <td class="right">Rp {{ number_format($transaksi->total_tagihan, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td>Jumlah Bayar</td>
            <td class="right paid">Rp {{ number_format($transaksi->jumlah_bayar, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td>Kembalian</td>
            <td class="right change">Rp {{ number_format($transaksi->kembalian, 0, ',', '.') }}</td>
        </tr>
    </table>

    <div class="footer">
        Terima kasih telah berbelanja di Toko Barakah.
    </div>
</body>
</html>
