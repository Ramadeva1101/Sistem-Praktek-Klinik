<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithMapping;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class DetailObatExport implements FromQuery, WithHeadings, WithStyles, WithColumnWidths, WithMapping
{
    use Exportable;

    protected $query;
    protected $totalKeseluruhan = 0;

    public function __construct($query)
    {
        $this->query = $query;
        $this->totalKeseluruhan = $query->sum('total_harga');
    }

    public function query()
    {
        return $this->query;
    }

    public function headings(): array
    {
        $periodeAwal = request('dari_tanggal', 'Semua Data');
        $periodeAkhir = request('sampai_tanggal', 'Semua Data');

        $periodeTeks = "Periode: ";
        if ($periodeAwal === 'Semua Data' && $periodeAkhir === 'Semua Data') {
            $periodeTeks .= "Semua Data";
        } else {
            $periodeTeks .= $periodeAwal . " s/d " . $periodeAkhir;
        }

        return [
            ['LAPORAN DETAIL OBAT KUNJUNGAN'],
            [$periodeTeks],
            [''],
            [
                'Kode Pelanggan',
                'Nama Pasien',
                'Kode Obat',
                'Nama Obat',
                'Jumlah',
                'Harga',
                'Total Harga',
                'Tanggal Kunjungan',
                'Status Pembayaran',
            ]
        ];
    }

    public function map($row): array
    {
        return [
            $row->kode_pelanggan,
            $row->nama_pasien,
            $row->kode_obat,
            $row->nama_obat,
            $row->jumlah,
            'Rp ' . number_format($row->harga, 0, ',', '.'),
            'Rp ' . number_format($row->total_harga, 0, ',', '.'),
            $row->tanggal_kunjungan ? $row->tanggal_kunjungan->format('d/m/Y H:i') : '-',
            $row->status_pembayaran,
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 15,  // Kode Pelanggan
            'B' => 25,  // Nama Pasien
            'C' => 15,  // Kode Obat
            'D' => 30,  // Nama Obat
            'E' => 10,  // Jumlah
            'F' => 20,  // Harga
            'G' => 20,  // Total Harga
            'H' => 15,  // Tanggal Kunjungan
            'I' => 20,  // Status Pembayaran
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $lastRow = $sheet->getHighestRow();
        $totalRow = $lastRow + 2; // Baris untuk total keseluruhan

        // Merge cells untuk total keseluruhan
        $sheet->mergeCells('A' . $totalRow . ':F' . $totalRow);

        // Tambahkan text "Total Keseluruhan"
        $sheet->setCellValue('A' . $totalRow, 'Total Keseluruhan:');

        // Tambahkan nilai total
        $sheet->setCellValue('G' . $totalRow, 'Rp ' . number_format($this->totalKeseluruhan, 0, ',', '.'));

        // Style untuk total keseluruhan
        $sheet->getStyle('A' . $totalRow . ':G' . $totalRow)->applyFromArray([
            'font' => [
                'bold' => true,
            ],
            'borders' => [
                'outline' => [
                    'borderStyle' => Border::BORDER_THIN,
                ],
            ],
        ]);

        // Rata kanan untuk nilai total
        $sheet->getStyle('G' . $totalRow)->applyFromArray([
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_RIGHT,
            ],
        ]);

        // Rata kanan untuk label total
        $sheet->getStyle('A' . $totalRow)->applyFromArray([
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_RIGHT,
            ],
        ]);

        // Style untuk judul
        $sheet->mergeCells('A1:I1');
        $sheet->mergeCells('A2:I2');

        // Style untuk seluruh cell
        $sheet->getStyle('A1:I' . ($sheet->getHighestRow()))->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                ],
            ],
        ]);

        // Style untuk header
        $sheet->getStyle('A4:I4')->applyFromArray([
            'font' => [
                'bold' => true,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => [
                    'rgb' => 'E2EFDA',
                ],
            ],
        ]);

        // Style untuk judul laporan
        $sheet->getStyle('A1:A2')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 14,
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        // Style untuk konten
        $sheet->getStyle('A4:I' . $sheet->getHighestRow())->applyFromArray([
            'alignment' => [
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        // Style untuk kolom jumlah, harga dan total (rata kanan)
        $sheet->getStyle('E5:G' . $sheet->getHighestRow())->applyFromArray([
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_RIGHT,
            ],
        ]);

        // Style untuk tanggal dan status (rata tengah)
        $sheet->getStyle('H5:I' . $sheet->getHighestRow())->applyFromArray([
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
            ],
        ]);

        // Set tinggi baris untuk header
        $sheet->getRowDimension(1)->setRowHeight(30);
        $sheet->getRowDimension(2)->setRowHeight(25);
        $sheet->getRowDimension(4)->setRowHeight(20);
        $sheet->getRowDimension($totalRow)->setRowHeight(25);

        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
