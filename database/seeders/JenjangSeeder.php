<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Jenjang;

class JenjangSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Seeds education levels (jenjang) for the school system
     */
    public function run(): void
    {
        $jenjangs = [
            ['nama_jenjang' => 'KB/TK'],
            ['nama_jenjang' => 'SD'],
            ['nama_jenjang' => 'SMP'],
        ];

        foreach ($jenjangs as $jenjang) {
            Jenjang::firstOrCreate($jenjang);
        }

        $this->command->info('Jenjang seeded successfully!');
    }
}
