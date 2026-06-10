<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetailTransaksi extends Model
{
    use HasFactory;

    protected $table = 'detail_transaksi';
    protected $primaryKey = 'id_detail';
    protected $guarded = ['id_detail'];

    public function transaksi()
    {
        return $this->belongsTo(Transaksi::class, 'id_transaksi', 'id_transaksi');
    }

    public function satuanProduk()
    {
        return $this->belongsTo(SatuanProduk::class, 'id_satuan', 'id_satuan');
    }
    // Menambahkan attribute virtual sehingga kita bisa panggil $detail->kode_barang
    public function getKodeBarangAttribute()
    {
        return $this->satuanProduk->produk->kode_barang;
    }
}
