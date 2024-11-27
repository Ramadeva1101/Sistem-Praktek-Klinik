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

    // protected function getFilters(): ?array
    // {
    //     return [
    //         // '7hari' => '7 Hari Terakhir',
    //         'bulan' => 'Bulan Ini',
    //         // 'tahun' => 'Tahun Ini',
    //     ];
    // }

    protected function getData(): array
    {
        $currentMonth = now()->month;
        $currentYear = now()->year;
        $daysInMonth = now()->daysInMonth;

        // Buat array untuk semua tanggal dalam bulan
        $dates = collect(range(1, $daysInMonth))->map(function ($day) use ($currentMonth, $currentYear) {
            return Carbon::createFromDate($currentYear, $currentMonth, $day)->format('Y-m-d');
        });

        // Ambil data kunjungan
        $kunjungan = RiwayatPembayaran::select(
            DB::raw('DATE(tanggal_pembayaran) as date'),
            DB::raw('COUNT(*) as total_kunjungan')
        )
        ->whereMonth('tanggal_pembayaran', $currentMonth)
        ->whereYear('tanggal_pembayaran', $currentYear)
        ->groupBy('date')
        ->pluck('total_kunjungan', 'date')
        ->toArray();

        // Siapkan data untuk setiap tanggal
        $data = $dates->map(function ($date) use ($kunjungan) {
            return $kunjungan[$date] ?? 0;
        });

        $labels = $dates->map(function ($date) {
            return Carbon::parse($date)->format('d');
        });

        return [
            'datasets' => [
                [
                    'label' => 'Total Kunjungan',
                    'data' => $data->toArray(),
                    'backgroundColor' => '#36A2EB',
                ],
            ],
            'labels' => $labels->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
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
                    'title' => [
                        'display' => true,
                        'text' => 'Tanggal'
                    ],
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
                    'text' => 'Statistik Kunjungan Bulan ' . now()->format('F Y'),
                ],
            ],
            'responsive' => true,
            'maintainAspectRatio' => false,
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
