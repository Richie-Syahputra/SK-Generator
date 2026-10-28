<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('penomoran_tahun', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('tahun')->unique();
            $table->unsignedInteger('nomor_mulai')->default(1);
            $table->timestamp('reset_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('penomoran_tahun');
    }
};
