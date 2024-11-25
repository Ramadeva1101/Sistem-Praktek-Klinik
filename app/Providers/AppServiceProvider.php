<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\DB;
use Filament\Facades\Filament;
use Filament\Navigation\NavigationGroup;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Schema;

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

        // Fix untuk MySQL versi < 5.7.7
        Schema::defaultStringLength(191);

        // Comment bagian ini dulu
        // if (config('app.env') === 'production') {
        //     URL::forceScheme('https');
        // }
    }
}
