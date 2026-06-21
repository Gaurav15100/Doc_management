<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            [
                'phone' => '9999999999'
            ],
            [
                'name' => 'Admin',
                'password' => 'admin123',
                'role' => 'admin',
                'is_active' => true,
                'outlet_id' => null,
            ]
        );
    }
}