<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('setting_spps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jenjang_id')
                ->constrained('jenjangs')
                ->cascadeOnDelete();
            $table->string('kelas'); // e.g. "Kelas 1", "Kelas 2", etc.
            $table->string('kategori'); // e.g. "Reguler", "Anak Pegawai", etc.
            $table->decimal('nominal', 15, 2)->default(0);
            $table->timestamps();

            $table->unique(['jenjang_id', 'kelas', 'kategori'], 'setting_spp_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('setting_spps');
    }
};
