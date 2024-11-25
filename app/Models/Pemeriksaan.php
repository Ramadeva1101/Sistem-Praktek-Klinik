<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Pemeriksaan extends Model
{
    use HasFactory;

    protected $primaryKey = 'kode_pemeriksaan';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'kode_pemeriksaan',
        'nama_pemeriksaan',
        'harga_pemeriksaan',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($pemeriksaan) {
            $pemeriksaan->kode_pemeriksaan = 'PM-' . strtoupper(Str::random(8));
        });
    }

    public function kunjungans(): BelongsToMany
    {
        return $this->belongsToMany(Kunjungan::class, 'kunjungan_pemeriksaan', 'kode_pemeriksaan', 'kunjungan_id')
            ->withTimestamps();
    }
}
