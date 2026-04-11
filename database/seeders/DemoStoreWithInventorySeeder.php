<?php

namespace Database\Seeders;

use App\Enums\BranchStatusEnum;
use App\Models\Branch;
use App\Models\Cashier;
use App\Models\Category;
use App\Models\Product;
use App\Models\Store;
use App\Models\StoreCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Auth;

class DemoStoreWithInventorySeeder extends Seeder
{
    private const string STORE_EMAIL = 'store@demo.com';

    public function run(): void
    {
        $store = Store::query()->updateOrCreate(
            ['email' => self::STORE_EMAIL],
            [
                'name' => ['en' => 'Spice Route Kitchen', 'ar' => 'مطبخ طريق التوابل'],
                'description' => [
                    'en' => 'Neighborhood grill and kitchen with grilled platters, bowls, and comfort classics for delivery.',
                    'ar' => 'مشاوي ومطبخ حي مع صحون مشوية وأطباق مريحة للتوصيل.',
                ],
                'keywords' => ['en' => ['restaurant', 'grill', 'delivery'], 'ar' => ['مطعم', 'مشاوي', 'توصيل']],
                'social_media' => ['instagram' => '@spiceroutekitchen', 'x' => '@spiceroutekitchen'],
                'phone' => '+966500000000',
                'password' => 'password',
                'is_active' => true,
            ],
        );

        $storeCategory = StoreCategory::query()->firstOrCreate(
            ['name->en' => 'Restaurants & Cafés'],
            [
                'name' => ['en' => 'Restaurants & Cafés', 'ar' => 'مطاعم ومقاهي'],
                'description' => [
                    'en' => 'Dining, takeaway, and cafe-style food.',
                    'ar' => 'طعام للمطاعم والطلب الخارجي والمقاهي.',
                ],
                'parent_id' => null,
            ],
        );

        $store->storeCategories()->syncWithoutDetaching([$storeCategory->id]);

        $branches = $this->seedBranches($store);
        $this->seedCashiers($branches);

        Auth::guard('store')->login($store);

        try {
            $this->seedCategoriesAndProducts($store, $branches);
        } finally {
            Auth::guard('store')->logout();
        }
    }

    /**
     * @return array{primary: Branch, secondary: Branch}
     */
    private function seedBranches(Store $store): array
    {
        $primary = Branch::query()->updateOrCreate(
            ['store_id' => $store->id, 'name->en' => 'Al Olaya — Flagship Dining'],
            [
                'name' => ['en' => 'Al Olaya — Flagship Dining', 'ar' => 'العلياء — المطعم الرئيسي'],
                'address' => ['en' => 'King Fahd Rd, Al Olaya, Riyadh', 'ar' => 'طريق الملك فهد، العلياء، الرياض'],
                'delivery_time_from' => 25,
                'delivery_time_to' => 45,
                'delivery_fee' => 10.00,
                'status' => BranchStatusEnum::AVAILABLE,
                'is_active' => true,
                'range_of_area_polygon' => null,
                'location' => ['latitude' => '24.7136', 'longitude' => '46.6753'],
            ],
        );

        $secondary = Branch::query()->updateOrCreate(
            ['store_id' => $store->id, 'name->en' => 'Al Malaz — Express Kitchen'],
            [
                'name' => ['en' => 'Al Malaz — Express Kitchen', 'ar' => 'الملز — مطبخ سريع'],
                'address' => ['en' => 'Al Malaz district, Riyadh', 'ar' => 'حي الملز، الرياض'],
                'delivery_time_from' => 30,
                'delivery_time_to' => 55,
                'delivery_fee' => 12.00,
                'status' => BranchStatusEnum::AVAILABLE,
                'is_active' => true,
                'range_of_area_polygon' => null,
                'location' => ['latitude' => '24.6720', 'longitude' => '46.7330'],
            ],
        );

        return ['primary' => $primary, 'secondary' => $secondary];
    }

    /**
     * @param  array{primary: Branch, secondary: Branch}  $branches
     */
    private function seedCashiers(array $branches): void
    {
        Cashier::query()->updateOrCreate(
            ['email' => 'cashier.olaya@demo.com'],
            [
                'name' => 'Demo Cashier — Al Olaya',
                'phone_number' => '+966501111111',
                'password' => 'password',
                'branch_id' => $branches['primary']->id,
            ],
        );

        Cashier::query()->updateOrCreate(
            ['email' => 'cashier.malaz@demo.com'],
            [
                'name' => 'Demo Cashier — Al Malaz',
                'phone_number' => '+966502222222',
                'password' => 'password',
                'branch_id' => $branches['secondary']->id,
            ],
        );
    }

    /**
     * @param  array{primary: Branch, secondary: Branch}  $branches
     */
    private function seedCategoriesAndProducts(Store $store, array $branches): void
    {
        $categoryDefinitions = [
            'Starters & Salads' => [
                ['Hummus & Warm Pita', 18.00, 22.00],
                ['Fattoush Salad', 22.00, 26.00],
                ['Lentil Soup', 16.00, 19.00],
                ['Crispy Calamari', 32.00, 38.00],
                ['Cheese Sambousek (4 pcs)', 24.00, 28.00],
            ],
            'Signature Mains' => [
                ['Grilled Chicken Platter', 45.00, 52.00],
                ['Lamb Kabsa (single)', 55.00, 62.00],
                ['Butter Chicken with Rice', 42.00, 48.00],
                ['Mixed Grill for Two', 120.00, 135.00],
                ['Sea Bass Sayadiyah', 68.00, 78.00],
            ],
            'Burgers & Sandwiches' => [
                ['Classic Beef Burger', 35.00, 40.00],
                ['Spicy Chicken Burger', 32.00, 37.00],
                ['Mushroom Swiss Burger', 38.00, 43.00],
                ['Falafel Wrap', 22.00, 26.00],
                ['Kids Cheeseburger Meal', 28.00, 32.00],
            ],
            'Sides & Add-ons' => [
                ['Truffle Parmesan Fries', 22.00, 26.00],
                ['Garlic Naan (2 pcs)', 12.00, 14.00],
                ['Seasoned Rice', 15.00, 18.00],
                ['Grilled Vegetables', 20.00, 24.00],
                ['Tahini Sauce Tub', 8.00, 10.00],
            ],
            'Drinks & Desserts' => [
                ['Fresh Lemon Mint', 14.00, 17.00],
                ['Jallab', 16.00, 19.00],
                ['Karak Chai', 10.00, 12.00],
                ['Kunafa Slice', 24.00, 28.00],
                ['Vanilla Ice Cream Sundae', 18.00, 22.00],
            ],
        ];

        foreach ($categoryDefinitions as $categoryNameEn => $products) {
            $category = Category::query()->updateOrCreate(
                ['store_id' => $store->id, 'name->en' => $categoryNameEn],
                [
                    'name' => ['en' => $categoryNameEn, 'ar' => $this->toArabicCategoryName($categoryNameEn)],
                    'description' => [
                        'en' => "Explore {$categoryNameEn} from our kitchen.",
                        'ar' => 'اكتشف أطباقنا من مطبخنا.',
                    ],
                    'is_active' => true,
                ],
            );

            foreach ($products as [$productNameEn, $price, $comparePrice]) {
                $product = Product::query()->updateOrCreate(
                    [
                        'store_id' => $store->id,
                        'category_id' => $category->id,
                        'name->en' => $productNameEn,
                    ],
                    [
                        'name' => ['en' => $productNameEn, 'ar' => $this->toArabicProductName($productNameEn)],
                        'description' => [
                            'en' => 'Prepared to order and packed hot for delivery.',
                            'ar' => 'يُحضر عند الطلب ويُعبأ ساخناً للتوصيل.',
                        ],
                        'keywords' => [
                            'en' => [$categoryNameEn, 'delivery', 'spice route kitchen'],
                            'ar' => ['مطعم', 'مطبخ طريق التوابل'],
                        ],
                        'price' => $price,
                        'compare_price' => $comparePrice,
                        'is_active' => true,
                        'is_accepted' => true,
                    ],
                );

                // $product->branches()->syncWithoutDetaching([
                //     $branches['primary']->id => [
                //         'price' => $price,
                //         'compare_price' => $comparePrice,
                //         'is_available' => true,
                //         'quantity' => 50,
                //     ],
                //     $branches['secondary']->id => [
                //         'price' => max($price - 0.50, 1.00),
                //         'compare_price' => $comparePrice,
                //         'is_available' => true,
                //         'quantity' => 30,
                //     ],
                // ]);
            }
        }
    }

    private function toArabicCategoryName(string $categoryNameEn): string
    {
        return match ($categoryNameEn) {
            'Starters & Salads' => 'مقبلات وسلطات',
            'Signature Mains' => 'أطباق رئيسية مميزة',
            'Burgers & Sandwiches' => 'برجر وساندويش',
            'Sides & Add-ons' => 'إضافات وجوانب',
            'Drinks & Desserts' => 'مشروبات وحلويات',
            default => $categoryNameEn,
        };
    }

    private function toArabicProductName(string $productNameEn): string
    {
        return match ($productNameEn) {
            'Hummus & Warm Pita' => 'حمص وخبز بيتا دافئ',
            'Fattoush Salad' => 'سلطة فتوش',
            'Lentil Soup' => 'شوربة عدس',
            'Crispy Calamari' => 'كاليماري مقرمش',
            'Cheese Sambousek (4 pcs)' => 'سمبوسة جبن (4 قطع)',
            'Grilled Chicken Platter' => 'صحن دجاج مشوي',
            'Lamb Kabsa (single)' => 'كبسة لحم (وجبة فردية)',
            'Butter Chicken with Rice' => 'دجاج بالزبدة مع أرز',
            'Mixed Grill for Two' => 'مشاوي مشكلة لشخصين',
            'Sea Bass Sayadiyah' => 'قاروص سيدية',
            'Classic Beef Burger' => 'برجر لحم كلاسيكي',
            'Spicy Chicken Burger' => 'برجر دجاج حار',
            'Mushroom Swiss Burger' => 'برجر فطر وجبن سويسري',
            'Falafel Wrap' => 'راب فلافل',
            'Kids Cheeseburger Meal' => 'وجبة برجر جبن للأطفال',
            'Truffle Parmesan Fries' => 'بطاطس بارميزان بالكمأة',
            'Garlic Naan (2 pcs)' => 'خبز نان بالثوم (قطعتان)',
            'Seasoned Rice' => 'أرز بالتوابل',
            'Grilled Vegetables' => 'خضار مشوية',
            'Tahini Sauce Tub' => 'علبة صلصة طحينة',
            'Fresh Lemon Mint' => 'ليمون ونعناع طازج',
            'Jallab' => 'جلاب',
            'Karak Chai' => 'شاي كرك',
            'Kunafa Slice' => 'قطعة كنافة',
            'Vanilla Ice Cream Sundae' => 'آيس كريم فانيليا سنداي',
            default => $productNameEn,
        };
    }
}
