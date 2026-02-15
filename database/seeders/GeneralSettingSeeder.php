<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\GeneralSetting;

class GeneralSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        GeneralSetting::insert([
            [
                'key'=> 'site_name',
                'slug' => 'site_name',
                'value' => 'Panna Diamond World',
                'is_active' => 1
            ],
            [
                'key'=> 'site_phone',
                'slug' => 'site_phone',
                'value' => '9876543210',
                'is_active' => 1
            ],
            [
                'key'=> 'site_phone2',
                'slug' => 'site_phone2',
                'value' => '9876543210',
                'is_active' => 1
            ],
            [
                'key'=> 'address',
                'slug' => 'address',
                'value' => 'Test address goes here',
                'is_active' => 1
            ],
            [
                'key'=> 'site_mail',
                'slug' => 'site_mail',
                'value' => 'test@test.com',
                'is_active' => 1
            ],
            [
                'key'=> 'system_email',
                'slug' => 'system_email',
                'value' => 'test@test.com',
                'is_active' => 1
            ],
            [
                'key'=> 'site_url',
                'slug' => 'site_url',
                'value' => 'http://localhost/pdw_crm/',
                'is_active' => 1
            ],
            [
                'key'=> 'description',
                'slug' => 'description',
                'value' => 'Panna Diamond World CRM',
                'is_active' => 1
            ],
            [
                'key'=> 'site_logo',
                'slug' => 'site_logo',
                'value' => '1738074536logo.jpg',
                'is_active' => 1
            ],
            [
                'key'=> 'site_footer_logo',
                'slug' => 'site_footer_logo',
                'value' => '1738074536logo.jpg',
                'is_active' => 1
            ],
            [
                'key'=> 'site_favicon',
                'slug' => 'site_favicon',
                'value' => '1738074536logo.jpg',
                'is_active' => 1
            ],
            [
                'key'=> 'copyright_statement',
                'slug' => 'copyright_statement',
                'value' => '',
                'is_active' => 1
            ],
            [
                'key'=> 'google_map_api_code',
                'slug' => 'google_map_api_code',
                'value' => '',
                'is_active' => 1
            ],
            [
                'key'=> 'google_analytics_code',
                'slug' => 'google_analytics_code',
                'value' => '',
                'is_active' => 1
            ],
            [
                'key'=> 'google_pixel_code',
                'slug' => 'google_pixel_code',
                'value' => '',
                'is_active' => 1
            ],
            [
                'key'=> 'facebook_tracking_code',
                'slug' => 'facebook_tracking_code',
                'value' => '',
                'is_active' => 1
            ],
            [
                'key'=> 'theme_color',
                'slug' => 'theme_color',
                'value' => '#000000',
                'is_active' => 1
            ],
            [
                'key'=> 'font_color',
                'slug' => 'font_color',
                'value' => '#f20707',
                'is_active' => 1
            ],
            [
                'key'=> 'home_page_youtube_link',
                'slug' => 'home_page_youtube_link',
                'value' => '',
                'is_active' => 1
            ],
            [
                'key'=> 'home_page_youtube_code',
                'slug' => 'home_page_youtube_code',
                'value' => '',
                'is_active' => 1
            ],
            [
                'key'=> 'twitter_profile',
                'slug' => 'twitter_profile',
                'value' => '',
                'is_active' => 1
            ],
            [
                'key'=> 'facebook_profile',
                'slug' => 'facebook_profile',
                'value' => '',
                'is_active' => 1
            ],
            [
                'key'=> 'instagram_profile',
                'slug' => 'instagram_profile',
                'value' => '',
                'is_active' => 1
            ],
            [
                'key'=> 'linkedin_profile',
                'slug' => 'linkedin_profile',
                'value' => '',
                'is_active' => 1
            ],
            [
                'key'=> 'youtube_profile',
                'slug' => 'youtube_profile',
                'value' => '',
                'is_active' => 1
            ],
            [
                'key'=> 'topbar_text',
                'slug' => 'topbar_text',
                'value' => '',
                'is_active' => 1
            ],
            [
                'key'=> 'sms_authentication_key',
                'slug' => 'sms_authentication_key',
                'value' => '',
                'is_active' => 1
            ],
            [
                'key'=> 'sms_sender_id',
                'slug' => 'sms_sender_id',
                'value' => '',
                'is_active' => 1
            ],
            [
                'key'=> 'sms_base_url',
                'slug' => 'sms_base_url',
                'value' => '',
                'is_active' => 1
            ],
            [
                'key'=> 'from_email',
                'slug' => 'from_email',
                'value' => '',
                'is_active' => 1
            ],
            [
                'key'=> 'from_name',
                'slug' => 'from_name',
                'value' => '',
                'is_active' => 1
            ],
            [
                'key'=> 'smtp_host',
                'slug' => 'smtp_host',
                'value' => '',
                'is_active' => 1
            ],
            [
                'key'=> 'smtp_username',
                'slug' => 'smtp_username',
                'value' => '',
                'is_active' => 1
            ],
            [
                'key'=> 'smtp_password',
                'slug' => 'smtp_password',
                'value' => '',
                'is_active' => 1
            ],
            [
                'key'=> 'smtp_port',
                'slug' => 'smtp_port',
                'value' => '',
                'is_active' => 1
            ],
            [
                'key'=> 'email_template_user_signup',
                'slug' => 'email_template_user_signup',
                'value' => '',
                'is_active' => 1
            ],
            [
                'key'=> 'email_template_forgot_password',
                'slug' => 'email_template_forgot_password',
                'value' => '',
                'is_active' => 1
            ],
            [
                'key'=> 'email_template_change_password',
                'slug' => 'email_template_change_password',
                'value' => '',
                'is_active' => 1
            ],
            [
                'key'=> 'email_template_failed_login',
                'slug' => 'email_template_failed_login',
                'value' => '',
                'is_active' => 1
            ],
            [
                'key'=> 'email_template_contactus',
                'slug' => 'email_template_contactus',
                'value' => '',
                'is_active' => 1
            ],
            [
                'key'=> 'meta_title',
                'slug' => 'meta_title',
                'value' => '',
                'is_active' => 1
            ],
            [
                'key'=> 'meta_description',
                'slug' => 'meta_description',
                'value' => '',
                'is_active' => 1
            ],
            [
                'key'=> 'meta_keywords',
                'slug' => 'meta_keywords',
                'value' => '',
                'is_active' => 1
            ],
            [
                'key'=> 'footer_text',
                'slug' => 'footer_text',
                'value' => '',
                'is_active' => 1
            ],
            [
                'key'=> 'footer_link_name',
                'slug' => 'footer_link_name',
                'value' => '',
                'is_active' => 1
            ],
            [
                'key'=> 'footer_link',
                'slug' => 'footer_link',
                'value' => '',
                'is_active' => 1
            ],
            [
                'key'=> 'footer_link_name2',
                'slug' => 'footer_link_name2',
                'value' => '',
                'is_active' => 1
            ],
            [
                'key'=> 'footer_link2',
                'slug' => 'footer_link2',
                'value' => '',
                'is_active' => 1
            ],
            [
                'key'=> 'footer_link_name3',
                'slug' => 'footer_link_name3',
                'value' => '',
                'is_active' => 1
            ],
            [
                'key'=> 'footer_link3',
                'slug' => 'footer_link3',
                'value' => '',
                'is_active' => 1
            ],
            [
                'key'=> 'footer_data',
                'slug' => 'footer_data',
                'value' => '',
                'is_active' => 1
            ]
        ]);
    }
}
