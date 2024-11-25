<?php

namespace App\Filament\Widgets;

use App\Models\RiwayatPembayaran;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class KunjunganChart extends ChartWidget
{
    public static function canView(): bool
    {
        return auth()->user()->role === 'admin';
    }

    protected static ?string $heading = 'Statistik Kunjungan';
    protected static ?int $sort = 1;
    protected int | string | array $columnSpan = 'full';
    protected static ?string $maxHeight = '300px';

    protected function getData(): array
    {
        $data = collect();
        $labels = collect();

        // Data 7 hari terakhir
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);

            // Hitung kunjungan dari riwayat pembayaran
            $count = RiwayatPembayaran::whereDate('tanggal_pembayaran', $date->format('Y-m-d'))
                ->count();

            $data->push($count);
            $labels->push($date->isoFormat('D MMMM'));
        }

        return [
            'datasets' => [
                [
                    'label' => 'Jumlah Kunjungan',
                    'data' => $data->toArray(),
                    'fill' => true,
                    'backgroundColor' => 'rgba(59, 130, 246, 0.2)',
                    'borderColor' => 'rgb(59, 130, 246)',
                    'tension' => 0.4,
                ]
            ],
            'labels' => $labels->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'responsive' => true,
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'stepSize' => 1, // Memastikan skala Y dalam bilangan bulat
                    ],
                ],
            ],
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'top',
                ],
                'tooltip' => [
                    'enabled' => true,
                    'callbacks' => [
                        'label' => 'function(context) {
                            return "Kunjungan: " + context.raw + " pasien";
                        }',
                    ],
                ],
                'datalabels' => [
                    'display' => true,
                    'anchor' => 'end',
                    'align' => 'top',
                    'formatter' => 'function(value) {
                        return value + " pasien";
                    }',
                ],
            ],
        ];
    }
}
