@extends('layouts.app')

@section('content')

{{-- Bagian alert pesan error jika ada --}}
@if(session('error'))
    <div class="alert alert-danger mb-3">{{ session('error') }}</div>
@endif

<div class="card">
    <div class="card-body">

        <h3>Kasir</h3>

        <form action="{{ route('transaksi.store') }}" method="POST">
            @csrf

            <div class="row mb-3">
                <div class="col-md-4">
                    <label>Pilih Produk</label>
                    <select id="produk" class="form-control" onchange="updateInfoStok()">
                        <option value="">-- pilih --</option>
                        @foreach($produk as $p)
                            @php
                                // Mengambil sisa stok fisik dan hitung batas konversi berdasarkan satuannya
                                $stokFisik = $p->produk->total_stok_terkecil ?? 0;
                                $pengali = $p->kuantiti_per_satuan ?? 1;
                                $stokKonversi = $pengali > 0 ? floor($stokFisik / $pengali) : 0;
                            @endphp
                            <option value="{{ $p->id_satuan }}"
                                data-harga="{{ $p->harga_jual }}"
                                data-nama="{{ $p->produk->nama_produk }}"
                                data-satuan="{{ $p->nama_satuan }}"
                                data-stok="{{ $stokKonversi }}">
                                {{ $p->produk->nama_produk }} ({{ $p->nama_satuan }}) - Rp {{ number_format($p->harga_jual) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- TEMPAT BUAT LIAT ADA BERAPA STOK BARANG (DI SEBELAH QTY) --}}
                <div class="col-md-2">
                    <label>Sisa Stok</label>
                    <input type="text" id="info-stok" class="form-control" readonly value="0" style="background-color: #e9ecef; font-weight: bold; text-center;">
                </div>

                <div class="col-md-3">
                    <label>Qty</label>
                    <input type="number" id="qty" class="form-control" min="1" value="1">
                </div>

                <div class="col-md-3 d-flex align-items-end">
                    <button type="button" onclick="tambahItem()" class="btn btn-primary w-100">
                        + Tambah
                    </button>
                </div>
            </div>

            <hr>

            <table class="table table-bordered" id="tableItem">
                <thead>
                    <tr>
                        <th>Produk</th>
                        <th>Qty</th>
                        <th>Harga</th>
                        <th>Subtotal</th>
                        <th>Aksi</th>
                    </tr>
                </thead>

                <tbody></tbody>
            </table>

            <h4>Total: Rp <span id="total">0</span></h4>

            <div class="mt-3">
                <label>Bayar</label>
                <input type="number" name="jumlah_bayar" id="bayar" class="form-control">
            </div>

            <button class="btn btn-success mt-3">
                Simpan Transaksi
            </button>

        </form>

    </div>
</div>

<script>
let total = 0;
let itemIndex = 0; 

// Fungsi baru untuk memunculkan angka stok saat produk dipilih di dropdown
function updateInfoStok() {
    let select = document.getElementById('produk');
    let selected = select.options[select.selectedIndex];
    
    if (!selected.value) {
        document.getElementById('info-stok').value = "0";
        return;
    }
    
    let stokTersedia = selected.dataset.stok;
    let namaSatuan = selected.dataset.satuan;
    document.getElementById('info-stok').value = stokTersedia + " " + namaSatuan;
}

function tambahItem() {
    let select = document.getElementById('produk');
    let selected = select.options[select.selectedIndex];

    let id = selected.value;
    let nama = selected.dataset.nama;
    let harga = parseInt(selected.dataset.harga);
    let qty = parseInt(document.getElementById('qty').value);
    
    // Ambil data stok & nama satuan dari atribut data HTML
    let maxStok = parseInt(selected.dataset.stok);
    let satuan = selected.dataset.satuan;

    if (!id) return alert('Pilih produk dulu');

    // 1. Peringatan Validasi jika stok di database kosong/habis
    if (maxStok <= 0) {
        return alert('Peringatan: Stok anda habis!');
    }

    // 2. Peringatan Validasi jika input qty kasir melebihi kapasitas stok barang
    if (qty > maxStok) {
        return alert('Peringatan: Stok tidak mencukupi! Sisa stok tersedia: ' + maxStok + ' ' + satuan);
    }

    let subtotal = harga * qty;
    total += subtotal;

    let row = `
        <tr>
            <td>${nama} (${satuan})</td>
            <td>${qty}</td>
            <td>${harga}</td>
            <td>${subtotal}</td>
            <td>
                <button type="button" onclick="hapusItem(this, ${subtotal}, '${id}', ${qty})" class="btn btn-danger btn-sm">
                    Hapus
                </button>
            </td>
            <input type="hidden" name="produk[${itemIndex}][id_satuan]" value="${id}">
            <input type="hidden" name="produk[${itemIndex}][qty]" value="${qty}">
            <input type="hidden" name="produk[${itemIndex}][harga_jual]" value="${harga}">
        </tr>
    `;

    document.querySelector('#tableItem tbody').insertAdjacentHTML('beforeend', row);
    document.getElementById('total').innerText = total;
    
    // Potong data kuantitas di halaman web sementara supaya tidak bisa over-input di baris berikutnya
    selected.dataset.stok = maxStok - qty;
    updateInfoStok();

    itemIndex++; 
    document.getElementById('qty').value = 1; // Reset input qty ke 1
}

function hapusItem(btn, subtotal, id, qty) {
    btn.closest('tr').remove();
    total -= subtotal;
    document.getElementById('total').innerText = total;

    // Kembalikan jumlah stok di dropdown jika item keranjang dihapus
    let select = document.getElementById('produk');
    for (let i = 0; i < select.options.length; i++) {
        if (select.options[i].value == id) {
            let currentStok = parseInt(select.options[i].dataset.stok);
            select.options[i].dataset.stok = currentStok + parseInt(qty);
            break;
        }
    }
    updateInfoStok();
}
</script>

@endsection