<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIndexes extends Migration
{
    public function up()
    {
        Schema::table('kunjungans', function (Blueprint $table) {
            $table->index(['kode_pelanggan', 'tanggal_kunjungan']);
        });

        Schema::table('detail_pemeriksaan_kunjungans', function (Blueprint $table) {
            $table->index(['kode_pelanggan', 'tanggal_kunjungan']);
        });

        Schema::table('detail_obat_kunjungans', function (Blueprint $table) {
            $table->index(['kode_pelanggan', 'tanggal_kunjungan']);
        });
    }

    public function down()
    {
        Schema::table('kunjungans', function (Blueprint $table) {
            $table->dropIndex(['kode_pelanggan', 'tanggal_kunjungan']);
        });

        Schema::table('detail_pemeriksaan_kunjungans', function (Blueprint $table) {
            $table->dropIndex(['kode_pelanggan', 'tanggal_kunjungan']);
        });

        Schema::table('detail_obat_kunjungans', function (Blueprint $table) {
            $table->dropIndex(['kode_pelanggan', 'tanggal_kunjungan']);
        });
    }
}
