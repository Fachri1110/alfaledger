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
        Schema::create('setting_item_uang_pangkals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jenjang_id')
                ->constrained('jenjangs')
                ->cascadeOnDelete();
            $table->string('kelas'); // e.g. "Kelas 1", "Kelas 2", etc.
            $table->string('kategori'); // e.g. "Reguler", "Anak Pegawai", etc.
            $table->unsignedInteger('item_index'); // 1-9
            $table->string('nama_item'); // e.g. "Item 1", "Seragam", etc.
            $table->decimal('nominal', 15, 2)->default(0);
            $table->boolean('is_aktif')->default(false);
            $table->timestamps();

            $table->unique(['jenjang_id', 'kelas', 'kategori', 'item_index'], 'setting_item_pangkal_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('setting_item_uang_pangkals');
    }
};
