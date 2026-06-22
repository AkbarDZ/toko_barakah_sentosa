<?php

namespace App\Http\Controllers\Api;

use App\Exports\TransaksiExport;
use App\Http\Controllers\Controller;
use App\Models\DetailTransaksi;
use App\Models\PergerakanStok;
use App\Models\Produk;
use App\Models\SatuanProduk;
use App\Models\Transaksi;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;
use Throwable;

class TransaksiController extends Controller
{
    public function index()
    {
        $transaksi = Transaksi::with([
            'detailTransaksi.satuanProduk.produk',
        ])->latest()->get();

        $livePenjualan = DB::table('detail_transaksi')
            ->join('satuan_produk', 'detail_transaksi.id_satuan', '=', 'satuan_produk.id_satuan')
            ->join('produk', 'satuan_produk.id_produk', '=', 'produk.id_produk')
            ->select(
                'produk.kode_produk',
                'produk.nama_produk',
                'satuan_produk.nama_satuan as satuan_jual',
                DB::raw('SUM(detail_transaksi.kuantiti) as total_qty_terjual'),
                DB::raw('SUM(detail_transaksi.subtotal) as total_omset'),
                DB::raw('SUM(detail_transaksi.keuntungan) as total_keuntungan')
            )
            ->groupBy('produk.id_produk', 'produk.kode_produk', 'produk.nama_produk', 'satuan_produk.nama_satuan')
            ->get()
            ->map(function ($item) {
                return [
                    'kode_produk'     => $item->kode_produk,
                    'nama_produk'     => $item->nama_produk,
                    'satuan_jual'     => $item->satuan_jual ?? 'Pcs',
                    'total_terjual'   => (int) $item->total_qty_terjual,
                    'total_omset'     => (float) $item->total_omset,
                    'total_keuntungan'=> (float) $item->total_keuntungan,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => [
                'transaksi' => $transaksi,
                'live_penjualan' => $livePenjualan
            ]
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'produk' => 'required|array|min:1',
            'produk.*.id_satuan' => 'required|integer|distinct|exists:satuan_produk,id_satuan',
            'produk.*.qty' => 'required|integer|min:1',
            'jumlah_bayar' => 'required|integer|min:0',
        ]);

        try {
            $trx = DB::transaction(function () use ($validated) {
                $unitIds = collect($validated['produk'])
                    ->pluck('id_satuan')
                    ->map(fn ($id) => (int) $id);

                $units = SatuanProduk::whereIn('id_satuan', $unitIds)
                    ->get()
                    ->keyBy('id_satuan');

                $productIds = $units->pluck('id_produk')->unique()->sort()->values();
                $products = Produk::whereIn('id_produk', $productIds)
                    ->orderBy('id_produk')
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('id_produk');

                $items = [];
                $stockUsage = [];
                $totalTagihan = 0;
                $totalKeuntungan = 0;

                foreach ($validated['produk'] as $input) {
                    $unit = $units->get((int) $input['id_satuan']);
                    $product = $unit ? $products->get($unit->id_produk) : null;

                    if (! $unit || ! $product) {
                        throw ValidationException::withMessages([
                            'produk' => 'Produk atau satuan yang dipilih tidak tersedia.',
                        ]);
                    }

                    $quantity = (int) $input['qty'];
                    $salePrice = (int) $unit->harga_jual;
                    $purchasePrice = (int) ($unit->harga_beli ?? 0);
                    $multiplier = max(1, (int) ($unit->kuantiti_per_satuan ?? 1));
                    $smallestQuantity = $quantity * $multiplier;
                    $subtotal = $quantity * $salePrice;
                    $profit = ($salePrice - $purchasePrice) * $quantity;

                    $stockUsage[$product->id_produk] =
                        ($stockUsage[$product->id_produk] ?? 0) + $smallestQuantity;

                    $items[] = [
                        'unit' => $unit,
                        'product' => $product,
                        'quantity' => $quantity,
                        'purchase_price' => $purchasePrice,
                        'sale_price' => $salePrice,
                        'subtotal' => $subtotal,
                        'profit' => $profit,
                    ];
                    $totalTagihan += $subtotal;
                    $totalKeuntungan += $profit;
                }

                foreach ($stockUsage as $productId => $usedStock) {
                    $product = $products->get($productId);
                    if ($usedStock > (int) $product->total_stok_terkecil) {
                        throw ValidationException::withMessages([
                            'produk' => "Stok {$product->nama_produk} tidak mencukupi.",
                        ]);
                    }
                }

                $amountPaid = (int) $validated['jumlah_bayar'];
                if ($amountPaid < $totalTagihan) {
                    throw ValidationException::withMessages([
                        'jumlah_bayar' => 'Uang pembayaran kurang dari total tagihan.',
                    ]);
                }

                $invoicePrefix = 'TRX-'.date('j').date('n').date('Y').'-';
                $dailyCount = Transaksi::where(
                    'kode_transaksi',
                    'like',
                    $invoicePrefix.'%'
                )->lockForUpdate()->count();
                $invoice = $invoicePrefix.sprintf('%03d', $dailyCount + 1);

                $transaction = Transaksi::create([
                    'kode_transaksi' => $invoice,
                    'total_tagihan' => $totalTagihan,
                    'jumlah_bayar' => $amountPaid,
                    'kembalian' => $amountPaid - $totalTagihan,
                    'total_keuntungan' => $totalKeuntungan,
                ]);

                $movementPrefix = 'OUT-'.date('Ym').'-';
                $lastMovement = PergerakanStok::where(
                    'kode_pergerakan',
                    'like',
                    $movementPrefix.'%'
                )->orderByDesc('kode_pergerakan')->lockForUpdate()->first();
                $sequence = $lastMovement
                    ? ((int) substr($lastMovement->kode_pergerakan, -4)) + 1
                    : 1;

                $movement = PergerakanStok::create([
                    'kode_pergerakan' => $movementPrefix.str_pad(
                            (string) $sequence,
                            4,
                            '0',
                            STR_PAD_LEFT
                        ),
                    'tipe_pergerakan' => 'keluar',
                    'tanggal_pergerakan' => now(),
                    'catatan' => 'Penjualan Nota: '.$invoice,
                ]);

                foreach ($items as $item) {
                    $unit = $item['unit'];
                    $product = $item['product'];

                    DetailTransaksi::create([
                        'id_transaksi' => $transaction->id_transaksi,
                        'id_satuan' => $unit->id_satuan,
                        'kuantiti' => $item['quantity'],
                        'harga_beli' => $item['purchase_price'],
                        'harga_jual' => $item['sale_price'],
                        'subtotal' => $item['subtotal'],
                        'keuntungan' => $item['profit'],
                    ]);

                    $movement->detail()->create([
                        'id_satuan' => $unit->id_satuan,
                        'kuantiti' => $item['quantity'],
                        'snapshot_nama_produk' => $product->nama_produk,
                        'snapshot_kode_produk' => $product->kode_produk,
                        'snapshot_nama_satuan' => $unit->nama_satuan,
                        'snapshot_harga_beli' => $item['purchase_price'],
                    ]);
                }

                foreach ($stockUsage as $productId => $usedStock) {
                    $product = $products->get($productId);
                    $product->total_stok_terkecil =
                        (int) $product->total_stok_terkecil - $usedStock;
                    $product->save();
                }

                return $transaction->load([
                    'detailTransaksi.satuanProduk.produk',
                ]);
            }, 3);

            return response()->json([
                'success' => true,
                'message' => 'Transaksi berhasil disimpan',
                'data' => [
                    'invoice' => $trx->kode_transaksi,
                    'transaksi' => $trx,
                ],
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => collect($e->errors())->flatten()->first(),
                'errors' => $e->errors(),
            ], 422);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem: '.$e->getMessage(),
            ], 500);
        }
    }

    public function show($id)
    {
        $transaksi = Transaksi::with(['detailTransaksi.satuanProduk.produk'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $transaksi
        ]);
    }

    public function destroy($id)
    {
        try {
            DB::transaction(function () use ($id) {
                $transaction = Transaksi::with(
                    'detailTransaksi.satuanProduk'
                )->lockForUpdate()->findOrFail($id);

                $productIds = $transaction->detailTransaksi
                    ->map(fn ($detail) => $detail->satuanProduk?->id_produk)
                    ->filter()
                    ->unique()
                    ->sort()
                    ->values();
                $products = Produk::whereIn('id_produk', $productIds)
                    ->orderBy('id_produk')
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('id_produk');

                foreach ($transaction->detailTransaksi as $detail) {
                    $unit = $detail->satuanProduk;
                    $product = $unit ? $products->get($unit->id_produk) : null;
                    if (! $unit || ! $product) {
                        continue;
                    }

                    $product->total_stok_terkecil =
                        (int) $product->total_stok_terkecil
                        + ((int) $detail->kuantiti
                            * max(1, (int) $unit->kuantiti_per_satuan));
                    $product->save();
                }

                PergerakanStok::where(
                    'catatan',
                    'Penjualan Nota: '.$transaction->kode_transaksi
                )->delete();
                $transaction->delete();
            }, 3);

            return response()->json([
                'success' => true,
                'message' => 'Transaksi dihapus dan stok telah dikembalikan.',
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus transaksi: '.$e->getMessage(),
            ], 500);
        }
    }

    public function exportExcel(Request $request)
    {
        $validated = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);
        $start = $validated['start_date'];
        $end = $validated['end_date'];
        $fileName = "Laporan_Transaksi_{$start}_sd_{$end}.xlsx";

        return Excel::download(
            new TransaksiExport($start, $end),
            $fileName
        );
    }

    public function cetak($id)
    {
        $transaksi = Transaksi::with([
            'detailTransaksi.satuanProduk.produk',
        ])->findOrFail($id);
        $fileName = 'Nota_'.$transaksi->kode_transaksi.'.pdf';
        $pdf = Pdf::loadView(
            'format-dokumen.nota-transaksi',
            compact('transaksi')
        )->setPaper('A5', 'portrait');

        return $pdf->download($fileName);
    }
}
