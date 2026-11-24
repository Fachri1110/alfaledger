<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Nama tetap item Uang Pangkal (urutan sesuai item_index 1-9)
     */
    private array $itemNames = [
        1 => 'Gedung',
        2 => 'Materi',
        3 => 'Praktek',
        4 => 'Kegiatan',
        5 => 'Ekstrakurikuler',
        6 => 'Komputer',
        7 => 'Administrasi',
        8 => 'Raport',
        9 => 'Badge',
    ];

    public function up(): void
    {
        // Update setting_item_uang_pangkals
        foreach ($this->itemNames as $index => $name) {
            DB::table('setting_item_uang_pangkals')
                ->where('item_index', $index)
                ->update(['nama_item' => $name]);
        }

        // Update item_uang_pangkals (actual student payment records)
        // Nama item di tabel ini mungkin masih pakai nama lama dari seeder.
        // Mapping dilakukan berdasarkan urutan persentase dari total.
        // Karena tidak ada item_index di sana, kita map berdasarkan nama_item.
        $oldToNew = [
            'Seragam'                  => 'Gedung',
            'Buku Paket'               => 'Materi',
            'Alat Tulis'               => 'Praktek',
            'Kegiatan Ekstrakurikuler' => 'Kegiatan',
            'Biaya Pendaftaran'        => 'Ekstrakurikuler',
        ];
        foreach ($oldToNew as $old => $new) {
            DB::table('item_uang_pangkals')
                ->where('nama_item', $old)
                ->update(['nama_item' => $new]);
        }
    }

    public function down(): void
    {
        // Revert to generic names
        foreach ($this->itemNames as $index => $name) {
            DB::table('setting_item_uang_pangkals')
                ->where('item_index', $index)
                ->update(['nama_item' => 'Item ' . $index]);
        }
    }
};
