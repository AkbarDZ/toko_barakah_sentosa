<?php

namespace Database\Factories;

use App\Models\Kategori;
use Illuminate\Database\Eloquent\Factories\Factory;

class KategoriFactory extends Factory
{
    protected $model = Kategori::class;

    public function definition(): array
    {
        $kategoriSembako = [
            'Bahan Pokok',      // Beras, minyak, gula, telur
            'Mie & Makanan Instan',
            'Minuman & Susu',
            'Bumbu Dapur',      // Garam, kecap, penyedap, saus
            'Sabun & Perlengkapan Mandi',
            'Kebutuhan Rumah Tangga' // Obat nyamuk, deterjen, tisu
        ];

        return [
            // Mengambil kategori secara acak dan dipastikan tidak duplikat
            'nama_kategori' => $this->faker->unique()->randomElement($kategoriSembako),
            'deskripsi' => 'Kategori untuk produk sembako sehari-hari.',
        ];
    }
}
