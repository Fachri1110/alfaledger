<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE siswas MODIFY COLUMN status_siswa VARCHAR(50) NOT NULL DEFAULT 'aktif'");
        
        // Migrate any legacy 'pindah' to 'non_aktif'
        DB::statement("UPDATE siswas SET status_siswa = 'non_aktif' WHERE status_siswa = 'pindah'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE siswas MODIFY COLUMN status_siswa ENUM('lunas', 'belum_lunas', 'pindah') NOT NULL DEFAULT 'belum_lunas'");
    }
};
