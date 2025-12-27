<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Services\Settings\SettingsService;

class SettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
    */
    public function run(): void
    {
        $s = app(SettingsService::class);

        // General
        $s->set('site_name', 'أفلاك', 'string', 'general');
        $s->set('site_description', 'Landing page management system', 'text', 'general');

        // Branding
        $s->set('branding.logo', null, 'image', 'branding');
        $s->set('branding.favicon', null, 'image', 'branding');

        // Colors
        $s->set('colors.secondary', '#0ea5e9', 'color', 'colors');
        $s->set('colors.accent', '#22c55e', 'color', 'colors');
        $s->set('colors.background', '#0b1220', 'color', 'colors');

        // SEO
        $s->set('seo.meta_title', 'Landing Page', 'string', 'seo');
        $s->set('seo.meta_description', 'Manage your landing page content easily.', 'text', 'seo');
        $s->set('seo.keywords', 'landing page, laravel, cms', 'text', 'seo');
    }
}
