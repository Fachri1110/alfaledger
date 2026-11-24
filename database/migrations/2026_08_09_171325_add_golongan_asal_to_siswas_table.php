<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('siswas', function (Blueprint $table) {
            // Menyimpan golongan asal yang diinput admin (REG atau ANP)
            // golongan_kategori bisa berubah menjadi HALF/FULL tergantung tanggal_masuk
            $table->string('golongan_asal', 10)->nullable()->default('REG')->after('golongan_kategori');
        });

        // Seed existing data: derive golongan_asal from golongan_kategori
        // HALF/FULL → REG, ANP → ANP, REG → REG
        \DB::statement("
            UPDATE siswas
            SET golongan_asal = CASE
                WHEN golongan_kategori = 'ANP' THEN 'ANP'
                ELSE 'REG'
            END
        ");
    }

    public function down(): void
    {
        Schema::table('siswas', function (Blueprint $table) {
            $table->dropColumn('golongan_asal');
        });
    }
};
