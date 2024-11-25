<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetailObatKunjungan extends Model
{
    protected $table = 'detail_obat_kunjungans';

    protected $guarded = [];

    protected $casts = [
        'tanggal_kunjungan' => 'datetime',
        'harga' => 'decimal:2',
        'total_harga' => 'decimal:2',
        'jumlah' => 'integer',
    ];

    public function kasir(): BelongsTo
    {
        return $this->belongsTo(Kasir::class, 'kode_pelanggan', 'kode_pelanggan')
            ->where('tanggal_kunjungan', $this->tanggal_kunjungan);
    }

    public function getStatusPembayaranAttribute()
    {
        return $this->kasir?->status_pembayaran ?? 'Belum Dibayar';
    }

    public function kunjungan(): BelongsTo
    {
        return $this->belongsTo(Kunjungan::class, 'kode_pelanggan', 'kode_pelanggan')
            ->where('tanggal_kunjungan', $this->tanggal_kunjungan);
    }

    public function pasien(): BelongsTo
    {
        return $this->belongsTo(Pasien::class, 'kode_pelanggan', 'kode_pelanggan');
    }
}
