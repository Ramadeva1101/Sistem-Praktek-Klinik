<?php

namespace App\Filament\Exports;

use App\Models\DetailObatKunjungan;
use Filament\Actions\Exports\Models\Export;
use Filament\Actions\Exports\ExportColumn;
use Filament\Forms\Components\DatePicker;
use Illuminate\Database\Eloquent\Builder;

class DetailObatKunjunganExporter extends Export
{
    public static string $model = DetailObatKunjungan::class;

    public function getColumns(): array
    {
        return [
            ExportColumn::make('kode_pelanggan')
                ->label('Kode Pelanggan'),
            ExportColumn::make('pasien.nama')
                ->label('Nama Pasien'),
            ExportColumn::make('tanggal_kunjungan')
                ->label('Tanggal Kunjungan'),
            ExportColumn::make('jumlah')
                ->label('Jumlah'),
            ExportColumn::make('harga')
                ->label('Harga'),
            ExportColumn::make('total_harga')
                ->label('Total Harga'),
        ];
    }

    public static function getCompletedNotificationBody(): string
    {
        return 'Export detail obat kunjungan telah selesai dan siap diunduh.';
    }

    protected function getFormSchema(): array
    {
        return [
            DatePicker::make('start_date')
                ->label('Tanggal Mulai'),
            DatePicker::make('end_date')
                ->label('Tanggal Selesai'),
        ];
    }

    public function modifyQuery(Builder $query): Builder
    {
        return $query
            ->when(
                $this->start_date,
                fn (Builder $query, $date): Builder => $query->whereDate('tanggal_kunjungan', '>=', $date),
            )
            ->when(
                $this->end_date,
                fn (Builder $query, $date): Builder => $query->whereDate('tanggal_kunjungan', '<=', $date),
            );
    }
}
