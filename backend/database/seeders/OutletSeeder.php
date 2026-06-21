<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Outlet;

class OutletSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Outlet::insert([
            ['name' => 'Surat'],
            ['name' => 'Ahmedabad'],
            ['name' => 'Mumbai'],
        ]);
    }
}