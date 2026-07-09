<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@admin.com'], // The unique column it checks first
            [
                'name' => 'System Administrator',
                'password' => bcrypt('password'), // This will reset the password to 'password'
                'role' => 'admin',
            ]
        );
    }
}
