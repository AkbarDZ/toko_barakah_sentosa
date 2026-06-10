<?php

namespace App\Exports;

use App\Models\Transaksi;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class TransaksiExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    protected $startDate;
    protected $endDate;

    public function __construct($startDate, $endDate)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    // Mengambil data transaksi berdasarkan filter tanggal yang dipilih
    public function query()
    {
        return Transaksi::query()
            ->whereBetween('created_at', [
                $this->startDate . ' 00:00:00', 
                $this->endDate . ' 23:59:59'
            ])
            ->orderBy('created_at', 'desc');
    }

    // Baris Heading/Judul Atas Kolom Excel
    public function headings(): array
    {
        return [
            'No',
            'Kode Transaksi',
            'Total Tagihan',
            'Jumlah Bayar',
            'Kembalian',
            'Total Keuntungan',
            'Tanggal Transaksi',
        ];
    }

    // Memetakan struktur kolom data dari database ke sel Excel
    private $rowNumber = 0;
    public function map($transaksi): array
    {
        $this->rowNumber++;
        return [
            $this->rowNumber,
            $transaksi->kode_transaksi,
            'Rp ' . number_format($transaksi->total_tagihan, 0, ',', '.'),
            'Rp ' . number_format($transaksi->jumlah_bayar, 0, ',', '.'),
            'Rp ' . number_format($transaksi->kembalian, 0, ',', '.'),
            'Rp ' . number_format($transaksi->total_keuntungan, 0, ',', '.'),
            $transaksi->created_at->format('d/m/Y H:i:s'),
        ];
    }
}