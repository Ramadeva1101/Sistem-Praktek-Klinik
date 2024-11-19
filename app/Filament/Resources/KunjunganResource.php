<?php

namespace App\Filament\Resources;
use Filament\Forms;
use Filament\Tables;
use App\Models\Kasir;
use Filament\Forms\Form;
use App\Models\Kunjungan;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Filament\Resources\Resource;
use Illuminate\Support\Facades\DB;
use Filament\Tables\Actions\Action;
use Illuminate\Support\Facades\Log;
use Filament\Support\Enums\MaxWidth;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Navigation\NavigationItem;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ViewField;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextInputColumn;
use App\Filament\Resources\KunjunganResource\Pages;

class KunjunganResource extends Resource
{
    protected static ?string $model = Kunjungan::class;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Data Pasien')
                    ->schema([
                        Forms\Components\TextInput::make('kode_pelanggan')
                            ->required()
                            ->maxLength(255)
                            ->label('Kode Pelanggan'),
                        Forms\Components\TextInput::make('nama')
                            ->required()
                            ->maxLength(255)
                            ->label('Nama Pasien'),
                        Forms\Components\DatePicker::make('tanggal_lahir')
                            ->required()
                            ->label('Tanggal Lahir'),
                        Forms\Components\Select::make('jenis_kelamin')
                            ->options([
                                'Laki-laki' => 'Laki-laki',
                                'Perempuan' => 'Perempuan',
                            ])
                            ->required()
                            ->label('Jenis Kelamin'),
                        Forms\Components\Textarea::make('alamat')
                            ->required()
                            ->maxLength(65535)
                            ->columnSpanFull()
                            ->label('Alamat'),
                        Forms\Components\DateTimePicker::make('tanggal_kunjungan')
                            ->required()
                            ->label('Tanggal Kunjungan'),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('kode_pelanggan')
                    ->sortable()
                    ->searchable()
                    ->label('Kode Pelanggan'),
                Tables\Columns\TextColumn::make('nama')
                    ->searchable()
                    ->label('Nama Pasien'),
                Tables\Columns\TextColumn::make('tanggal_lahir')
                    ->date()
                    ->sortable()
                    ->label('Tanggal Lahir'),
                Tables\Columns\TextColumn::make('jenis_kelamin')
                    ->label('Jenis Kelamin'),
                Tables\Columns\TextColumn::make('tanggal_kunjungan')
                    ->dateTime()
                    ->sortable()
                    ->label('Tanggal Kunjungan'),
            ])
            ->actions([
                Action::make('pilih_obat')
                    ->label('Obat')
                    ->icon('heroicon-m-plus-circle')
                    ->color('success')
                    ->modalHeading('Pilih Obat')
                    ->modalDescription('Silahkan pilih obat untuk pasien ini')
                    ->form([
                        Select::make('obats')
                            ->multiple()
                            ->label('Pilih Obat')
                            ->options(\App\Models\Obat::query()->pluck('nama_obat', 'kode_obat'))
                            ->required()
                            ->preload()
                    ])
                    ->action(function ($record, array $data): void {
                        $record->obats()->sync($data['obats']);
                        Notification::make()
                            ->title('Obat berhasil dipilih')
                            ->success()
                            ->send();
                    })
                    ->modalWidth(MaxWidth::Medium),

                Action::make('pilih_pemeriksaan')
                    ->label('Pemeriksaan')
                    ->icon('heroicon-m-plus-circle')
                    ->color('success')
                    ->modalHeading('Pilih Pemeriksaan')
                    ->modalDescription('Silahkan pilih pemeriksaan untuk pasien ini')
                    ->form([
                        Select::make('pemeriksaans')
                            ->multiple()
                            ->label('Pilih Pemeriksaan')
                            ->options(\App\Models\Pemeriksaan::query()->pluck('nama_pemeriksaan', 'kode_pemeriksaan'))
                            ->required()
                            ->preload()
                    ])
                    ->action(function ($record, array $data): void {
                        $record->pemeriksaans()->sync($data['pemeriksaans']);
                        Notification::make()
                            ->title('Pemeriksaan berhasil dipilih')
                            ->success()
                            ->send();
                    })
                    ->modalWidth(MaxWidth::Medium),

                Action::make('view')
                    ->label('View')
                    ->icon('heroicon-m-eye')
                    ->color('warning')
                    ->modalHeading('Detail Kunjungan')
                    ->modalWidth(MaxWidth::ExtraLarge)
                    ->form([
                        Section::make('Data Pasien')
                            ->schema([
                                TextInput::make('kode_pelanggan')
                                    ->label('Kode Pelanggan')
                                    ->disabled()
                                    ->default(fn ($record) => $record->kode_pelanggan),
                                TextInput::make('nama')
                                    ->label('Nama Pasien')
                                    ->disabled()
                                    ->default(fn ($record) => $record->nama),
                                Forms\Components\DatePicker::make('tanggal_lahir')
                                    ->label('Tanggal Lahir')
                                    ->disabled()
                                    ->default(fn ($record) => $record->tanggal_lahir),
                                TextInput::make('jenis_kelamin')
                                    ->label('Jenis Kelamin')
                                    ->disabled()
                                    ->default(fn ($record) => $record->jenis_kelamin),
                                Textarea::make('alamat')
                                    ->label('Alamat')
                                    ->disabled()
                                    ->columnSpanFull()
                                    ->default(fn ($record) => $record->alamat),
                                Forms\Components\DateTimePicker::make('tanggal_kunjungan')
                                    ->label('Tanggal Kunjungan')
                                    ->disabled()
                                    ->default(fn ($record) => $record->tanggal_kunjungan),
                            ])
                            ->columns(2),

                        Section::make('Obat yang Diberikan')
                            ->schema([
                                Textarea::make('selected_obat')
                                    ->disabled()
                                    ->default(function ($record) {
                                        return $record->obats->map(function ($obat) {
                                            return $obat->nama_obat . ' (Rp ' . number_format($obat->harga, 0, ',', '.') . ')';
                                        })->join("\n");
                                    }),
                            ]),

                        Section::make('Pemeriksaan yang Dilakukan')
                            ->schema([
                                Textarea::make('selected_pemeriksaan')
                                    ->disabled()
                                    ->default(function ($record) {
                                        return $record->pemeriksaans->map(function ($pemeriksaan) {
                                            return $pemeriksaan->nama_pemeriksaan . ' (Rp ' . number_format($pemeriksaan->harga_pemeriksaan, 0, ',', '.') . ')';
                                        })->join("\n");
                                    }),
                            ]),

                        Section::make('Total Biaya')
                            ->schema([
                                TextInput::make('total_cost')
                                    ->prefix('Rp')
                                    ->disabled()
                                    ->default(function (Kunjungan $record): string {
                                        $totalObat = $record->obats->sum('harga');
                                        $totalPemeriksaan = $record->pemeriksaans->sum('harga_pemeriksaan');
                                        $total = $totalObat + $totalPemeriksaan;
                                        return number_format($total, 0, ',', '.');
                                    }),
                            ]),
                    ])
                    ->modalSubmitAction(false),

                Action::make('selesai')
                    ->label('Selesai')
                    ->icon('heroicon-m-check-circle')
                    ->color('success')
                    ->action(function (Kunjungan $record) {
                        DB::beginTransaction();
                        try {
                            Log::info('Memulai proses pemindahan data ke Kasir');

                            // Hitung total biaya
                            $totalBiaya = $record->obats->sum('harga') + $record->pemeriksaans->sum('harga_pemeriksaan');

                            // Generate ID Pembayaran
                            $idPembayaran = 'INV-' . strtoupper(Str::random(8));

                            // Buat record baru di tabel kasir
                            $kasir = Kasir::create([
                                'kode_pelanggan' => $record->kode_pelanggan,
                                'nama' => $record->nama,
                                'jumlah_biaya' => $totalBiaya,
                                'status_pembayaran' => 'Belum Dibayar',
                                'kunjungan_id' => $record->id,
                                'tanggal_kunjungan' => $record->created_at,
                                'tanggal_pembayaran' => null,
                                'id_pembayaran' => $idPembayaran,
                                'metode_pembayaran' => null,
                            ]);

                            if (!$kasir) {
                                throw new \Exception('Gagal membuat record kasir');
                            }

                            // Hapus data pasien dari tabel pasien
                            DB::table('kunjungans')->where('kode_pelanggan', $record->kode_pelanggan)->delete();

                            DB::commit();

                            Log::info('Data berhasil dipindahkan ke Kasir dan pasien dihapus', [
                                'kasir_id' => $kasir->id,
                                'kode_pelanggan' => $record->kode_pelanggan,
                                'id_pembayaran' => $idPembayaran
                            ]);

                            Notification::make()
                                ->title('Kunjungan berhasil diselesaikan dan data dipindahkan ke kasir')
                                ->success()
                                ->send();

                            // Refresh halaman setelah berhasil
                            return redirect(KunjunganResource::getUrl());

                        } catch (\Exception $e) {
                            DB::rollback();
                            Log::error('Terjadi kesalahan saat memindahkan data: ' . $e->getMessage());

                            Notification::make()
                                ->title('Terjadi kesalahan saat memindahkan data')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Selesaikan Kunjungan')
                    ->modalDescription('Data pasien akan dipindahkan ke kasir untuk proses pembayaran dan data pasien akan dihapus'),
                    Tables\Actions\DeleteAction::make()
                    ->label('Delete')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
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
            'index' => Pages\ListKunjungans::route('/'),
            'create' => Pages\CreateKunjungan::route('/create'),
            // 'edit' => Pages\EditKunjungan::route('/{record}/edit'),
        ];
    }
    public static function getNavigationItems(): array
    {
        return in_array(auth()->user()?->role, ['admin', 'dokter'])
            ? [
                NavigationItem::make('Kunjungan')

                ->url(static::getUrl())
                    ->icon('heroicon-o-users')
                    ->group('Kegiatan')
            ]
            : [];
    }
}

