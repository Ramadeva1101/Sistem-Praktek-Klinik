<?php

namespace App\Filament\Resources\RiwayatPembayaranResource\Pages;

use App\Filament\Resources\RiwayatPembayaranResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListRiwayatPembayaran extends ListRecords
{
    protected static string $resource = RiwayatPembayaranResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Jika diperlukan actions tambahan
        ];
    }
}
