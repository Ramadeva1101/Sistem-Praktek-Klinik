<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\DB;
use Filament\Facades\Filament;
use Filament\Navigation\NavigationGroup;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        DB::statement("SET SQL_MODE=''");

        Filament::serving(function () {
            // Tambahkan navigation groups
            Filament::registerNavigationGroups([
                NavigationGroup::make()
                    ->label('Master Data')
                    ->icon('heroicon-o-database'),
                NavigationGroup::make()
                    ->label('Pemeriksaan')
                    ->icon('heroicon-o-clipboard-document-list'),
                NavigationGroup::make()
                    ->label('Transaksi')
                    ->icon('heroicon-o-currency-dollar'),
            ]);
        });
    }
}
