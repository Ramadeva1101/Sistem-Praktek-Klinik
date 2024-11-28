<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\DB;
use Filament\Facades\Filament;
use Filament\Navigation\NavigationGroup;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Schema;
use App\Filament\Widgets\AdminKunjunganChart;
use Filament\Support\View\Components\ViewComponent;
use Filament\Support\Facades\FilamentView;
use Illuminate\Support\Facades\Blade;

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

        Filament::registerWidgets([
            AdminKunjunganChart::class,
        ]);

        FilamentView::registerRenderHook(
            'panels::auth.login.form.before',
            fn (): string => '
                <style>
                    body {
                        margin: 0;
                        padding: 0;
                    }
                    .fi-layout {
                        position: relative;
                        background: url("'.asset('img/login-bg.jpg').'") !important;
                        background-size: cover !important;
                        background-position: center !important;
                        background-repeat: no-repeat !important;
                        min-height: 100vh !important;
                    }
                    .fi-simple-main {
                        background: rgba(255, 255, 255, 0.95) !important;
                        backdrop-filter: blur(10px);
                        border-radius: 15px !important;
                        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.15) !important;
                    }
                </style>
            '
        );
    }
}
