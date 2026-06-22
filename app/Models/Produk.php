<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Produk extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'produk';
    protected $primaryKey = 'id_produk';
    
    // Kosongkan guarded agar tidak ada kolom yang diblokir oleh Laravel
    protected $guarded = []; 

    protected $appends = ['gambar_url'];

    public function getGambarUrlAttribute()
    {
        if ($this->direktori_gambar) {
            return asset('storage/' . $this->direktori_gambar);
        }
        return null;
    }

    public function kategori()
    {
        return $this->belongsTo(Kategori::class, 'id_kategori', 'id_kategori');
    }

    public function satuanProduk()
    {
        return $this->hasMany(SatuanProduk::class, 'id_produk', 'id_produk');
    }
}