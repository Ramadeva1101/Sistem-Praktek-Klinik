<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pasien', function (Blueprint $table) {
            $table->integer('jumlah_kunjungan')->default(0)->after('jenis_kelamin');
            $table->timestamp('kunjungan_terakhir')->nullable()->after('jumlah_kunjungan');
        });
    }

    public function down(): void
    {
        Schema::table('pasien', function (Blueprint $table) {
            $table->dropColumn(['jumlah_kunjungan', 'kunjungan_terakhir']);
        });
    }
};
