<?php

namespace App\Filament\Widgets;

use App\Models\DetailPemeriksaanKunjungan;
use App\Models\DetailObatKunjungan;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class PendapatanChart extends ChartWidget
{
    public static function canView(): bool
    {
        return auth()->user()->role === 'admin';
    }

    protected static ?string $heading = 'Grafik Pendapatan';
    protected static ?int $sort = 3;
    protected int | string | array $columnSpan = 'full';

    protected function getData(): array
    {
        $labels = [];
        $totalPendapatan = [];

        // Data 6 bulan terakhir
        for ($i = 5; $i >= 0; $i--) {
            $startDate = Carbon::now()->subMonths($i)->startOfMonth();
            $endDate = Carbon::now()->subMonths($i)->endOfMonth();

            // Total pendapatan (pemeriksaan + obat)
            $totalPemeriksaan = DetailPemeriksaanKunjungan::whereBetween('tanggal_kunjungan', [$startDate, $endDate])
                ->sum('harga');

            $totalObat = DetailObatKunjungan::whereBetween('tanggal_kunjungan', [$startDate, $endDate])
                ->sum('total_harga');

            $labels[] = $startDate->isoFormat('MMMM Y');
            $totalPendapatan[] = $totalPemeriksaan + $totalObat;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Total Pendapatan',
                    'data' => $totalPendapatan,
                    'backgroundColor' => 'rgba(75, 192, 192, 0.6)',
                    'borderColor' => 'rgba(75, 192, 192, 1)',
                    'borderWidth' => 1,
                ]
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'responsive' => true,
            'scales' => [
                'y' => [
                    'display' => true,
                    'beginAtZero' => true,
                    'ticks' => [
                        'callback' => 'function(value) {
                            return "Rp " + new Intl.NumberFormat("id-ID").format(value);
                        }',
                    ],
                ],
                'x' => [
                    'display' => true,
                    'grid' => [
                        'display' => false
                    ],
                ],
            ],
            'plugins' => [
                'tooltip' => [
                    'enabled' => true,
                    'mode' => 'index',
                    'intersect' => false,
                    'callbacks' => [
                        'label' => 'function(context) {
                            return "Total: Rp " + new Intl.NumberFormat("id-ID").format(context.raw);
                        }',
                    ],
                ],
                'legend' => [
                    'display' => true,
                    'position' => 'top',
                ],
                'datalabels' => [
                    'display' => true,
                    'anchor' => 'end',
                    'align' => 'top',
                    'formatter' => 'function(value) {
                        if (value === 0) return "";
                        return "Rp " + new Intl.NumberFormat("id-ID").format(value);
                    }',
                    'font' => [
                        'weight' => 'bold'
                    ],
                ],
            ],
        ];
    }
}
