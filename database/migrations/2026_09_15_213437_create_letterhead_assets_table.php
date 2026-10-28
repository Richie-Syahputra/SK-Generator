<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('letterhead_assets', function (Blueprint $table) {
            $table->id();
            $table->string('file_path');
            $table->boolean('is_active')->default(false);
            $table->string('catatan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('letterhead_assets');
    }
};
