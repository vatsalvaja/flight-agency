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
        // Seed default roles
        \App\Models\Role::updateOrCreate(
            ['role_name' => 'Manager'],
            ['status' => 0]
        );

        \App\Models\Role::updateOrCreate(
            ['role_name' => 'Driver'],
            ['status' => 0]
        );

        User::updateOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'name' => 'Admin User',
                'password' => \Illuminate\Support\Facades\Hash::make('Admin@123'),
                'role_id' => 0,
                'status' => 0,
            ]
        );
    }
}
