<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PergerakanStok extends Model
{
    use HasFactory;

    protected $table = 'pergerakan_stok';
    protected $primaryKey = 'id_pergerakan';
    protected $guarded = ['id_pergerakan'];

    // Hanya menyisakan relasi ke detail
    public function detail()
    {
        return $this->hasMany(DetailPergerakanStok::class, 'id_pergerakan', 'id_pergerakan');
    }
}
