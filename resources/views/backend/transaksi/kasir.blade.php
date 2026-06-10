@extends('layouts.app')

@section('content')

<div class="card">
    <div class="card-body">

        <h3>Kasir</h3>

        <form action="{{ route('transaksi.store') }}" method="POST">
            @csrf

            <div class="row mb-3">
                <div class="col-md-6">
                    <label>Pilih Produk</label>
                    <select id="produk" class="form-control">
                        <option value="">-- pilih --</option>
                        @foreach($produk as $p)
                            <option value="{{ $p->id_satuan }}"
                                data-harga="{{ $p->harga_jual }}"
                                data-nama="{{ $p->produk->nama_produk }}">
                                {{ $p->produk->nama_produk }} - Rp {{ number_format($p->harga_jual) }}
                            </option>
                        @endforeach
                    </select>
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

function tambahItem() {
    let select = document.getElementById('produk');
    let selected = select.options[select.selectedIndex];

    let id = selected.value;
    let nama = selected.dataset.nama;
    let harga = parseInt(selected.dataset.harga);
    let qty = parseInt(document.getElementById('qty').value);

    if (!id) return alert('Pilih produk dulu');

    let subtotal = harga * qty;
    total += subtotal;

    let row = `
        <tr>
            <td>${nama}</td>
            <td>${qty}</td>
            <td>${harga}</td>
            <td>${subtotal}</td>
            <td>
                <button type="button" onclick="hapusItem(this, ${subtotal})" class="btn btn-danger btn-sm">
                    Hapus
                </button>
            </td>

            <input type="hidden" name="produk[][id_satuan]" value="${id}">
            <input type="hidden" name="produk[][qty]" value="${qty}">
            <input type="hidden" name="produk[][harga_jual]" value="${harga}">
        </tr>
    `;

    document.querySelector('#tableItem tbody').insertAdjacentHTML('beforeend', row);

    document.getElementById('total').innerText = total;
}

function hapusItem(btn, subtotal) {
    btn.closest('tr').remove();
    total -= subtotal;
    document.getElementById('total').innerText = total;
}
</script>

@endsection