<?php

namespace App\Filament\Resources\KunjunganResource\Pages;

use App\Filament\Resources\KunjunganResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions\Action;

class ListKunjungans extends ListRecords
{
    protected static string $resource = KunjunganResource::class;

 

    public function selesaiKunjungan($record): void
    {
        // Redirect ke halaman kasir dengan data kunjungan
        redirect()->route('filament.resources.kasirs.create', [
            'kode_pelanggan' => $record->kode_pelanggan,
            'tanggal_kunjungan' => $record->tanggal_kunjungan
        ]);
    }
}
