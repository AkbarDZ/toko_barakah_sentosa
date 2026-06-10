<?php

namespace Database\Factories;

use App\Models\Produk;
use App\Models\Kategori;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProdukFactory extends Factory
{
    protected $model = Produk::class;

    public function definition(): array
    {
        $produkSembako = [
            'Beras Sentra Ramos 5kg', 'Minyak Goreng Bimoli 1L', 'Minyak Goreng Sania 2L',
            'Gula Pasir Gulaku 1kg', 'Telur Ayam Ras 1kg', 'Indomie Goreng Spesiial',
            'Sedaap Soto Kuah', 'Kopi Kapal Api Mix', 'Teh Celup Sosro',
            'Susu Kental Manis Frisian Flag', 'Kecap Manis Bango 135ml', 'Garam Dapur Cap Kapal',
            'Masako Rasa Ayam', 'Royco Rasa Sapi', 'Sabun Lifebuoy Merah',
            'Shampoo Sunsilk Sachet', 'Pepsodent White 120g', 'Deterjen Rinso Anti Noda 1kg',
            'Sabun Cuci Piring Mama Lemon', 'Obat Nyamuk Hit Semprot'
        ];

        // Pakai randomElement biasa tanpa unique() karena jumlah produk yang kita buat (misal 30)
        // mungkin lebih banyak dari daftar di atas, jadi tidak apa-apa kalau ada nama yang sama/mirip.
        $namaProduk = $this->faker->randomElement($produkSembako);

        return [
            'kode_produk' => 'SBK' . strtoupper($this->faker->unique()->bothify('#######')),
            'id_kategori' => Kategori::factory(),
            'nama_produk' => $namaProduk,
            'deskripsi' => 'Stok produk ' . $namaProduk . ' untuk warung sembako.',
            'direktori_gambar' => 'images/produk/sembako-default.jpg',
            // Untuk toko kecil, stoknya biasanya berkisar antara 5 sampai 50 biji/bungkus
            'total_stok_terkecil' => $this->faker->numberBetween(5, 50),
        ];
    }
}
