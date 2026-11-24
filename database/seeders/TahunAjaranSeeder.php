<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Tahun_ajaran;

class TahunAjaranSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Tahun_ajaran::firstOrCreate(
            ['tahun_ajaran' => '2026/2027'],
            [
                'semester' => 1,
                'status'   => 'aktif',
            ]
        );

        if ($this->command) {
            $this->command->info('Tahun Ajaran seeded successfully!');
        }
    }
}
