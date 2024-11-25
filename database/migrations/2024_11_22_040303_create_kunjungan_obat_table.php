<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kunjungan_obat', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('kunjungan_id');
            $table->string('kode_obat');
            $table->integer('jumlah')->default(1);
            $table->decimal('total_harga', 10, 2)->default(0);
            $table->timestamps();

            $table->foreign('kunjungan_id')
                  ->references('id')
                  ->on('kunjungans')
                  ->onDelete('cascade');

            $table->foreign('kode_obat')
                  ->references('kode_obat')
                  ->on('obats')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kunjungan_obat');
    }
};
