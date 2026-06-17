@extends('layouts.app')

@section('content')

{{-- Bagian alert pesan error dari session --}}
@if(session('error'))
    <div class="alert alert-danger mb-3">{{ session('error') }}</div>
@endif

{{-- TEMPAT ALERT DINAMIS JAVASCRIPT --}}
<div id="js-alert-container"></div>

<div class="card">
    <div class="card-body">

        <h3>Kasir / Transaksi Baru</h3>

        <form action="{{ route('transaksi.store') }}" method="POST">
            @csrf

            <div class="row mb-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label">Pilih Produk</label>
                    {{-- Menggunakan class 'use-select2' sesuai dengan contoh acuan --}}
                    <select id="produk" class="form-control use-select2">
                        <option value="">-- Pilih Produk --</option>
                        @foreach($produk as $p)
                            @php
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

                <div class="col-md-2">
                    <label class="form-label">Sisa Stok</label>
                    <input type="text" id="info-stok" class="form-control" readonly value="0" style="background-color: #e9ecef; font-weight: bold; text-align: center;">
                </div>

                <div class="col-md-3">
                    <label class="form-label">Qty</label>
                    <input type="number" id="qty" class="form-control" min="1" value="1">
                </div>

                <div class="col-md-3">
                    <button type="button" onclick="tambahItem()" class="btn btn-primary w-100">
                        <i class="mdi mdi-cart-plus me-1"></i> + Tambah Ke Keranjang
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

            <h4 class="mt-3">Total Tagihan: Rp <span id="total">0</span></h4>

            <div class="row mt-3">
                <div class="col-md-4">
                    <label class="form-label">Nominal Bayar (Rp)</label>
                    <input type="number" name="jumlah_bayar" id="bayar" class="form-control" placeholder="Masukkan jumlah uang">
                </div>
            </div>

            <button type="submit" class="btn btn-success mt-3">
                Simpan Transaksi
            </button>
        </form>

    </div>
</div>

<script>
let total = 0;
let itemIndex = 0; 

$(document).ready(function() {
    // Re-inisialisasi/Pastikan select2 terpasang dengan tema bootstrap yang benar
    $('.use-select2').select2({
        theme: 'bootstrap4', 
        width: '100%',
        placeholder: '-- Pilih Produk --'
    });

    // Event handler jQuery Select2 untuk mendeteksi perubahan produk
    $('#produk').on('select2:select', function (e) {
        updateInfoStok();
    });
});

function tampilkanAlert(pesan, tipe = 'danger') {
    let alertContainer = document.getElementById('js-alert-container');
    let htmlAlert = `
        <div class="alert alert-${tipe} alert-dismissible fade show border-0 shadow-sm mb-3" role="alert">
            <strong>Peringatan:</strong> ${pesan}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close" style="float: right; background: none; border: none; font-weight: bold; color: inherit;">X</button>
        </div>
    `;
    alertContainer.innerHTML = htmlAlert;
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function updateInfoStok() {
    // Menggunakan jQuery agar sinkron dengan manipulasi DOM dari Select2
    let selected = $('#produk').find(':selected');
    
    if (!selected.val()) {
        document.getElementById('info-stok').value = "0";
        return;
    }
    
    let stokTersedia = selected.data('stok');
    let namaSatuan = selected.data('satuan');
    document.getElementById('info-stok').value = stokTersedia + " " + namaSatuan;
}

function tambahItem() {
    let selected = $('#produk').find(':selected');

    if (!selected.val()) {
        return tampilkanAlert('Silahkan pilih produk terlebih dahulu melalui kolom pencarian!');
    }

    let id = selected.val();
    let nama = selected.data('nama');
    let harga = parseInt(selected.data('harga'));
    let qty = parseInt(document.getElementById('qty').value);
    
    let maxStok = parseInt(selected.data('stok'));
    let satuan = selected.data('satuan');

    if (isNaN(qty) || qty < 1) {
        return tampilkanAlert('Isi kuantitas (Qty) belanja dengan benar minimal 1!');
    }

    if (maxStok <= 0) {
        return tampilkanAlert(`Stok untuk produk <strong>${nama}</strong> sudah habis!`, 'danger');
    }

    if (qty > maxStok) {
        return tampilkanAlert(`Stok tidak mencukupi! Sisa stok <strong>${nama}</strong> tersedia hanya: ${maxStok} ${satuan}`, 'warning');
    }

    document.getElementById('js-alert-container').innerHTML = '';

    let subtotal = harga * qty;
    total += subtotal;

    let row = `
        <tr>
            <td>${nama} (${satuan})</td>
            <td>${qty}</td>
            <td>Rp ${harga.toLocaleString()}</td>
            <td>Rp ${subtotal.toLocaleString()}</td>
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
    document.getElementById('total').innerText = total.toLocaleString();
    
    // Potong sisa data stok di sisi client menggunakan jQuery data method
    selected.data('stok', maxStok - qty);
    updateInfoStok();

    itemIndex++; 
    document.getElementById('qty').value = 1; 
    
    // Reset Select2 ke posisi default awal setelah sukses tambah data
    $('.use-select2').val('').trigger('change');
}

function hapusItem(btn, subtotal, id, qty) {
    btn.closest('tr').remove();
    total -= subtotal;
    document.getElementById('total').innerText = total.toLocaleString();

    // Kembalikan stok menggunakan pencarian elemen option jQuery
    let option = $('#produk').find(`option[value="${id}"]`);
    if(option.length) {
        let currentStok = parseInt(option.data('stok'));
        option.data('stok', currentStok + parseInt(qty));
    }
    
    updateInfoStok();
}
</script>

@endsection