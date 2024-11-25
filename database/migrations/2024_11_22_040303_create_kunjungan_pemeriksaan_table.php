<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('kunjungan_pemeriksaan', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('kunjungan_id');
            $table->string('kode_pemeriksaan');
            $table->decimal('total_harga', 10, 2)->default(0);
            $table->timestamps();

            $table->foreign('kunjungan_id')
                  ->references('id')
                  ->on('kunjungans')
                  ->onDelete('cascade');

            $table->foreign('kode_pemeriksaan')
                  ->references('kode_pemeriksaan')
                  ->on('pemeriksaans')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kunjungan_pemeriksaan');
    }
};
