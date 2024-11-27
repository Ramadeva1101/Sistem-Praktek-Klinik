<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RiwayatPembayaran extends Model
{
    use HasFactory;

    protected $table = 'riwayat_pembayarans';

    protected $fillable = [
        'id_pembayaran',
        'kode_pelanggan',
        'nama_pasien',
        'tanggal_kunjungan',
        'tanggal_pembayaran',
        'nama_pemeriksaan',
        'biaya_pemeriksaan',
        'nama_obat',
        'jumlah_obat',
        'satuan_obat',
        'total_biaya_obat',
        'jumlah_biaya',
        'metode_pembayaran'
    ];

    protected $casts = [
        'tanggal_kunjungan' => 'datetime',
        'tanggal_pembayaran' => 'datetime',
        'biaya_pemeriksaan' => 'decimal:2',
        'total_biaya_obat' => 'decimal:2',
        'jumlah_biaya' => 'decimal:2'
    ];

    public function kasir()
    {
        return $this->belongsTo(Kasir::class, 'kasir_id');
    }

    public function pasien()
    {
        return $this->belongsTo(Pasien::class, 'kode_pelanggan', 'kode_pelanggan');
    }

    /**
     * Scope untuk filter berdasarkan rentang tanggal
     */
    public function scopeDateRange($query, $from, $to)
    {
        return $query->whereBetween('tanggal_pembayaran', [
            $from . ' 00:00:00',
            $to . ' 23:59:59'
        ]);
    }

    /**
     * Scope untuk filter berdasarkan tanggal kunjungan
     */
    public function scopeKunjunganRange($query, $from, $to)
    {
        return $query->whereBetween('tanggal_kunjungan', [
            $from . ' 00:00:00',
            $to . ' 23:59:59'
        ]);
    }

    /**
     * Scope untuk filter berdasarkan tanggal (single date)
     */
    public function scopeFilterTanggal($query, $tanggal)
    {
        return $query->whereDate('tanggal_pembayaran', $tanggal);
    }

    /**
     * Scope untuk filter berdasarkan tanggal kunjungan (single date)
     */
    public function scopeFilterTanggalKunjungan($query, $tanggal)
    {
        return $query->whereDate('tanggal_kunjungan', $tanggal);
    }

    /**
     * Scope untuk filter berdasarkan bulan
     */
    public function scopeFilterBulan($query, $bulan, $tahun)
    {
        return $query->whereMonth('tanggal_pembayaran', $bulan)
                    ->whereYear('tanggal_pembayaran', $tahun);
    }

    /**
     * Scope untuk filter berdasarkan tahun
     */
    public function scopeFilterTahun($query, $tahun)
    {
        return $query->whereYear('tanggal_pembayaran', $tahun);
    }

    /**
     * Scope baru untuk filter dari-sampai dengan opsional
     */
    public function scopeFilterTanggalDariSampai($query, $dari = null, $sampai = null)
    {
        // Filter hanya dari tanggal
        if ($dari && !$sampai) {
            return $query->where('tanggal_pembayaran', '>=', $dari . ' 00:00:00');
        }

        // Filter hanya sampai tanggal
        if (!$dari && $sampai) {
            return $query->where('tanggal_pembayaran', '<=', $sampai . ' 23:59:59');
        }

        // Filter dari-sampai tanggal
        if ($dari && $sampai) {
            return $query->whereBetween('tanggal_pembayaran', [
                $dari . ' 00:00:00',
                $sampai . ' 23:59:59'
            ]);
        }

        return $query;
    }

    public function scopeFilterKunjunganDariSampai($query, $dari = null, $sampai = null)
    {
        // Filter hanya dari tanggal
        if ($dari && !$sampai) {
            return $query->where('tanggal_kunjungan', '>=', $dari . ' 00:00:00');
        }

        // Filter hanya sampai tanggal
        if (!$dari && $sampai) {
            return $query->where('tanggal_kunjungan', '<=', $sampai . ' 23:59:59');
        }

        // Filter dari-sampai tanggal
        if ($dari && $sampai) {
            return $query->whereBetween('tanggal_kunjungan', [
                $dari . ' 00:00:00',
                $sampai . ' 23:59:59'
            ]);
        }

        return $query;
    }
}
