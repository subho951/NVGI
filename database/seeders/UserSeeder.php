<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::insert([
            [
                'role_id'       => 1,
                'branch_id'     => 0,
                'first_name'    => 'Super',
                'last_name'     => 'Admin',
                'email'         => 'superadmin@pdw.com',
                'country_code'  => '+91',
                'phone'         => '0000000000',
                'password'      => Hash::make('admin123'),
                'status'        => 1
            ]
        ]);
    }
}
