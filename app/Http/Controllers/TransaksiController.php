<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaksi;
use App\Models\DetailTransaksi;
use App\Models\SatuanProduk;
use App\Models\PergerakanStok;
use App\Exports\TransaksiExport;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class TransaksiController extends Controller
{
    /**
     * Menampilkan daftar transaksi dan live akumulasi penjualan produk
     */
    public function index(Request $request)
    {
        // 1. Mengambil semua data transaksi utama untuk tabel bagian atas
        $transaksi = Transaksi::with([
            'detailTransaksi.satuanProduk' => function ($q) { $q->withTrashed(); },
            'detailTransaksi.satuanProduk.produk' => function ($q) { $q->withTrashed(); }
        ])->latest()->get();

        $filter = $request->input('filter', 'all');

        // 2. Mengambil data Live Penjualan Produk (Murni Qty Terjual di Nota Kasir)
        $query = DB::table('detail_transaksi')
            ->join('satuan_produk', 'detail_transaksi.id_satuan', '=', 'satuan_produk.id_satuan')
            ->join('produk', 'satuan_produk.id_produk', '=', 'produk.id_produk')
            ->select(
                'produk.kode_produk',
                'produk.nama_produk',
                'satuan_produk.nama_satuan as satuan_jual', // Nama satuan saat dijual di kasir
                DB::raw('SUM(detail_transaksi.kuantiti) as total_qty_terjual'), // Qty asli terjual
                DB::raw('SUM(detail_transaksi.subtotal) as total_omset'),
                DB::raw('SUM(detail_transaksi.keuntungan) as total_keuntungan')
            );

        if ($filter == 'this_month') {
            $query->whereMonth('detail_transaksi.created_at', date('m'))
                  ->whereYear('detail_transaksi.created_at', date('Y'));
        } elseif ($filter == 'this_year') {
            $query->whereYear('detail_transaksi.created_at', date('Y'));
        } elseif ($filter == 'today') {
            $query->whereDate('detail_transaksi.created_at', date('Y-m-d'));
        } elseif ($filter == 'custom') {
            if ($request->has('start_date') && $request->has('end_date')) {
                $query->whereDate('detail_transaksi.created_at', '>=', $request->start_date)
                      ->whereDate('detail_transaksi.created_at', '<=', $request->end_date);
            }
        }

        $livePenjualan = $query->groupBy('produk.id_produk', 'produk.kode_produk', 'produk.nama_produk', 'satuan_produk.nama_satuan')
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

        return view('backend.transaksi.index', compact('transaksi', 'livePenjualan', 'filter'));
    }

    public function create()
    {
        $produk = SatuanProduk::with('produk')->whereHas('produk')->get();
        return view('backend.transaksi.create', compact('produk'));
    }

    public function store(Request $request)
    {
        // 1. Validasi Input Kasir
        if (!$request->has('produk') || empty($request->produk)) {
            return back()->with('error', 'Silahkan pilih produk dan tambahkan ke keranjang terlebih dahulu.');
        }

        if (!$request->has('jumlah_bayar') || $request->jumlah_bayar === null || $request->jumlah_bayar === '') {
            return back()->with('error', 'Kolom nominal Bayar wajib diisi angka.');
        }

        DB::beginTransaction();
        try {
            $total_tagihan = 0;
            $total_keuntungan = 0;

            // Loop pertama: Hitung total tagihan dan keuntungan
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

            // Cek uang pembayaran
            if ($request->jumlah_bayar < $total_tagihan) {
                return back()->with('error', 'Uang pembayaran kurang dari total tagihan.');
            }

            // =============================================================
            // 🔥 LOGIKA BARU: GENERATE INVOICE TRX-[TANGGAL][BULAN][TAHUN]-[SERI]
            // =============================================================
            $hari  = date('j'); // Contoh tanggal 5 -> "5"
            $bulan = date('n'); // Contoh juni -> "6"
            $tahun = date('Y'); // Contoh tahun -> "2026"
            $formatWaktu = $hari . $bulan . $tahun; // Hasil: "562026"

            // Hitung jumlah transaksi yang menggunakan format tanggal yang sama hari ini
            $prefixInvoice = 'TRX-' . $formatWaktu . '-';
            $jumlahTransaksiHariIni = Transaksi::where('kode_transaksi', 'like', $prefixInvoice . '%')->count();
            
            $nomorUrut = sprintf('%03d', $jumlahTransaksiHariIni + 1);
            $invoiceFinal = $prefixInvoice . $nomorUrut; // Hasil final: TRX-562026-001

            // Simpan ke tabel transaksi utama
            $trx = Transaksi::create([
                'kode_transaksi' => $invoiceFinal, 
                'total_tagihan' => $total_tagihan,
                'jumlah_bayar' => $request->jumlah_bayar,
                'kembalian' => $request->jumlah_bayar - $total_tagihan,
                'total_keuntungan' => $total_keuntungan,
            ]);

            // =============================================================
            // 2. SIMPAN KE TABEL INDUK PERGERAKAN STOK (DISINKRONKAN)
            // =============================================================
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

            // =============================================================
            // 3. LOOP KEDUA: SIMPAN DETAIL NOTA, LOG DETAIL STOK, & POTONG STOK FISIK
            // =============================================================
            foreach ($request->produk as $item) {
                $satuan = SatuanProduk::with('produk')->find($item['id_satuan']);
                if (!$satuan) continue;

                $harga_beli = $satuan->harga_beli ?? 0;
                $pengali = $satuan->kuantiti_per_satuan ?? 1;
                $kuantitiTerkecil = $item['qty'] * $pengali;

                // a. Simpan ke tabel detail_transaksi
                DetailTransaksi::create([
                    'id_transaksi' => $trx->id_transaksi, 
                    'id_satuan' => $item['id_satuan'],
                    'kuantiti' => $item['qty'],
                    'harga_beli' => $harga_beli,
                    'harga_jual' => $item['harga_jual'],
                    'subtotal' => $item['qty'] * $item['harga_jual'],
                    'keuntungan' => ($item['harga_jual'] - $harga_beli) * $item['qty'],
                ]);

                // b. Simpan ke tabel detail_pergerakan_stok
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

                // c. POTONG STOK FISIK DI TABEL INDUK PRODUK
                if ($satuan->produk) {
                    $satuan->produk->decrement('total_stok_terkecil', $kuantitiTerkecil);
                }
            }

            DB::commit();
            return redirect()->route('transaksi.index')->with('success', 'Transaksi berhasil disimpan dengan invoice: ' . $invoiceFinal);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan sistem: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $transaksi = Transaksi::with(['detailTransaksi.satuanProduk.produk'])->findOrFail($id);
        return view('backend.transaksi.show', compact('transaksi'));
    }

    public function destroy($id)
    {
        try {
            $transaksi = Transaksi::findOrFail($id);
            $transaksi->delete();
            return redirect()->route('transaksi.index')->with('success', 'Transaksi berhasil dihapus.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus transaksi: ' . $e->getMessage());
        }
    }

    /**
     * PROSES DOWNLOAD EXCEL DATA TRANSAKSI
     */
    public function exportExcel(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $start = $request->start_date;
        $end = $request->end_date;
        
        $namaFile = 'Laporan_Transaksi_' . $start . '_sd_' . $end . '.xlsx';

        return Excel::download(new TransaksiExport($start, $end), $namaFile);
    }

    public function archive()
    {
        $transaksi = Transaksi::onlyTrashed()->get();
        return view('backend.transaksi.archive', compact('transaksi'));
    }

    public function restore($id)
    {
        $transaksi = Transaksi::withTrashed()->findOrFail($id);
        $transaksi->restore();
        return redirect()->route('transaksi.archive')->with('success', 'Transaksi berhasil dipulihkan.');
    }
}