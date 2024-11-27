<?php

namespace App\Filament\Widgets;

use App\Models\Kunjungan;
use App\Models\Pasien;
use App\Models\Obat;
use App\Models\RiwayatPembayaran;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AdminStatsWidget extends BaseWidget
{
    protected static ?string $pollingInterval = '10s';

    public static function canView(): bool
    {
        return auth()->user()->role === 'admin';
    }

    protected function getStats(): array
    {
        return [
            Stat::make('Total Pasien', Pasien::count())
                ->description('Jumlah seluruh pasien')
                ->descriptionIcon('heroicon-m-users')
                ->color('success'),

            Stat::make('Total Kunjungan', Kunjungan::count())
                ->description('Jumlah seluruh kunjungan')
                ->descriptionIcon('heroicon-m-clipboard-document-list')
                ->color('info'),

            Stat::make('Total Obat', Obat::count())
                ->description('Jumlah jenis obat')
                ->descriptionIcon('heroicon-m-beaker')
                ->color('warning'),
        ];
    }

    protected static ?int $sort = 3;
}
