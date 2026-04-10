<?php

namespace Database\Seeders;

use App\Enums\BranchStatusEnum;
use App\Models\Branch;
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
                'name' => ['en' => 'Green Basket Market', 'ar' => 'سوق السلة الخضراء'],
                'description' => [
                    'en' => 'Neighborhood grocery with fresh produce, bakery, and daily essentials.',
                    'ar' => 'بقالة الحي مع خضار طازجة ومخبوزات واحتياجات يومية.',
                ],
                'keywords' => ['en' => ['grocery', 'fresh', 'bakery'], 'ar' => ['بقالة', 'طازج', 'مخبوزات']],
                'social_media' => ['instagram' => '@greenbasket', 'x' => '@greenbasket'],
                'phone' => '+966500000000',
                'password' => 'password',
                'is_active' => true,
            ],
        );

        $storeCategory = StoreCategory::query()->firstOrCreate(
            ['name->en' => 'Groceries & Supermarket'],
            [
                'name' => ['en' => 'Groceries & Supermarket', 'ar' => 'بقالة وسوبرماركت'],
                'description' => [
                    'en' => 'Food, daily essentials, and household supplies.',
                    'ar' => 'مواد غذائية واحتياجات يومية ومستلزمات منزلية.',
                ],
                'parent_id' => null,
            ],
        );

        $store->storeCategories()->syncWithoutDetaching([$storeCategory->id]);

        $branches = $this->seedBranches($store);

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
            ['store_id' => $store->id, 'name->en' => 'Al Olaya — Main Branch'],
            [
                'name' => ['en' => 'Al Olaya — Main Branch', 'ar' => 'العلياء — الفرع الرئيسي'],
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
            ['store_id' => $store->id, 'name->en' => 'Al Malaz — City Branch'],
            [
                'name' => ['en' => 'Al Malaz — City Branch', 'ar' => 'الملز — فرع المدينة'],
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
    private function seedCategoriesAndProducts(Store $store, array $branches): void
    {
        $categoryDefinitions = [
            'Fresh Produce' => [
                ['Bananas (1 kg)', 8.50, 10.00],
                ['Tomatoes (1 kg)', 7.00, 8.50],
                ['Cucumbers (1 kg)', 6.50, 7.50],
                ['Baby Spinach (250 g)', 9.75, 12.00],
                ['Mixed Salad Box', 11.50, 13.50],
            ],
            'Dairy & Eggs' => [
                ['Fresh Milk (1 L)', 6.25, 7.00],
                ['Greek Yogurt (500 g)', 12.50, 14.00],
                ['Cheddar Cheese Slices', 15.00, 18.00],
                ['Free-range Eggs (12 pcs)', 14.75, 16.50],
                ['Butter (200 g)', 9.95, 11.50],
            ],
            'Bakery' => [
                ['Sourdough Bread', 14.00, 16.00],
                ['Whole Wheat Toast', 11.50, 13.00],
                ['Croissants (4 pcs)', 18.00, 20.00],
                ['Chocolate Muffins (4 pcs)', 17.50, 19.50],
                ['Mini Kunafa Bites', 22.00, 25.00],
            ],
            'Beverages' => [
                ['Still Water (12 x 330 ml)', 18.00, 20.00],
                ['Sparkling Water (6 x 250 ml)', 16.50, 19.00],
                ['Cold Brew Coffee (250 ml)', 13.00, 15.00],
                ['Orange Juice (1 L)', 12.25, 14.00],
                ['Mint Lemonade (500 ml)', 10.50, 12.00],
            ],
            'Snacks' => [
                ['Sea Salt Potato Chips', 7.50, 9.00],
                ['Roasted Almonds (250 g)', 22.00, 25.00],
                ['Dark Chocolate Bar (70%)', 9.50, 11.00],
                ['Granola Bars (6 pcs)', 14.00, 16.00],
                ['Mixed Nuts Cup', 8.75, 10.00],
            ],
        ];

        foreach ($categoryDefinitions as $categoryNameEn => $products) {
            $category = Category::query()->updateOrCreate(
                ['store_id' => $store->id, 'name->en' => $categoryNameEn],
                [
                    'name' => ['en' => $categoryNameEn, 'ar' => $this->toArabicCategoryName($categoryNameEn)],
                    'description' => [
                        'en' => "Browse {$categoryNameEn} from our daily selection.",
                        'ar' => 'تسوق من تشكيلتنا اليومية.',
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
                            'en' => 'Freshly stocked and packed for delivery.',
                            'ar' => 'متوفر يومياً ومجهز للتوصيل.',
                        ],
                        'keywords' => [
                            'en' => [$categoryNameEn, 'daily essentials', 'green basket'],
                            'ar' => ['احتياجات يومية', 'السلة الخضراء'],
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
            'Fresh Produce' => 'خضار وفواكه',
            'Dairy & Eggs' => 'ألبان وبيض',
            'Bakery' => 'مخبوزات',
            'Beverages' => 'مشروبات',
            'Snacks' => 'وجبات خفيفة',
            default => $categoryNameEn,
        };
    }

    private function toArabicProductName(string $productNameEn): string
    {
        return match ($productNameEn) {
            'Bananas (1 kg)' => 'موز (1 كجم)',
            'Tomatoes (1 kg)' => 'طماطم (1 كجم)',
            'Cucumbers (1 kg)' => 'خيار (1 كجم)',
            'Baby Spinach (250 g)' => 'سبانخ صغيرة (250 جم)',
            'Mixed Salad Box' => 'علبة سلطة مشكلة',
            'Fresh Milk (1 L)' => 'حليب طازج (1 لتر)',
            'Greek Yogurt (500 g)' => 'زبادي يوناني (500 جم)',
            'Cheddar Cheese Slices' => 'شرائح جبن شيدر',
            'Free-range Eggs (12 pcs)' => 'بيض بلدي (12 حبة)',
            'Butter (200 g)' => 'زبدة (200 جم)',
            'Sourdough Bread' => 'خبز ساوردو',
            'Whole Wheat Toast' => 'توست قمح كامل',
            'Croissants (4 pcs)' => 'كرواسون (4 قطع)',
            'Chocolate Muffins (4 pcs)' => 'مافن شوكولاتة (4 قطع)',
            'Mini Kunafa Bites' => 'لقيمات كنافة صغيرة',
            'Still Water (12 x 330 ml)' => 'مياه (12 × 330 مل)',
            'Sparkling Water (6 x 250 ml)' => 'مياه غازية (6 × 250 مل)',
            'Cold Brew Coffee (250 ml)' => 'قهوة كولد برو (250 مل)',
            'Orange Juice (1 L)' => 'عصير برتقال (1 لتر)',
            'Mint Lemonade (500 ml)' => 'ليمون بالنعناع (500 مل)',
            'Sea Salt Potato Chips' => 'شيبس ملح بحري',
            'Roasted Almonds (250 g)' => 'لوز محمص (250 جم)',
            'Dark Chocolate Bar (70%)' => 'شوكولاتة داكنة (70%)',
            'Granola Bars (6 pcs)' => 'ألواح جرانولا (6 قطع)',
            'Mixed Nuts Cup' => 'كوب مكسرات مشكلة',
            default => $productNameEn,
        };
    }
}
