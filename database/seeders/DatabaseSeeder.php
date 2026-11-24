<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * Order matters: Jenjang MUST be before SuperAdminSeeder
     */
    public function run(): void
    {
        $this->call([
            // System configuration - ROLES FIRST
            RoleSeeder::class,

            // Education structure BEFORE users (users need jenjang_id)
            JenjangSeeder::class,

            // Users (depends on Roles and Jenjangs)
            SuperAdminSeeder::class,

            // Master data
            GolonganSeeder::class,
            TahunAjaranSeeder::class,
            SettingPembayaranSeeder::class,
        ]);

        $this->command->info('✅ All seeders completed successfully!');
    }
}
