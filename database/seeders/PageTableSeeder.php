<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Page;

class PageTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Page::insert([
            [
                'page_name'                      => 'Terms and conditions',
                'page_slug'                      => 'terms-and-conditions'
            ],
            [
                'page_name'                      => 'Privacy Policy',
                'page_slug'                      => 'privacy-policy'
            ]
        ]);
    }
}
