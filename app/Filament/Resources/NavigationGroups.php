<?php

namespace App\Filament\Resources\NavigationGroups;

use Filament\Navigation\NavigationGroup;

class NavigationGroups
{
    public static function getNavigationGroups(): array
    {
        return [
            NavigationGroup::make()
                ->label('Master Data')
                ->icon('heroicon-o-database'),
            NavigationGroup::make()
                ->label('Pemeriksaan')
                ->icon('heroicon-o-clipboard-document-list'),
            NavigationGroup::make()
                ->label('Transaksi')
                ->icon('heroicon-o-currency-dollar'),
        ];
    }
}
