<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kunjungans', function (Blueprint $table) {
            $table->id();
            $table->string('kode_pelanggan');
            $table->string('nama');
            $table->date('tanggal_lahir')->nullable(false); // Pastikan tipe date dan tidak boleh null
            $table->string('jenis_kelamin');
            $table->text('alamat');
            $table->datetime('tanggal_kunjungan');
            $table->string('status')->default('active');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kunjungans');
    }
};
