<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Produk extends Model
{
    use HasFactory;

    protected $table = 'produk';
    protected $primaryKey = 'id_produk';
    
    // Kosongkan guarded agar tidak ada kolom yang diblokir oleh Laravel
    protected $guarded = []; 

    public function kategori()
    {
        return $this->belongsTo(Kategori::class, 'id_kategori', 'id_kategori');
    }

    public function satuanProduk()
    {
        return $this->hasMany(SatuanProduk::class, 'id_produk', 'id_produk');
    }
}