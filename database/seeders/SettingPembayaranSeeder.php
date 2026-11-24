<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Jenjang;
use App\Models\SettingItemUangPangkal;
use App\Models\SettingSpp;

class SettingPembayaranSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sd = Jenjang::where('nama_jenjang', 'SD')->first();
        if (!$sd) return;

        $kelasList = ['Kelas 1', 'Kelas 2', 'Kelas 3', 'Kelas 4', 'Kelas 5', 'Kelas 6'];
        // Kategori UAP mencakup Reguler, Anak Pegawai, Anak Pegawai Khusus (APK), Pindahan Satu Tahun (FULL), Pindahan Setengah Tahun (HALF)
        $kategoriPangkal = [
            'Reguler', 'Anak Pegawai', 'Anak Pegawai Khusus', 'Pindahan Satu Tahun', 'Pindahan Setengah Tahun',
            'REG', 'ANP', 'APK', 'FULL', 'HALF'
        ];
        // SPP hanya Reguler dan Anak Pegawai (golongan APK mengikuti SPP Reguler)
        $kategoriSpp = ['Reguler', 'Anak Pegawai'];

        $itemNames = [
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

        // Seed Uang Pangkal Items
        foreach ($kelasList as $kelas) {
            foreach ($kategoriPangkal as $kategori) {
                for ($i = 1; $i <= 9; $i++) {
                    SettingItemUangPangkal::updateOrCreate(
                        [
                            'jenjang_id' => $sd->id,
                            'kelas' => $kelas,
                            'kategori' => $kategori,
                            'item_index' => $i,
                        ],
                        [
                            'nama_item' => $itemNames[$i],
                            'nominal' => 0,
                            'is_aktif' => false,
                        ]
                    );
                }
            }

            // Seed SPP
            foreach ($kategoriSpp as $kategori) {
                SettingSpp::firstOrCreate(
                    [
                        'jenjang_id' => $sd->id,
                        'kelas' => $kelas,
                        'kategori' => $kategori,
                    ],
                    [
                        'nominal' => 0,
                    ]
                );
            }
        }
    }
}
