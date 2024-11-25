<?php

namespace App\Filament\Widgets;

use App\Models\RiwayatPembayaran;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Carbon\Carbon;

class AdminPendapatanStats extends BaseWidget
{
    protected static ?string $pollingInterval = '10s';
    protected static ?int $sort = 3;

    public static function canView(): bool
    {
        return auth()->user()->role === 'admin';
    }

    protected function getStats(): array
    {
        return [
            Stat::make('Pendapatan Hari Ini', function () {
                return 'Rp ' . number_format(
                    RiwayatPembayaran::whereDate('tanggal_pembayaran', today())
                        ->sum('jumlah_biaya'),
                    0, ',', '.'
                );
            })
                ->description('Total pendapatan hari ini')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success')
                ->chart($this->getPendapatanTrend()),

            Stat::make('Pendapatan Minggu Ini', function () {
                return 'Rp ' . number_format(
                    RiwayatPembayaran::whereBetween('tanggal_pembayaran', [
                        now()->startOfWeek(),
                        now()->endOfWeek()
                    ])->sum('jumlah_biaya'),
                    0, ',', '.'
                );
            })
                ->description('Total pendapatan minggu ini')
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color('info'),

            Stat::make('Pendapatan Bulan Ini', function () {
                return 'Rp ' . number_format(
                    RiwayatPembayaran::whereMonth('tanggal_pembayaran', now()->month)
                        ->whereYear('tanggal_pembayaran', now()->year)
                        ->sum('jumlah_biaya'),
                    0, ',', '.'
                );
            })
                ->description('Total pendapatan bulan ini')
                ->descriptionIcon('heroicon-m-presentation-chart-line')
                ->color('success'),
        ];
    }

    private function getPendapatanTrend()
    {
        return RiwayatPembayaran::select('jumlah_biaya')
            ->whereDate('tanggal_pembayaran', '>=', now()->subDays(7))
            ->orderBy('tanggal_pembayaran')
            ->pluck('jumlah_biaya')
            ->map(function ($value) {
                return $value / 1000; // Konversi ke ribu untuk chart
            })
            ->toArray();
    }
}
