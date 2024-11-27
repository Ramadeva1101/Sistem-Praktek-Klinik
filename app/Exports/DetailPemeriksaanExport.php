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

class DetailPemeriksaanExport implements FromQuery, WithHeadings, WithStyles, WithColumnWidths, WithMapping
{
    use Exportable;

    protected $query;

    public function __construct($query)
    {
        $this->query = $query;
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
            ['LAPORAN DETAIL PEMERIKSAAN KUNJUNGAN'],
            [$periodeTeks],
            [''],
            [
                'Kode Pelanggan',
                'Nama Pasien',
                'Kode Pemeriksaan',
                'Nama Pemeriksaan',
                'Harga',
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
            $row->kode_pemeriksaan,
            $row->nama_pemeriksaan,
            'Rp ' . number_format($row->harga, 0, ',', '.'),
            $row->tanggal_kunjungan ? date('d/m/Y', strtotime($row->tanggal_kunjungan)) : '-',
            $row->status_pembayaran,
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 15,  // Kode Pelanggan
            'B' => 25,  // Nama Pasien
            'C' => 15,  // Kode Pemeriksaan
            'D' => 30,  // Nama Pemeriksaan
            'E' => 20,  // Harga
            'F' => 20,  // Tanggal Kunjungan
            'G' => 20,  // Status Pembayaran
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Style untuk judul
        $sheet->mergeCells('A1:G1');
        $sheet->mergeCells('A2:G2');

        // Style untuk seluruh cell
        $sheet->getStyle('A1:G' . ($sheet->getHighestRow()))->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                ],
            ],
        ]);

        // Style untuk header
        $sheet->getStyle('A4:G4')->applyFromArray([
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
        $sheet->getStyle('A4:G' . $sheet->getHighestRow())->applyFromArray([
            'alignment' => [
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        // Style untuk kolom harga (rata kanan)
        $sheet->getStyle('E5:E' . $sheet->getHighestRow())->applyFromArray([
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_RIGHT,
            ],
        ]);

        // Style untuk tanggal (rata tengah)
        $sheet->getStyle('F5:F' . $sheet->getHighestRow())->applyFromArray([
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
            ],
        ]);

        // Style untuk status pembayaran (rata tengah)
        $sheet->getStyle('G5:G' . $sheet->getHighestRow())->applyFromArray([
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
            ],
        ]);

        // Set tinggi baris untuk header
        $sheet->getRowDimension(1)->setRowHeight(30);
        $sheet->getRowDimension(2)->setRowHeight(25);
        $sheet->getRowDimension(4)->setRowHeight(20);

        // Auto-fit untuk baris data
        foreach ($sheet->getRowDimensions() as $rd) {
            $rd->setRowHeight(-1);
        }

        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
