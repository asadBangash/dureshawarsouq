<?php

namespace Database\Seeders;

use App\Models\Pro_category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;

class CosmeticsCategoriesSeeder extends Seeder
{
    protected string $excelPath;

    /** Sheet name => parent category label */
    protected array $sheetCategoryMap = [
        'AL MASCARA' => 'Mascara',
        'FOUNDATION ' => 'Foundation',
        'POWDER ' => 'Powder',
        'LIPSTIC ' => 'Lip Products',
        'LIPS TIC ' => 'Lip Products',
    ];

    protected array $parentOrder = [
        'Mascara',
        'Foundation',
        'Powder',
        'Lip Products',
    ];

    public function run(): void
    {
        $this->excelPath = base_path('docs/COSMETICS FILE.xlsx');

        if (! is_file($this->excelPath)) {
            throw new \RuntimeException('Excel file not found: '.$this->excelPath);
        }

        $this->clearDummyCatalog();
        $categoryMap = $this->seedCategoriesFromExcel();
        $this->syncShopMegaMenu();

        $this->command?->info('Cosmetics categories seeded from Excel. Products and brands cleared.');
        $this->command?->info('Run your product import next — categories are ready.');
    }

    protected function clearDummyCatalog(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('related_products')->truncate();
        DB::table('pro_images')->truncate();
        DB::table('products')->truncate();
        DB::table('brands')->truncate();
        DB::table('pro_categories')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }

    /**
     * @return array<string, int> slug => category id
     */
    protected function seedCategoriesFromExcel(): array
    {
        $spreadsheet = IOFactory::load($this->excelPath);
        $subcategoryNames = $this->extractSubcategoryNames($spreadsheet);
        $map = [];
        $sort = 1;

        foreach ($this->parentOrder as $parentName) {
            $parent = $this->createCategory($parentName, null, $sort++);
            $map[$parent->slug] = $parent->id;

            if ($parentName === 'Lip Products') {
                foreach ($subcategoryNames as $subName) {
                    $child = $this->createCategory($subName, $parent->id, $sort++);
                    $map[$child->slug] = $child->id;
                }
            }
        }

        return $map;
    }

    protected function extractSubcategoryNames(\PhpOffice\PhpSpreadsheet\Spreadsheet $spreadsheet): array
    {
        $subs = [];

        foreach ($spreadsheet->getSheetNames() as $sheetName) {
            if (($this->sheetCategoryMap[$sheetName] ?? null) !== 'Lip Products') {
                continue;
            }

            $sheet = $spreadsheet->getSheetByName($sheetName);
            $rows = $sheet->toArray();
            $headers = $this->normalizeHeaders($rows[0] ?? []);
            $categoryIndex = array_search('Category', $headers, true);

            if ($categoryIndex === false) {
                continue;
            }

            for ($i = 1, $count = count($rows); $i < $count; $i++) {
                $row = $rows[$i];
                if (! $this->rowHasData($row)) {
                    continue;
                }

                $value = trim((string) ($row[$categoryIndex] ?? ''));
                if ($value !== '') {
                    $subs[$value] = true;
                }
            }
        }

        $names = array_keys($subs);
        sort($names, SORT_NATURAL | SORT_FLAG_CASE);

        return $names;
    }

    protected function createCategory(string $name, ?int $parentId, int $sortOrder): Pro_category
    {
        $slug = $this->uniqueSlug($name, $parentId);

        return Pro_category::create([
            'name' => $name,
            'slug' => $slug,
            'thumbnail' => '',
            'subheader_image' => '',
            'description' => "Browse {$name} from The Dur-e-Shahwar Souq.",
            'lan' => 'en',
            'parent_id' => $parentId,
            'is_subheader' => 0,
            'is_publish' => 1,
            'og_title' => $name,
            'og_image' => '',
            'og_description' => $name,
            'og_keywords' => $name,
        ]);
    }

    protected function uniqueSlug(string $name, ?int $parentId): string
    {
        if ($parentId) {
            $parent = Pro_category::find($parentId);
            $base = $parent
                ? Str::slug($parent->name.'-'.$name)
                : Str::slug($name);
        } else {
            $base = Str::slug($name);
        }

        $slug = $base;
        $suffix = 1;

        while (Pro_category::where('slug', $slug)->exists()) {
            $slug = $base.'-'.$suffix++;
        }

        return $slug;
    }

    protected function normalizeHeaders(array $headers): array
    {
        return array_map(static fn ($header) => trim((string) $header), $headers);
    }

    protected function rowHasData(array $row): bool
    {
        foreach ($row as $cell) {
            if ($cell !== null && trim((string) $cell) !== '') {
                return true;
            }
        }

        return false;
    }

    /** Sync Shop nav item as a two-column mega menu (Categories + More). */
    public function syncShopMegaMenu(): void
    {
        $menu = DB::table('menus')
            ->where('menu_position', 'header')
            ->where('lan', 'en')
            ->first();

        if (! $menu) {
            return;
        }

        $menuId = (int) $menu->id;

        $shopMenu = DB::table('menu_parents')
            ->where('menu_id', $menuId)
            ->where(function ($query) {
                $query->where('item_label', 'Shop')
                    ->orWhere('item_label', 'Products');
            })
            ->first();

        if (! $shopMenu) {
            return;
        }

        $menuParentId = (int) $shopMenu->id;
        $now = now();

        DB::table('menu_parents')
            ->where('id', $menuParentId)
            ->update([
                'item_label' => 'Shop',
                'child_menu_type' => 'mega_menu',
                'custom_url' => '#',
                'column' => 2,
                'width_type' => 'fixed_width',
                'width' => 550,
                'updated_at' => $now,
            ]);

        DB::table('menu_childs')->where('menu_parent_id', $menuParentId)->delete();
        DB::table('mega_menus')->where('menu_parent_id', $menuParentId)->delete();

        $megaCategoriesId = DB::table('mega_menus')->insertGetId([
            'menu_id' => $menuId,
            'menu_parent_id' => $menuParentId,
            'mega_menu_title' => 'Categories',
            'is_title' => 1,
            'is_image' => 0,
            'lan' => 'en',
            'sort_order' => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $megaMoreId = DB::table('mega_menus')->insertGetId([
            'menu_id' => $menuId,
            'menu_parent_id' => $menuParentId,
            'mega_menu_title' => 'More',
            'is_title' => 1,
            'is_image' => 0,
            'lan' => 'en',
            'sort_order' => 2,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $parents = Pro_category::where('lan', 'en')
            ->where('is_publish', 1)
            ->where(function ($query) {
                $query->whereNull('parent_id')->orWhere('parent_id', 0);
            })
            ->get()
            ->sortBy(function ($category) {
                $index = array_search($category->name, $this->parentOrder, true);

                return $index === false ? 999 : $index;
            })
            ->values();

        $sort = 1;
        foreach ($parents as $parent) {
            $this->insertMenuCategory($menuId, $menuParentId, $parent, $sort++, $megaCategoriesId);
        }

        $lipParent = $parents->firstWhere('name', 'Lip Products');
        if ($lipParent) {
            $children = Pro_category::where('lan', 'en')
                ->where('is_publish', 1)
                ->where('parent_id', $lipParent->id)
                ->orderBy('name')
                ->get();

            foreach ($children as $child) {
                $this->insertMenuCategory($menuId, $menuParentId, $child, $sort++, $megaMoreId);
            }
        }

        DB::table('menu_childs')->where('menu_parent_id', 952)->delete();
    }

    protected function insertMenuCategory(int $menuId, int $menuParentId, Pro_category $category, int $sort, ?int $megaMenuId = null): void
    {
        DB::table('menu_childs')->insert([
            'menu_id' => $menuId,
            'menu_parent_id' => $menuParentId,
            'mega_menu_id' => $megaMenuId,
            'menu_type' => 'product_category',
            'item_id' => $category->id,
            'item_label' => $category->name,
            'custom_url' => $category->slug,
            'target_window' => '_self',
            'lan' => 'en',
            'sort_order' => $sort,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
