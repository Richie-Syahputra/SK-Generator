<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sk_terbit', function (Blueprint $table) {
            $table->dropUnique(['nomor_surat']);
        });

        Schema::table('sk_terbit', function (Blueprint $table) {
            $table->unsignedInteger('nomor_urut')->nullable()->after('nomor_surat');
            $table->index('nomor_urut');
        });

        // Isi nomor_urut untuk surat yang sudah ada, diambil dari teks "KEP.xxx" di nomor_surat
        DB::table('sk_terbit')->orderBy('id')->each(function ($baris) {
            if (preg_match('/KEP\.(\d+)/', (string) $baris->nomor_surat, $m)) {
                DB::table('sk_terbit')->where('id', $baris->id)->update(['nomor_urut' => (int) $m[1]]);
            }
        });
    }

    public function down(): void
    {
        Schema::table('sk_terbit', function (Blueprint $table) {
            $table->dropIndex(['nomor_urut']);
            $table->dropColumn('nomor_urut');
        });
    }
};
