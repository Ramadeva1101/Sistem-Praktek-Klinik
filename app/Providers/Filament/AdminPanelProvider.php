<?php

namespace App\Providers\Filament;

use Filament\Pages;
use Filament\Panel;
use Filament\Widgets;
use Filament\PanelProvider;
use Filament\Pages\Dashboard;
use Filament\Support\Colors\Color;
use App\Filament\Widgets\StatsOverview;
use App\Filament\Widgets\KunjunganChart;
use Filament\Navigation\NavigationGroup;
use App\Filament\Widgets\PendapatanChart;
use Filament\Widgets\StatsOverviewWidget;
use App\Filament\Widgets\AdminStatsWidget;
use App\Filament\Widgets\KasirStatsWidget;
use Filament\Http\Middleware\Authenticate;
use App\Filament\Widgets\DokterStatsWidget;
use App\Filament\Widgets\AdminKunjunganChart;
use App\Filament\Widgets\AdminKunjunganStats;
use App\Filament\Widgets\LatestKunjunganWidget;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('praktek-klinik')
            ->path('praktek-klinik')
            ->login()
            // ->logout()
            ->brandName('Praktek Klinik')
            ->topNavigation(false)

            ->brandLogoHeight('64px')
            ->colors([
                'primary' => Color::Amber,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->widgets([
                \App\Filament\Widgets\AdminKunjunganChart::class,
                \App\Filament\Widgets\AdminPendapatanStats::class, // Pastikan ada di sini
                \App\Filament\Widgets\AdminStatsWidget::class,
                \App\Filament\Widgets\LatestKunjunganWidget::class,
                StatsOverviewWidget::class,
                DokterStatsWidget::class,
                LatestKunjunganWidget::class,
                KasirStatsWidget::class,
                AdminStatsWidget::class,


            ])
            ->navigationGroups([
                'Master Data',
                'Pemeriksaan',
                'Transaksi',
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
                \Hasnayeen\Themes\Http\Middleware\SetTheme::class

                ])
                ->plugin(
                    \Hasnayeen\Themes\ThemesPlugin::make()
                )
            ->authMiddleware([
                Authenticate::class,

            ]);

    }

}
