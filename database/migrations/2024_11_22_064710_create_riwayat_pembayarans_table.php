<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('riwayat_pembayarans', function (Blueprint $table) {
            $table->id();
            $table->string('id_pembayaran');
            $table->string('kode_pelanggan');
            $table->string('nama_pasien');
            $table->datetime('tanggal_kunjungan');
            $table->datetime('tanggal_pembayaran');
            // Pemeriksaan bisa null
            $table->string('nama_pemeriksaan')->nullable();
            $table->decimal('biaya_pemeriksaan', 12, 2)->default(0);
            // Obat bisa null
            $table->string('nama_obat')->nullable();
            $table->integer('jumlah_obat')->nullable();
            $table->string('satuan_obat')->nullable();
            $table->decimal('total_biaya_obat', 12, 2)->default(0);
            // Total keseluruhan
            $table->decimal('jumlah_biaya', 12, 2);
            $table->string('metode_pembayaran');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('riwayat_pembayarans');
    }
};
