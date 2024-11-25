<?php
// app/Models/Kunjungan.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
        'tanggal_lahir' => 'date',
        'tanggal_kunjungan' => 'datetime'
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
        return $this->belongsToMany(Obat::class, 'kunjungan_obat', 'kunjungan_id', 'kode_obat')
            ->withPivot('jumlah', 'total_harga')
            ->withTimestamps();
    }

    public function pemeriksaans(): BelongsToMany
    {
        return $this->belongsToMany(Pemeriksaan::class, 'kunjungan_pemeriksaan', 'kunjungan_id', 'kode_pemeriksaan')
            ->withTimestamps();
    }

    public function detailPemeriksaan(): HasMany
    {
        return $this->hasMany(DetailPemeriksaanKunjungan::class, 'kode_pelanggan', 'kode_pelanggan')
            ->where('tanggal_kunjungan', $this->tanggal_kunjungan);
    }

    public function detailObat(): HasMany
    {
        return $this->hasMany(DetailObatKunjungan::class, 'kode_pelanggan', 'kode_pelanggan')
            ->where('tanggal_kunjungan', $this->tanggal_kunjungan);
    }

    public static function canCreateNewVisit($kodePelanggan)
    {
        // Cek apakah ada kunjungan yang belum dibayar di kasir
        return !Kasir::where('kode_pelanggan', $kodePelanggan)
            ->where('status_pembayaran', 'Belum Bayar')
            ->exists();
    }
}
