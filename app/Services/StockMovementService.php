<?php

namespace App\Services;

use App\Models\PergerakanStok;
use App\Models\DetailPergerakanStok;
use App\Models\SatuanProduk;
use Illuminate\Support\Facades\DB;

class StockMovementService
{
    public function create(string $tipePergerakan, string $tanggalPergerakan, ?string $catatan, array $details): PergerakanStok
    {
        return DB::transaction(function () use ($tipePergerakan, $tanggalPergerakan, $catatan, $details) {
            $tipeInput = strtolower($tipePergerakan);

            $tipeMap = [
                'masuk'       => 'IN',
                'keluar'      => 'OUT',
                'penyesuaian' => 'ADJ'
            ];
            $kodeTipe = $tipeMap[$tipeInput];
            
            $tahunBulan = date('Ym', strtotime($tanggalPergerakan)); 
            $prefix = "{$kodeTipe}-{$tahunBulan}-";

            $lastPergerakan = DB::table('pergerakan_stok')
                ->where('kode_pergerakan', 'like', $prefix . '%')
                ->orderBy('kode_pergerakan', 'desc')
                ->lockForUpdate()
                ->first();

            $sequence = 1;
            if ($lastPergerakan) {
                $lastSequence = (int) substr($lastPergerakan->kode_pergerakan, -4);
                $sequence = $lastSequence + 1;
            }

            $finalKode = $prefix . str_pad($sequence, 4, '0', STR_PAD_LEFT);

            $pergerakan = PergerakanStok::create([
                'kode_pergerakan'    => $finalKode,
                'tipe_pergerakan'    => $tipeInput,
                'tanggal_pergerakan' => $tanggalPergerakan,
                'catatan'            => $catatan,
            ]);

            foreach ($details as $item) {
                $satuan = SatuanProduk::with('produk')->findOrFail($item['id_satuan']);
                $produk = $satuan->produk;

                $pengali = $satuan->kuantiti_per_satuan ?? 1;
                $kuantitiTerkecil = $item['kuantiti'] * $pengali;

                if ($tipeInput === 'keluar') {
                    if ($produk->total_stok_terkecil < $kuantitiTerkecil) {
                        throw new \Exception("Stok tidak mencukupi untuk produk [{$produk->kode_produk}].");
                    }
                    $produk->total_stok_terkecil -= $kuantitiTerkecil;

                } elseif ($tipeInput === 'masuk') {
                    $produk->total_stok_terkecil += $kuantitiTerkecil;

                } elseif ($tipeInput === 'penyesuaian') {
                    $produk->total_stok_terkecil += $kuantitiTerkecil; 
                }

                $produk->save();

                DetailPergerakanStok::create([
                    'id_pergerakan' => $pergerakan->id_pergerakan,
                    'id_satuan'     => $satuan->id_satuan,
                    'kuantiti'      => $item['kuantiti'],
                    'snapshot_kode_produk' => $produk->kode_produk, 
                    'snapshot_nama_produk' => $produk->nama_produk,
                    'snapshot_nama_satuan' => $satuan->nama_satuan,
                    'snapshot_harga_beli'  => $satuan->harga_beli ?? 0, 
                ]);
            }
            
            return $pergerakan;
        });
    }
}
