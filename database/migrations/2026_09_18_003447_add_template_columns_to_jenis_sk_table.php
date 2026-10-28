<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('jenis_sk', function (Blueprint $table) {
            $table->text('template_menimbang')->nullable();
            $table->text('template_mengingat')->nullable();
            $table->text('template_diktum')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('jenis_sk', function (Blueprint $table) {
            $table->dropColumn(['template_menimbang', 'template_mengingat', 'template_diktum']);
        });
    }
};
