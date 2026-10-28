<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('jenis_sk', function (Blueprint $table) {
            $table->text('template_memperhatikan')->nullable()->after('template_mengingat');
        });
    }

    public function down(): void
    {
        Schema::table('jenis_sk', function (Blueprint $table) {
            $table->dropColumn('template_memperhatikan');
        });
    }
};
