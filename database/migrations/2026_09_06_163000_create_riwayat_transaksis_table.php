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
        Schema::create('riwayat_transaksis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('siswa_id')
                ->constrained('siswas')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->dateTime('tanggal_transaksi')->nullable();
            $table->string('function', 50)->default('Bayar'); // Bayar / Modifikasi
            $table->string('jenis', 20)->default('SPP'); // UAP / SPP
            $table->decimal('nominal', 12, 2)->default(0);
            $table->decimal('sisa', 12, 2)->nullable();
            $table->decimal('nilai_akhir', 12, 2)->nullable();
            $table->string('keterangan')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('riwayat_transaksis');
    }
};
