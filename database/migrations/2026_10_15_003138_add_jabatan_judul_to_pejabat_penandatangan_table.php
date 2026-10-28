<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pejabat_penandatangan', function (Blueprint $table) {
            $table->string('jabatan_judul')->nullable()->after('jabatan');
        });
    }

    public function down(): void
    {
        Schema::table('pejabat_penandatangan', function (Blueprint $table) {
            $table->dropColumn('jabatan_judul');
        });
    }
};
