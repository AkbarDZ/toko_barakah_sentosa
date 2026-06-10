<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SatuanProduk extends Model
{
    use HasFactory;

    protected $table = 'satuan_produk';
    protected $primaryKey = 'id_satuan';
    protected $guarded = ['id_satuan'];

    public function produk()
    {
        return $this->belongsTo(Produk::class, 'id_produk', 'id_produk');
    }

    public function detailTransaksi()
    {
        return $this->hasMany(DetailTransaksi::class, 'id_satuan', 'id_satuan');
    }

    // UPDATE: Ubah dari pergerakanStok menjadi detailPergerakanStok
    // Arahkan ke model DetailPergerakanStok
    public function detailPergerakanStok()
    {
        return $this->hasMany(DetailPergerakanStok::class, 'id_satuan', 'id_satuan');
    }
}