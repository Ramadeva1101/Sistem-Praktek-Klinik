<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('riwayat_pembayarans', function (Blueprint $table) {
            // Drop kolom yang akan dimodifikasi
            $table->dropColumn([
                'nama_pemeriksaan',
                'biaya_pemeriksaan',
                'nama_obat',
                'jumlah_obat',
                'satuan_obat',
                'total_biaya_obat'
            ]);
        });

        Schema::table('riwayat_pembayarans', function (Blueprint $table) {
            // Tambah ulang kolom dengan nullable
            $table->string('nama_pemeriksaan')->nullable()->after('tanggal_pembayaran');
            $table->decimal('biaya_pemeriksaan', 12, 2)->default(0)->after('nama_pemeriksaan');
            $table->string('nama_obat')->nullable()->after('biaya_pemeriksaan');
            $table->integer('jumlah_obat')->nullable()->after('nama_obat');
            $table->string('satuan_obat')->nullable()->after('jumlah_obat');
            $table->decimal('total_biaya_obat', 12, 2)->default(0)->after('satuan_obat');
        });
    }

    public function down()
    {
        Schema::table('riwayat_pembayarans', function (Blueprint $table) {
            // Drop kolom
            $table->dropColumn([
                'nama_pemeriksaan',
                'biaya_pemeriksaan',
                'nama_obat',
                'jumlah_obat',
                'satuan_obat',
                'total_biaya_obat'
            ]);
        });

        Schema::table('riwayat_pembayarans', function (Blueprint $table) {
            // Tambah ulang kolom tanpa nullable
            $table->string('nama_pemeriksaan')->after('tanggal_pembayaran');
            $table->decimal('biaya_pemeriksaan', 12, 2)->after('nama_pemeriksaan');
            $table->string('nama_obat')->after('biaya_pemeriksaan');
            $table->integer('jumlah_obat')->after('nama_obat');
            $table->string('satuan_obat')->after('jumlah_obat');
            $table->decimal('total_biaya_obat', 12, 2)->after('satuan_obat');
        });
    }
};
