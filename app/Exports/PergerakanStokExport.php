<?php

namespace App\Exports;

use App\Models\DetailPergerakanStok;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class PergerakanStokExport implements FromQuery, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected $startDate;
    protected $endDate;
    private $rowNumber = 0;

    // Menangkap input tanggal dari Controller
    public function __construct($startDate, $endDate)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    // Melakukan Query hanya pada rentang tanggal yang dipilih
    public function query()
    {
        return DetailPergerakanStok::query()
            ->join('pergerakan_stok', 'pergerakan_stok.id_pergerakan', '=', 'detail_pergerakan_stok.id_pergerakan')
            ->whereBetween('pergerakan_stok.tanggal_pergerakan', [$this->startDate, $this->endDate])
            ->orderBy('pergerakan_stok.tanggal_pergerakan', 'asc')
            ->select('detail_pergerakan_stok.*', 'pergerakan_stok.kode_pergerakan', 'pergerakan_stok.tanggal_pergerakan', 'pergerakan_stok.tipe_pergerakan', 'pergerakan_stok.catatan');
    }

    // Mendefinisikan Judul Kolom (Header)
    public function headings(): array
    {
        return [
            'No',
            'Kode Dokumen',
            'Tanggal',
            'Tipe Mutasi',
            'Kode Produk',
            'Nama Produk',
            'Kuantiti Input',
            'Satuan',
            'Total Mutasi (Terkecil)',
            'Catatan'
        ];
    }

    // Memetakan isi baris (Row) dari database ke kolom Excel
    public function map($detail): array
    {
        $this->rowNumber++;
        
        $tipe = strtolower($detail->tipe_pergerakan);
        // Ambil pengali (jika Anda load relasi satuanProduk, tapi disini kita asumsikan kuantiti * 1 sebagai fallback jika relasi terhapus)
        $pengali = $detail->satuanProduk ? $detail->satuanProduk->kuantiti_per_satuan : 1;
        $mutasiTerkecil = $detail->kuantiti * $pengali;

        // Tambahkan minus jika keluar
        if ($tipe == 'keluar') {
            $mutasiTerkecil = -$mutasiTerkecil;
        }

        return [
            $this->rowNumber,
            $detail->kode_pergerakan,
            \Carbon\Carbon::parse($detail->tanggal_pergerakan)->format('d/m/Y'),
            ucfirst($tipe),
            $detail->snapshot_kode_produk,
            $detail->snapshot_nama_produk,
            $detail->kuantiti,
            $detail->snapshot_nama_satuan,
            $mutasiTerkecil,
            $detail->catatan
        ];
    }

    // Memberikan style agar Excel tampil cantik
    public function styles(Worksheet $sheet)
    {
        return [
            // Style untuk Baris 1 (Header)
            1 => [
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FF2C3E50'], // Warna Dark Slate
                ],
                'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
            ],
        ];
    }
}