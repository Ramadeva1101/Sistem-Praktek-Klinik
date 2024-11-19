<?php

namespace App\Filament\Resources;

use App\Filament\Resources\KasirResource\Pages;
use App\Models\Kasir;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Filament\Notifications\Notification;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Filament\Tables\Filters\SelectFilter;

class KasirResource extends Resource
{
    protected static ?string $model = Kasir::class;
    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';
    protected static ?string $navigationLabel = 'Kasir';
    protected static ?string $navigationGroup = 'Transaksi';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('kode_pelanggan')
                    ->label('Kode Pelanggan')
                    ->disabled(),

                Forms\Components\TextInput::make('id_pembayaran')
                    ->label('ID Pembayaran')
                    ->disabled(),

                Forms\Components\TextInput::make('nama')
                    ->label('Nama Pasien')
                    ->required()
                    ->disabled(),

                Forms\Components\TextInput::make('jumlah_biaya')
                    ->label('Total Biaya')
                    ->prefix('Rp')
                    ->required()
                    ->numeric()
                    ->disabled(),

                Forms\Components\DatePicker::make('tanggal_kunjungan')
                    ->label('Tanggal Kunjungan')
                    ->disabled(),

                Forms\Components\Select::make('metode_pembayaran')
                    ->label('Metode Pembayaran')
                    ->options([
                        'cash' => 'Cash',
                        'card' => 'Card',
                    ])
                    ->visible(fn ($record) => $record?->status_pembayaran === 'Belum Dibayar'),

                Forms\Components\Select::make('status_pembayaran')
                    ->label('Status Pembayaran')
                    ->options([
                        'Belum Dibayar' => 'Belum Dibayar',
                        'Sudah Dibayar' => 'Sudah Dibayar',
                    ])
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(Kasir::query()->orderByRaw("status_pembayaran = 'Belum Dibayar' DESC, nama ASC"))
            ->columns([
                Tables\Columns\TextColumn::make('kode_pelanggan')
                    ->label('Kode Pelanggan')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('nama')
                    ->label('Nama Pasien')
                    ->searchable()
                    ->sortable()
                    ->formatStateUsing(function ($state) {
                        return strlen($state) > 20 ? substr($state, 0, 17) . '...' : $state;
                    }),

                Tables\Columns\TextColumn::make('jumlah_biaya')
                    ->label('Total Biaya')
                    ->money('IDR'),

                Tables\Columns\TextColumn::make('tanggal_kunjungan')
                    ->label('Tanggal Kunjungan')
                    ->date('d-m-Y')
                    ,

                Tables\Columns\TextColumn::make('status_pembayaran')
                    ->label('Status Pembayaran')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Sudah Dibayar' => 'success',
                        'Belum Dibayar' => 'danger',
                        default => 'warning',
                    }),
            ])
            ->filters([
                SelectFilter::make('status_pembayaran')
                    ->label('Status Pembayaran')
                    ->options([
                        'Belum Dibayar' => 'Belum Dibayar',
                        'Sudah Dibayar' => 'Sudah Dibayar',
                    ]),
            ])
            ->actions([
                Action::make('view')
                    ->label('View')
                    ->icon('heroicon-o-eye')
                    ->modalHeading('Detail Kasir')
                    ->modalWidth('lg')
                    ->form(function (Kasir $record) {
                        $fields = [
                            Forms\Components\TextInput::make('kode_pelanggan')
                                ->label('Kode Pelanggan')
                                ->default($record->kode_pelanggan)
                                ->disabled(),

                            Forms\Components\TextInput::make('id_pembayaran')
                                ->label('ID Pembayaran')
                                ->default($record->id_pembayaran)
                                ->disabled(),

                            Forms\Components\TextInput::make('nama')
                                ->label('Nama Pasien')
                                ->default($record->nama)
                                ->disabled(),

                            Forms\Components\TextInput::make('jumlah_biaya')
                                ->label('Total Biaya')
                                ->default('Rp' . number_format($record->jumlah_biaya, 0, ',', '.'))
                                ->disabled(),

                            Forms\Components\DatePicker::make('tanggal_kunjungan')
                                ->label('Tanggal Kunjungan')
                                ->default($record->tanggal_kunjungan)
                                ->disabled(),
                        ];

                        // Hanya tampilkan tanggal pembayaran jika status sudah dibayar
                        if ($record->status_pembayaran === 'Sudah Dibayar') {
                            $fields[] = Forms\Components\DateTimePicker::make('tanggal_pembayaran')
                                ->label('Tanggal Pembayaran')
                                ->default($record->tanggal_pembayaran)
                                ->disabled();

                            $fields[] = Forms\Components\TextInput::make('metode_pembayaran')
                                ->label('Metode Pembayaran')
                                ->default($record->metode_pembayaran)
                                ->disabled();
                        }

                        return $fields;
                    })
                    ->color('secondary'),

                Action::make('mark_as_paid')
                    ->label('Bayar')
                    ->icon('heroicon-o-banknotes')
                    ->color('success')
                    ->requiresConfirmation()
                    ->form([
                        Forms\Components\Select::make('metode_pembayaran')
                            ->label('Metode Pembayaran')
                            ->options([
                                'cash' => 'Cash',
                                'card' => 'Card',
                            ])
                            ->required(),
                    ])
                    ->action(function (Kasir $record, array $data): void {
                        DB::beginTransaction();
                        try {
                            $record->update([
                                'status_pembayaran' => 'Sudah Dibayar',
                                'tanggal_pembayaran' => now(),
                                'metode_pembayaran' => $data['metode_pembayaran'],
                                'id_pembayaran' => 'INV-' . strtoupper(Str::random(8)),
                            ]);

                            // Update status kunjungan jika ada
                            if ($record->kunjungan) {
                                $record->kunjungan->update(['status_pembayaran' => 'Selesai']);
                            }

                            DB::commit();

                            Notification::make()
                                ->success()
                                ->title('Pembayaran Berhasil')
                                ->body('Pembayaran telah berhasil dilakukan!')
                                ->send();

                        } catch (\Exception $e) {
                            DB::rollBack();
                            Log::error('Error in mark_as_paid action: ' . $e->getMessage());

                            Notification::make()
                                ->danger()
                                ->title('Terjadi Kesalahan')
                                ->body($e->getMessage())
                                ->send();
                        }
                    })
                    ->visible(fn (Kasir $record): bool =>
                        $record->status_pembayaran === 'Belum Dibayar'
                    ),

                Tables\Actions\DeleteAction::make()
                    ->label('Delete')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListKasirs::route('/'),
            'create' => Pages\CreateKasir::route('/create'),
            'edit' => Pages\EditKasir::route('/{record}/edit'),
        ];
    }
}
