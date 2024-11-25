<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kasirs', function (Blueprint $table) {
            $table->id();
            $table->string('kode_pelanggan');
            $table->string('nama');
            $table->decimal('jumlah_biaya', 15, 2);
            $table->string('status_pembayaran');
            $table->string('id_pembayaran')->nullable();
            $table->string('metode_pembayaran')->nullable();
            $table->datetime('tanggal_kunjungan');
            $table->datetime('tanggal_pembayaran')->nullable();
            $table->unsignedBigInteger('kunjungan_id')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kasirs');
    }
};
