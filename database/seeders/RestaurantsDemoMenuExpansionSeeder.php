<?php

namespace Database\Seeders;

use App\Models\Addition;
use App\Models\Branch;
use App\Models\Category;
use App\Models\Option;
use App\Models\OptionGroup;
use App\Models\Product;
use App\Models\Store;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Auth;

/**
 * Rebuilds the menu for the seed.restaurants@example.com demo store.
 *
 * Categories are seeded in the order the operator wanted them to appear:
 * Burgers → Pizza → Salads & wraps → Sides & fries → Desserts → Drinks.
 *
 * Products, option groups, options, and additions are created with updateOrCreate
 * so the seeder is idempotent and safe to re-run. Three products get rich
 * option/addition wiring to demonstrate customisation on the store page.
 */
class RestaurantsDemoMenuExpansionSeeder extends Seeder
{
    private const string STORE_EMAIL = 'seed.restaurants@example.com';

    /**
     * @return array{en: string, ar: string}
     */
    private function t(string $en, string $ar): array
    {
        return ['en' => $en, 'ar' => $ar];
    }

    public function run(): void
    {
        $store = Store::query()->where('email', self::STORE_EMAIL)->first();

        if (! $store) {
            $this->command?->warn('[RestaurantsDemoMenuExpansionSeeder] Store '.self::STORE_EMAIL.' not found — skipping.');

            return;
        }

        Auth::guard('store')->login($store);

        try {
            $branchIds = $this->resolveBranchIds($store);
            $optionLookup = $this->seedOptionGroupsAndOptions($store);
            $additionLookup = $this->seedAdditions($store);
            $productLookup = [];

            foreach ($this->menu() as $categoryRow) {
                $category = Category::query()->updateOrCreate(
                    ['store_id' => $store->id, 'name->en' => $categoryRow['name']['en']],
                    [
                        'name' => $categoryRow['name'],
                        'description' => $categoryRow['description'],
                        'is_active' => true,
                    ],
                );

                foreach ($categoryRow['products'] as $productRow) {
                    $product = Product::query()->updateOrCreate(
                        ['store_id' => $store->id, 'name->en' => $productRow['name']['en']],
                        [
                            'category_id' => $category->id,
                            'name' => $productRow['name'],
                            'description' => $productRow['description'],
                            'keywords' => $productRow['keywords'],
                            'price' => $productRow['price'],
                            'compare_price' => $productRow['compare_price'],
                            'is_active' => true,
                            'is_accepted' => true,
                        ],
                    );

                    $productLookup[$productRow['name']['en']] = $product;

                    $targetBranches = $this->branchesForCategory($categoryRow['name']['en'], $branchIds);

                    if ($targetBranches === []) {
                        continue;
                    }

                    $pivot = [];
                    foreach ($targetBranches as $branchId) {
                        $pivot[$branchId] = [
                            'price' => $product->price,
                            'compare_price' => $product->compare_price,
                            'is_available' => true,
                            'quantity' => 100,
                        ];
                    }

                    $product->branches()->syncWithoutDetaching($pivot);
                }
            }

            $this->wireOptionsAndAdditions($productLookup, $optionLookup, $additionLookup);
        } finally {
            Auth::guard('store')->logout();
        }
    }

    /**
     * @return array{main: ?int, burgers: ?int, pizza: ?int, desserts: ?int, seafood: ?int, shawarma: ?int}
     */
    private function resolveBranchIds(Store $store): array
    {
        $byName = fn (string $needle): ?int => Branch::query()
            ->where('store_id', $store->id)
            ->where('name->en', 'LIKE', '%'.$needle.'%')
            ->value('id');

        return [
            'main' => $byName('Restaurants — Al Olaya') ?? Branch::query()->where('store_id', $store->id)->value('id'),
            'burgers' => $byName('Burgers'),
            'pizza' => $byName('Pizza'),
            'desserts' => $byName('Desserts'),
            'seafood' => $byName('Seafood'),
            'shawarma' => $byName('Shawarma'),
        ];
    }

    /**
     * @param  array{main: ?int, burgers: ?int, pizza: ?int, desserts: ?int, seafood: ?int, shawarma: ?int}  $branchIds
     * @return list<int>
     */
    private function branchesForCategory(string $categoryEn, array $branchIds): array
    {
        $map = [
            'Burgers' => ['main', 'burgers'],
            'Pizza' => ['main', 'pizza'],
            'Salads & wraps' => ['main', 'shawarma'],
            'Sides & fries' => ['main', 'burgers'],
            'Desserts' => ['main', 'desserts'],
            'Drinks' => ['main', 'burgers'],
        ];

        $keys = $map[$categoryEn] ?? ['main'];

        return collect($keys)
            ->map(fn (string $key) => $branchIds[$key] ?? null)
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    /**
     * @return array<string, Option>
     */
    private function seedOptionGroupsAndOptions(Store $store): array
    {
        $groups = [
            'Cooking preference' => [
                'ar' => 'درجة التسوية',
                'options' => [
                    ['en' => 'Medium', 'ar' => 'متوسط'],
                    ['en' => 'Medium well', 'ar' => 'متوسط إلى جيد'],
                    ['en' => 'Well done', 'ar' => 'جيد'],
                ],
            ],
            'Cheese' => [
                'ar' => 'جبن',
                'options' => [
                    ['en' => 'Standard slice', 'ar' => 'شريحة عادية'],
                    ['en' => 'Double cheese', 'ar' => 'جبن مزدوج'],
                ],
            ],
            'Crust' => [
                'ar' => 'نوع العجينة',
                'options' => [
                    ['en' => 'Thin crust', 'ar' => 'عجينة رقيقة'],
                    ['en' => 'Classic crust', 'ar' => 'عجينة كلاسيكية'],
                    ['en' => 'Stuffed crust', 'ar' => 'عجينة محشوة بالجبن'],
                ],
            ],
        ];

        $lookup = [];

        foreach ($groups as $groupEn => $groupData) {
            $group = OptionGroup::query()->updateOrCreate(
                ['store_id' => $store->id, 'name->en' => $groupEn],
                ['name' => $this->t($groupEn, $groupData['ar'])],
            );

            foreach ($groupData['options'] as $optionRow) {
                $option = Option::query()->updateOrCreate(
                    [
                        'store_id' => $store->id,
                        'option_group_id' => $group->id,
                        'name->en' => $optionRow['en'],
                    ],
                    [
                        'name' => $this->t($optionRow['en'], $optionRow['ar']),
                        'is_active' => true,
                    ],
                );

                $lookup[$optionRow['en']] = $option;
            }
        }

        return $lookup;
    }

    /**
     * @return array<string, Addition>
     */
    private function seedAdditions(Store $store): array
    {
        $additions = [
            ['en' => 'Crispy bacon', 'ar' => 'لحم مقدد مقرمش'],
            ['en' => 'Extra beef patty', 'ar' => 'قطعة لحم إضافية'],
            ['en' => 'Grilled jalapeños', 'ar' => 'هالابينو مشوي'],
            ['en' => 'Extra cheese', 'ar' => 'جبن إضافي'],
            ['en' => 'Mushrooms', 'ar' => 'فطر'],
            ['en' => 'Green olives', 'ar' => 'زيتون أخضر'],
        ];

        $lookup = [];

        foreach ($additions as $row) {
            $lookup[$row['en']] = Addition::query()->updateOrCreate(
                ['store_id' => $store->id, 'name->en' => $row['en']],
                [
                    'name' => $this->t($row['en'], $row['ar']),
                    'is_active' => true,
                ],
            );
        }

        return $lookup;
    }

    /**
     * @param  array<string, Product>  $products
     * @param  array<string, Option>  $options
     * @param  array<string, Addition>  $additions
     */
    private function wireOptionsAndAdditions(array $products, array $options, array $additions): void
    {
        $wiring = [
            'Classic beef burger' => [
                'options' => [
                    ['name' => 'Medium', 'price' => 0.00],
                    ['name' => 'Medium well', 'price' => 0.00],
                    ['name' => 'Well done', 'price' => 0.00],
                    ['name' => 'Standard slice', 'price' => 0.00],
                    ['name' => 'Double cheese', 'price' => 4.00],
                ],
                'additions' => [
                    ['name' => 'Crispy bacon', 'price' => 6.00],
                    ['name' => 'Extra beef patty', 'price' => 14.00],
                    ['name' => 'Grilled jalapeños', 'price' => 3.00],
                ],
            ],
            'Crispy chicken burger' => [
                'options' => [
                    ['name' => 'Standard slice', 'price' => 0.00],
                    ['name' => 'Double cheese', 'price' => 4.00],
                ],
                'additions' => [
                    ['name' => 'Crispy bacon', 'price' => 5.00],
                    ['name' => 'Grilled jalapeños', 'price' => 3.00],
                ],
            ],
            'Margherita pizza' => [
                'options' => [
                    ['name' => 'Thin crust', 'price' => 0.00],
                    ['name' => 'Classic crust', 'price' => 0.00],
                    ['name' => 'Stuffed crust', 'price' => 6.00],
                ],
                'additions' => [
                    ['name' => 'Extra cheese', 'price' => 8.00],
                    ['name' => 'Mushrooms', 'price' => 5.00],
                    ['name' => 'Green olives', 'price' => 4.00],
                ],
            ],
        ];

        foreach ($wiring as $productName => $config) {
            $product = $products[$productName] ?? null;

            if (! $product) {
                continue;
            }

            $optionSync = [];
            foreach ($config['options'] as $row) {
                $option = $options[$row['name']] ?? null;
                if (! $option) {
                    continue;
                }
                $optionSync[$option->id] = [
                    'price' => $row['price'],
                    'is_available' => true,
                    'quantity' => null,
                ];
            }
            $product->options()->sync($optionSync);

            $additionSync = [];
            foreach ($config['additions'] as $row) {
                $addition = $additions[$row['name']] ?? null;
                if (! $addition) {
                    continue;
                }
                $additionSync[$addition->id] = ['price' => $row['price']];
            }
            $product->additions()->sync($additionSync);
        }
    }

    /**
     * @return list<array{
     *     name: array{en: string, ar: string},
     *     description: array{en: string, ar: string},
     *     products: list<array{
     *         name: array{en: string, ar: string},
     *         description: array{en: string, ar: string},
     *         keywords: array{en: list<string>, ar: list<string>},
     *         price: float,
     *         compare_price: float|null
     *     }>
     * }>
     */
    private function menu(): array
    {
        return [
            [
                'name' => $this->t('Burgers', 'برجر'),
                'description' => $this->t('Beef, chicken, and house specials.', 'لحم ودجاج وأطباق المطعم.'),
                'products' => [
                    [
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
                    ],
                    [
                        'name' => $this->t('Crispy chicken burger', 'برجر دجاج مقرمش'),
                        'description' => $this->t(
                            'Buttermilk-marinated fillet, pickles, and garlic mayo.',
                            'فيليه منقوع باللبن والمخلل ومايونيز بالثوم.',
                        ),
                        'keywords' => [
                            'en' => ['chicken', 'burger', 'crispy'],
                            'ar' => ['دجاج', 'برجر'],
                        ],
                        'price' => 28.00,
                        'compare_price' => 33.00,
                    ],
                    [
                        'name' => $this->t('Double smash burger', 'برجر دبل سماش'),
                        'description' => $this->t(
                            'Two smashed beef patties, American cheese, caramelized onions, and smash sauce on a toasted brioche.',
                            'قطعتان من لحم البقر المضغوط، جبنة أمريكية، بصل مكرمل، وصلصة سماش على خبز بريوش محمص.',
                        ),
                        'keywords' => [
                            'en' => ['burger', 'smash', 'double', 'beef'],
                            'ar' => ['برجر', 'سماش', 'لحم'],
                        ],
                        'price' => 39.00,
                        'compare_price' => 45.00,
                    ],
                    [
                        'name' => $this->t('BBQ mushroom & swiss burger', 'برجر فطر وسويسري بالباربكيو'),
                        'description' => $this->t(
                            'Grilled beef patty with sautéed mushrooms, Swiss cheese, crispy onions, and smoky BBQ sauce.',
                            'قطعة لحم مشوية مع فطر مقلي، جبنة سويسرية، بصل مقرمش، وصلصة باربكيو مدخنة.',
                        ),
                        'keywords' => [
                            'en' => ['burger', 'mushroom', 'bbq', 'swiss'],
                            'ar' => ['برجر', 'فطر', 'باربكيو'],
                        ],
                        'price' => 36.00,
                        'compare_price' => 42.00,
                    ],
                ],
            ],
            [
                'name' => $this->t('Pizza', 'بيتزا'),
                'description' => $this->t('Stone-baked pizzas with stretched dough.', 'بيتزا بالعجين الممدود المخبوز على الحجر.'),
                'products' => [
                    [
                        'name' => $this->t('Margherita pizza', 'بيتزا مارجريتا'),
                        'description' => $this->t(
                            'San Marzano tomato sauce, fresh mozzarella, basil, and extra virgin olive oil.',
                            'صلصة طماطم سان مارزانو، موزاريلا طازجة، ريحان، وزيت زيتون بكر ممتاز.',
                        ),
                        'keywords' => [
                            'en' => ['pizza', 'margherita', 'mozzarella'],
                            'ar' => ['بيتزا', 'مارجريتا', 'موزاريلا'],
                        ],
                        'price' => 45.00,
                        'compare_price' => 52.00,
                    ],
                    [
                        'name' => $this->t('Pepperoni pizza', 'بيتزا بيبروني'),
                        'description' => $this->t(
                            'Classic tomato base, mozzarella, and spicy beef pepperoni baked until the edges crisp.',
                            'صلصة طماطم كلاسيكية، موزاريلا، وبيبروني لحم بقر حار مخبوز حتى تقرمش الحواف.',
                        ),
                        'keywords' => [
                            'en' => ['pizza', 'pepperoni', 'spicy'],
                            'ar' => ['بيتزا', 'بيبروني'],
                        ],
                        'price' => 52.00,
                        'compare_price' => 60.00,
                    ],
                    [
                        'name' => $this->t('Four cheese pizza', 'بيتزا أربع أجبان'),
                        'description' => $this->t(
                            'Mozzarella, provolone, parmesan, and blue cheese on a light cream base.',
                            'موزاريلا، بروفولوني، بارميزان، وجبنة زرقاء على قاعدة كريمة خفيفة.',
                        ),
                        'keywords' => [
                            'en' => ['pizza', 'cheese', 'quattro formaggi'],
                            'ar' => ['بيتزا', 'أجبان'],
                        ],
                        'price' => 55.00,
                        'compare_price' => 62.00,
                    ],
                    [
                        'name' => $this->t('BBQ chicken pizza', 'بيتزا دجاج بالباربكيو'),
                        'description' => $this->t(
                            'Grilled chicken, smoky BBQ sauce, red onion, mozzarella, and fresh coriander.',
                            'دجاج مشوي، صلصة باربكيو مدخنة، بصل أحمر، موزاريلا، وكزبرة طازجة.',
                        ),
                        'keywords' => [
                            'en' => ['pizza', 'chicken', 'bbq'],
                            'ar' => ['بيتزا', 'دجاج', 'باربكيو'],
                        ],
                        'price' => 54.00,
                        'compare_price' => 62.00,
                    ],
                ],
            ],
            [
                'name' => $this->t('Salads & wraps', 'سلطات ولفائف'),
                'description' => $this->t('Lighter plates and handheld wraps.', 'أطباق خفيفة ولفائف سريعة.'),
                'products' => [
                    [
                        'name' => $this->t('Grilled chicken Caesar salad', 'سلطة سيزر بالدجاج المشوي'),
                        'description' => $this->t(
                            'Romaine hearts, grilled chicken, shaved parmesan, sourdough croutons, and Caesar dressing.',
                            'خس روماني، دجاج مشوي، جبن بارميزان، خبز محمص بالخميرة، وصلصة سيزر.',
                        ),
                        'keywords' => [
                            'en' => ['salad', 'caesar', 'chicken'],
                            'ar' => ['سلطة', 'سيزر', 'دجاج'],
                        ],
                        'price' => 34.00,
                        'compare_price' => 40.00,
                    ],
                    [
                        'name' => $this->t('Tabbouleh bowl', 'طبق تبولة'),
                        'description' => $this->t(
                            'Finely chopped parsley, tomato, bulgur, mint, and lemon with extra virgin olive oil.',
                            'بقدونس مفروم ناعماً، طماطم، برغل، نعناع، وليمون مع زيت زيتون بكر.',
                        ),
                        'keywords' => [
                            'en' => ['salad', 'tabbouleh', 'parsley'],
                            'ar' => ['تبولة', 'سلطة', 'بقدونس'],
                        ],
                        'price' => 22.00,
                        'compare_price' => 26.00,
                    ],
                    [
                        'name' => $this->t('Grilled halloumi wrap', 'لفافة حلومي مشوي'),
                        'description' => $this->t(
                            'Grilled halloumi, rocket, roasted peppers, and garlic yogurt in a warm saj bread.',
                            'حلومي مشوي، جرجير، فلفل مشوي، وزبادي بالثوم داخل خبز صاج دافئ.',
                        ),
                        'keywords' => [
                            'en' => ['wrap', 'halloumi', 'vegetarian'],
                            'ar' => ['لفافة', 'حلومي', 'نباتي'],
                        ],
                        'price' => 28.00,
                        'compare_price' => 33.00,
                    ],
                    [
                        'name' => $this->t('Falafel wrap', 'لفافة فلافل'),
                        'description' => $this->t(
                            'Crispy falafel, tahini, pickles, tomato, and parsley wrapped in soft markook bread.',
                            'فلافل مقرمش، طحينة، مخلل، طماطم، وبقدونس في خبز مرقوق طري.',
                        ),
                        'keywords' => [
                            'en' => ['wrap', 'falafel', 'vegan'],
                            'ar' => ['لفافة', 'فلافل'],
                        ],
                        'price' => 24.00,
                        'compare_price' => 29.00,
                    ],
                ],
            ],
            [
                'name' => $this->t('Sides & fries', 'بطاطس وإضافات'),
                'description' => $this->t('Sides to round out your order.', 'أطباق جانبية تكمل طلبك.'),
                'products' => [
                    [
                        'name' => $this->t('Truffle parmesan fries', 'بطاطس بارميزان بالكمأة'),
                        'description' => $this->t(
                            'Hand-cut fries with truffle oil and aged parmesan.',
                            'بطاطس مقطعة يدوياً مع زيت كمأ وبارميزان.',
                        ),
                        'keywords' => [
                            'en' => ['fries', 'truffle', 'side'],
                            'ar' => ['بطاطس', 'كمأ'],
                        ],
                        'price' => 22.00,
                        'compare_price' => 26.00,
                    ],
                    [
                        'name' => $this->t('Classic french fries', 'بطاطس فرنسية كلاسيكية'),
                        'description' => $this->t(
                            'Golden hand-cut fries, lightly salted and served hot.',
                            'بطاطس مقطعة يدوياً ذهبية بالملح تقدم ساخنة.',
                        ),
                        'keywords' => [
                            'en' => ['fries', 'classic', 'side'],
                            'ar' => ['بطاطس', 'كلاسيك'],
                        ],
                        'price' => 12.00,
                        'compare_price' => 15.00,
                    ],
                    [
                        'name' => $this->t('Spicy cheddar wedges', 'ودجز حار بالشيدر'),
                        'description' => $this->t(
                            'Seasoned potato wedges topped with melted cheddar, jalapeños, and chipotle drizzle.',
                            'ودجز بطاطس متبلة مع جبنة شيدر ذائبة، هالابينو، ورذاذ تشيبوتلي.',
                        ),
                        'keywords' => [
                            'en' => ['wedges', 'cheddar', 'spicy'],
                            'ar' => ['ودجز', 'شيدر', 'حار'],
                        ],
                        'price' => 19.00,
                        'compare_price' => 23.00,
                    ],
                    [
                        'name' => $this->t('Mozzarella sticks', 'أصابع موزاريلا'),
                        'description' => $this->t(
                            'Crispy breaded mozzarella sticks served with a side of marinara sauce.',
                            'أصابع موزاريلا مقرمشة بالبقسماط مع صلصة مارينارا.',
                        ),
                        'keywords' => [
                            'en' => ['mozzarella', 'cheese', 'fried'],
                            'ar' => ['موزاريلا', 'جبن'],
                        ],
                        'price' => 21.00,
                        'compare_price' => 25.00,
                    ],
                ],
            ],
            [
                'name' => $this->t('Desserts', 'حلويات'),
                'description' => $this->t('Sweet finishes made in-house.', 'حلويات مُحضّرة داخل المطعم.'),
                'products' => [
                    [
                        'name' => $this->t('Molten chocolate cake', 'كيكة الشوكولاتة السائلة'),
                        'description' => $this->t(
                            'Warm chocolate cake with a molten center, served with vanilla ice cream.',
                            'كيكة شوكولاتة دافئة بقلب سائل تقدم مع آيس كريم فانيليا.',
                        ),
                        'keywords' => [
                            'en' => ['dessert', 'chocolate', 'warm'],
                            'ar' => ['حلى', 'شوكولاتة'],
                        ],
                        'price' => 26.00,
                        'compare_price' => 30.00,
                    ],
                    [
                        'name' => $this->t('Baklava plate', 'طبق بقلاوة'),
                        'description' => $this->t(
                            'Layered filo pastry with pistachios and orange-blossom syrup, six assorted pieces.',
                            'طبقات عجين فيلو بالفستق وقطر بماء الزهر، ست قطع متنوعة.',
                        ),
                        'keywords' => [
                            'en' => ['dessert', 'baklava', 'pistachio'],
                            'ar' => ['حلى', 'بقلاوة', 'فستق'],
                        ],
                        'price' => 28.00,
                        'compare_price' => 33.00,
                    ],
                    [
                        'name' => $this->t('Vanilla cheesecake', 'تشيز كيك فانيليا'),
                        'description' => $this->t(
                            'Baked New York-style cheesecake on a buttery biscuit base with berry compote.',
                            'تشيز كيك مخبوز على طريقة نيويورك بقاعدة بسكويت بالزبدة مع مربى التوت.',
                        ),
                        'keywords' => [
                            'en' => ['dessert', 'cheesecake', 'vanilla'],
                            'ar' => ['حلى', 'تشيز كيك'],
                        ],
                        'price' => 24.00,
                        'compare_price' => 29.00,
                    ],
                    [
                        'name' => $this->t('Kunafa cheese', 'كنافة بالجبن'),
                        'description' => $this->t(
                            'Crisp shredded kataifi over melted akkawi cheese, finished with rose-water syrup and pistachios.',
                            'كنافة مفرومة مقرمشة فوق جبنة عكاوي ذائبة مع قطر ماء ورد وفستق.',
                        ),
                        'keywords' => [
                            'en' => ['dessert', 'kunafa', 'cheese'],
                            'ar' => ['حلى', 'كنافة', 'جبن'],
                        ],
                        'price' => 30.00,
                        'compare_price' => 35.00,
                    ],
                ],
            ],
            [
                'name' => $this->t('Drinks', 'مشروبات'),
                'description' => $this->t('Soft drinks and chilled beverages.', 'مشروبات غازية وباردة.'),
                'products' => [
                    [
                        'name' => $this->t('Iced cola', 'كولا مثلجة'),
                        'description' => $this->t(
                            'Chilled cola served over ice.',
                            'كولا باردة مع ثلج.',
                        ),
                        'keywords' => [
                            'en' => ['cola', 'soft drink', 'cold'],
                            'ar' => ['كولا', 'مشروب'],
                        ],
                        'price' => 8.00,
                        'compare_price' => 10.00,
                    ],
                    [
                        'name' => $this->t('Fresh mint lemonade', 'ليموناضة بالنعناع الطازج'),
                        'description' => $this->t(
                            'Freshly squeezed lemon blended with crushed mint and a touch of cane sugar.',
                            'عصير ليمون طازج مع نعناع مهروس وقليل من السكر.',
                        ),
                        'keywords' => [
                            'en' => ['lemonade', 'mint', 'fresh'],
                            'ar' => ['ليموناضة', 'نعناع'],
                        ],
                        'price' => 14.00,
                        'compare_price' => 17.00,
                    ],
                    [
                        'name' => $this->t('Sparkling water', 'مياه فوارة'),
                        'description' => $this->t(
                            'Chilled sparkling mineral water served with a lemon wedge.',
                            'مياه فوارة باردة تقدم مع شريحة ليمون.',
                        ),
                        'keywords' => [
                            'en' => ['sparkling', 'water'],
                            'ar' => ['فوارة', 'مياه'],
                        ],
                        'price' => 9.00,
                        'compare_price' => null,
                    ],
                    [
                        'name' => $this->t('Mango smoothie', 'سموذي مانجو'),
                        'description' => $this->t(
                            'Fresh mango blended with yogurt and a hint of honey — no added syrups.',
                            'مانجو طازجة مع زبادي ولمسة من العسل بدون شراب مضاف.',
                        ),
                        'keywords' => [
                            'en' => ['smoothie', 'mango', 'yogurt'],
                            'ar' => ['سموذي', 'مانجو'],
                        ],
                        'price' => 18.00,
                        'compare_price' => 22.00,
                    ],
                ],
            ],
        ];
    }
}
