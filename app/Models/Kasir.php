<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Kasir extends Model
{
    use HasFactory;

    protected $fillable = [
        'kode_pelanggan',
        'nama',
        'jumlah_biaya',
        'status_pembayaran',
        'tanggal_pembayaran',
        'tanggal_kunjungan',
        'kunjungan_id',
        'id_pembayaran',
        'metode_pembayaran'
    ];

    protected $casts = [
        'tanggal_pembayaran' => 'datetime',
        'tanggal_kunjungan' => 'datetime'
    ];

    public function kunjungan(): BelongsTo
    {
        return $this->belongsTo(Kunjungan::class);
    }

    public function pasien(): BelongsTo
    {
        return $this->belongsTo(Pasien::class, 'kode_pelanggan', 'kode_pelanggan');
    }

    protected static function booted()
    {
        static::creating(function ($kasir) {
            // Generate id_pembayaran jika kosong
            if (empty($kasir->id_pembayaran)) {
                $kasir->id_pembayaran = 'INV-' . strtoupper(Str::random(8));
            }

            // Generate kode_pelanggan secara otomatis jika kosong
            if (empty($kasir->kode_pelanggan)) {
                $latestKasir = Kasir::orderBy('created_at', 'desc')->first();
                $latestId = $latestKasir ? $latestKasir->id : 0;
                $newId = $latestId + 1;
                $kasir->kode_pelanggan = 'PAS-' . str_pad($newId, 5, '0', STR_PAD_LEFT);
            }

            // Set tanggal_kunjungan jika kosong
            if (empty($kasir->tanggal_kunjungan)) {
                $kasir->tanggal_kunjungan = now();
            }
        });
    }
}
