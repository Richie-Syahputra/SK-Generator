<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pengaturan_sekolah', function (Blueprint $table) {
            $table->string('format_nomor')->default('{klasifikasi}/KEP.{nomor}–SMKM.3/DISDIKBUD/{bulan}/{tahun}');
            $table->unsignedTinyInteger('digit_nomor')->default(3);
        });
    }

    public function down(): void
    {
        Schema::table('pengaturan_sekolah', function (Blueprint $table) {
            $table->dropColumn(['format_nomor', 'digit_nomor']);
        });
    }
};
