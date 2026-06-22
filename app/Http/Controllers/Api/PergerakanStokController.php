<?php

namespace App\Http\Controllers\Api;

use App\Exports\PergerakanStokExport;
use App\Http\Controllers\Controller;
use App\Models\PergerakanStok;
use App\Models\Produk;
use App\Services\StockMovementService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Throwable;

class PergerakanStokController extends Controller
{
    public function index()
    {
        $pergerakan = PergerakanStok::with('detail')
            ->orderBy('tanggal_pergerakan', 'desc')
            ->paginate(10);
        $liveStok = $this->getLiveStokData();

        return response()->json([
            'success' => true,
            'data' => [
                'pergerakan' => $pergerakan,
                'live_stok' => $liveStok,
            ],
        ]);
    }

    private function getLiveStokData()
    {
        $allProducts = Produk::with(['satuanProduk.detailPergerakanStok.pergerakanStok'])->get();

        return $allProducts->map(function ($produk) {
            $allDetails = $produk->satuanProduk->flatMap(function ($satuan) {
                return $satuan->detailPergerakanStok;
            });

            $latestDetail = $allDetails->sortByDesc(function ($detail) {
                return [
                    $detail->pergerakanStok->tanggal_pergerakan ?? '',
                    $detail->id_detail,
                ];
            })->first();

            $kuantitiTerkecilTerakhir = 0;
            if ($latestDetail) {
                $pengali = $latestDetail->satuanProduk->kuantiti_per_satuan ?? 1;
                $kuantitiTerkecilTerakhir = $latestDetail->kuantiti * $pengali;
            }

            return [
                'nama_produk' => $produk->nama_produk,
                'kode_produk' => $produk->kode_produk,
                'stok_sekarang' => $produk->total_stok_terkecil,
                'tanggal_terakhir' => $latestDetail ? $latestDetail->pergerakanStok->tanggal_pergerakan : null,
                'tipe_terakhir' => $latestDetail ? strtolower($latestDetail->pergerakanStok->tipe_pergerakan) : null,
                'jumlah_terakhir' => $kuantitiTerkecilTerakhir,
            ];
        });
    }

    public function store(
        Request $request,
        StockMovementService $stockMovementService
    ) {
        $validated = $request->validate([
            'tipe_pergerakan' => 'required|in:masuk,keluar,penyesuaian',
            'tanggal_pergerakan' => 'required|date',
            'catatan' => 'nullable|string|max:255',
            'details' => 'required|array|min:1',
            'details.*.id_satuan' => 'required|exists:satuan_produk,id_satuan',
            'details.*.kuantiti' => 'required|integer|min:1',
        ]);

        try {
            $movement = $stockMovementService->create(
                $validated['tipe_pergerakan'],
                $validated['tanggal_pergerakan'],
                $validated['catatan'] ?? null,
                $validated['details']
            );

            return response()->json([
                'success' => true,
                'message' => 'Pergerakan stok berhasil dicatat.',
                'data' => $movement,
            ], 201);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    public function show($id)
    {
        $pergerakan = PergerakanStok::with('detail')->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $pergerakan,
        ]);
    }

    public function cetak($id)
    {
        $pergerakan = PergerakanStok::with('detail.satuanProduk')
            ->findOrFail($id);
        $fileName = 'Detail_Pergerakan_'.$pergerakan->kode_pergerakan.'.pdf';
        $pdf = Pdf::loadView('format-dokumen.pdf', compact('pergerakan'))
            ->setPaper('A4', 'landscape');

        return $pdf->download($fileName);
    }

    public function exportExcel(Request $request)
    {
        $validated = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);
        $start = $validated['start_date'];
        $end = $validated['end_date'];
        $fileName = "Laporan_Mutasi_Stok_{$start}_sd_{$end}.xlsx";

        return Excel::download(
            new PergerakanStokExport($start, $end),
            $fileName
        );
    }
}
