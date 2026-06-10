<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Transaksi;
use App\Models\DetailTransaksi;
use App\Models\SatuanProduk;
use App\Models\PergerakanStok;
use Illuminate\Support\Facades\DB;

class TransaksiController extends Controller
{
    public function index()
    {
        $transaksi = Transaksi::latest()->get();

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
        if (!$request->has('produk') || empty($request->produk)) {
            return response()->json([
                'success' => false,
                'message' => 'Silahkan pilih produk dan tambahkan ke keranjang terlebih dahulu.'
            ], 400);
        }

        if (!$request->has('jumlah_bayar') || $request->jumlah_bayar === null || $request->jumlah_bayar === '') {
            return response()->json([
                'success' => false,
                'message' => 'Kolom nominal Bayar wajib diisi angka.'
            ], 400);
        }

        DB::beginTransaction();
        try {
            $total_tagihan = 0;
            $total_keuntungan = 0;

            foreach ($request->produk as $item) {
                $satuan = SatuanProduk::with('produk')->find($item['id_satuan']);

                if ($satuan) {
                    $harga_beli = $satuan->harga_beli ?? 0;
                    $subtotal = $item['qty'] * $item['harga_jual'];
                    $keuntungan = ($item['harga_jual'] - $harga_beli) * $item['qty'];

                    $total_tagihan += $subtotal;
                    $total_keuntungan += $keuntungan;
                }
            }

            if ($request->jumlah_bayar < $total_tagihan) {
                return response()->json([
                    'success' => false,
                    'message' => 'Uang pembayaran kurang dari total tagihan.'
                ], 400);
            }

            $hari  = date('j'); 
            $bulan = date('n'); 
            $tahun = date('Y'); 
            $formatWaktu = $hari . $bulan . $tahun; 

            $prefixInvoice = 'TRX-' . $formatWaktu . '-';
            $jumlahTransaksiHariIni = Transaksi::where('kode_transaksi', 'like', $prefixInvoice . '%')->count();
            
            $nomorUrut = sprintf('%03d', $jumlahTransaksiHariIni + 1);
            $invoiceFinal = $prefixInvoice . $nomorUrut; 

            $trx = Transaksi::create([
                'kode_transaksi' => $invoiceFinal, 
                'total_tagihan' => $total_tagihan,
                'jumlah_bayar' => $request->jumlah_bayar,
                'kembalian' => $request->jumlah_bayar - $total_tagihan,
                'total_keuntungan' => $total_keuntungan,
            ]);

            $tahunBulan = date('Ym');
            $prefix = "OUT-{$tahunBulan}-";

            $lastPergerakan = DB::table('pergerakan_stok')
                ->where('kode_pergerakan', 'like', $prefix . '%')
                ->orderBy('kode_pergerakan', 'desc')
                ->first();

            $sequence = 1;
            if ($lastPergerakan) {
                $lastSequence = (int) substr($lastPergerakan->kode_pergerakan, -4);
                $sequence = $lastSequence + 1;
            }

            $kodeDokumenStok = $prefix . str_pad($sequence, 4, '0', STR_PAD_LEFT);

            $pergerakanInduk = PergerakanStok::create([
                'kode_pergerakan' => $kodeDokumenStok,
                'tipe_pergerakan' => 'keluar', 
                'tanggal_pergerakan' => now(),
                'catatan' => 'Penjualan Nota: ' . $invoiceFinal,
            ]);

            foreach ($request->produk as $item) {
                $satuan = SatuanProduk::with('produk')->find($item['id_satuan']);
                if (!$satuan) continue;

                $harga_beli = $satuan->harga_beli ?? 0;
                $pengali = $satuan->kuantiti_per_satuan ?? 1;
                $kuantitiTerkecil = $item['qty'] * $pengali;

                DetailTransaksi::create([
                    'id_transaksi' => $trx->id_transaksi, 
                    'id_satuan' => $item['id_satuan'],
                    'kuantiti' => $item['qty'],
                    'harga_beli' => $harga_beli,
                    'harga_jual' => $item['harga_jual'],
                    'subtotal' => $item['qty'] * $item['harga_jual'],
                    'keuntungan' => ($item['harga_jual'] - $harga_beli) * $item['qty'],
                ]);

                DB::table('detail_pergerakan_stok')->insert([
                    'id_pergerakan' => $pergerakanInduk->id_pergerakan, 
                    'id_satuan' => $item['id_satuan'],
                    'kuantiti' => $item['qty'], 
                    'snapshot_nama_produk' => $satuan->produk->nama_produk ?? '-',
                    'snapshot_kode_produk' => $satuan->produk->kode_produk ?? '-',
                    'snapshot_nama_satuan' => $satuan->nama_satuan ?? '-',
                    'snapshot_harga_beli' => $harga_beli,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                if ($satuan->produk) {
                    $satuan->produk->decrement('total_stok_terkecil', $kuantitiTerkecil);
                }
            }

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Transaksi berhasil disimpan',
                'data' => [
                    'invoice' => $invoiceFinal,
                    'transaksi' => $trx
                ]
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage()
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
            $transaksi = Transaksi::findOrFail($id);
            $transaksi->delete();
            return response()->json([
                'success' => true,
                'message' => 'Transaksi berhasil dihapus.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus transaksi: ' . $e->getMessage()
            ], 500);
        }
    }
}
