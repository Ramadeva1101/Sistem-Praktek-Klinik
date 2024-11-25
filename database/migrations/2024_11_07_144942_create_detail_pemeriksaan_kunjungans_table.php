<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('detail_pemeriksaan_kunjungans', function (Blueprint $table) {
            $table->id();
            $table->string('kode_pelanggan');
            $table->string('nama_pasien');
            $table->string('kode_pemeriksaan');
            $table->string('nama_pemeriksaan');
            $table->decimal('harga', 12, 2);
            $table->decimal('total_harga', 12, 2);
            $table->timestamp('tanggal_kunjungan');
            $table->timestamps();

            $table->index('kode_pelanggan');
            $table->index('kode_pemeriksaan');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detail_pemeriksaan_kunjungans');
    }
};
