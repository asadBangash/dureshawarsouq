<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class ShopMenuSeeder extends Seeder
{
    public function run(): void
    {
        (new CosmeticsCategoriesSeeder())->syncShopMegaMenu();
        $this->command?->info('Shop mega menu synced (Categories + More columns).');
    }
}
