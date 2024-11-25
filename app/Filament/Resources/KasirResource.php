<?php

namespace App\Filament\Resources;

use Carbon\Carbon;
use Filament\Forms;
use Filament\Tables;
use App\Models\Kasir;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Filament\Resources\Resource;
use App\Models\RiwayatPembayaran;
use Illuminate\Support\Facades\DB;
use App\Models\DetailObatKunjungan;
use Filament\Forms\Components\Card;
use Filament\Tables\Actions\Action;
use Illuminate\Support\Facades\Log;
use Filament\Support\Enums\MaxWidth;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Navigation\NavigationItem;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Filters\SelectFilter;
use App\Models\DetailPemeriksaanKunjungan;
use App\Filament\Resources\KasirResource\Pages;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;

class KasirResource extends Resource
{
    protected static ?string $model = Kasir::class;
    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';
    protected static ?string $navigationGroup = 'Transaksi';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Informasi Pembayaran')
                    ->schema([
                        Forms\Components\TextInput::make('kode_pelanggan')
                            ->label('Kode Pelanggan')
                            ->disabled(),

                        Forms\Components\TextInput::make('id_pembayaran')
                            ->label('ID Pembayaran')
                            ->disabled()
                            ->visible(fn (?Kasir $record) => $record && $record->status_pembayaran === 'Sudah Dibayar'),

                        Forms\Components\TextInput::make('nama')
                            ->label('Nama Pasien')
                            ->required()
                            ->disabled(),

                        Forms\Components\TextInput::make('jumlah_biaya')
                            ->label('Total Biaya')
                            ->prefix('Rp')
                            ->required()
                            ->numeric()
                            ->disabled()
                            ->default(fn ($record) => number_format($record->jumlah_biaya, 0, ',', '.')),

                        Forms\Components\DateTimePicker::make('tanggal_kunjungan')
                            ->label('Tanggal Kunjungan')
                            ->displayFormat('d F Y H:i')
                            ->timezone('Asia/Makassar')
                            ->disabled(),

                        Forms\Components\Select::make('metode_pembayaran')
                            ->label('Metode Pembayaran')
                            ->options([
                                'Cash' => 'Cash',
                                'Card' => 'Card',
                            ])
                            ->visible(fn (?Kasir $record) => $record && $record->status_pembayaran === 'Belum Dibayar'),

                        Forms\Components\Select::make('status_pembayaran')
                            ->label('Status Pembayaran')
                            ->options([
                                'Belum Dibayar' => 'Belum Dibayar',
                                'Sudah Dibayar' => 'Sudah Dibayar',
                            ])
                            ->required(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(
                Kasir::with(['kunjungan', 'kunjungan.obats', 'kunjungan.pemeriksaans'])
                    ->orderByRaw("status_pembayaran = 'Belum Dibayar' DESC, nama ASC")
            )
            ->columns([
                Tables\Columns\TextColumn::make('kode_pelanggan')
                    ->label('Kode Pelanggan')
                    ->searchable()
                    ->limit(10),

                Tables\Columns\TextColumn::make('nama')
                    ->label('Nama Pasien')
                    ->searchable()
                    ->formatStateUsing(fn ($state) => strlen($state) > 20 ? substr($state, 0, 17) . '...' : $state)
                    ->limit(20),

                Tables\Columns\TextColumn::make('jumlah_biaya')
                    ->label('Total Biaya')
                    ->formatStateUsing(fn ($state) => 'Rp ' . number_format($state, 0, ',', '.'))
                    ->limit(15),

                Tables\Columns\TextColumn::make('tanggal_kunjungan')
                    ->label('Tanggal Kunjungan')
                    ->dateTime('d F Y - H:i')
                    ->timezone('Asia/Makassar')
                    ->sortable(),

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
                    ->icon('heroicon-m-eye')
                    ->color('warning')
                    ->modalHeading('Detail Kasir')
                    ->modalWidth(MaxWidth::ExtraLarge)
                    ->form([
                        Section::make('Data Pasien')
                            ->schema([
                                TextInput::make('kode_pelanggan')
                                    ->label('Kode Pasien')
                                    ->disabled()
                                    ->default(fn ($record) => $record->kode_pelanggan),

                                TextInput::make('id_pembayaran')
                                    ->label('ID Pembayaran')
                                    ->disabled()
                                    ->default(fn ($record) => $record->id_pembayaran)
                                    ->visible(fn ($record) => $record->status_pembayaran === 'Sudah Dibayar'),

                                TextInput::make('nama')
                                    ->label('Nama Pasien')
                                    ->disabled()
                                    ->default(fn ($record) => $record->nama),

                                TextInput::make('jumlah_biaya')
                                    ->label('Total Biaya')
                                    ->prefix('Rp')
                                    ->disabled()
                                    ->default(fn ($record) => number_format($record->jumlah_biaya, 0, ',', '.')),

                                TextInput::make('status_pembayaran')
                                    ->label('Status Pembayaran')
                                    ->disabled()
                                    ->default(fn ($record) => $record->status_pembayaran),

                                Forms\Components\DateTimePicker::make('tanggal_kunjungan')
                                    ->label('Tanggal Kunjungan')
                                    ->disabled()
                                    ->displayFormat('d F Y H:i')
                                    ->timezone('Asia/Makassar')
                                    ->default(fn ($record) => $record->tanggal_kunjungan ? Carbon::parse($record->tanggal_kunjungan) : null),

                                TextInput::make('metode_pembayaran')
                                    ->label('Metode Pembayaran')
                                    ->disabled()
                                    ->default(fn ($record) => ucfirst($record->metode_pembayaran ?? '-'))
                                    ->visible(fn ($record) => $record->status_pembayaran === 'Sudah Dibayar'),

                                Forms\Components\DateTimePicker::make('tanggal_pembayaran')
                                    ->label('Tanggal Pembayaran')
                                    ->disabled()
                                    ->displayFormat('d F Y H:i')
                                    ->timezone('Asia/Makassar')
                                    ->default(fn ($record) => $record->tanggal_pembayaran ? Carbon::parse($record->tanggal_pembayaran) : null)
                                    ->visible(fn ($record) => $record->status_pembayaran === 'Sudah Dibayar'),
                            ])
                            ->columns(2),
                    ])
                    ->modalSubmitAction(false),

                Action::make('mark_as_paid')
                    ->label('Bayar')
                    ->icon('heroicon-m-currency-dollar')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Konfirmasi Pembayaran')
                    ->modalDescription('Apakah Anda yakin ingin menyelesaikan pembayaran ini?')
                    ->visible(fn (Kasir $record) => $record->status_pembayaran === 'Belum Dibayar')
                    ->form([
                        Forms\Components\Select::make('metode_pembayaran')
                            ->label('Metode Pembayaran')
                            ->options([
                                'Cash' => 'Cash',
                                'Card' => 'Card',
                            ])
                            ->required()
                    ])
                    ->action(function (Kasir $record, array $data): void {
                        DB::beginTransaction();
                        try {
                            // Generate ID Pembayaran
                            $idPembayaran = 'INV-' . strtoupper(Str::random(8));

                            // Update record kasir
                            $record->update([
                                'id_pembayaran' => $idPembayaran,
                                'status_pembayaran' => 'Sudah Dibayar',
                                'metode_pembayaran' => $data['metode_pembayaran'],
                                'tanggal_pembayaran' => now()->setTimezone('Asia/Makassar'),
                            ]);

                            // Ambil data pemeriksaan jika ada
                            $detailPemeriksaan = DetailPemeriksaanKunjungan::where('kode_pelanggan', $record->kode_pelanggan)
                                ->whereDate('tanggal_kunjungan', Carbon::parse($record->tanggal_kunjungan)->toDateString())
                                ->first();

                            // Ambil data obat jika ada
                            $detailObat = DetailObatKunjungan::where('kode_pelanggan', $record->kode_pelanggan)
                                ->whereDate('tanggal_kunjungan', Carbon::parse($record->tanggal_kunjungan)->toDateString())
                                ->first();

                            // Simpan ke riwayat pembayaran
                            RiwayatPembayaran::create([
                                'id_pembayaran' => $idPembayaran,
                                'kode_pelanggan' => $record->kode_pelanggan,
                                'nama_pasien' => $record->nama,
                                'tanggal_kunjungan' => Carbon::parse($record->tanggal_kunjungan)->setTimezone('Asia/Makassar'),
                                'tanggal_pembayaran' => now()->setTimezone('Asia/Makassar'),
                                'nama_pemeriksaan' => $detailPemeriksaan ? $detailPemeriksaan->nama_pemeriksaan : null,
                                'biaya_pemeriksaan' => $detailPemeriksaan ? $detailPemeriksaan->harga : 0,
                                'nama_obat' => $detailObat ? $detailObat->nama_obat : null,
                                'jumlah_obat' => $detailObat ? $detailObat->jumlah : null,
                                'satuan_obat' => $detailObat ? $detailObat->satuan : null,
                                'total_biaya_obat' => $detailObat ? $detailObat->total_harga : 0,
                                'jumlah_biaya' => $record->jumlah_biaya,
                                'metode_pembayaran' => $data['metode_pembayaran']
                            ]);

                            DB::commit();

                            Notification::make()
                                ->success()
                                ->title('Pembayaran Berhasil')
                                ->send();

                        } catch (\Exception $e) {
                            DB::rollBack();
                            Log::error('Error dalam proses pembayaran:', [
                                'message' => $e->getMessage(),
                                'file' => $e->getFile(),
                                'line' => $e->getLine()
                            ]);

                            Notification::make()
                                ->danger()
                                ->title('Terjadi kesalahan')
                                ->body('Detail error: ' . $e->getMessage())
                                ->send();
                        }
                    }),

                Action::make('delete')
                    ->label('Hapus')
                    ->icon('heroicon-m-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Hapus Data Kasir')
                    ->modalDescription('Apakah Anda yakin ingin menghapus data kasir ini?')
                    ->visible(fn () => auth()->user()->role === 'admin')
                    ->action(function (Kasir $record): void {
                        DB::beginTransaction();
                        try {
                            $record->delete();
                            DB::commit();

                            Notification::make()
                                ->success()
                                ->title('Data berhasil dihapus')
                                ->send();

                        } catch (\Exception $e) {
                            DB::rollBack();
                            Notification::make()
                                ->danger()
                                ->title('Terjadi kesalahan')
                                ->body('Detail error: ' . $e->getMessage())
                                ->send();
                        }
                    }),

                Action::make('selesai_pembayaran')
                    ->action(function (Kasir $record): void {
                        DB::transaction(function () use ($record) {
                            // Update status di Kasir
                            $record->update([
                                'status_pembayaran' => 'Sudah Dibayar',
                                'tanggal_pembayaran' => now()
                            ]);

                            // Update status di DetailObatKunjungan
                            DetailObatKunjungan::where('kode_pelanggan', $record->kode_pelanggan)
                                ->where('tanggal_kunjungan', $record->tanggal_kunjungan)
                                ->update(['status_pembayaran' => 'Sudah Dibayar']);

                            // Update status di DetailPemeriksaanKunjungan
                            DetailPemeriksaanKunjungan::where('kode_pelanggan', $record->kode_pelanggan)
                                ->where('tanggal_kunjungan', $record->tanggal_kunjungan)
                                ->update(['status_pembayaran' => 'Sudah Dibayar']);
                        });
                    }),
            ])
            ->defaultSort('created_at', 'desc')
            ->paginated([10])
            ->poll('')
            ->deferLoading()
            ->persistFiltersInSession()
            ->recordUrl(false);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListKasirs::route('/'),
            'create' => Pages\CreateKasir::route('/create'),
        ];
    }

    public static function getNavigationItems(): array
    {
        return [
            NavigationItem::make()
                ->label('Kasir')
                ->icon('heroicon-o-currency-dollar')
                ->group('Transaksi')
                ->url(static::getUrl())
                ->sort(3),
        ];
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()->role !== 'dokter';
    }

    public static function canAccess(): bool
    {
        return auth()->user()->role !== 'dokter';
    }

    public static function getNavigationLabel(): string
    {
        return 'Kasir';
    }
}
