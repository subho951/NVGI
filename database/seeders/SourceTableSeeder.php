<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Source;

class SourceTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Source::insert([
            [
                'name' => 'FACEBOOK'
            ],
            [
                'name' => 'GOOGLE'
            ],
            [
                'name' => 'WHATSAPP'
            ],
            [
                'name' => 'INSTAGRAM'
            ],
            [
                'name' => 'LINKEDIN'
            ],
            [
                'name' => 'YOUTUBE'
            ],
            [
                'name' => 'NEWSPAPER'
            ],
            [
                'name' => 'PINTEREST'
            ],
            [
                'name' => 'OFFLINE BANNER'
            ],
            [
                'name' => 'BLOG'
            ],
            [
                'name' => 'DIGITAL ADS'
            ]
        ]);
    }
}
