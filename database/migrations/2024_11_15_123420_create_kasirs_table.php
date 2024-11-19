<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('kasirs', function (Blueprint $table) {
            $table->id();
            $table->string('kode_pelanggan')->unique();
            $table->string('nama');
            $table->decimal('jumlah_biaya', 12, 2);
            $table->enum('status_pembayaran', ['Belum Dibayar', 'Sudah Dibayar'])->default('Belum Dibayar');
            $table->dateTime('tanggal_pembayaran')->nullable();
            $table->dateTime('tanggal_kunjungan')->nullable();
            $table->string('id_pembayaran')->unique()->nullable();
            $table->enum('metode_pembayaran', ['cash', 'card'])->nullable();
            $table->foreignId('kunjungan_id')->nullable()->constrained()->onDelete('set null');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('kasirs');
    }
};
