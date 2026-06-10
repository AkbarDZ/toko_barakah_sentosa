<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PergerakanStok;
use App\Models\DetailPergerakanStok;
use App\Models\SatuanProduk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PergerakanStokController extends Controller
{
    public function index()
    {
        $pergerakan = PergerakanStok::orderBy('tanggal_pergerakan', 'desc')->paginate(10);
        $liveStok = $this->getLiveStokData();
        
        return response()->json([
            'success' => true,
            'data' => [
                'pergerakan' => $pergerakan,
                'live_stok' => $liveStok
            ]
        ]);
    }

    private function getLiveStokData()
    {
        $allProducts = \App\Models\Produk::with(['satuanProduk.detailPergerakanStok.pergerakanStok'])->get();

        return $allProducts->map(function ($produk) {
            $allDetails = $produk->satuanProduk->flatMap(function ($satuan) {
                return $satuan->detailPergerakanStok;
            });

            $latestDetail = $allDetails->sortByDesc(function ($detail) {
                return [
                    $detail->pergerakanStok->tanggal_pergerakan ?? '',
                    $detail->id_detail
                ];
            })->first();

            $kuantitiTerkecilTerakhir = 0;
            if ($latestDetail) {
                $pengali = $latestDetail->satuanProduk->kuantiti_per_satuan ?? 1;
                $kuantitiTerkecilTerakhir = $latestDetail->kuantiti * $pengali;
            }

            return [
                'nama_produk'       => $produk->nama_produk,
                'kode_produk'       => $produk->kode_produk,
                'stok_sekarang'     => $produk->total_stok_terkecil,
                'tanggal_terakhir'  => $latestDetail ? $latestDetail->pergerakanStok->tanggal_pergerakan : null,
                'tipe_terakhir'     => $latestDetail ? strtolower($latestDetail->pergerakanStok->tipe_pergerakan) : null,
                'jumlah_terakhir'   => $kuantitiTerkecilTerakhir,
            ];
        });
    }

    public function store(Request $request)
    {
        $request->validate([
            'tipe_pergerakan' => 'required|in:masuk,keluar,penyesuaian',
            'tanggal_pergerakan' => 'required|date',
            'catatan' => 'nullable|string',
            'details' => 'required|array|min:1',
            'details.*.id_satuan' => 'required|exists:satuan_produk,id_satuan',
            'details.*.kuantiti' => 'required|integer|min:1',
        ]);

        try {
            $pergerakan = DB::transaction(function () use ($request) {
                $tipeInput = strtolower($request->tipe_pergerakan);

                $tipeMap = [
                    'masuk'       => 'IN',
                    'keluar'      => 'OUT',
                    'penyesuaian' => 'ADJ'
                ];
                $kodeTipe = $tipeMap[$tipeInput];
                
                $tahunBulan = date('Ym', strtotime($request->tanggal_pergerakan)); 
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
                    'tanggal_pergerakan' => $request->tanggal_pergerakan,
                    'catatan'            => $request->catatan,
                ]);

                foreach ($request->details as $item) {
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

            return response()->json([
                'success' => true,
                'message' => 'Pergerakan stok berhasil dicatat.',
                'data' => $pergerakan
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function show($id)
    {
        $pergerakan = PergerakanStok::with('detail')->findOrFail($id);
        
        return response()->json([
            'success' => true,
            'data' => $pergerakan
        ]);
    }
}
