<?php

namespace Database\Seeders;

use App\Enums\BranchStatusEnum;
use App\Models\Addition;
use App\Models\Branch;
use App\Models\Category;
use App\Models\Option;
use App\Models\OptionGroup;
use App\Models\Product;
use App\Models\Store;
use App\Models\StoreCategory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Auth;

/**
 * Seeds five top-level store categories (each with five subcategories), one branded store per category,
 * area branches, subcategory branches, and a full sample menu (options/additions) on one branch.
 */
class StoreCategoriesWithBranchesSeeder extends Seeder
{
    private const string RESTAURANT_STORE_EMAIL = 'orders@baytnagrill.sa';

    /**
     * @return array{en: string, ar: string}
     */
    private function t(string $en, string $ar): array
    {
        return ['en' => $en, 'ar' => $ar];
    }

    public function run(): void
    {
        $trees = $this->categoryTreesWithBrands();

        $branchPlaces = [
            [
                'suffix_en' => 'Al Olaya',
                'suffix_ar' => 'العلياء',
                'lat' => '24.7136',
                'lng' => '46.6753',
            ],
            [
                'suffix_en' => 'Al Malaz',
                'suffix_ar' => 'الملز',
                'lat' => '24.6720',
                'lng' => '46.7330',
            ],
            [
                'suffix_en' => 'Al Nakheel',
                'suffix_ar' => 'النخيل',
                'lat' => '24.8020',
                'lng' => '46.6410',
            ],
            [
                'suffix_en' => 'Al Sahafa',
                'suffix_ar' => 'الصحافة',
                'lat' => '24.8310',
                'lng' => '46.6290',
            ],
            [
                'suffix_en' => 'Al Yasmin',
                'suffix_ar' => 'الياسمين',
                'lat' => '24.7890',
                'lng' => '46.6120',
            ],
        ];

        foreach ($trees as $index => $tree) {
            $brand = $tree['brand'];
            $parent = StoreCategory::query()->firstOrCreate(
                [
                    'parent_id' => null,
                    'name->en' => $tree['name']['en'],
                ],
                [
                    'name' => $tree['name'],
                    'description' => $tree['description'],
                    'parent_id' => null,
                ],
            );

            $childModels = [];
            foreach ($tree['children'] as $childName) {
                $childModels[] = StoreCategory::query()->firstOrCreate(
                    [
                        'parent_id' => $parent->id,
                        'name->en' => $childName['en'],
                    ],
                    [
                        'name' => $childName,
                        'description' => $this->t(
                            'Merchants and menus centered on '.$childName['en'].'.',
                            'متاجر وقوائم تركز على '.$childName['ar'].'.',
                        ),
                    ],
                );
            }

            $store = Store::query()->updateOrCreate(
                ['email' => $brand['email']],
                [
                    'name' => $brand['name'],
                    'description' => $brand['description'],
                    'keywords' => $brand['keywords'],
                    'social_media' => $brand['social_media'],
                    'phone' => $brand['phone'],
                    'password' => 'password',
                    'is_active' => true,
                ],
            );

            $store->storeCategories()->syncWithoutDetaching(
                array_merge([$parent->id], array_map(fn (StoreCategory $c) => $c->id, $childModels)),
            );

            $brandEn = $brand['name']['en'];

            foreach ($branchPlaces as $placeIndex => $place) {
                Branch::query()->updateOrCreate(
                    [
                        'store_id' => $store->id,
                        'name->en' => $brandEn.' — '.$place['suffix_en'],
                    ],
                    [
                        'name' => $this->t(
                            $brandEn.' — '.$place['suffix_en'],
                            $brand['name']['ar'].' — '.$place['suffix_ar'],
                        ),
                        'address' => $this->t(
                            $place['suffix_en'].', Riyadh',
                            $place['suffix_ar'].'، الرياض',
                        ),
                        'delivery_time_from' => 20 + ($placeIndex * 2),
                        'delivery_time_to' => 40 + ($placeIndex * 3),
                        'delivery_fee' => 8.00 + $placeIndex,
                        'status' => BranchStatusEnum::AVAILABLE,
                        'is_active' => true,
                        'range_of_area_polygon' => null,
                        'location' => [
                            'latitude' => $place['lat'],
                            'longitude' => $place['lng'],
                        ],
                    ],
                );
            }

            foreach ($childModels as $childIndex => $childCategory) {
                $place = $branchPlaces[$childIndex];
                $childEn = $tree['children'][$childIndex]['en'];
                Branch::query()->updateOrCreate(
                    [
                        'store_id' => $store->id,
                        'name->en' => $brandEn.' · '.$childEn.' — '.$place['suffix_en'],
                    ],
                    [
                        'name' => $this->t(
                            $brandEn.' · '.$childEn.' — '.$place['suffix_en'],
                            $brand['name']['ar'].' · '.$tree['children'][$childIndex]['ar'].' — '.$place['suffix_ar'],
                        ),
                        'address' => $this->t(
                            $childEn.' — '.$place['suffix_en'].', Riyadh',
                            $tree['children'][$childIndex]['ar'].' — '.$place['suffix_ar'].'، الرياض',
                        ),
                        'delivery_time_from' => 18 + $childIndex,
                        'delivery_time_to' => 38 + ($childIndex * 2),
                        'delivery_fee' => 7.50 + ($childIndex * 0.5),
                        'status' => BranchStatusEnum::AVAILABLE,
                        'is_active' => true,
                        'range_of_area_polygon' => null,
                        'location' => [
                            'latitude' => $place['lat'],
                            'longitude' => $place['lng'],
                        ],
                    ],
                );
            }
        }

        $restaurantsStore = Store::query()->where('email', self::RESTAURANT_STORE_EMAIL)->firstOrFail();

        $burgersBranchEn = $trees[0]['brand']['name']['en']
            .' · '.$trees[0]['children'][0]['en']
            .' — '.$branchPlaces[0]['suffix_en'];

        $burgersBranch = Branch::query()
            ->where('store_id', $restaurantsStore->id)
            ->where('name->en', $burgersBranchEn)
            ->firstOrFail();

        $this->seedBaytnaGrillMenu($restaurantsStore, $burgersBranch);
    }

    /**
     * @return list<array{
     *     name: array{en: string, ar: string},
     *     description: array{en: string, ar: string},
     *     brand: array{
     *         name: array{en: string, ar: string},
     *         description: array{en: string, ar: string},
     *         email: string,
     *         keywords: array{en: list<string>, ar: list<string>},
     *         social_media: array<string, string>,
     *         phone: string
     *     },
     *     children: list<array{en: string, ar: string}>
     * }>
     */
    private function categoryTreesWithBrands(): array
    {
        return [
            [
                'name' => $this->t('Restaurants', 'مطاعم'),
                'description' => $this->t(
                    'Dining, delivery, and takeaway food.',
                    'طعام للجلوس والتوصيل والطلب الخارجي.',
                ),
                'brand' => [
                    'name' => $this->t('Baytna Grill', 'مشاوي بيتنا'),
                    'description' => $this->t(
                        'Char-grilled mains, burgers, and sides—made to order and delivered across Riyadh.',
                        'مشاوي وأطباق رئيسية وبرجر يُحضّر عند الطلب مع توصيل في الرياض.',
                    ),
                    'email' => self::RESTAURANT_STORE_EMAIL,
                    'keywords' => [
                        'en' => ['grill', 'burgers', 'delivery', 'riyadh', 'fried chicken'],
                        'ar' => ['مشاوي', 'برجر', 'توصيل', 'الرياض'],
                    ],
                    'social_media' => [
                        'instagram' => '@baytnagrill',
                        'x' => '@baytnagrill',
                    ],
                    'phone' => '+966501100101',
                ],
                'children' => [
                    $this->t('Burgers', 'برجر'),
                    $this->t('Pizza', 'بيتزا'),
                    $this->t('Desserts', 'حلويات'),
                    $this->t('Seafood', 'مأكولات بحرية'),
                    $this->t('Shawarma & grills', 'شاورما ومشاوي'),
                ],
            ],
            [
                'name' => $this->t('Groceries & markets', 'بقالة وأسواق'),
                'description' => $this->t(
                    'Supermarkets and everyday groceries.',
                    'سوبرماركت ومستلزمات يومية.',
                ),
                'brand' => [
                    'name' => $this->t('Green Basket Market', 'سلة خضراء'),
                    'description' => $this->t(
                        'Fresh produce, pantry staples, and household essentials from local and imported lines.',
                        'خضار وفواكه ومؤونة ومنتجات منزلية من مصادر محلية ومستوردة.',
                    ),
                    'email' => 'shop@greenbasket.market',
                    'keywords' => [
                        'en' => ['grocery', 'organic', 'fresh', 'supermarket', 'pantry'],
                        'ar' => ['بقالة', 'طازج', 'سوبرماركت'],
                    ],
                    'social_media' => [
                        'instagram' => '@greenbasketksa',
                    ],
                    'phone' => '+966501100102',
                ],
                'children' => [
                    $this->t('Fresh produce', 'خضار وفواكه'),
                    $this->t('Meat & poultry', 'لحوم ودواجن'),
                    $this->t('Dairy & eggs', 'ألبان وبيض'),
                    $this->t('Bakery', 'مخبوزات'),
                    $this->t('Snacks & pantry', 'وجبات خفيفة ومؤونة'),
                ],
            ],
            [
                'name' => $this->t('Pharmacy & wellness', 'صيدلية وعناية'),
                'description' => $this->t(
                    'Health, pharmacy, and personal care.',
                    'صحة وصيدلية وعناية شخصية.',
                ),
                'brand' => [
                    'name' => $this->t('Al-Nour Care Pharmacy', 'صيدلية النور للعناية'),
                    'description' => $this->t(
                        'Licensed pharmacy with vitamins, skincare, baby care, and over-the-counter guidance.',
                        'صيدلية مرخّصة وفيتامينات وعناية بالبشرة والأطفال وأدوية بدون وصف.',
                    ),
                    'email' => 'care@alnour-care.sa',
                    'keywords' => [
                        'en' => ['pharmacy', 'vitamins', 'skincare', 'baby', 'OTC'],
                        'ar' => ['صيدلية', 'فيتامينات', 'عناية'],
                    ],
                    'social_media' => [
                        'instagram' => '@alnourcare_sa',
                    ],
                    'phone' => '+966501100103',
                ],
                'children' => [
                    $this->t('Vitamins & supplements', 'فيتامينات ومكملات'),
                    $this->t('Skincare & beauty', 'عناية بالبشرة وجمال'),
                    $this->t('OTC & first aid', 'أدوية بدون وصف وإسعافات أولية'),
                    $this->t('Baby care', 'عناية بالأطفال'),
                    $this->t('Personal hygiene', 'نظافة شخصية'),
                ],
            ],
            [
                'name' => $this->t('Electronics & tech', 'إلكترونيات وتقنية'),
                'description' => $this->t(
                    'Devices, accessories, and gadgets.',
                    'أجهزة وإكسسوارات وأدوات ذكية.',
                ),
                'brand' => [
                    'name' => $this->t('Circuit Avenue', 'دائرة التقنية'),
                    'description' => $this->t(
                        'Phones, laptops, audio, gaming gear, and smart-home accessories with warranty-backed sourcing.',
                        'هواتف وحواسيب وصوتيات وألعاب ومنزل ذكي مع ضمان ومصادر موثوقة.',
                    ),
                    'email' => 'sales@circuitavenue.sa',
                    'keywords' => [
                        'en' => ['electronics', 'phones', 'laptops', 'gaming', 'audio'],
                        'ar' => ['إلكترونيات', 'هواتف', 'ألعاب'],
                    ],
                    'social_media' => [
                        'instagram' => '@circuitavenue_sa',
                        'x' => '@circuitavenue',
                    ],
                    'phone' => '+966501100104',
                ],
                'children' => [
                    $this->t('Phones & tablets', 'هواتف وأجهزة لوحية'),
                    $this->t('Computers & laptops', 'حواسيب ومحمول'),
                    $this->t('TV & audio', 'تلفزيون وصوتيات'),
                    $this->t('Gaming', 'ألعاب'),
                    $this->t('Smart home & accessories', 'منزل ذكي وإكسسوارات'),
                ],
            ],
            [
                'name' => $this->t('Coffee & cafés', 'قهوة ومقاهي'),
                'description' => $this->t(
                    'Cafés, coffee shops, and light bites.',
                    'مقاهي وقهوة ووجبات خفيفة.',
                ),
                'brand' => [
                    'name' => $this->t('Qahwa House', 'بيت قهوة'),
                    'description' => $this->t(
                        'Specialty espresso, cold brew, pastries, and light meals for dine-in and takeaway.',
                        'إسبريسو مختص وقهوة باردة ومعجنات ووجبات خفيفة للمحل والطلب.',
                    ),
                    'email' => 'hello@qawahouse.sa',
                    'keywords' => [
                        'en' => ['coffee', 'espresso', 'café', 'pastries', 'cold brew'],
                        'ar' => ['قهوة', 'إسبريسو', 'مقهى'],
                    ],
                    'social_media' => [
                        'instagram' => '@qawahouse_sa',
                    ],
                    'phone' => '+966501100105',
                ],
                'children' => [
                    $this->t('Espresso & specialty coffee', 'إسبريسو وقهوة مختصة'),
                    $this->t('Pastries & desserts', 'معجنات وحلويات'),
                    $this->t('Cold drinks & juice', 'مشروبات باردة وعصائر'),
                    $this->t('Breakfast & sandwiches', 'فطور وساندويشات'),
                    $this->t('Tea & herbal drinks', 'شاي ومشروبات أعشاب'),
                ],
            ],
        ];
    }

    private function seedBaytnaGrillMenu(Store $store, Branch $branch): void
    {
        Auth::guard('store')->login($store);

        try {
            $catBurgers = Category::query()->updateOrCreate(
                ['store_id' => $store->id, 'name->en' => 'Burgers'],
                [
                    'name' => $this->t('Burgers', 'برجر'),
                    'description' => $this->t('Beef, chicken, and house specials.', 'لحم ودجاج وأطباق المطعم.'),
                    'is_active' => true,
                ],
            );

            $catSides = Category::query()->updateOrCreate(
                ['store_id' => $store->id, 'name->en' => 'Sides & fries'],
                [
                    'name' => $this->t('Sides & fries', 'بطاطس وإضافات'),
                    'description' => $this->t('Sides to round out your order.', 'أطباق جانبية تكمل طلبك.'),
                    'is_active' => true,
                ],
            );

            $catDrinks = Category::query()->updateOrCreate(
                ['store_id' => $store->id, 'name->en' => 'Drinks'],
                [
                    'name' => $this->t('Drinks', 'مشروبات'),
                    'description' => $this->t('Soft drinks and chilled beverages.', 'مشروبات غازية وباردة.'),
                    'is_active' => true,
                ],
            );

            $groupDoneness = OptionGroup::query()->updateOrCreate(
                [
                    'store_id' => $store->id,
                    'name->en' => 'Cooking preference',
                ],
                [
                    'name' => $this->t('Cooking preference', 'درجة التسوية'),
                ],
            );

            $optMedium = Option::query()->updateOrCreate(
                [
                    'store_id' => $store->id,
                    'option_group_id' => $groupDoneness->id,
                    'name->en' => 'Medium',
                ],
                [
                    'name' => $this->t('Medium', 'متوسط'),
                    'is_active' => true,
                ],
            );

            $optMedWell = Option::query()->updateOrCreate(
                [
                    'store_id' => $store->id,
                    'option_group_id' => $groupDoneness->id,
                    'name->en' => 'Medium well',
                ],
                [
                    'name' => $this->t('Medium well', 'متوسط إلى جيد'),
                    'is_active' => true,
                ],
            );

            $optWell = Option::query()->updateOrCreate(
                [
                    'store_id' => $store->id,
                    'option_group_id' => $groupDoneness->id,
                    'name->en' => 'Well done',
                ],
                [
                    'name' => $this->t('Well done', 'جيد'),
                    'is_active' => true,
                ],
            );

            $groupCheese = OptionGroup::query()->updateOrCreate(
                [
                    'store_id' => $store->id,
                    'name->en' => 'Cheese',
                ],
                [
                    'name' => $this->t('Cheese', 'جبن'),
                ],
            );

            $optCheeseRegular = Option::query()->updateOrCreate(
                [
                    'store_id' => $store->id,
                    'option_group_id' => $groupCheese->id,
                    'name->en' => 'Standard slice',
                ],
                [
                    'name' => $this->t('Standard slice', 'شريحة عادية'),
                    'is_active' => true,
                ],
            );

            $optCheeseDouble = Option::query()->updateOrCreate(
                [
                    'store_id' => $store->id,
                    'option_group_id' => $groupCheese->id,
                    'name->en' => 'Double cheese',
                ],
                [
                    'name' => $this->t('Double cheese', 'جبن مزدوج'),
                    'is_active' => true,
                ],
            );

            $addBacon = Addition::query()->updateOrCreate(
                ['store_id' => $store->id, 'name->en' => 'Crispy bacon'],
                [
                    'name' => $this->t('Crispy bacon', 'لحم مقدد مقرمش'),
                    'is_active' => true,
                ],
            );

            $addPatty = Addition::query()->updateOrCreate(
                ['store_id' => $store->id, 'name->en' => 'Extra beef patty'],
                [
                    'name' => $this->t('Extra beef patty', 'قطعة لحم إضافية'),
                    'is_active' => true,
                ],
            );

            $addJalapeno = Addition::query()->updateOrCreate(
                ['store_id' => $store->id, 'name->en' => 'Grilled jalapeños'],
                [
                    'name' => $this->t('Grilled jalapeños', 'هالابينو مشوي'),
                    'is_active' => true,
                ],
            );

            $hero = Product::query()->updateOrCreate(
                ['store_id' => $store->id, 'name->en' => 'Classic beef burger'],
                [
                    'category_id' => $catBurgers->id,
                    'name' => $this->t('Classic beef burger', 'برجر لحم كلاسيكي'),
                    'description' => $this->t(
                        'Char-grilled beef patty, lettuce, tomato, onion, and house sauce.',
                        'قطعة لحم مشوية، خس، طماطم، بصل، وصلصة المنزل.',
                    ),
                    'keywords' => [
                        'en' => ['burger', 'beef', 'classic'],
                        'ar' => ['برجر', 'لحم'],
                    ],
                    'price' => 32.00,
                    'compare_price' => 38.00,
                    'is_active' => true,
                    'is_accepted' => true,
                ],
            );

            $hero->options()->sync([
                $optMedium->id => ['price' => 0.00, 'is_available' => true, 'quantity' => null],
                $optMedWell->id => ['price' => 0.00, 'is_available' => true, 'quantity' => null],
                $optWell->id => ['price' => 0.00, 'is_available' => true, 'quantity' => null],
                $optCheeseRegular->id => ['price' => 0.00, 'is_available' => true, 'quantity' => null],
                $optCheeseDouble->id => ['price' => 4.00, 'is_available' => true, 'quantity' => null],
            ]);

            $hero->additions()->sync([
                $addBacon->id => ['price' => 6.00],
                $addPatty->id => ['price' => 14.00],
                $addJalapeno->id => ['price' => 3.00],
            ]);

            $simpleProducts = [
                [
                    'en' => 'Crispy chicken burger',
                    'ar' => 'برجر دجاج مقرمش',
                    'category_id' => $catBurgers->id,
                    'price' => 28.00,
                    'compare_price' => 33.00,
                    'desc_en' => 'Buttermilk-marinated fillet, pickles, and garlic mayo.',
                    'desc_ar' => 'فيليه منقوع باللبن والمخلل ومايونيز بالثوم.',
                    'kw_en' => ['chicken', 'burger', 'crispy'],
                    'kw_ar' => ['دجاج', 'برجر'],
                ],
                [
                    'en' => 'Truffle parmesan fries',
                    'ar' => 'بطاطس بارميزان بالكمأة',
                    'category_id' => $catSides->id,
                    'price' => 22.00,
                    'compare_price' => 26.00,
                    'desc_en' => 'Hand-cut fries with truffle oil and aged parmesan.',
                    'desc_ar' => 'بطاطس مقطعة يدوياً مع زيت كمأ وبارميزان.',
                    'kw_en' => ['fries', 'truffle', 'side'],
                    'kw_ar' => ['بطاطس', 'كمأ'],
                ],
                [
                    'en' => 'Iced cola',
                    'ar' => 'كولا مثلجة',
                    'category_id' => $catDrinks->id,
                    'price' => 8.00,
                    'compare_price' => 10.00,
                    'desc_en' => 'Chilled cola served over ice.',
                    'desc_ar' => 'كولا باردة مع ثلج.',
                    'kw_en' => ['cola', 'soft drink', 'cold'],
                    'kw_ar' => ['كولا', 'مشروب'],
                ],
            ];

            foreach ($simpleProducts as $row) {
                Product::query()->updateOrCreate(
                    ['store_id' => $store->id, 'name->en' => $row['en']],
                    [
                        'category_id' => $row['category_id'],
                        'name' => $this->t($row['en'], $row['ar']),
                        'description' => $this->t($row['desc_en'], $row['desc_ar']),
                        'keywords' => [
                            'en' => $row['kw_en'],
                            'ar' => $row['kw_ar'],
                        ],
                        'price' => $row['price'],
                        'compare_price' => $row['compare_price'],
                        'is_active' => true,
                        'is_accepted' => true,
                    ],
                );
            }

            $branchPivot = [
                'price' => null,
                'compare_price' => null,
                'is_available' => true,
                'quantity' => 100,
            ];

            /** @var Collection<int, Product> $menuProducts */
            $menuProducts = Product::query()->where('store_id', $store->id)->get();

            foreach ($menuProducts as $product) {
                $product->branches()->sync([
                    $branch->id => array_merge($branchPivot, [
                        'price' => $product->price,
                        'compare_price' => $product->compare_price,
                    ]),
                ]);
            }
        } finally {
            Auth::guard('store')->logout();
        }
    }
}
