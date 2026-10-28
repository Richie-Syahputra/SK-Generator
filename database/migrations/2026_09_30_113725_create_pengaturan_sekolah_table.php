<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pengaturan_sekolah', function (Blueprint $table) {
            $table->id();
            $table->string('nama_sekolah')->default('Nama Sekolah');
            $table->string('kota')->default('Banjarmasin');
            $table->string('singkatan_nomor')->default('SMK');
            $table->text('tembusan_default')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengaturan_sekolah');
    }
};
