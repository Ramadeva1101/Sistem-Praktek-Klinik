<?php

namespace App\Filament\Widgets;

use App\Models\Kunjungan;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Carbon\Carbon;

class DokterStatsWidget extends BaseWidget
{
    protected static ?string $pollingInterval = '10s';
    protected static ?int $sort = 1;

    public static function canView(): bool
    {
        return auth()->user()->role === 'dokter';
    }

    protected function getStats(): array
    {
        return [
            Stat::make('Kunjungan Hari Ini',
                Kunjungan::whereDate('created_at', Carbon::today())->count()
            )
            ->description('Total kunjungan hari ini')
            ->descriptionIcon('heroicon-m-user-group')
            ->color('success')
            ->chart([7, 2, 10, 3, 15, 4, 17]),

            Stat::make('Menunggu Pemeriksaan',
                Kunjungan::where('status', 'Menunggu')
                    ->whereDate('created_at', Carbon::today())
                    ->count()
            )
            ->description('Pasien yang belum diperiksa')
            ->descriptionIcon('heroicon-m-clock')
            ->color('warning'),

            Stat::make('Sudah Diperiksa',
                Kunjungan::where('status', 'Selesai')
                    ->whereDate('created_at', Carbon::today())
                    ->count()
            )
            ->description('Pasien yang sudah diperiksa')
            ->descriptionIcon('heroicon-m-check-circle')
            ->color('success'),
        ];
    }
}
