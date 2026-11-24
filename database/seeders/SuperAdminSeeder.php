<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Darlina (Ketua Yayasan)
        $darlina = User::firstOrCreate(
            ['username' => 'darlina'],
            [
                'name' => 'Darlina',
                'email' => 'darlinaalforn@gmail.com',
                'password' => Hash::make('super123')
            ]
        );
        $darlina->syncRoles(['Ketua Yayasan']);

        // 2. Staf Yayasan
        $stafYayasan = User::firstOrCreate(
            ['username' => 'stafyayasan'],
            [
                'name' => 'Staf Yayasan',
                'email' => 'stafyayasan@gmail.com',
                'password' => Hash::make('staf123')
            ]
        );
        $stafYayasan->syncRoles(['Staf Yayasan']);

        // 3. Felix (Staf TU / KB/TK)
        $felix = User::firstOrCreate(
            ['username' => 'felix'],
            [
                'name' => 'Felix',
                'email' => 'felixjoyful@gmail.com',
                'password' => Hash::make('admin123'),
                'jenjang_id' => 1
            ]
        );
        $felix->syncRoles(['Staf TU']);

        // 4. Olivia (Staf TU / SD)
        $olivia = User::firstOrCreate(
            ['username' => 'olivia'],
            [
                'name' => 'Olivia',
                'email' => 'oliviaforever@gmail.com',
                'password' => Hash::make('admin123'),
                'jenjang_id' => 2
            ]
        );
        $olivia->syncRoles(['Staf TU']);

        // 5. Stella (Staf TU / SMP)
        $stella = User::firstOrCreate(
            ['username' => 'stella'],
            [
                'name' => 'Stella',
                'email' => 'stellasweet@gmail.com',
                'password' => Hash::make('admin123'),
                'jenjang_id' => 3
            ]
        );
        $stella->syncRoles(['Staf TU']);
    }
}
