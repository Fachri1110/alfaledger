<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Jenjang;
use App\Models\Golongan;

class GolonganSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Golongan per Jenjang & Kelas. 
        // Kategori siswa: REG (Reguler), ANP (Anak Pegawai), APK (Anak Pegawai Khusus),
        // FULL (Pindahan Satu Tahun), HALF (Pindahan Setengah Tahun).
        $kbtk = Jenjang::where('nama_jenjang', 'KB/TK')->first();
        if ($kbtk) {
            $kbtkGolongan = ['KB', 'TK A', 'TK B'];
            foreach ($kbtkGolongan as $g) {
                Golongan::firstOrCreate(
                    ['jenjang_id' => $kbtk->id, 'nama_golongan' => $g],
                    ['nominal_uang_pangkal' => 0, 'nominal_uang_spp' => 0]
                );
            }
        }

        $sd = Jenjang::where('nama_jenjang', 'SD')->first();
        if ($sd) {
            $sdGolongan = ['Kelas 1', 'Kelas 2', 'Kelas 3', 'Kelas 4', 'Kelas 5', 'Kelas 6'];
            foreach ($sdGolongan as $g) {
                Golongan::firstOrCreate(
                    ['jenjang_id' => $sd->id, 'nama_golongan' => $g],
                    ['nominal_uang_pangkal' => 0, 'nominal_uang_spp' => 0]
                );
            }
        }

        $smp = Jenjang::where('nama_jenjang', 'SMP')->first();
        if ($smp) {
            $smpGolongan = ['Kelas 7', 'Kelas 8', 'Kelas 9'];
            foreach ($smpGolongan as $g) {
                Golongan::firstOrCreate(
                    ['jenjang_id' => $smp->id, 'nama_golongan' => $g],
                    ['nominal_uang_pangkal' => 0, 'nominal_uang_spp' => 0]
                );
            }
        }

        if ($this->command) {
            $this->command->info('Golongan seeded successfully!');
        }
    }
}
