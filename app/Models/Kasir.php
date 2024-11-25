<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

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
        'tanggal_pembayaran' => 'datetime:Y-m-d H:i:s',
        'tanggal_kunjungan' => 'datetime:Y-m-d H:i:s'
    ];

    public function kunjungan(): BelongsTo
    {
        return $this->belongsTo(Kunjungan::class);
    }

    public function pasien(): BelongsTo
    {
        return $this->belongsTo(Pasien::class, 'kode_pelanggan', 'kode_pelanggan');
    }

    public function detailObat()
    {
        return $this->hasMany(DetailObatKunjungan::class, 'kode_pelanggan', 'kode_pelanggan')
            ->whereDate('tanggal_kunjungan', Carbon::parse($this->tanggal_kunjungan)->toDateString());
    }

    public function detailPemeriksaan()
    {
        return $this->hasMany(DetailPemeriksaanKunjungan::class, 'kode_pelanggan', 'kode_pelanggan')
            ->whereDate('tanggal_kunjungan', Carbon::parse($this->tanggal_kunjungan)->toDateString());
    }

    public function simpanKeRiwayat()
    {
        // Log data awal
        Log::info('Memulai simpan riwayat:', [
            'kode_pelanggan' => $this->kode_pelanggan,
            'tanggal_kunjungan' => $this->tanggal_kunjungan
        ]);

        // Ambil data pemeriksaan
        $pemeriksaan = DetailPemeriksaanKunjungan::where('kode_pelanggan', $this->kode_pelanggan)
            ->where('tanggal_kunjungan', $this->tanggal_kunjungan)
            ->first();

        Log::info('Data pemeriksaan:', [
            'pemeriksaan' => $pemeriksaan ? $pemeriksaan->toArray() : null
        ]);

        // Ambil data obat
        $obat = DetailObatKunjungan::where('kode_pelanggan', $this->kode_pelanggan)
            ->where('tanggal_kunjungan', $this->tanggal_kunjungan)
            ->get();

        Log::info('Data obat:', [
            'obat' => $obat->toArray()
        ]);

        // Ambil data pasien
        $pasien = Pasien::where('kode_pelanggan', $this->kode_pelanggan)->first();
        Log::info('Data pasien:', [
            'pasien' => $pasien ? $pasien->toArray() : null
        ]);

        try {
            // Simpan ke riwayat pembayaran
            $riwayat = RiwayatPembayaran::create([
                'kode_pelanggan' => $this->kode_pelanggan,
                'nama_pasien' => $pasien ? $pasien->nama : null,
                'tanggal_pembayaran' => now(),
                'nama_pemeriksaan' => $pemeriksaan ? $pemeriksaan->nama_pemeriksaan : null,
                'biaya_pemeriksaan' => $pemeriksaan ? $pemeriksaan->harga : 0,
                'nama_obat' => $obat->isNotEmpty() ? $obat->first()->nama_obat : null,
                'jumlah_obat' => $obat->isNotEmpty() ? $obat->first()->jumlah : null,
                'satuan_obat' => $obat->isNotEmpty() ? $obat->first()->satuan : null,
                'total_biaya_obat' => $obat->sum('total_harga'),
                'jumlah_biaya' => $this->jumlah_biaya
            ]);

            Log::info('Riwayat berhasil disimpan:', [
                'riwayat_id' => $riwayat->id
            ]);

            return $riwayat;

        } catch (\Exception $e) {
            Log::error('Gagal menyimpan riwayat:', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            throw $e;
        }
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

    public function shouldBeDeleted(): bool
    {
        if ($this->status_pembayaran !== 'Sudah Dibayar') {
            return false;
        }

        return $this->tanggal_pembayaran <= Carbon::now()->subHours(24);
    }

    // Accessor jika diperlukan
    public function getTanggalPembayaranAttribute($value)
    {
        return $value ? Carbon::parse($value)->setTimezone('Asia/Makassar') : null;
    }

    public function getTanggalKunjunganAttribute($value)
    {
        return $value ? Carbon::parse($value)->setTimezone('Asia/Makassar') : null;
    }
}
