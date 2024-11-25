<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\AdminKunjunganStats;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    public function getWidgets(): array
    {
        // Untuk role admin
        if (auth()->user()->role === 'admin') {
            return [
                // Statistik Kunjungan di paling atas
                
                // Chart di bawahnya
                \App\Filament\Widgets\AdminKunjunganChart::class,
                // Widget lainnya
                \App\Filament\Widgets\AdminPendapatanStats::class,
                \App\Filament\Widgets\AdminStatsWidget::class,
                \App\Filament\Widgets\LatestKunjunganWidget::class,
            ];
        }

        // Untuk role dokter
        if (auth()->user()->role === 'dokter') {
            return [
                \App\Filament\Widgets\DokterStatsWidget::class,
                \App\Filament\Widgets\LatestKunjunganWidget::class,
            ];
        }

        // Untuk role kasir
        if (auth()->user()->role === 'kasir') {
            return [
                \App\Filament\Widgets\KasirStatsWidget::class,
            ];
        }

        return []; // Return empty array untuk role lainnya
    }
}
