<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('detail_obat_kunjungans', function (Blueprint $table) {
            $table->id();
            $table->string('kode_pelanggan');
            $table->string('nama_pasien');
            $table->string('kode_obat');
            $table->string('nama_obat');
            $table->integer('jumlah');
            $table->decimal('harga', 12, 2);
            $table->decimal('total_harga', 12, 2);
            $table->timestamp('tanggal_kunjungan');
            $table->timestamps();

            $table->index('kode_pelanggan');
            $table->index('kode_obat');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detail_obat_kunjungans');
    }
};
