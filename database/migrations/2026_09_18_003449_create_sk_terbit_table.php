<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sk_terbit', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jenis_sk_id')->constrained('jenis_sk');
            $table->string('nomor_surat')->unique();
            $table->date('tanggal_sk');
            $table->json('data_isian');
            $table->string('file_docx')->nullable();
            $table->string('file_pdf')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sk_terbit');
    }
};
