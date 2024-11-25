<?php

namespace App\Filament\Widgets;

use App\Models\Kasir;
use App\Models\RiwayatPembayaran;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class KasirStatsWidget extends BaseWidget
{
    protected static ?string $pollingInterval = '10s';

    protected function getStats(): array
    {
        return [
            Stat::make('Total Transaksi',
                Kasir::count()
            )
            ->description('Total semua transaksi')
            ->descriptionIcon('heroicon-m-shopping-cart')
            ->chart([7, 2, 10, 3, 15, 4, 17])
            ->color('success'),

            Stat::make('Belum Dibayar',
                Kasir::where('status_pembayaran', 'Belum Dibayar')->count()
            )
            ->description('Menunggu pembayaran')
            ->descriptionIcon('heroicon-m-clock')
            ->color('danger'),

            Stat::make('Sudah Dibayar',
                Kasir::where('status_pembayaran', 'Sudah Dibayar')->count()
            )
            ->description('Transaksi selesai')
            ->descriptionIcon('heroicon-m-check-circle')
            ->color('success'),

            Stat::make('Pendapatan Hari Ini', function () {
                return 'Rp ' . number_format(
                    RiwayatPembayaran::whereDate('tanggal_pembayaran', today())
                        ->sum('jumlah_biaya'),
                    0, ',', '.'
                );
            })
                ->description('Total pendapatan hari ini')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),

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
}
