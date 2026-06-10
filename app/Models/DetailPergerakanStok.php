<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetailPergerakanStok extends Model
{
    use HasFactory;

    protected $table = 'detail_pergerakan_stok';
    protected $primaryKey = 'id_detail';
    protected $guarded = ['id_detail'];

    // Relasi balik ke Header
    public function pergerakanStok()
    {
        return $this->belongsTo(PergerakanStok::class, 'id_pergerakan', 'id_pergerakan');
    }

    // Pindahan relasi dari model PergerakanStok sebelumnya
    public function satuanProduk()
    {
        return $this->belongsTo(SatuanProduk::class, 'id_satuan', 'id_satuan');
    }

    // Karena SatuanProduk terhubung ke Produk (berdasarkan ERD), 
    // kita bisa menarik relasi Produk secara tidak langsung dari SatuanProduk
    public function produk()
    {
        return $this->hasOneThrough(
            Produk::class,
            SatuanProduk::class,
            'id_satuan', // Foreign key di tabel satuan_produk
            'id_produk', // Foreign key di tabel produk
            'id_satuan', // Local key di tabel detail_pergerakan_stok
            'id_produk'  // Local key di tabel satuan_produk
        );
    }

    // Accessor dipindahkan ke sini
    // Mengambil snapshot_kode_produk yang sudah disimpan di tabel ini
    public function getKodeBarangAttribute()
    {
        return $this->snapshot_kode_produk;
    }
}