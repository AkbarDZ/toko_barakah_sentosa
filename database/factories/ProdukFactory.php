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
            'Gula Pasir Gulaku 1kg', 'Telur Ayam Ras 1kg', 'Indomie Goreng Spesial',
            'Sedaap Soto Kuah', 'Kopi Kapal Api Mix', 'Teh Celup Sosro',
            'Susu Kental Manis Frisian Flag', 'Kecap Manis Bango 135ml', 'Garam Dapur Cap Kapal',
            'Masako Rasa Ayam', 'Royco Rasa Sapi', 'Sabun Lifebuoy Merah',
            'Shampoo Sunsilk Sachet', 'Pepsodent White 120g', 'Deterjen Rinso Anti Noda 1kg',
            'Sabun Cuci Piring Mama Lemon', 'Obat Nyamuk Hit Semprot',
            'Beras Rojo Lele 5kg', 'Beras Pandan Wangi 5kg', 'Minyak Goreng Sunco 2L',
            'Minyak Goreng Filma 1L', 'Gula Pasir Rose Brand 1kg', 'Telur Bebek 1kg',
            'Indomie Ayam Bawang', 'Indomie Kari Ayam', 'Sedaap Mie Goreng',
            'Kopi Luwak White Koffie', 'Kopi ABC Susu', 'Teh Pucuk Harum 350ml',
            'Teh Kotak 200ml', 'Susu Beruang Bear Brand', 'Susu Ultra Milk Coklat 250ml',
            'Saus Sambal ABC 340ml', 'Garam Dapur Cap Refina',
            'Sabun Nuvo Biru', 'Sabun Giv Putih', 'Shampoo Clear Men Sachet',
            'Deterjen Daia Putih 1kg', 'Pewangi Downy Mystique', 'Obat Nyamuk Baygon Bakar'
        ];

        // Pakai unique() agar tidak ada nama produk yang terduplikat
        $namaProduk = $this->faker->unique()->randomElement($produkSembako);

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
