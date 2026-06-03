<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Product;
use App\Models\Pro_category;
use App\Models\Tp_option;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class CosmeticsShopSeeder extends Seeder
{
    protected string $mediaPath;

    public function run(): void
    {
        $this->mediaPath = public_path('media');
        if (! File::isDirectory($this->mediaPath)) {
            File::makeDirectory($this->mediaPath, 0755, true);
        }

        $logoFiles = $this->installLogo();
        $this->updateThemeOptions($logoFiles);
        $this->clearGroceryCatalog();
        $imagePool = $this->downloadProductImages();
        $categories = $this->seedCategories($imagePool);
        $brands = $this->seedBrands();
        $this->seedProducts($categories, $brands, $imagePool);
        $this->updateHomepageContent($logoFiles['banner']);
        $this->updateOffersAndVideoSection();
        $this->fixHomeMenuDropdown();
        $this->syncHeaderMenus();
        $this->updateHeroImagery($logoFiles['banner']);

        $this->command?->info('Dur-e-Shahwar Souq cosmetics catalog seeded successfully.');
    }

    protected function installLogo(): array
    {
        $source = base_path('logo THE DUR-E-SHAHWAR SOUQ_page-0001.jpg.jpeg');
        if (! File::exists($source)) {
            throw new \RuntimeException('Logo file not found at project root.');
        }

        $stamp = now()->format('dmYHis');
        $front = "{$stamp}-200x200-logo.png";
        $banner = "{$stamp}-1200x400-banner.jpg";
        $favicon = "{$stamp}-favicon.png";

        File::copy($source, "{$this->mediaPath}/{$front}");
        File::copy($source, "{$this->mediaPath}/{$banner}");
        File::copy($source, "{$this->mediaPath}/{$favicon}");

        return [
            'front' => $front,
            'back' => $front,
            'favicon' => $favicon,
            'banner' => $banner,
        ];
    }

    protected function updateThemeOptions(array $logo): void
    {
        $settings = [
            'general_settings' => [
                'company' => 'The Dur-e-Shahwar Souq',
                'email' => 'info@dureshawarsouq.test',
                'phone' => '+92 300 0000000',
                'site_name' => 'The Dur-e-Shahwar Souq',
                'site_title' => 'Luxury Beauty & Cosmetics Online Store',
                'address' => 'Pakistan',
                'timezone' => 'Asia/Karachi',
            ],
            'theme_logo' => [
                'favicon' => $logo['favicon'],
                'front_logo' => $logo['front'],
                'back_logo' => $logo['back'],
            ],
            'theme_color' => [
                'theme_color' => '#b87a6a',
                'green_color' => '#1b3a5c',
                'light_green_color' => '#f0e4df',
                'lightness_green_color' => '#faf7f5',
                'gray_color' => '#8d949d',
                'dark_gray_color' => '#3d4f5f',
                'light_gray_color' => '#e8e0dc',
                'black_color' => '#1b3a5c',
                'white_color' => '#ffffff',
            ],
            'theme_option_seo' => [
                'og_title' => 'The Dur-e-Shahwar Souq | Beauty & Cosmetics',
                'og_image' => $logo['banner'],
                'og_description' => 'Premium skincare, makeup, fragrance and beauty essentials.',
                'og_keywords' => 'cosmetics, beauty, skincare, makeup, perfume, Dur-e-Shahwar Souq',
                'is_publish' => 1,
            ],
            'theme_option_footer' => [
                'about_logo' => $logo['front'],
                'about_desc' => 'The Dur-e-Shahwar Souq offers curated luxury cosmetics, skincare, and beauty products for every occasion.',
                'is_publish_about' => 1,
                'address' => 'Pakistan',
                'email' => 'info@dureshawarsouq.test',
                'phone' => '+92 300 0000000',
                'is_publish_contact' => 1,
                'copyright' => '© '.date('Y').' The Dur-e-Shahwar Souq. All rights reserved.',
                'is_publish_copyright' => 1,
                'payment_gateway_icon' => '',
                'is_publish_payment' => 0,
            ],
            'theme_option_header' => [
                'address' => 'Luxury Beauty & Cosmetics',
                'phone' => '+92 300 0000000',
                'is_publish' => 1,
            ],
            'custom_css' => '',
        ];

        foreach ($settings as $name => $value) {
            $encoded = is_array($value) ? json_encode($value) : $value;
            Tp_option::updateOrCreate(
                ['option_name' => $name],
                ['option_value' => $encoded]
            );
        }
    }

    protected function clearGroceryCatalog(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('related_products')->truncate();
        DB::table('pro_images')->truncate();
        DB::table('order_items')->truncate();
        DB::table('order_masters')->truncate();
        DB::table('reviews')->truncate();
        DB::table('products')->truncate();
        DB::table('brands')->truncate();
        DB::table('pro_categories')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }

    protected function downloadProductImages(): array
    {
        $urls = [
            'https://images.unsplash.com/photo-1570199471067-af220fa69127?w=400&q=80',
            'https://images.unsplash.com/photo-1522335789203-aabd1fc54bc9?w=400&q=80',
            'https://images.unsplash.com/photo-1586495777744-4413f21062fa?w=400&q=80',
            'https://images.unsplash.com/photo-1541643600914-78b084683601?w=400&q=80',
            'https://images.unsplash.com/photo-1617897903246-369394cabca7?w=400&q=80',
            'https://images.unsplash.com/photo-1620916564538-247ba26830c6?w=400&q=80',
            'https://images.unsplash.com/photo-1527799820374-dcf8d9a73788?w=400&q=80',
            'https://images.unsplash.com/photo-1512496015851-a90fb38ba796?w=400&q=80',
            'https://images.unsplash.com/photo-1604655661848-26aec4a44e6e?w=400&q=80',
            'https://images.unsplash.com/photo-1519678047107-f827c038eaea?w=400&q=80',
            'https://images.unsplash.com/photo-1596755094514-f87e34085b2f?w=400&q=80',
            'https://images.unsplash.com/photo-1556228578-0d198b106f2e?w=400&q=80',
        ];

        $files = [];
        foreach ($urls as $i => $url) {
            $name = now()->format('dmYHis')."-cosmetic-{$i}.jpg";
            $path = "{$this->mediaPath}/{$name}";
            try {
                $data = @file_get_contents($url);
                if ($data !== false) {
                    File::put($path, $data);
                    $files[] = $name;
                }
            } catch (\Throwable $e) {
                // skip failed downloads
            }
        }

        if (empty($files)) {
            $fallback = collect(File::glob("{$this->mediaPath}/*logo*"))->first();
            if ($fallback) {
                $name = basename($fallback);
                for ($i = 0; $i < 8; $i++) {
                    $copy = now()->format('dmYHis')."-placeholder-{$i}.jpg";
                    File::copy($fallback, "{$this->mediaPath}/{$copy}");
                    $files[] = $copy;
                }
            }
        }

        return $files;
    }

    protected function seedCategories(array $images): array
    {
        $names = [
            'Skincare',
            'Makeup',
            'Fragrance',
            'Hair Care',
            'Bath & Body',
            'Nails',
            'Tools & Accessories',
        ];

        $map = [];
        foreach ($names as $i => $name) {
            $thumb = $images[$i % count($images)] ?? ($images[0] ?? '');
            $cat = Pro_category::create([
                'name' => $name,
                'slug' => Str::slug($name),
                'thumbnail' => $thumb,
                'subheader_image' => $thumb,
                'description' => "Shop premium {$name} at The Dur-e-Shahwar Souq.",
                'lan' => 'en',
                'is_publish' => 1,
                'og_title' => $name,
                'og_image' => $thumb,
                'og_description' => $name,
                'og_keywords' => $name,
            ]);
            $map[$name] = $cat->id;
        }

        return $map;
    }

    protected function seedBrands(): array
    {
        $names = [
            'L\'Oréal Paris',
            'Maybelline',
            'MAC Cosmetics',
            'Nivea',
            'The Body Shop',
            'Lakmé',
            'Huda Beauty',
            'Charlotte Tilbury',
        ];

        $map = [];
        foreach ($names as $name) {
            $brand = Brand::create([
                'name' => $name,
                'thumbnail' => '',
                'is_featured' => 1,
                'is_publish' => 1,
                'lan' => 'en',
            ]);
            $map[$name] = $brand->id;
        }

        return $map;
    }

    protected function seedProducts(array $categories, array $brands, array $images): void
    {
        $catalog = [
            ['Rose Gold Radiance Serum', 'Skincare', 'Charlotte Tilbury', 4500, 5200, 'Luxury anti-aging serum with hyaluronic acid and vitamin C.'],
            ['Velvet Matte Lipstick – Rose Nude', 'Makeup', 'MAC Cosmetics', 3200, 3800, 'Long-wear matte lipstick in an elegant rose-nude shade.'],
            ['Hydrating Face Cream SPF 30', 'Skincare', 'Nivea', 2800, null, 'Daily moisturizer with broad-spectrum sun protection.'],
            ['Eau de Parfum – Midnight Bloom', 'Fragrance', 'Huda Beauty', 8900, 9900, 'Floral oriental fragrance with notes of jasmine and sandalwood.'],
            ['Volumizing Mascara – Black', 'Makeup', 'Maybelline', 2100, null, 'Dramatic lash volume and length without clumping.'],
            ['Keratin Repair Shampoo', 'Hair Care', 'L\'Oréal Paris', 2400, 2900, 'Sulfate-free shampoo for damaged and color-treated hair.'],
            ['Shea Butter Body Lotion', 'Bath & Body', 'The Body Shop', 1900, null, 'Rich body lotion with organic shea butter.'],
            ['Gel Nail Polish Set – Blush', 'Nails', 'Lakmé', 3500, 4200, 'Set of 6 long-lasting gel-effect nail polishes.'],
            ['Professional Makeup Brush Kit', 'Tools & Accessories', 'Huda Beauty', 5500, null, '12-piece synthetic brush set for face and eyes.'],
            ['Vitamin C Brightening Toner', 'Skincare', 'The Body Shop', 3100, 3600, 'Alcohol-free toner to even skin tone and boost glow.'],
            ['Full Coverage Foundation', 'Makeup', 'Lakmé', 4200, null, 'Buildable medium-to-full coverage foundation, 12 shades.'],
            ['Rose Oud Luxury Perfume', 'Fragrance', 'Charlotte Tilbury', 12500, 14000, 'Exclusive oud and rose eau de parfum, 50ml.'],
            ['Curl Defining Hair Mask', 'Hair Care', 'L\'Oréal Paris', 3600, null, 'Deep conditioning mask for curly and wavy hair.'],
            ['Rose Gold Highlighter Palette', 'Makeup', 'Huda Beauty', 6800, 7500, 'Four-shade illuminator palette for face and body.'],
            ['Gentle Micellar Cleansing Water', 'Skincare', 'Nivea', 1700, null, 'Removes makeup and impurities without rinsing.'],
            ['Luxury Bath & Body Gift Set', 'Bath & Body', 'The Body Shop', 7200, 8500, 'Curated set with body wash, lotion, and scrub.'],
            ['BB Cream SPF 25 – Medium', 'Makeup', 'Maybelline', 2600, null, 'All-in-one beauty balm for natural coverage.'],
            ['Argan Oil Hair Serum', 'Hair Care', 'Lakmé', 2900, 3400, 'Lightweight frizz-control serum with argan oil.'],
            ['Precision Liquid Eyeliner', 'Makeup', 'MAC Cosmetics', 2800, null, 'Waterproof liquid liner with ultra-fine tip.'],
            ['Overnight Renewal Night Cream', 'Skincare', 'Charlotte Tilbury', 5900, null, 'Rich night cream with retinol and peptides.'],
        ];

        $brandKeys = array_keys($brands);
        $imgIndex = 0;

        foreach ($catalog as $i => $item) {
            [$title, $catName, $brandName, $sale, $old, $desc] = $item;
            $thumb = $images[$imgIndex % count($images)] ?? '';
            $imgIndex++;

            Product::create([
                'title' => $title,
                'slug' => Str::slug($title),
                'f_thumbnail' => $thumb,
                'short_desc' => Str::limit($desc, 120),
                'description' => "<p>{$desc}</p>",
                'cost_price' => round($sale * 0.65, 2),
                'sale_price' => $sale,
                'old_price' => $old,
                'is_discount' => $old ? 1 : 0,
                'is_stock' => 1,
                'sku' => 'DS-'.str_pad((string) ($i + 1), 4, '0', STR_PAD_LEFT),
                'stock_status_id' => 1,
                'stock_qty' => 50,
                'u_stock_qty' => '50',
                'category_ids' => (string) $categories[$catName],
                'cat_id' => $categories[$catName],
                'brand_id' => $brands[$brandName] ?? $brands[$brandKeys[0]],
                'is_featured' => $i < 8 ? 1 : 0,
                'is_publish' => 1,
                'user_id' => 1,
                'lan' => 'en',
                'og_title' => $title,
                'og_image' => $thumb,
                'og_description' => $desc,
                'og_keywords' => 'cosmetics, beauty, '.Str::slug($catName),
            ]);
        }
    }

    protected function syncHeaderMenus(): void
    {
        DB::table('menu_childs')->where('menu_parent_id', 952)->delete();
        $sort = 1;
        foreach (DB::table('brands')->where('is_publish', 1)->orderBy('name')->get() as $brand) {
            DB::table('menu_childs')->insert([
                'menu_id' => 120,
                'menu_parent_id' => 952,
                'menu_type' => 'brand',
                'item_id' => $brand->id,
                'item_label' => $brand->name,
                'custom_url' => Str::slug($brand->name),
                'target_window' => '_self',
                'lan' => 'en',
                'sort_order' => $sort++,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        DB::table('menu_childs')->where('menu_parent_id', 971)->delete();
        $megaIds = DB::table('mega_menus')->where('menu_parent_id', 971)->orderBy('id')->pluck('id');
        if ($megaIds->count() < 2) {
            return;
        }

        $categories = DB::table('pro_categories')->where('is_publish', 1)->orderBy('name')->get();
        $half = (int) ceil($categories->count() / 2);
        $sort = 1;
        foreach ($categories as $index => $cat) {
            DB::table('menu_childs')->insert([
                'menu_id' => 120,
                'menu_parent_id' => 971,
                'mega_menu_id' => $index < $half ? $megaIds[0] : $megaIds[1],
                'menu_type' => 'product_category',
                'item_id' => $cat->id,
                'item_label' => $cat->name,
                'custom_url' => $cat->slug,
                'target_window' => '_self',
                'lan' => 'en',
                'sort_order' => $sort++,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    protected function updateHeroImagery(string $fallbackImage): void
    {
        $mediaPath = $this->mediaPath;
        $stamp = now()->format('dmYHis');
        $heroFile = "{$stamp}-hero-beauty.jpg";
        $bgFile = "{$stamp}-hero-bg.jpg";

        $heroUrl = 'https://images.unsplash.com/photo-1596462502278-27bfdc403348?w=900&q=80';
        $bgUrl = 'https://images.unsplash.com/photo-1571781926291-c477ebfd024b?w=1920&q=80';

        if (@file_get_contents($heroUrl) !== false) {
            File::put("{$mediaPath}/{$heroFile}", file_get_contents($heroUrl));
        } else {
            $heroFile = $fallbackImage;
        }

        if (@file_get_contents($bgUrl) !== false) {
            File::put("{$mediaPath}/{$bgFile}", file_get_contents($bgUrl));
        } else {
            $bgFile = '18082022135936-home1-bg-slider.jpg';
        }

        $sliderMeta = json_encode([
            'sub_title' => 'The Dur-e-Shahwar Souq',
            'layer_image_1' => null,
            'layer_image_2' => null,
            'layer_image_3' => null,
            'button_text' => 'Shop Beauty',
            'target' => null,
        ]);

        DB::table('sliders')->where('slider_type', 'home_1')->update([
            'title' => 'Luxury Beauty & Cosmetics',
            'desc' => $sliderMeta,
            'image' => $heroFile,
            'url' => '/',
        ]);

        DB::table('section_manages')
            ->where('manage_type', 'home_1')
            ->where('section', 'section_1')
            ->update(['image' => $bgFile]);
    }

    protected function fixHomeMenuDropdown(): void
    {
        $homeParents = DB::table('menu_parents')
            ->where('item_label', 'Home')
            ->where('child_menu_type', 'dropdown')
            ->pluck('id');

        if ($homeParents->isNotEmpty()) {
            DB::table('menu_childs')->whereIn('menu_parent_id', $homeParents)->delete();
            DB::table('menu_parents')
                ->whereIn('id', $homeParents)
                ->update(['child_menu_type' => 'none', 'custom_url' => '/']);
        }
    }

    protected function updateHomepageContent(string $banner): void
    {
        $slides = [
            [
                'id' => 15,
                'title' => 'Luxury Beauty & Cosmetics',
                'image' => '03062026151754-cosmetic-1.jpg',
                'url' => '/',
                'sub_title' => 'The Dur-e-Shahwar Souq',
                'lead_text' => 'Discover premium skincare, makeup & fragrance for everyday luxury.',
                'button_text' => 'Shop Beauty',
            ],
            [
                'id' => 16,
                'title' => 'Radiant Skincare',
                'image' => '03062026151755-cosmetic-2.jpg',
                'url' => '/product-category/1/skincare',
                'sub_title' => 'New Collection',
                'lead_text' => 'Nourish your skin with dermatologist-loved formulas & serums.',
                'button_text' => 'Shop Skincare',
            ],
            [
                'id' => 17,
                'title' => 'Makeup That Inspires',
                'image' => '03062026151755-cosmetic-3.jpg',
                'url' => '/product-category/2/makeup',
                'sub_title' => 'Trending Now',
                'lead_text' => 'Bold lips, flawless base — express yourself with top brands.',
                'button_text' => 'Shop Makeup',
            ],
        ];

        foreach ($slides as $s) {
            $meta = json_encode([
                'sub_title' => $s['sub_title'],
                'lead_text' => $s['lead_text'],
                'layer_image_1' => null,
                'layer_image_2' => null,
                'layer_image_3' => null,
                'button_text' => $s['button_text'],
                'target' => null,
            ]);

            $data = [
                'slider_type' => 'home_1',
                'url' => $s['url'],
                'image' => $s['image'],
                'title' => $s['title'],
                'desc' => $meta,
                'is_publish' => 1,
                'updated_at' => now(),
            ];

            if (DB::table('sliders')->where('id', $s['id'])->exists()) {
                DB::table('sliders')->where('id', $s['id'])->update($data);
            } else {
                $data['id'] = $s['id'];
                $data['created_at'] = now();
                DB::table('sliders')->insert($data);
            }
        }

        DB::table('section_manages')
            ->whereIn('section', ['section_1', 'section_2', 'section_3'])
            ->update([
                'title' => 'Premium Beauty Collection',
                'desc' => 'Skincare, makeup, fragrance & more — curated for you.',
            ]);
    }

    protected function updateOffersAndVideoSection(): void
    {
        DB::table('section_manages')
            ->where('manage_type', 'home_1')
            ->where('section', 'section_4')
            ->update([
                'title' => 'Special Beauty Offers',
                'desc' => 'Limited-time cosmetics deals',
                'image' => '03062026153302-hero-bg.jpg',
            ]);

        $offers = [
            1 => [
                'text_1' => '20% Off Selected Fragrance',
                'text_2' => 'Discover iconic scents from top luxury perfume houses.',
                'bg_color' => '#f5efe6',
                'url' => '/product-category/3/fragrance',
            ],
            2 => [
                'text_1' => 'Save 25% on Luxury Makeup',
                'text_2' => 'Lipsticks, foundations & palettes — limited time only.',
                'bg_color' => '#e8f0f8',
                'url' => '/product-category/2/makeup',
            ],
            3 => [
                'text_1' => 'Up to 30% Off Skincare',
                'text_2' => 'Serums, creams & cleansers for your daily glow routine.',
                'bg_color' => '#f8e8e4',
                'url' => '/product-category/1/skincare',
            ],
        ];

        foreach ($offers as $id => $o) {
            DB::table('offer_ads')->where('id', $id)->update([
                'desc' => json_encode([
                    'bg_color' => $o['bg_color'],
                    'text_1' => $o['text_1'],
                    'text_2' => $o['text_2'],
                    'button_text' => 'Shop Now',
                    'target' => null,
                ]),
                'url' => $o['url'],
            ]);
        }

        Tp_option::updateOrCreate(
            ['option_name' => 'home-video'],
            ['option_value' => json_encode([
                'title' => 'Experience Luxury Beauty',
                'short_desc' => 'Watch how we curate premium skincare, makeup and fragrance — then explore collections made for everyday elegance.',
                'url' => '/',
                'video_url' => 'https://www.youtube.com/watch?v=lxMWSmQ6R3w',
                'button_text' => 'Shop Beauty',
                'target' => null,
                'image' => '03062026154646-slider-dark-bg.jpg',
                'is_publish' => '1',
            ])]
        );
    }
}
