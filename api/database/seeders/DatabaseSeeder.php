<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            CoreDataSeeder::class,
            GovernanceSeeder::class,
            FinancialSeeder::class,
            ComplianceSupportSeeder::class,
        ]);

        User::updateOrCreate(
            ['email' => 'admin@upravnik.rs'],
            [
                'name' => 'Sistem Administrator',
                'password' => Hash::make('Upravnik!2024'),
                'is_admin' => true,
            ]
        );
    }
}
