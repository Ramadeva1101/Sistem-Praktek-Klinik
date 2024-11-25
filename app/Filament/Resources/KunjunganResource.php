<?php

namespace App\Filament\Resources;

use Carbon\Carbon;
use Filament\Forms;
use Filament\Tables;
use App\Models\Kasir;
use App\Models\Pasien;
use Filament\Forms\Form;
use App\Models\Kunjungan;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Filament\Resources\Resource;
use Illuminate\Support\Facades\DB;
use App\Models\DetailObatKunjungan;
use Filament\Tables\Actions\Action;
use Illuminate\Support\Facades\Log;
use Filament\Support\Enums\MaxWidth;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Navigation\NavigationItem;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ViewField;
use Filament\Notifications\Notification;
use Filament\Forms\Components\DatePicker;
use App\Models\DetailPemeriksaanKunjungan;
use Filament\Tables\Columns\TextInputColumn;
use App\Filament\Resources\KunjunganResource\Pages;

class KunjunganResource extends Resource
{
    protected static ?string $model = Kunjungan::class;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $modelLabel = 'Kunjungan';
    protected static ?string $pluralModelLabel = 'Kunjungan';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('kode_pelanggan')
                    ->required()
                    ->label('Kode Pelanggan'),
                TextInput::make('nama')
                    ->required(),
                DatePicker::make('tanggal_lahir')
                    ->required(),
                Select::make('jenis_kelamin')
                    ->options([
                        'pria' => 'Pria',
                        'wanita' => 'Wanita'
                    ])
                    ->required(),
                TextInput::make('alamat')
                    ->required(),
                Hidden::make('status')
                    ->default('active'),
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
                    ->date('d-M-Y')
                    ->sortable()
                    ->label('Tanggal Lahir'),
                Tables\Columns\TextColumn::make('jenis_kelamin')
                    ->label('Jenis Kelamin'),
                Tables\Columns\TextColumn::make('tanggal_kunjungan')
                    ->dateTime('d-M-Y H:i')
                    ->timezone('Asia/Makassar')
                    ->sortable()
                    ->label('Tanggal Kunjungan'),
            ])
            ->actions([
                Action::make('pilih_obat')
                    ->label('Obat')
                    ->icon('heroicon-m-plus-circle')
                    ->color('success')
                    ->modalHeading('Pilih Obat')
                    ->modalDescription('Silahkan pilih obat dan jumlahnya')
                    ->modalWidth('5xl')
                    ->form([
                        Forms\Components\Repeater::make('obat_items')
                            ->schema([
                                Select::make('kode_obat')
                                    ->label('Pilih Obat')
                                    ->options(\App\Models\Obat::query()->pluck('nama_obat', 'kode_obat'))
                                    ->required()
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                                        if ($state) {
                                            $obat = \App\Models\Obat::where('kode_obat', $state)->first();
                                            if ($obat) {
                                                $harga = $obat->harga;
                                                $jumlah = $get('jumlah') ?? 1;
                                                $total = $harga * $jumlah;

                                                $set('harga', $harga);
                                                $set('total_harga', $total);
                                                $set('nama_obat', $obat->nama_obat);
                                            }
                                        }
                                    }),
                                TextInput::make('jumlah')
                                    ->numeric()
                                    ->default(1)
                                    ->minValue(1)
                                    ->required()
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                                        $harga = $get('harga') ?? 0;
                                        $total = $state * $harga;
                                        $set('total_harga', $total);
                                    }),
                                TextInput::make('harga')
                                    ->disabled()
                                    ->prefix('Rp')
                                    ->numeric()
                                    ->default(0),
                                TextInput::make('total_harga')
                                    ->disabled()
                                    ->prefix('Rp')
                                    ->numeric()
                                    ->default(0),
                            ])
                            ->columns(4)
                            ->defaultItems(1)
                            ->addActionLabel('Tambah Obat')
                            ->deletable(true)
                            ->reorderable(false)
                    ])
                    ->action(function ($record, array $data): void {
                        try {
                            DB::beginTransaction();

                            // Hapus relasi yang ada
                            $record->obats()->detach();

                            // Simpan data baru dengan jumlah
                            foreach ($data['obat_items'] as $item) {
                                // Pastikan semua data yang diperlukan ada
                                if (!isset($item['kode_obat']) || !isset($item['jumlah'])) {
                                    throw new \Exception('Data obat tidak lengkap');
                                }

                                $obat = \App\Models\Obat::where('kode_obat', $item['kode_obat'])->first();
                                if (!$obat) {
                                    throw new \Exception('Obat tidak ditemukan');
                                }

                                // Hitung ulang total_harga untuk memastikan
                                $total_harga = $obat->harga * $item['jumlah'];

                                $record->obats()->attach($item['kode_obat'], [
                                    'jumlah' => $item['jumlah'],
                                    'total_harga' => $total_harga
                                ]);
                            }

                            DB::commit();

                            Notification::make()
                                ->title('Obat berhasil dipilih')
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            DB::rollBack();
                            Notification::make()
                                ->title('Gagal menyimpan data')
                                ->danger()
                                ->body($e->getMessage())
                                ->send();
                        }
                    }),

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
                                    ->timezone('Asia/Makassar')
                                    ->default(fn ($record) => $record->tanggal_kunjungan),
                            ])
                            ->columns(2),

                        Section::make('Obat yang Diberikan')
                            ->schema([
                                Textarea::make('selected_obat')
                                    ->disabled()
                                    ->default(function ($record) {
                                        return $record->obats->map(function ($obat) {
                                            return $obat->nama_obat .
                                                   ' (Jumlah: ' . $obat->pivot->jumlah . ') ' .
                                                   '(Rp ' . number_format($obat->pivot->total_harga, 0, ',', '.') . ')';
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
    ->visible(function (Kunjungan $record): bool {
        $hasObat = $record->obats()->count() > 0;
        $hasPemeriksaan = $record->pemeriksaans()->count() > 0;

        return $hasObat || $hasPemeriksaan;
    })
    ->requiresConfirmation()
    ->modalHeading('Konfirmasi Selesai Kunjungan')
    ->modalDescription(function (Kunjungan $record): string {
        $obatList = $record->obats->map(function ($obat) {
            return "\n- " . $obat->nama_obat .
                   " (Jumlah: " . $obat->pivot->jumlah . " " . $obat->satuan . ") " .
                   "(Rp " . number_format($obat->pivot->total_harga, 0, ',', '.') . ")";
        })->join('');

        $pemeriksaanList = $record->pemeriksaans->map(function ($pemeriksaan) {
            return "\n- " . $pemeriksaan->nama_pemeriksaan .
                   " (Rp " . number_format($pemeriksaan->harga_pemeriksaan, 0, ',', '.') . ")";
        })->join('');

        $totalBiaya = $record->obats->sum('pivot.total_harga') +
                     $record->pemeriksaans->sum('harga_pemeriksaan');

        return "Anda akan menyelesaikan kunjungan untuk pasien: \n\n" .
               "Nama: " . $record->nama . "\n" .
               "Kode Pelanggan: " . $record->kode_pelanggan . "\n\n" .
               ($record->obats->count() > 0 ? "Obat yang diberikan:" . $obatList . "\n\n" : "") .
               ($record->pemeriksaans->count() > 0 ? "Pemeriksaan yang dilakukan:" . $pemeriksaanList . "\n\n" : "") .
               "Total Biaya: Rp " . number_format($totalBiaya, 0, ',', '.') . "\n\n" .
               "Data akan dipindahkan ke kasir untuk proses pembayaran.\n" .
               "PERHATIAN: Pastikan data sudah benar karena tidak dapat diubah setelah dikonfirmasi!";
    })
    ->action(function (Kunjungan $record) {
        try {
            DB::beginTransaction();

            // Log awal proses
            Log::info('Memulai proses selesai kunjungan', [
                'kode_pelanggan' => $record->kode_pelanggan,
                'nama' => $record->nama
            ]);

            // Update jumlah kunjungan pada tabel pasien
            $pasien = Pasien::where('kode_pelanggan', $record->kode_pelanggan)->first();
            if ($pasien) {
                $pasien->increment('jumlah_kunjungan');
                $pasien->update(['kunjungan_terakhir' => now()->setTimezone('Asia/Makassar')]);
            }

            // Simpan data obat ke DetailObatKunjungan jika ada
            if ($record->obats->count() > 0) {
                foreach ($record->obats as $obat) {
                    DetailObatKunjungan::create([
                        'kode_pelanggan' => $record->kode_pelanggan,
                        'nama_pasien' => $record->nama,
                        'kode_obat' => $obat->kode_obat,
                        'nama_obat' => $obat->nama_obat,
                        'jumlah' => $obat->pivot->jumlah,
                        'satuan' => $obat->satuan,
                        'harga' => $obat->harga,
                        'total_harga' => $obat->pivot->total_harga,
                        'tanggal_kunjungan' => $record->tanggal_kunjungan,
                    ]);
                }
            }

            // Simpan data pemeriksaan ke DetailPemeriksaanKunjungan jika ada
            if ($record->pemeriksaans->count() > 0) {
                foreach ($record->pemeriksaans as $pemeriksaan) {
                    DetailPemeriksaanKunjungan::create([
                        'kode_pelanggan' => $record->kode_pelanggan,
                        'nama_pasien' => $record->nama,
                        'kode_pemeriksaan' => $pemeriksaan->kode_pemeriksaan,
                        'nama_pemeriksaan' => $pemeriksaan->nama_pemeriksaan,
                        'harga' => $pemeriksaan->harga_pemeriksaan,
                        'total_harga' => $pemeriksaan->harga_pemeriksaan,
                        'tanggal_kunjungan' => $record->tanggal_kunjungan,
                    ]);
                }
            }

            // Hitung total biaya
            $totalBiaya = $record->obats->sum('pivot.total_harga') +
                         $record->pemeriksaans->sum('harga_pemeriksaan');

            // Generate ID Pembayaran
            $idPembayaran = 'INV-' . strtoupper(Str::random(8));

            // Buat record baru di tabel kasir
            Kasir::create([
                'id_pembayaran' => $idPembayaran,
                'kode_pelanggan' => $record->kode_pelanggan,
                'nama' => $record->nama,
                'jumlah_biaya' => $totalBiaya,
                'status_pembayaran' => 'Belum Dibayar',
                'kunjungan_id' => $record->id,
                'tanggal_kunjungan' => $record->tanggal_kunjungan ?? now()->setTimezone('Asia/Makassar'),
            ]);

            // Hapus data kunjungan
            $record->delete();

            DB::commit();

            Notification::make()
                ->success()
                ->title('Kunjungan Berhasil Diselesaikan')
                ->body('Data telah dipindahkan ke kasir untuk proses pembayaran.')
                ->send();

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error dalam proses selesai kunjungan', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            Notification::make()
                ->danger()
                ->title('Terjadi Kesalahan')
                ->body('Error: ' . $e->getMessage())
                ->send();
        }
    }),

                Tables\Actions\DeleteAction::make()
                    ->label('Delete')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->visible(function () {
                        return auth()->user()->role === 'admin';
                    }),
            ]);
    }

    public static function navigation(): array
    {
        return [
            NavigationItem::make('Kunjungan')
                ->icon('heroicon-o-clipboard-list')
                ->url(static::getUrl())
                ->badge(fn () => Kunjungan::count())
                ->badgeColor('success'),
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListKunjungans::route('/'),
            'create' => Pages\CreateKunjungan::route('/create'),
        ];
    }

    public static function shouldRegisterNavigation(): bool
    {
        return in_array(auth()->user()->role, ['admin', 'dokter']);
    }

    public static function canCreate(): bool
    {
        return auth()->user()->role === 'admin';
    }
}
