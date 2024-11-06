<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
}