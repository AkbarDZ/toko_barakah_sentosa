<?php

namespace Database\Seeders;

use App\Models\Produk;
use App\Models\Kategori;
use App\Models\SatuanProduk;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProdukSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Buat 6 kategori sembako seperti sebelumnya
        $categories = Kategori::factory()->count(6)->create();

        // 2. Buat 30 produk acak
        $products = Produk::factory()->count(30)->create([
            'id_kategori' => fn () => $categories->random()->id_kategori,
        ]);

        // 3. Loop setiap produk untuk dibuatkan satuan dan harganya yang sesuai
        foreach ($products as $produk) {
            $namaLower = Str::lower($produk->nama_produk);

            // Logika penentuan satuan & harga berdasarkan nama produk sembako
            if (Str::contains($namaLower, 'sachet') || Str::contains($namaLower, 'shampoo')) {
                $satuan = 'sachet';
                $hargaBeli = 1000;
                $hargaJual = 1500;
            } elseif (Str::contains($namaLower, ['indomie', 'sedaap', 'mie'])) {
                $satuan = 'bks';
                $hargaBeli = 2800;
                $hargaJual = 3500;
            } elseif (Str::contains($namaLower, ['minyak', 'gula', 'beras', 'telur'])) {
                $satuan = 'kg'; // atau 'liter'/'pouch' sesuai kebutuhan
                $hargaBeli = $this->getHargaSembakoUtama($namaLower)['beli'];
                $hargaJual = $this->getHargaSembakoUtama($namaLower)['jual'];
            } elseif (Str::contains($namaLower, ['rokok', 'pack'])) {
                $satuan = 'pack';
                $hargaBeli = 28000;
                $hargaJual = 30000;
            } else {
                // Default untuk barang umum seperti sabun batang, odol, masako, dll
                $satuan = 'pcs';
                $hargaBeli = 4000;
                $hargaJual = 5000;
            }

            // Masukkan data satuan ke database berkaitan dengan id_produk saat ini
            SatuanProduk::factory()->create([
                'id_produk' => $produk->id_produk,
                'nama_satuan' => $satuan,
                'kuantiti_per_satuan' => 1,
                'harga_beli' => $hargaBeli,
                'harga_jual' => $hargaJual,
            ]);
        }
    }

    /**
     * Helper untuk menentukan harga dinamis sembako berat/volume
     */
    private function getHargaSembakoUtama(string $nama): array
    {
        if (Str::contains($nama, 'beras')) {
            return ['beli' => 65000, 'jual' => 72000]; // Harga per 5kg
        }
        if (Str::contains($nama, 'minyak')) {
            return ['beli' => 15000, 'jual' => 17500];
        }
        return ['beli' => 12000, 'jual' => 14000]; // Gula / Telur
    }
}
