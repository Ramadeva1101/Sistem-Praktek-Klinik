<?php
// app/Models/Kunjungan.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Kunjungan extends Model
{
    use HasFactory;

    protected $table = 'kunjungans';

    protected $fillable = [
        'kode_pelanggan',
        'nama',
        'tanggal_lahir',
        'jenis_kelamin',
        'alamat',
        'tanggal_kunjungan',
        'status'
    ];

    protected $casts = [
        'tanggal_kunjungan' => 'datetime',
        'tanggal_lahir' => 'date'
    ];

    public function kasir(): HasOne
    {
        return $this->hasOne(Kasir::class);
    }

    public function pasien(): BelongsTo
    {
        return $this->belongsTo(Pasien::class, 'kode_pelanggan', 'kode_pelanggan');
    }

    public function obats(): BelongsToMany
    {
        return $this->belongsToMany(
            Obat::class,
            'detail_obat_kunjungans',
            'kunjungan_id',
            'obat_id',
            'id',
            'kode_obat'
        );
    }

    public function pemeriksaans(): BelongsToMany
    {
        return $this->belongsToMany(
            Pemeriksaan::class,
            'detail_pemeriksaan_kunjungans',
            'kunjungan_id',
            'pemeriksaan_id',
            'id',
            'kode_pemeriksaan'
        );
    }
}
