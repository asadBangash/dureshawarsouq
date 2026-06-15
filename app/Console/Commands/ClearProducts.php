<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ClearProducts extends Command
{
    protected $signature = 'products:clear {--force : Run without confirmation}';

    protected $description = 'Delete all products and related data (images, related links, reviews)';

    public function handle(): int
    {
        if (! $this->option('force') && ! $this->confirm('This will permanently delete ALL products, gallery images, related products, and product reviews. Continue?')) {
            $this->info('Cancelled.');

            return self::SUCCESS;
        }

        $productCount = DB::table('products')->count();

        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        $reviews = DB::table('reviews')->delete();
        $images = DB::table('pro_images')->delete();
        $related = DB::table('related_products')->delete();
        $products = DB::table('products')->delete();

        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $this->info("Deleted {$products} products, {$images} gallery images, {$related} related links, {$reviews} reviews.");
        $this->comment("Previously had {$productCount} products. Categories, brands, and orders were kept.");

        return self::SUCCESS;
    }
}
