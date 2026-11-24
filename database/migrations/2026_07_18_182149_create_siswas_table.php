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
        Schema::create('siswas', function (Blueprint $table) {
            $table->id();
            $table->string('nama_siswa');
            $table->string('nisn')->unique();
            $table->date('tanggal_masuk');
            // Status keseluruhan siswa: lunas / belum_lunas (status pembayaran berjalan)
            // atau pindah (siswa sudah tidak aktif)
            $table->enum('status_siswa', ['lunas', 'belum_lunas', 'pindah'])
                ->default('belum_lunas');
            // golongan_id = golongan/kelas siswa SAAT INI.
            // Riwayat kenaikan kelas tersimpan di tabel riwayat_kelas_siswas.
            $table->foreignId('golongan_id')
                ->constrained('golongans')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('siswas');
    }
};
