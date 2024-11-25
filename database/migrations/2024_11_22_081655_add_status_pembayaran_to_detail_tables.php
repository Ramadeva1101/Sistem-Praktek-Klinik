<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('detail_obat_kunjungans', function (Blueprint $table) {
            $table->string('status_pembayaran')->default('Belum Dibayar');
        });

        Schema::table('detail_pemeriksaan_kunjungans', function (Blueprint $table) {
            $table->string('status_pembayaran')->default('Belum Dibayar');
        });
    }

    public function down(): void
    {
        Schema::table('detail_obat_kunjungans', function (Blueprint $table) {
            $table->dropColumn('status_pembayaran');
        });

        Schema::table('detail_pemeriksaan_kunjungans', function (Blueprint $table) {
            $table->dropColumn('status_pembayaran');
        });
    }
};
