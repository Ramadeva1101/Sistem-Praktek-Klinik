<?php

namespace App\Filament\Widgets;

use App\Models\Kasir;
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
        ];
    }
}
