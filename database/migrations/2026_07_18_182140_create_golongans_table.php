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
        Schema::create('golongans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jenjang_id')
                ->constrained('jenjangs')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
            $table->string('nama_golongan'); // contoh: SD - Kelas 1
            $table->decimal('nominal_uang_pangkal', 15, 2);
            $table->decimal('nominal_uang_spp', 15, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('golongans');
    }
};
