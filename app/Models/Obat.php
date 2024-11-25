<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Obat extends Model
{
    use HasFactory;

    protected $primaryKey = 'kode_obat';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = ['kode_obat', 'nama_obat', 'harga'];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($obat) {
            $obat->kode_obat = $obat->kode_obat ?? 'OBT-' . strtoupper(Str::random(8));
        });
    }

    public function kunjungans(): BelongsToMany
    {
        return $this->belongsToMany(Kunjungan::class, 'kunjungan_obat', 'kode_obat', 'kunjungan_id')
            ->withTimestamps();
    }
}
