<?php

namespace App\Filament\Widgets;

use App\Models\Kasir;
use App\Models\DetailObatKunjungan;
use App\Models\DetailPemeriksaanKunjungan;
use App\Models\Pasien;
use Illuminate\Support\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;
use App\Models\RiwayatPembayaran;

class StatsOverview extends BaseWidget
{
    protected static ?string $pollingInterval = null;

    public static function canView(): bool
    {
        return auth()->user()->role === 'admin';
    }

    protected function getStats(): array
    {
        // Total Pasien
        $totalPasien = Pasien::count();

        // Total Kunjungan Hari Ini
        $totalKunjunganHariIni = RiwayatPembayaran::whereDate('tanggal_pembayaran', Carbon::today())
            ->count();

        // Total Pendapatan Hari Ini
        $totalPendapatanHariIni = RiwayatPembayaran::whereDate('tanggal_pembayaran', Carbon::today())
            ->sum('jumlah_biaya');

        // Total Pendapatan Bulan Ini
        $totalPendapatanBulanIni = RiwayatPembayaran::whereMonth('tanggal_pembayaran', Carbon::now()->month)
            ->whereYear('tanggal_pembayaran', Carbon::now()->year)
            ->sum('jumlah_biaya');

        // Obat Terlaris
        $obatTerlaris = RiwayatPembayaran::select('nama_obat', DB::raw('SUM(jumlah_obat) as total_penggunaan'))
            ->whereNotNull('nama_obat')
            ->whereMonth('tanggal_pembayaran', Carbon::now()->month)
            ->whereYear('tanggal_pembayaran', Carbon::now()->year)
            ->groupBy('nama_obat')
            ->orderByDesc('total_penggunaan')
            ->first() ?? (object)['nama_obat' => 'Belum ada data', 'total_penggunaan' => 0];

        // Pemeriksaan Terlaris
        $pemeriksaanTerlaris = RiwayatPembayaran::select('nama_pemeriksaan', DB::raw('COUNT(*) as total'))
            ->whereNotNull('nama_pemeriksaan')
            ->whereMonth('tanggal_pembayaran', Carbon::now()->month)
            ->whereYear('tanggal_pembayaran', Carbon::now()->year)
            ->groupBy('nama_pemeriksaan')
            ->orderByDesc('total')
            ->first() ?? (object)['nama_pemeriksaan' => 'Belum ada data', 'total' => 0];

        return [
            Stat::make('Total Pasien', number_format($totalPasien))
                ->description($totalKunjunganHariIni . ' kunjungan selesai hari ini')
                ->descriptionIcon('heroicon-o-users')
                ->color('success'),

            Stat::make('Pendapatan Hari Ini', 'Rp ' . number_format($totalPendapatanHariIni, 0, ',', '.'))
                ->description('Total bulan ini: Rp ' . number_format($totalPendapatanBulanIni, 0, ',', '.'))
                ->descriptionIcon('heroicon-o-banknotes')
                ->color('warning'),

            Stat::make('Obat Terlaris', $obatTerlaris->nama_obat)
                ->description('Total penggunaan: ' . number_format($obatTerlaris->total_penggunaan))
                ->descriptionIcon('heroicon-o-beaker')
                ->color('info'),

            Stat::make('Pemeriksaan Terlaris', $pemeriksaanTerlaris->nama_pemeriksaan)
                ->description('Total: ' . number_format($pemeriksaanTerlaris->total))
                ->descriptionIcon('heroicon-o-clipboard-document-list')
                ->color('success'),
        ];
    }
}
