<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\LeadHeader;

class LeadHeaderTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        LeadHeader::insert([
            [
                'name'                      => 'Company Name',
                'slug'                      => 'company-name',
                'input_type'                => 'TEXTBOX',
                'rank'                      => 1,
                'is_visible_in_lead_list'   => 'YES'
            ],
            [
                'name'                      => 'Contact Person Name',
                'slug'                      => 'contact-person-name',
                'input_type'                => 'TEXTBOX',
                'rank'                      => 2,
                'is_visible_in_lead_list'   => 'YES'
            ],
            [
                'name'                      => 'Phone Code',
                'slug'                      => 'phone-code',
                'input_type'                => 'TEXTBOX',
                'rank'                      => 3,
                'is_visible_in_lead_list'   => 'YES'
            ],
            [
                'name'                      => 'Phone',
                'slug'                      => 'phone',
                'input_type'                => 'TEXTBOX',
                'rank'                      => 4,
                'is_visible_in_lead_list'   => 'YES'
            ],
            [
                'name'                      => 'Email',
                'slug'                      => 'email',
                'input_type'                => 'TEXTBOX',
                'rank'                      => 5,
                'is_visible_in_lead_list'   => 'YES'
            ],
            [
                'name'                      => 'Address',
                'slug'                      => 'address',
                'input_type'                => 'TEXTAREA',
                'rank'                      => 6,
                'is_visible_in_lead_list'   => 'NO'
            ],
            [
                'name'                      => 'Country',
                'slug'                      => 'country',
                'input_type'                => 'DROPDOWN',
                'rank'                      => 7,
                'is_visible_in_lead_list'   => 'NO'
            ],
            [
                'name'                      => 'State',
                'slug'                      => 'state',
                'input_type'                => 'DROPDOWN',
                'rank'                      => 8,
                'is_visible_in_lead_list'   => 'NO'
            ],
            [
                'name'                      => 'City',
                'slug'                      => 'city',
                'input_type'                => 'DROPDOWN',
                'rank'                      => 9,
                'is_visible_in_lead_list'   => 'NO'
            ],
            [
                'name'                      => 'Pincode',
                'slug'                      => 'pincode',
                'input_type'                => 'TEXTBOX',
                'rank'                      => 10,
                'is_visible_in_lead_list'   => 'NO'
            ],
            [
                'name'                      => 'Source',
                'slug'                      => 'source',
                'input_type'                => 'DROPDOWN',
                'rank'                      => 11,
                'is_visible_in_lead_list'   => 'NO'
            ],
            [
                'name'                      => 'DOB',
                'slug'                      => 'dob',
                'input_type'                => 'DATE',
                'rank'                      => 12,
                'is_visible_in_lead_list'   => 'NO'
            ],
            [
                'name'                      => 'Anniversary',
                'slug'                      => 'anniversary',
                'input_type'                => 'DATE',
                'rank'                      => 13,
                'is_visible_in_lead_list'   => 'NO'
            ]
        ]);
    }
}
