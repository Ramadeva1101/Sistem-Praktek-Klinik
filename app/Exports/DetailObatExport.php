<?php

namespace App\Exports;

use App\Models\DetailObatKunjungan;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class DetailObatExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle, ShouldAutoSize, WithEvents
{
    private $totalKeseluruhan = 0;

    public function collection()
    {
        $data = DetailObatKunjungan::query()
            ->select([
                'kode_pelanggan',
                'nama_pasien',
                'tanggal_kunjungan',
                'kode_obat',
                'nama_obat',
                'jumlah',
                'harga',
                'total_harga',
                'status_pembayaran'
            ])
            ->orderBy('tanggal_kunjungan', 'desc')
            ->get();

        $this->totalKeseluruhan = $data->sum('total_harga');

        return $data;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $lastRow = $event->sheet->getHighestRow();
                $lastColumn = $event->sheet->getHighestColumn();

                // Set width untuk semua kolom
                foreach(range('A', $lastColumn) as $column) {
                    $event->sheet->getColumnDimension($column)->setAutoSize(true);
                }

                // Tambahkan total di bawah
                $totalRow = $lastRow + 2;
                $event->sheet->setCellValue("A{$totalRow}", 'Total Keseluruhan:');
                $event->sheet->mergeCells("A{$totalRow}:G{$totalRow}");
                $event->sheet->setCellValue("H{$totalRow}", $this->totalKeseluruhan);

                // Style untuk total
                $event->sheet->getStyle("A{$totalRow}:I{$totalRow}")->applyFromArray([
                    'font' => ['bold' => true],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                        ],
                    ],
                ]);

                $event->sheet->getStyle("A{$totalRow}")->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_RIGHT);

                // Format currency untuk kolom harga
                $event->sheet->getStyle('G6:H'.$lastRow)
                    ->getNumberFormat()
                    ->setFormatCode('#,##0');

                $event->sheet->getStyle("H{$totalRow}")
                    ->getNumberFormat()
                    ->setFormatCode('#,##0');
            },
        ];
    }

    public function headings(): array
    {
        return [
            ['LAPORAN DETAIL OBAT'],
            ['Bamboomedia'],
            ['Tanggal: ' . now()->format('d/m/Y')],
            [''],
            [
                'Kode Pelanggan',
                'Nama Pasien',
                'Tanggal Kunjungan',
                'Kode Obat',
                'Nama Obat',
                'Jumlah',
                'Harga',
                'Total Harga',
                'Status Pembayaran'
            ],
        ];
    }

    public function map($row): array
    {
        return [
            $row->kode_pelanggan,
            $row->nama_pasien,
            $row->tanggal_kunjungan->format('d/m/Y H:i'),
            $row->kode_obat,
            $row->nama_obat,
            $row->jumlah,
            $row->harga,
            $row->total_harga,
            $row->status_pembayaran
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $lastRow = $sheet->getHighestRow();
        $lastColumn = $sheet->getHighestColumn();

        // Style untuk judul
        $sheet->mergeCells('A1:I1');
        $sheet->mergeCells('A2:I2');
        $sheet->mergeCells('A3:I3');

        // Border style
        $borderStyle = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                ],
            ],
        ];

        // Apply style untuk seluruh data
        $sheet->getStyle('A1:I' . $lastRow)->applyFromArray($borderStyle);

        return [
            1 => ['font' => ['bold' => true, 'size' => 16], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]],
            2 => ['font' => ['bold' => true, 'size' => 14], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]],
            3 => ['font' => ['bold' => true], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]],
            5 => ['font' => ['bold' => true], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]],
            'A5:I5' => ['fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'color' => ['rgb' => 'E2EFDA']]],
        ];
    }

    public function title(): string
    {
        return 'Detail Obat';
    }
}
