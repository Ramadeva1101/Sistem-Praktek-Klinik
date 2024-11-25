<?php

namespace App\Filament\Widgets;

use App\Models\RiwayatPembayaran;
use Filament\Widgets\ChartWidget;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AdminKunjunganChart extends ChartWidget
{
    protected static ?string $heading = 'Statistik Kunjungan';
    protected static ?int $sort = 2;
    protected int | string | array $columnSpan = 'full';
    protected static ?string $maxHeight = '400px';

    public ?string $filter = '7hari';

    public static function canView(): bool
    {
        return auth()->user()->role === 'admin';
    }

    protected function getFilters(): ?array
    {
        return [
            '7hari' => '7 Hari Terakhir',
            'bulan' => 'Bulan Ini',
            'tahun' => 'Tahun Ini',
        ];
    }

    protected function getData(): array
    {
        $query = RiwayatPembayaran::select(
            DB::raw('DATE(tanggal_pembayaran) as date'),
            DB::raw('COUNT(*) as total_kunjungan')
        );

        $query = match ($this->filter) {
            '7hari' => $query->where('tanggal_pembayaran', '>=', now()->subDays(7)),
            'bulan' => $query->whereMonth('tanggal_pembayaran', now()->month)
                            ->whereYear('tanggal_pembayaran', now()->year),
            'tahun' => $query->whereYear('tanggal_pembayaran', now()->year),
            default => $query->where('tanggal_pembayaran', '>=', now()->subDays(7))
        };

        $data = $query->groupBy('date')
            ->orderBy('date')
            ->get();

        $labels = $data->pluck('date')->map(function ($date) {
            return match ($this->filter) {
                '7hari' => Carbon::parse($date)->format('d M'),
                'bulan' => Carbon::parse($date)->format('d M'),
                'tahun' => Carbon::parse($date)->format('M Y'),
                default => Carbon::parse($date)->format('d M')
            };
        })->toArray();

        return [
            'datasets' => [
                [
                    'label' => 'Total Kunjungan',
                    'data' => $data->pluck('total_kunjungan')->toArray(),
                    'borderColor' => '#36A2EB',
                    'backgroundColor' => 'rgba(54, 162, 235, 0.1)',
                    'fill' => true,
                    'tension' => 0.3,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'title' => [
                        'display' => true,
                        'text' => 'Jumlah Kunjungan'
                    ],
                    'ticks' => [
                        'stepSize' => 1,
                    ],
                ],
                'x' => [
                    'grid' => [
                        'display' => false
                    ],
                ],
            ],
            'plugins' => [
                'legend' => [
                    'position' => 'top',
                ],
                'title' => [
                    'display' => true,
                    'text' => $this->getChartTitle(),
                ],
            ],
            'responsive' => true,
            'maintainAspectRatio' => false,
            'interaction' => [
                'intersect' => false,
                'mode' => 'index',
            ],
        ];
    }

    protected function getChartTitle(): string
    {
        return match ($this->filter) {
            '7hari' => 'Statistik Kunjungan 7 Hari Terakhir',
            'bulan' => 'Statistik Kunjungan Bulan Ini',
            'tahun' => 'Statistik Kunjungan Tahun ' . now()->year,
            default => 'Statistik Kunjungan'
        };
    }
}
