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
}
