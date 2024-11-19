<?php

namespace App\Filament\Widgets;

use App\Models\Obat;
use App\Models\Pasien;
use App\Models\Kunjungan;
use App\Models\Pemeriksaan;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        // Statistik Pasien
        $totalPasien = Pasien::count();
        $pasienBaruBulanIni = Pasien::whereMonth('created_at', Carbon::now()->month)
                                   ->whereYear('created_at', Carbon::now()->year)
                                   ->count();

        // Hitung pasien berdasarkan jenis kelamin
        $pasienByGender = Pasien::select('jenis_kelamin', DB::raw('count(*) as total'))
                               ->groupBy('jenis_kelamin')
                               ->pluck('total', 'jenis_kelamin')
                               ->toArray();

        // Statistik Kunjungan
        $totalKunjungan = Kunjungan::count();
        $kunjunganHariIni = Kunjungan::whereDate('tanggal_kunjungan', Carbon::today())->count();
        $kunjunganBulanIni = Kunjungan::whereMonth('tanggal_kunjungan', Carbon::now()->month)
                                     ->whereYear('tanggal_kunjungan', Carbon::now()->year)
                                     ->count();

        // Statistik Obat
        $totalObat = Obat::count();
        $totalNilaiObat = Obat::sum('harga');
        $obatTerlaris = DB::table('detail_obat_kunjungans')
            ->select('obat_id', DB::raw('count(*) as total'))
            ->groupBy('obat_id')
            ->orderByDesc('total')
            ->first();

        $namaObatTerlaris = 'Tidak ada data';
        if ($obatTerlaris) {
            $obat = Obat::find($obatTerlaris->obat_id);
            $namaObatTerlaris = $obat ? $obat->nama_obat : 'Tidak ada data';
        }

        // Statistik Pemeriksaan
        $totalPemeriksaan = Pemeriksaan::count();
        $totalNilaiPemeriksaan = Pemeriksaan::sum('harga_pemeriksaan');
        $pemeriksaanTerlaris = DB::table('detail_pemeriksaan_kunjungans')
            ->select('pemeriksaan_id', DB::raw('count(*) as total'))
            ->groupBy('pemeriksaan_id')
            ->orderByDesc('total')
            ->first();

        $namaPemeriksaanTerlaris = 'Tidak ada data';
        if ($pemeriksaanTerlaris) {
            $pemeriksaan = Pemeriksaan::find($pemeriksaanTerlaris->pemeriksaan_id);
            $namaPemeriksaanTerlaris = $pemeriksaan ? $pemeriksaan->nama_pemeriksaan : 'Tidak ada data';
        }

        return [
            // Statistik Pasien
            Stat::make('Total Pasien', number_format($totalPasien))
                ->description('Total seluruh pasien terdaftar')
                ->descriptionIcon('heroicon-m-users')
                ->color('success')
                ->chart([7, 2, 10, 3, 15, 4, 17]),

            Stat::make('Pasien Baru', number_format($pasienBaruBulanIni))
                ->description('Pasien baru bulan ini')
                ->descriptionIcon('heroicon-m-user-plus')
                ->color('info')
                ->chart([3, 5, 7, 8, 6, 9, 10]),

            Stat::make(
                'Rasio Gender',
                sprintf(
                    'L: %d | P: %d',
                    $pasienByGender['L'] ?? 0,
                    $pasienByGender['P'] ?? 0
                )
            )
                ->description('Perbandingan pasien L/P')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('warning'),

            // Statistik Kunjungan
            Stat::make('Total Kunjungan', number_format($totalKunjungan))
                ->description('Total seluruh kunjungan')
                ->descriptionIcon('heroicon-m-clipboard-document-check')
                ->color('success'),

            Stat::make('Kunjungan Hari Ini', number_format($kunjunganHariIni))
                ->description('Jumlah kunjungan hari ini')
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('primary'),

            Stat::make('Kunjungan Bulan Ini', number_format($kunjunganBulanIni))
                ->description('Total kunjungan bulan ini')
                ->descriptionIcon('heroicon-m-calendar')
                ->color('warning'),

            // Statistik Obat
            Stat::make('Total Obat', number_format($totalObat))
                ->description('Jumlah jenis obat tersedia')
                ->descriptionIcon('heroicon-m-beaker')
                ->color('success'),

            Stat::make('Nilai Obat', 'Rp ' . number_format($totalNilaiObat, 0, ',', '.'))
                ->description('Total nilai seluruh obat')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('warning'),

            Stat::make('Obat Terlaris', $namaObatTerlaris)
                ->description('Obat yang paling sering digunakan')
                ->descriptionIcon('heroicon-m-star')
                ->color('primary'),

            // Statistik Pemeriksaan
            Stat::make('Jenis Pemeriksaan', number_format($totalPemeriksaan))
                ->description('Jumlah jenis pemeriksaan')
                ->descriptionIcon('heroicon-m-clipboard-document-list')
                ->color('success'),

            Stat::make('Nilai Pemeriksaan', 'Rp ' . number_format($totalNilaiPemeriksaan, 0, ',', '.'))
                ->description('Total nilai pemeriksaan')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('warning'),

            Stat::make('Pemeriksaan Terlaris', $namaPemeriksaanTerlaris)
                ->description('Pemeriksaan terpopuler')
                ->descriptionIcon('heroicon-m-star')
                ->color('primary'),
        ];
    }
}
