<?php

/**
 * One-time fix for live server: missing languages row + core theme options.
 * Run: php fix-live-minimal.php
 * Then delete this file.
 */

require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

if (! DB::table('languages')->where('language_code', 'en')->exists()) {
    DB::table('languages')->insert([
        'language_code' => 'en',
        'language_name' => 'English',
        'flag' => null,
        'language_default' => 1,
        'is_rtl' => 0,
        'status' => 1,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    echo "Language 'en' inserted.\n";
} else {
    echo "Language 'en' already exists.\n";
}

$defaults = [
    'page_variation' => [
        'home_variation' => 'home_1',
        'category_variation' => 'left_sidebar',
        'brand_variation' => 'left_sidebar',
        'seller_variation' => 'left_sidebar',
    ],
    'general_settings' => [
        'company' => 'The Dur-e-Shahwar Souq',
        'email' => 'info@dureshahwarsouq.ae',
        'phone' => '+92 300 0000000',
        'site_name' => 'The Dur-e-Shahwar Souq',
        'site_title' => 'Luxury Beauty & Cosmetics',
        'address' => 'UAE',
        'timezone' => 'Asia/Dubai',
    ],
];

foreach ($defaults as $name => $value) {
    if (! DB::table('tp_options')->where('option_name', $name)->exists()) {
        DB::table('tp_options')->insert([
            'option_name' => $name,
            'option_value' => json_encode($value),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        echo "Inserted tp_option: {$name}\n";
    }
}

echo "Done. Run: php artisan view:clear\n";
