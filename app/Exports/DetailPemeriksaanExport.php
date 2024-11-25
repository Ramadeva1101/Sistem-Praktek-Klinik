<?php

namespace App\Exports;

use App\Models\DetailPemeriksaanKunjungan;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class DetailPemeriksaanExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle, ShouldAutoSize, WithEvents
{
    private $totalKeseluruhan = 0;

    public function collection()
    {
        $data = DetailPemeriksaanKunjungan::query()
            ->select([
                'kode_pelanggan',
                'nama_pasien',
                'kode_pemeriksaan',
                'nama_pemeriksaan',
                'harga',
                'tanggal_kunjungan',
                'status_pembayaran'
            ])
            ->orderBy('tanggal_kunjungan', 'desc')
            ->get();

        $this->totalKeseluruhan = $data->sum('harga');

        return $data;
    }

    public function headings(): array
    {
        return [
            ['LAPORAN DETAIL PEMERIKSAAN'],
            ['Bamboomedia'],
            ['Tanggal: ' . now()->format('d/m/Y')],
            [''],
            [
                'Kode Pelanggan',
                'Nama Pasien',
                'Kode Pemeriksaan',
                'Nama Pemeriksaan',
                'Harga',
                'Tanggal Kunjungan',
                'Status Pembayaran'
            ],
        ];
    }

    public function map($row): array
    {
        return [
            $row->kode_pelanggan,
            $row->nama_pasien,
            $row->kode_pemeriksaan,
            $row->nama_pemeriksaan,
            $row->harga,
            $row->tanggal_kunjungan->format('d/m/Y H:i'),
            $row->status_pembayaran
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $lastRow = $sheet->getHighestRow();

        // Style untuk judul
        $sheet->mergeCells('A1:G1');
        $sheet->mergeCells('A2:G2');
        $sheet->mergeCells('A3:G3');

        // Border style
        $borderStyle = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                ],
            ],
        ];

        // Apply style untuk seluruh data
        $sheet->getStyle('A1:G' . $lastRow)->applyFromArray($borderStyle);

        return [
            1 => ['font' => ['bold' => true, 'size' => 16], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]],
            2 => ['font' => ['bold' => true, 'size' => 14], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]],
            3 => ['font' => ['bold' => true], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]],
            5 => ['font' => ['bold' => true], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]],
            'A5:G5' => ['fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'color' => ['rgb' => 'E2EFDA']]],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $lastRow = $event->sheet->getHighestRow();

                foreach(range('A', 'G') as $column) {
                    $event->sheet->getColumnDimension($column)->setAutoSize(true);
                }

                $totalRow = $lastRow + 2;
                $event->sheet->setCellValue("A{$totalRow}", 'Total Keseluruhan:');
                $event->sheet->mergeCells("A{$totalRow}:D{$totalRow}");
                $event->sheet->setCellValue("E{$totalRow}", $this->totalKeseluruhan);

                $event->sheet->getStyle("A{$totalRow}:G{$totalRow}")->applyFromArray([
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
                $event->sheet->getStyle('E6:E'.$lastRow)
                    ->getNumberFormat()
                    ->setFormatCode('#,##0');

                $event->sheet->getStyle("E{$totalRow}")
                    ->getNumberFormat()
                    ->setFormatCode('#,##0');
            },
        ];
    }

    public function title(): string
    {
        return 'Detail Pemeriksaan';
    }
}
