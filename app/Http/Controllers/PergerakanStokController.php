<?php

namespace App\Http\Controllers;

use App\Exports\PergerakanStokExport;
use App\Models\PergerakanStok;
use App\Models\DetailPergerakanStok;
use App\Models\SatuanProduk;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class PergerakanStokController extends Controller
{
    public function index()
    {
        // Menampilkan riwayat terbaru di atas
        $pergerakan = PergerakanStok::orderBy('tanggal_pergerakan', 'desc')->paginate(10);
        $liveStok = $this->getLiveStokData();
        return view('backend.stok.index', compact('pergerakan', 'liveStok'));
    }

    private function getLiveStokData()
    {
        $allProducts = \App\Models\Produk::with(['satuanProduk.detailPergerakanStok.pergerakanStok'])->get();

        return $allProducts->map(function ($produk) {
            
            // Gabungkan seluruh baris detail pergerakan dari semua opsi satuan produk ini
            $allDetails = $produk->satuanProduk->flatMap(function ($satuan) {
                return $satuan->detailPergerakanStok;
            });

            // Cari pergerakan terakhir berdasarkan tanggal dokumen dan id_detail terbesar
            $latestDetail = $allDetails->sortByDesc(function ($detail) {
                return [
                    $detail->pergerakanStok->tanggal_pergerakan ?? '',
                    $detail->id_detail
                ];
            })->first();

            // Hitung nilai kuantiti dalam satuan terkecil untuk pergerakan terakhir
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

    public function create()
    {
        // Mengambil data satuan beserta produknya untuk pilihan di form
        $satuanProduk = SatuanProduk::with('produk')->whereHas('produk')->get();
        return view('backend.stok.create', compact('satuanProduk'));
    }

    public function store(Request $request)
{
    // 1. Validasi diubah menjadi huruf kecil semua
    $request->validate([
        'tipe_pergerakan' => 'required|in:masuk,keluar,penyesuaian',
        'tanggal_pergerakan' => 'required|date',
        'catatan' => 'nullable|string',
        'details' => 'required|array|min:1',
        'details.*.id_satuan' => 'required|exists:satuan_produk,id_satuan',
        'details.*.kuantiti' => 'required|integer|min:1',
    ]);

    try {
        DB::transaction(function () use ($request) {
            
            // Paksa inputan menjadi lowercase untuk berjaga-jaga
            $tipeInput = strtolower($request->tipe_pergerakan);

            // 2. Mapping Tipe untuk Kode Dokumen
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

            // Simpan Header dengan tipe lowercase
            $pergerakan = PergerakanStok::create([
                'kode_pergerakan'    => $finalKode,
                'tipe_pergerakan'    => $tipeInput, // Tersimpan sebagai 'masuk'/'keluar'/'penyesuaian'
                'tanggal_pergerakan' => $request->tanggal_pergerakan,
                'catatan'            => $request->catatan,
            ]);

            foreach ($request->details as $item) {
                $satuan = SatuanProduk::with('produk')->findOrFail($item['id_satuan']);
                $produk = $satuan->produk;

                // Pastikan kolom nilai_konversi di DB Anda tidak bernilai null atau 0
                // $nilaiKonversi = $satuan->nilai_konversi ?? 1; 
                $pengali = $satuan->kuantiti_per_satuan ?? 1;
                $kuantitiTerkecil = $item['kuantiti'] * $pengali;

                // 3. Pengecekan Kondisi Menggunakan Huruf Kecil
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

                $produk->save(); // Sekarang baris ini pasti mendeteksi perubahan nilai

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
        });

        return redirect()->route('stok.index')
            ->with('success', 'Pergerakan stok berhasil dicatat.');

    } catch (\Exception $e) {
        return back()->with('error', $e->getMessage())->withInput();
    }
}

    public function show($id)
    {
        // Load header beserta relasi detailnya
        $pergerakan = PergerakanStok::with('detail')->findOrFail($id);
        return view('backend.stok.show', compact('pergerakan'));
    }

    public function cetak($id)
    {
        $pergerakan = PergerakanStok::with('detail')->findOrFail($id);

        // Load view khusus PDF
        $pdf = Pdf::loadView('format-dokumen.pdf', compact('pergerakan'));
        
        // Atur ukuran kertas (A4 Landscape agar lega seperti tampilan web)
        $pdf->setPaper('A4', 'landscape');

        // Menggunakan stream() agar terbuka di tab baru (Preview), 
        // Ubah menjadi download() jika ingin otomatis terunduh
        return $pdf->stream('Detail_Pergerakan_' . $pergerakan->kode_pergerakan . '.pdf');
    }

    public function exportExcel(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $start = $request->start_date;
        $end = $request->end_date;
        
        $namaFile = 'Laporan_Mutasi_Stok_' . $start . '_sd_' . $end . '.xlsx';

        return Excel::download(new PergerakanStokExport($start, $end), $namaFile);
    }

    public function destroy($id)
    {
        $pergerakan = PergerakanStok::findOrFail($id);
        $pergerakan->delete();
        return redirect()->route('stok.index')->with('success', 'Dokumen pergerakan stok berhasil dihapus.');
    }

    public function archive()
    {
        $pergerakan = PergerakanStok::onlyTrashed()->get();
        return view('backend.stok.archive', compact('pergerakan'));
    }

    public function restore($id)
    {
        $pergerakan = PergerakanStok::withTrashed()->findOrFail($id);
        $pergerakan->restore();
        return redirect()->route('stok.archive')->with('success', 'Data Stok berhasil dipulihkan.');
    }
}