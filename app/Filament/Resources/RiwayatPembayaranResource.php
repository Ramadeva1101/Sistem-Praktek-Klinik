<?php

namespace App\Filament\Resources;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use App\Models\RiwayatPembayaran;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Actions\ViewAction;
use Illuminate\Database\Eloquent\Model;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\ActionGroup;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Actions\DeleteAction;
use Illuminate\Database\Eloquent\Builder;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use App\Filament\Resources\RiwayatPembayaranResource\Pages;
use Filament\Forms\Components\Select;
use Carbon\Carbon;

class RiwayatPembayaranResource extends Resource
{
    protected static ?string $model = RiwayatPembayaran::class;
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationLabel = 'Riwayat Kunjungan';
    protected static ?string $navigationGroup = 'Transaksi';
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('tanggal_pembayaran')
                    ->label('Tanggal')
                    ->dateTime('d F Y - H:i')
                    ->timezone('Asia/Makassar')
                    ->sortable(),
                Tables\Columns\TextColumn::make('kode_pelanggan')
                    ->label('Kode Pasien')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('nama_pasien')
                    ->label('Nama Pasien')
                    ->searchable()
                    ->sortable(),
            ])
            ->filters([
                Filter::make('date_range')
                    ->form([
                        DatePicker::make('dari_tanggal')
                            ->label('Dari Tanggal'),
                        DatePicker::make('sampai_tanggal')
                            ->label('Sampai Tanggal'),
                    ])
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['dari_tanggal'] ?? null) {
                            $indicators['Dari'] = Carbon::parse($data['dari_tanggal'])->format('d/m/Y');
                        }
                        if ($data['sampai_tanggal'] ?? null) {
                            $indicators['Sampai'] = Carbon::parse($data['sampai_tanggal'])->format('d/m/Y');
                        }
                        return $indicators;
                    })
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['dari_tanggal'] || $data['sampai_tanggal'],
                            fn($q) => $q->filterTanggalDariSampai(
                                $data['dari_tanggal'] ?? null,
                                $data['sampai_tanggal'] ?? null
                            )
                        );
                    }),
            ])
            ->defaultSort('tanggal_pembayaran', 'desc')
            ->actions([
                ViewAction::make()
                    ->modalHeading('Detail Pembayaran')
                    ->modalWidth('2xl'),
                DeleteAction::make()
                    ->label('Hapus')
                    ->hidden(fn (): bool => auth()->user()->role !== 'admin')
                    ->requiresConfirmation()
                    ->modalHeading('Hapus Riwayat Pembayaran')
                    ->modalDescription('Apakah Anda yakin ingin menghapus riwayat pembayaran ini? Tindakan ini tidak dapat dibatalkan.')
                    ->modalSubmitActionLabel('Ya, Hapus')
                    ->modalCancelActionLabel('Batal')
                    ->action(function ($record) {
                        $record->delete();

                        Notification::make()
                            ->success()
                            ->title('Riwayat pembayaran berhasil dihapus')
                            ->body('Data telah dihapus dari sistem.')
                            ->send();
                    })
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->hidden(fn (): bool => auth()->user()->role !== 'admin')
                ]),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Informasi Pasien')
                    ->schema([
                        TextEntry::make('kode_pelanggan')
                            ->label('Kode Pelanggan'),
                        TextEntry::make('nama_pasien')
                            ->label('Nama Pasien'),
                        TextEntry::make('tanggal_pembayaran')
                            ->label('Tanggal Pembayaran')
                            ->dateTime('d F Y - H:i')
                            ->timezone('Asia/Makassar'),
                        TextEntry::make('tanggal_kunjungan')
                            ->label('Tanggal Kunjungan')
                            ->dateTime('d F Y - H:i')
                            ->timezone('Asia/Makassar'),
                    ])->columns(3),

                Section::make('Detail Pemeriksaan')
                    ->schema([
                        TextEntry::make('nama_pemeriksaan')
                            ->label('Jenis Pemeriksaan'),
                        TextEntry::make('biaya_pemeriksaan')
                            ->label('Biaya Pemeriksaan')
                            ->money('idr'),
                    ])->columns(2),

                Section::make('Detail Obat')
                    ->schema([
                        TextEntry::make('nama_obat')
                            ->label('Nama Obat'),
                        TextEntry::make('jumlah_obat')
                            ->label('Jumlah')
                            ->suffix(fn ($record) => $record->satuan_obat),
                        TextEntry::make('total_biaya_obat')
                            ->label('Total Biaya Obat')
                            ->money('idr'),
                    ])->columns(3)
                    ->visible(fn ($record) => $record->nama_obat !== null),

                Section::make('Total Pembayaran')
                    ->schema([
                        TextEntry::make('jumlah_biaya')
                            ->label('Total Pembayaran')
                            ->money('idr')
                            ->size('lg')
                            ->weight('bold'),
                    ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRiwayatPembayaran::route('/'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['pasien', 'kasir'])
            ->latest();
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()->role !== 'dokter';
    }

    public static function canAccess(): bool
    {
        return auth()->user()->role !== 'dokter';
    }
}
