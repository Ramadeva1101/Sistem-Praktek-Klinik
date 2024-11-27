<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PasienResource\Pages;
use App\Models\Pasien;
use App\Models\Kunjungan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Filament\Navigation\NavigationItem;
use App\Filament\Resources\KunjunganResource;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Filament\Navigation\NavigationGroup;

class PasienResource extends Resource
{
    protected static ?string $model = Pasien::class;
    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';
    protected static ?string $navigationLabel = 'Kasir';
    protected static ?string $navigationGroup = 'Master Data';

    public static function getNavigationGroup(): ?string
    {
        return auth()->user()->role === 'dokter' ? 'Data' : 'Master Data';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nama')
                    ->required()
                    ->maxLength(100),
                Forms\Components\DatePicker::make('tanggal_lahir')
                    ->required(),
                Forms\Components\Select::make('jenis_kelamin')
                    ->options([
                        'pria' => 'Pria',
                        'wanita' => 'Wanita',
                    ])
                    ->required(),
                Forms\Components\TextInput::make('alamat')
                    ->maxLength(300)
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('kode_pelanggan')
                    ->label('Kode Pelanggan')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('nama')
                    ->label('Nama Pasien')
                    ->searchable(),
                Tables\Columns\TextColumn::make('tanggal_lahir')
                    ->label('Tanggal Lahir')
                    ->date()
                    ->searchable(),
                Tables\Columns\TextColumn::make('jenis_kelamin')
                    ->label('Jenis Kelamin')
                    ->searchable(),
                Tables\Columns\TextColumn::make('alamat')
                    ->label('Alamat')
                    ->searchable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                Tables\Actions\Action::make('buat_kunjungan')
                ->label('Buat Kunjungan')
                ->icon('heroicon-o-plus')
                ->action(function (Pasien $record) {
                    try {
                        DB::beginTransaction();

                        // Debug tanggal lahir
                        Log::info('Tanggal Lahir Pasien:', [
                            'raw' => $record->tanggal_lahir,
                            'formatted' => $record->tanggal_lahir instanceof Carbon
                                ? $record->tanggal_lahir->format('Y-m-d')
                                : $record->tanggal_lahir
                        ]);

                        // Membuat kunjungan baru
                        $kunjungan = Kunjungan::create([
                            'kode_pelanggan' => $record->kode_pelanggan,
                            'nama' => $record->nama,
                            'tanggal_lahir' => $record->tanggal_lahir instanceof Carbon
                                ? $record->tanggal_lahir->format('Y-m-d')
                                : $record->tanggal_lahir,
                            'jenis_kelamin' => $record->jenis_kelamin,
                            'alamat' => $record->alamat,
                            'tanggal_kunjungan' => now()->setTimezone('Asia/Makassar'),
                            'status' => 'active'
                        ]);

                        DB::commit();

                        Notification::make()
                            ->title('Kunjungan berhasil dibuat')
                            ->success()
                            ->send();

                    } catch (\Exception $e) {
                        DB::rollBack();
                        Log::error('Error creating kunjungan: ' . $e->getMessage());

                        Notification::make()
                            ->title('Gagal membuat kunjungan')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                })
                ->successRedirectUrl(fn () => KunjunganResource::getUrl('index')),

                Tables\Actions\DeleteAction::make()
                ->label('Delete')
                ->icon('heroicon-o-trash')
                ->color('danger')
            ])

            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            // Define relations here if needed
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPasiens::route('/'),
            'create' => Pages\CreatePasien::route('/create'),

        ];
    }

    public static function getNavigationItems(): array
    {
        return in_array(auth()->user()?->role, ['admin', 'dokter'])
            ? [
                NavigationItem::make('Pasien')
                    ->url(static::getUrl())
                    ->icon('heroicon-o-user-group')
                    ->group('Master Data')

            ]
            : [];
    }
}
