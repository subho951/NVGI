<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Module;

class ModuleTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Module::insert([
            [
                'name' => 'Dashboard'
            ],
            [
                'name' => 'Access & Permission - Modules'
            ],
            [
                'name' => 'Access & Permission - Roles'
            ],
            [
                'name' => 'Access & Permission - Admin Users'
            ],
            [
                'name' => 'FAQ - Category'
            ],
            [
                'name' => 'FAQ - Sub Category'
            ],
            [
                'name' => 'FAQ - List'
            ],
            [
                'name' => 'Pages'
            ],
            [
                'name' => 'Account Settings'
            ],
            [
                'name' => 'Email Logs'
            ],
            [
                'name' => 'Login Logs'
            ],
            [
                'name' => 'User Activity Logs'
            ],
            [
                'name' => 'Country'
            ],
            [
                'name' => 'State'
            ],
            [
                'name' => 'City'
            ],
            [
                'name' => 'Campaign Type'
            ],
            [
                'name' => 'Campaign'
            ],
            [
                'name' => 'Source'
            ],
            [
                'name' => 'Leader Header'
            ],
            [
                'name' => 'Lead Status'
            ],
        ]);
    }
}
