<?php

namespace Database\Factories;

use App\Models\SatuanProduk;
use App\Models\Produk;
use Illuminate\Database\Eloquent\Factories\Factory;

class SatuanProdukFactory extends Factory
{
    protected $model = SatuanProduk::class;

    public function definition(): array
    {
        return [
            // Menghubungkan ke Produk secara otomatis jika tidak didefinisikan di seeder
            'id_produk' => Produk::factory(),

            // Nilai default (akan kita timpa menggunakan state / seeder di langkah berikutnya)
            'nama_satuan' => 'pcs',
            'kuantiti_per_satuan' => 1,
            'harga_beli' => 5000,
            'harga_jual' => 6000,
        ];
    }
}
