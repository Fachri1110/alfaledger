<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    /**
     * Run the migrations.
     * 
     * Creates item_uang_pangkals table for tracking individual payment items
     * for "Uang Pangkal" with support for partial payment percentages.
     */
    public function up(): void
    {
        Schema::create('item_uang_pangkals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('siswa_id')
                ->constrained('siswas')
                ->onDelete('cascade')
                ->comment('Reference to student');
            $table->foreignId('tahun_ajaran_id')
                ->constrained('tahun_ajarans')
                ->onDelete('cascade')
                ->comment('Reference to academic year');
            $table->string('nama_item')
                ->comment('Name of the payment item (e.g., Seragam, Buku, etc.)');
            $table->decimal('nominal', 15, 2)
                ->comment('Total amount for this item');
            $table->decimal('persentase_dibayar', 5, 2)
                ->default(0)
                ->comment('Percentage paid (0-100)');
            $table->enum('status', ['belum_lunas', 'lunas'])
                ->default('belum_lunas')
                ->comment('Payment status');
            $table->timestamps();

            // Indexes for better query performance
            $table->index('siswa_id');
            $table->index('tahun_ajaran_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('item_uang_pangkals');
    }
};
