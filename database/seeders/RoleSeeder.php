<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Role::insert([
            [
                'role_name' => 'Master Admin',
                'module_id' => '["1","2","3","4","5","6","7","8","9","10","11","12","13","14","15","16","17","18","19","20"]'
            ],
            [
                'role_name' => 'Sub Admin',
                'module_id' => '[]'
            ]
        ]);
    }
}
