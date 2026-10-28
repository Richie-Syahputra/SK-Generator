<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sk_terbit', function (Blueprint $table) {
            $table->string('kop_mode', 10)->nullable();
            $table->unsignedBigInteger('letterhead_asset_id')->nullable();
            $table->unsignedBigInteger('penandatangan_id')->nullable();
            $table->string('penandatangan_nama')->nullable();
            $table->string('penandatangan_jabatan')->nullable();
            $table->index('penandatangan_nama');
        });
    }

    public function down(): void
    {
        Schema::table('sk_terbit', function (Blueprint $table) {
            $table->dropIndex(['penandatangan_nama']);
            $table->dropColumn(['kop_mode', 'letterhead_asset_id', 'penandatangan_id', 'penandatangan_nama', 'penandatangan_jabatan']);
        });
    }
};
