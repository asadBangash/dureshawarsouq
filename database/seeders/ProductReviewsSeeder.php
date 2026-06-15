<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ProductReviewsSeeder extends Seeder
{
    protected int $reviewsPerProduct = 20;

    protected array $uaeNames = [
        'Fatima Al Maktoum', 'Aisha Hassan', 'Mariam Al Nuaimi', 'Sara Al Ketbi', 'Layla Rahman',
        'Noor Al Shamsi', 'Hessa Al Mansoori', 'Amna Siddiqui', 'Rania Al Falasi', 'Dana Al Qasimi',
        'Yasmin Karim', 'Huda Al Zaabi', 'Salma Al Owais', 'Lina Al Mazrouei', 'Noura Al Suwaidi',
        'Zahra Mohammed', 'Reem Al Dhaheri', 'Maha Al Ameri', 'Hanan Al Blooshi', 'Dina Al Kaabi',
        'Amina Farouk', 'Khadija Al Rumaithi', 'Sumaya Al Hosani', 'Rasha Al Mehairi', 'Lama Al Shehhi',
        'Nada Al Kaabi', 'Haya Al Mansouri', 'Jawaher Al Ali', 'Munira Al Shamsi', 'Shamma Al Darmaki',
        'Aysha Al Nuaimi', 'Bushra Al Ketbi', 'Camilla Al Hosani', 'Dalal Al Mazrouei', 'Eman Al Falasi',
        'Fouzia Rahman', 'Ghada Al Suwaidi', 'Hind Al Zaabi', 'Iman Al Qasimi', 'Jumana Al Dhaheri',
        'Kawthar Al Ameri', 'Lubna Al Blooshi', 'Manal Karim', 'Najla Al Owais', 'Ola Al Shehhi',
        'Pamela Al Rumaithi', 'Qamar Al Mehairi', 'Rabab Siddiqui', 'Sana Al Darmaki', 'Tala Mohammed',
        'Umm Kulthum Al Ali', 'Wafa Al Mansoori', 'Yara Al Shamsi', 'Zainab Hassan', 'Alya Al Ketbi',
        'Basma Al Nuaimi', 'Celine Al Falasi', 'Duaa Rahman', 'Elham Al Qasimi', 'Farah Al Mazrouei',
        'Ghalia Al Suwaidi', 'Hala Al Zaabi', 'Inas Al Dhaheri', 'Jana Al Ameri', 'Karma Al Blooshi',
        'Leena Karim', 'Maysa Al Owais', 'Nadia Al Shehhi', 'Omarah Al Rumaithi', 'Palwasha Siddiqui',
        'Qadira Al Mehairi', 'Raneem Al Darmaki', 'Sahar Mohammed', 'Tasneem Al Ali', 'Uzma Hassan',
        'Widad Al Mansoori', 'Yusra Al Shamsi', 'Zoya Al Ketbi', 'Afra Al Nuaimi', 'Bahar Al Falasi',
        'Chandni Rahman', 'Dima Al Qasimi', 'Esra Al Mazrouei', 'Fariha Al Suwaidi', 'Gina Al Zaabi',
        'Hafsa Al Dhaheri', 'Ibtisam Al Ameri', 'Jasmine Al Blooshi', 'Kiran Karim', 'Lulwa Al Owais',
        'Mariam Al Shehhi', 'Nour Al Rumaithi', 'Ola Siddiqui', 'Parisa Al Mehairi', 'Qistina Al Darmaki',
        'Ruqayya Mohammed', 'Saba Al Ali', 'Tahani Hassan', 'Ulfat Al Mansoori', 'Vania Al Shamsi',
        'Wijdan Al Ketbi', 'Xenia Al Nuaimi', 'Yumna Al Falasi', 'Zuhal Rahman', 'Alia Al Qasimi',
        'Bushra Al Mazrouei', 'Carla Al Suwaidi', 'Dalia Al Zaabi', 'Eman Al Dhaheri', 'Faten Al Ameri',
    ];

    protected array $uaeCities = [
        'Dubai', 'Abu Dhabi', 'Sharjah', 'Ajman', 'Al Ain', 'Ras Al Khaimah', 'Fujairah',
    ];

    protected array $commentOpeners = [
        'Mashallah,',
        'Honestly,',
        'Living in {city},',
        'Ordered to {city} —',
        'For UAE heat and humidity,',
        'Bought from Dure Shawar Souq —',
        'After trying many brands in the mall,',
        'My sisters in {city} recommended this —',
        'Perfect for daily wear in the GCC,',
        'Fast delivery across the Emirates —',
    ];

    protected array $commentBodies = [
        'this {type} blends beautifully and does not cake in air-conditioning.',
        'the coverage lasts through my long work day in {city}.',
        'SPF protection is essential here and this product delivers.',
        'shade matches my skin tone well under UAE lighting.',
        'lightweight feel — ideal for {city} summer months.',
        'stays put during souq shopping and outdoor errands.',
        'natural matte finish that photographs well for events.',
        'no heavy scent, which I prefer in this climate.',
        'a little goes a long way — good value for AED pricing.',
        'works well with my primer; smooth application every time.',
        'does not oxidise in the heat like others I have tried.',
        'gentle on skin; no breakouts in humid weather.',
        'packaging arrived intact — authentic and well sealed.',
        'I wear this from morning prayer to evening — still fresh.',
        'better than what I used to buy from Dubai Mall kiosks.',
        'my makeup artist friend in {city} approved this pick.',
        'finishes look professional for weddings and family gatherings.',
        'blends with a sponge or brush — very forgiving formula.',
        'finally found a {type} that suits combination skin here.',
        'will repurchase — already on my Ramadan gift list.',
    ];

    protected array $commentClosers = [
        'Highly recommend for UAE ladies.',
        'Five stars from Sharjah!',
        'Will order again, inshallah.',
        'Shukran to the team for quick shipping.',
        'Best purchase this season.',
        'Sharing with my cousins in Abu Dhabi.',
        'Trust this for everyday glam.',
        'A staple in my vanity now.',
        'Worth every fils.',
        'Genuine product — very happy.',
    ];

    public function run(): void
    {
        $products = Product::orderBy('id')->get();

        if ($products->isEmpty()) {
            $this->command?->warn('No products found. Add products before seeding reviews.');

            return;
        }

        $neededUsers = $products->count() * $this->reviewsPerProduct;

        DB::table('reviews')->whereIn('item_id', $products->pluck('id'))->delete();

        $reviewerIds = $this->ensureReviewerUsers($neededUsers);
        $userIndex = 0;
        $totalReviews = 0;

        foreach ($products as $product) {
            for ($i = 0; $i < $this->reviewsPerProduct; $i++) {
                $city = $this->uaeCities[array_rand($this->uaeCities)];
                $type = $this->productTypeLabel($product->title);

                $opener = str_replace('{city}', $city, $this->commentOpeners[array_rand($this->commentOpeners)]);
                $body = str_replace(
                    ['{city}', '{type}'],
                    [$city, $type],
                    $this->commentBodies[array_rand($this->commentBodies)]
                );
                $closer = $this->commentClosers[array_rand($this->commentClosers)];
                $comment = trim($opener.' '.$body.' '.$closer);

                $rating = $this->randomRating();
                $reviewDate = $this->randomReviewDate2026();

                Review::create([
                    'item_id' => $product->id,
                    'user_id' => $reviewerIds[$userIndex],
                    'rating' => $rating,
                    'comments' => $comment,
                    'created_at' => $reviewDate,
                    'updated_at' => $reviewDate,
                ]);

                $userIndex++;
                $totalReviews++;
            }
        }

        $this->command?->info("Seeded {$totalReviews} reviews ({$this->reviewsPerProduct} each) for {$products->count()} products.");
    }

    protected function ensureReviewerUsers(int $count): array
    {
        $ids = [];

        for ($i = 0; $i < $count; $i++) {
            $email = 'uae.reviewer.'.($i + 1).'@dureshawarsouq.seed';

            $existing = User::where('email', $email)->first();
            if ($existing) {
                $ids[] = $existing->id;
                continue;
            }

            $name = $this->uaeNames[$i % count($this->uaeNames)];
            if ($i >= count($this->uaeNames)) {
                $name .= ' '.($i + 1);
            }

            $city = $this->uaeCities[$i % count($this->uaeCities)];

            $ids[] = User::create([
                'name' => $name,
                'email' => $email,
                'password' => Hash::make('seeded-reviewer'),
                'phone' => '+9715'.random_int(10000000, 99999999),
                'city' => $city,
                'state' => 'UAE',
                'country_id' => 229,
                'status_id' => 1,
                'role_id' => 2,
            ])->id;
        }

        return $ids;
    }

    protected function productTypeLabel(string $title): string
    {
        $lower = strtolower($title);

        if (str_contains($lower, 'bb cream')) {
            return 'BB cream';
        }
        if (str_contains($lower, 'foundation')) {
            return 'foundation';
        }
        if (str_contains($lower, 'powder')) {
            return 'powder';
        }
        if (str_contains($lower, 'mascara')) {
            return 'mascara';
        }
        if (str_contains($lower, 'lip')) {
            return 'lip product';
        }

        return 'product';
    }

    protected function randomReviewDate2026(): \Illuminate\Support\Carbon
    {
        $month = random_int(5, 6);
        $daysInMonth = $month === 5 ? 31 : 30;
        $day = random_int(1, $daysInMonth);

        return \Illuminate\Support\Carbon::create(2026, $month, $day, random_int(8, 22), random_int(0, 59), random_int(0, 59));
    }

    protected function randomRating(): int
    {
        $weights = [1 => 2, 2 => 5, 3 => 10, 4 => 28, 5 => 55];
        $roll = random_int(1, 100);
        $cumulative = 0;

        foreach ($weights as $rating => $weight) {
            $cumulative += $weight;
            if ($roll <= $cumulative) {
                return $rating;
            }
        }

        return 5;
    }
}
