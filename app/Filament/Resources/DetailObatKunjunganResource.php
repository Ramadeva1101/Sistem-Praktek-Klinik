<?php

namespace App\Filament\Resources;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use App\Models\DetailObatKunjungan;
use Filament\Tables\Actions\Action;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\Layout\View;
use Illuminate\Database\Eloquent\Builder;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\RepeatableEntry;
use App\Filament\Resources\DetailObatKunjunganResource\Pages;
use App\Filament\Resources\DetailObatKunjunganResource\Pages\ListDetailObatKunjungans;
use App\Filament\Resources\DetailObatKunjunganResource\Pages\CreateDetailObatKunjungan;
use App\Filament\Resources\DetailObatKunjunganResource\Pages\EditDetailObatKunjungan;
use Illuminate\Support\Facades\DB;
use Filament\Tables\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Exports\DetailObatExport;
use Filament\Support\Colors\Color;

class DetailObatKunjunganResource extends Resource
{
    protected static ?string $model = DetailObatKunjungan::class;
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationLabel = 'Detail Obat';
    protected static ?string $navigationGroup = 'Transaksi';
    protected static ?string $slug = 'detail-obat-kunjungans';

    public static function table(Table $table): Table
    {
        return $table
            ->query(function () {
                return DetailObatKunjungan::query()
                    ->select([
                        'kode_pelanggan',
                        'nama_pasien',
                        'tanggal_kunjungan',
                        DB::raw('MAX(id) as id')
                    ])
                    ->groupBy('kode_pelanggan', 'nama_pasien', 'tanggal_kunjungan')
                    ->orderBy('tanggal_kunjungan', 'desc');
            })
            ->headerActions([
                Action::make('exportExcel')
                    ->label('Export Excel')
                    ->icon('heroicon-m-table-cells')
                    ->color(Color::Green)
                    ->size('lg')
                    ->button()
                    ->tooltip('Download Laporan Excel')
                    ->action(function () {
                        return Excel::download(new DetailObatExport, 'detail-obat-' . now()->format('Y-m-d') . '.xlsx');
                    }),
                Action::make('exportPdf')
                    ->label('Export PDF')
                    ->icon('heroicon-m-document-arrow-down')
                    ->color(Color::Red)
                    ->size('lg')
                    ->button()
                    ->tooltip('Download Laporan PDF')
                    ->action(function () {
                        $data = DetailObatKunjungan::query()
                            ->select([
                                'kode_pelanggan',
                                'nama_pasien',
                                'tanggal_kunjungan',
                                'kode_obat',
                                'nama_obat',
                                'jumlah',
                                'harga',
                                'total_harga',
                                'status_pembayaran'
                            ])
                            ->orderBy('tanggal_kunjungan', 'desc')
                            ->get();

                        $pdf = PDF::loadView('exports.detail-obat', [
                            'records' => $data
                        ]);

                        $pdf->setPaper('A4', 'landscape');
                        return response()->streamDownload(function () use ($pdf) {
                            echo $pdf->output();
                        }, 'detail-obat-' . now()->format('Y-m-d') . '.pdf');
                    })
            ])
            ->columns([
                Tables\Columns\TextColumn::make('kode_pelanggan')
                    ->searchable()
                    ->sortable()
                    ->label('Kode Pelanggan'),
                Tables\Columns\TextColumn::make('nama_pasien')
                    ->searchable()
                    ->sortable()
                    ->label('Nama Pasien'),
                Tables\Columns\TextColumn::make('tanggal_kunjungan')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->label('Tanggal Kunjungan'),
            ])
            ->defaultSort('tanggal_kunjungan', 'desc')
            ->actions([
                ViewAction::make()
                    ->label('View Detail')
                    ->infolist([
                        Section::make('Detail Obat')
                            ->description(fn ($record) => "Tanggal Kunjungan: " . $record->tanggal_kunjungan->format('d/m/Y H:i'))
                            ->schema([
                                Section::make('Daftar Obat')
                                    ->schema(function ($record) {
                                        $details = DetailObatKunjungan::where('kode_pelanggan', $record->kode_pelanggan)
                                            ->where('tanggal_kunjungan', $record->tanggal_kunjungan)
                                            ->get();

                                        return $details->map(fn ($detail) =>
                                            \Filament\Infolists\Components\Grid::make(6)
                                                ->schema([
                                                    TextEntry::make('kode_obat')
                                                        ->label('Kode Obat')
                                                        ->state($detail->kode_obat),
                                                    TextEntry::make('nama_obat')
                                                        ->label('Nama Obat')
                                                        ->state($detail->nama_obat),
                                                    TextEntry::make('jumlah')
                                                        ->label('Jumlah')
                                                        ->state($detail->jumlah),
                                                    TextEntry::make('harga')
                                                        ->label('Harga')
                                                        ->money('IDR')
                                                        ->state($detail->harga),
                                                    TextEntry::make('total_harga')
                                                        ->label('Total Harga')
                                                        ->money('IDR')
                                                        ->state($detail->total_harga),
                                                    TextEntry::make('status_pembayaran')
                                                        ->label('Status')
                                                        ->badge()
                                                        ->color(fn () => match ($detail->status_pembayaran) {
                                                            'Sudah Bayar' => 'success',
                                                            'Belum Bayar' => 'danger',
                                                            default => 'warning',
                                                        })
                                                        ->state($detail->status_pembayaran),
                                                ])
                                    )->toArray();
                                    }),

                                TextEntry::make('total_keseluruhan')
                                    ->label('Total Keseluruhan')
                                    ->money('IDR')
                                    ->weight(FontWeight::Bold)
                                    ->state(function ($record) {
                                        return DetailObatKunjungan::where('kode_pelanggan', $record->kode_pelanggan)
                                            ->where('tanggal_kunjungan', $record->tanggal_kunjungan)
                                            ->sum('total_harga');
                                    })
                            ])
                    ])
                    ->modalWidth('7xl'),
                DeleteAction::make()
                    ->label('Hapus')
                    ->hidden(fn (): bool => auth()->user()->role !== 'admin')
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        DetailObatKunjungan::where('kode_pelanggan', $record->kode_pelanggan)
                            ->where('tanggal_kunjungan', $record->tanggal_kunjungan)
                            ->delete();

                        Notification::make()
                            ->success()
                            ->title('Data berhasil dihapus')
                            ->send();
                    })
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDetailObatKunjungans::route('/'),
            // 'create' => Pages\CreateDetailObatKunjungan::route('/create'),
            // 'edit' => Pages\EditDetailObatKunjungan::route('/{record}/edit'),
        ];
    }

    public static function shouldRegisterNavigation(): bool
    {
        return in_array(auth()->user()->role, ['admin', 'kasir']);
    }
   
}
