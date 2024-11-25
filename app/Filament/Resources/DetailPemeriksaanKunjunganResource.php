<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DetailPemeriksaanKunjunganResource\Pages;
use App\Models\DetailPemeriksaanKunjungan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Actions\Action;
use Filament\Support\Colors\Color;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Tables\Actions\DeleteAction;
use Filament\Notifications\Notification;

class DetailPemeriksaanKunjunganResource extends Resource
{
    protected static ?string $model = DetailPemeriksaanKunjungan::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationLabel = 'Detail Pemeriksaan';

    protected static ?string $modelLabel = 'Detail Pemeriksaan';

    protected static ?string $navigationGroup = 'Transaksi';

    protected static ?int $navigationSort = 1;

 

// Untuk mencegah akses langsung via URL



    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('kode_pelanggan')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('nama_pasien')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('kode_pemeriksaan')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('nama_pemeriksaan')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('harga')
                    ->required()
                    ->numeric(),
                Forms\Components\DateTimePicker::make('tanggal_kunjungan')
                    ->required(),
                Forms\Components\Select::make('status_pembayaran')
                    ->required()
                    ->options([
                        'Belum Bayar' => 'Belum Bayar',
                        'Sudah Bayar' => 'Sudah Bayar',
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(DetailPemeriksaanKunjungan::query())
            ->columns([
                Tables\Columns\TextColumn::make('kode_pelanggan')
                    ->searchable()
                    ->sortable()
                    ->label('Kode Pelanggan')
                    ->extraAttributes(['class' => 'min-w-[120px]']),
                Tables\Columns\TextColumn::make('nama_pasien')
                    ->searchable()
                    ->sortable()
                    ->label('Nama Pasien')
                    ->extraAttributes(['class' => 'min-w-[150px]']),
                Tables\Columns\TextColumn::make('kode_pemeriksaan')
                    ->searchable()
                    ->sortable()
                    ->label('Kode Pemeriksaan')
                    ->extraAttributes(['class' => 'min-w-[150px]']),
                Tables\Columns\TextColumn::make('nama_pemeriksaan')
                    ->searchable()
                    ->sortable()
                    ->label('Nama Pemeriksaan')
                    ->extraAttributes(['class' => 'min-w-[180px]']),
                Tables\Columns\TextColumn::make('harga')
                    ->money('IDR')
                    ->sortable()
                    ->label('Harga')
                    ->alignEnd()
                    ->extraAttributes(['class' => 'min-w-[150px]']),
                Tables\Columns\TextColumn::make('tanggal_kunjungan')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->label('Tanggal Kunjungan')
                    ->extraAttributes(['class' => 'min-w-[150px]']),
                Tables\Columns\TextColumn::make('status_pembayaran')
                    ->sortable()
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Sudah Bayar' => 'success',
                        'Belum Bayar' => 'danger',
                        default => 'gray',
                    })
                    ->extraAttributes(['class' => 'min-w-[120px]']),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Action::make('exportExcel')
                    ->label('Export Excel')
                    ->icon('heroicon-m-table-cells')
                    ->color(Color::Green)
                    ->size('lg')
                    ->button()
                    ->tooltip('Download Laporan Excel')
                    ->action(function () {
                        return Excel::download(new \App\Exports\DetailPemeriksaanExport, 'detail-pemeriksaan-' . now()->format('Y-m-d') . '.xlsx');
                    }),
                Action::make('exportPdf')
                    ->label('Export PDF')
                    ->icon('heroicon-m-document-arrow-down')
                    ->color(Color::Red)
                    ->size('lg')
                    ->button()
                    ->tooltip('Download Laporan PDF')
                    ->action(function () {
                        $data = DetailPemeriksaanKunjungan::query()
                            ->select([
                                'kode_pelanggan',
                                'nama_pasien',
                                'kode_pemeriksaan',
                                'nama_pemeriksaan',
                                'harga',
                                'tanggal_kunjungan',
                                'status_pembayaran'
                            ])
                            ->orderBy('tanggal_kunjungan', 'desc')
                            ->get();

                        $pdf = PDF::loadView('exports.detail-pemeriksaan', [
                            'records' => $data
                        ]);

                        $pdf->setPaper('A4', 'landscape');
                        return response()->streamDownload(function () use ($pdf) {
                            echo $pdf->output();
                        }, 'detail-pemeriksaan-' . now()->format('Y-m-d') . '.pdf');
                    })
            ])
            ->actions([

                DeleteAction::make()
                    ->label('Hapus')
                    ->hidden(fn (): bool => auth()->user()->role !== 'admin')
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $record->delete();

                        Notification::make()
                            ->success()
                            ->title('Data berhasil dihapus')
                            ->send();
                    })
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->hidden(fn (): bool => auth()->user()->role !== 'admin')
                ]),
            ])
            ->striped()
            ->defaultSort('tanggal_kunjungan', 'desc')
            ->paginated([5, 10, 25, 50, 100])
            ->poll('10s');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDetailPemeriksaanKunjungans::route('/'),
            'create' => Pages\CreateDetailPemeriksaanKunjungan::route('/create'),
            'edit' => Pages\EditDetailPemeriksaanKunjungan::route('/{record}/edit'),
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
}
