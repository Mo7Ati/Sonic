<?php

namespace Database\Seeders;

use App\Enums\BranchStatusEnum;
use App\Enums\OrderStatusEnum;
use App\Enums\PaymentStatusEnum;
use App\Models\Address;
use App\Models\Branch;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Models\Store;
use App\Models\StoreCategory;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Seeds orders, customers, and addresses for {@see self::STORE_EMAIL} so the store
 * Filament dashboard (30d stats, charts, top products) has realistic data.
 *
 * Ensures the store has at least two active branches and a small catalog if missing.
 *
 *     php artisan db:seed --class=PsStoreDashboardMetricsSeeder
 *
 * Prerequisite: store row must exist (e.g. php artisan initial:users).
 */
class PsStoreDashboardMetricsSeeder extends Seeder
{
    private const string STORE_EMAIL = 'store@ps.com';

    private const string ORDER_NOTE_TAG = 'SEED_PS_STORE_DASHBOARD';

    private const string CUSTOMER_EMAIL_DOMAIN = 'ps.dashboard.seed.sonic';

    private const int ORDER_COUNT = 500;

    private const int CUSTOMER_COUNT = 120;

    public function run(): void
    {
        $this->cleanupPreviousSeed();

        $store = Store::query()->where('email', self::STORE_EMAIL)->first();

        if ($store === null) {
            throw new RuntimeException(
                'No store with email '.self::STORE_EMAIL.'. Run: php artisan initial:users'
            );
        }

        $this->ensureStoreInventory($store);

        $branches = Branch::query()
            ->where('store_id', $store->id)
            ->where('is_active', true)
            ->get();

        if ($branches->isEmpty()) {
            throw new RuntimeException('No active branches for '.self::STORE_EMAIL.'.');
        }

        $products = Product::query()
            ->where('store_id', $store->id)
            ->where('is_active', true)
            ->get();

        if ($products->isEmpty()) {
            throw new RuntimeException('No active products for '.self::STORE_EMAIL.'.');
        }

        $customers = $this->seedCustomers();
        $addressesByCustomer = $this->seedAddresses($customers);

        $this->command?->info('Seeding '.self::ORDER_COUNT.' orders for '.self::STORE_EMAIL.'…');

        $branchIds = $branches->pluck('id')->all();
        $productIds = $products->pluck('id')->all();
        $productById = $products->keyBy('id');

        DB::transaction(function () use ($customers, $addressesByCustomer, $branchIds, $productIds, $productById): void {
            for ($i = 0; $i < self::ORDER_COUNT; $i++) {
                $customer = $customers[array_rand($customers)];
                $addressId = $addressesByCustomer[$customer->id] ?? null;

                if ($addressId === null) {
                    continue;
                }

                $branchId = $branchIds[array_rand($branchIds)];
                $status = $this->randomOrderStatus();
                $paymentStatus = $this->paymentStatusFor($status);
                $createdAt = $this->randomOrderTimestamp();

                $lineCount = random_int(1, min(3, count($productIds)));
                $pickedProductIds = collect($productIds)->random($lineCount)->unique()->values()->all();

                $lines = [];
                $itemsAmount = 0.0;

                foreach ($pickedProductIds as $productId) {
                    $product = $productById[$productId];
                    $qty = random_int(1, 4);
                    $unit = (float) $product->price;
                    $lineTotal = round($unit * $qty, 2);
                    $itemsAmount += $lineTotal;

                    $lines[] = [
                        'product_id' => $productId,
                        'product' => $product,
                        'quantity' => $qty,
                        'unit_price' => $unit,
                        'total_price' => $lineTotal,
                    ];
                }

                $delivery = round(random_int(800, 1800) / 100, 2);
                $total = round($itemsAmount + $delivery, 2);

                $order = new Order([
                    'status' => $status,
                    'payment_status' => $paymentStatus,
                    'cancelled_reason' => in_array($status, [OrderStatusEnum::CANCELLED, OrderStatusEnum::REJECTED], true)
                        ? 'Seed cancellation'
                        : null,
                    'customer_id' => $customer->id,
                    'customer_data' => [
                        'name' => $customer->name,
                        'email' => $customer->email,
                        'phone' => $customer->phone_number,
                    ],
                    'branch_id' => $branchId,
                    'address_id' => $addressId,
                    'address_data' => [
                        'label' => 'Home',
                        'summary' => 'Riyadh',
                    ],
                    'total_items_amount' => round($itemsAmount, 2),
                    'delivery_amount' => $delivery,
                    'total' => $total,
                    'notes' => self::ORDER_NOTE_TAG,
                ]);

                $order->created_at = $createdAt;
                $order->updated_at = $createdAt;
                $order->save();

                foreach ($lines as $line) {
                    /** @var Product $p */
                    $p = $line['product'];

                    DB::table('order_items')->insert([
                        'order_id' => $order->id,
                        'product_id' => $p->id,
                        'product_data' => json_encode([
                            'name' => $p->getTranslations('name'),
                        ]),
                        'options_amount' => 0,
                        'options_data' => null,
                        'additions_amount' => 0,
                        'additions_data' => null,
                        'quantity' => $line['quantity'],
                        'unit_price' => $line['unit_price'],
                        'total_price' => $line['total_price'],
                        'deleted_at' => null,
                    ]);
                }
            }
        });

        $this->command?->info('PS store dashboard metrics seed complete.');
    }

    private function cleanupPreviousSeed(): void
    {
        Order::query()->where('notes', self::ORDER_NOTE_TAG)->delete();

        $customerIds = Customer::query()
            ->where('email', 'like', '%@'.self::CUSTOMER_EMAIL_DOMAIN)
            ->pluck('id');

        if ($customerIds->isNotEmpty()) {
            Address::query()->whereIn('customer_id', $customerIds)->forceDelete();

            Customer::query()->whereIn('id', $customerIds)->forceDelete();
        }
    }

    private function ensureStoreInventory(Store $store): void
    {
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

        Auth::guard('store')->login($store);

        try {
            if (Branch::query()->where('store_id', $store->id)->where('is_active', true)->doesntExist()) {
                Branch::query()->create([
                    'store_id' => $store->id,
                    'name' => ['en' => 'PS — Al Olaya', 'ar' => 'بي إس — العلياء'],
                    'address' => ['en' => 'King Fahd Rd, Al Olaya, Riyadh', 'ar' => 'طريق الملك فهد، العلياء، الرياض'],
                    'delivery_time_from' => 25,
                    'delivery_time_to' => 45,
                    'delivery_fee' => 10.00,
                    'status' => BranchStatusEnum::AVAILABLE,
                    'is_active' => true,
                    'range_of_area_polygon' => null,
                    'location' => ['latitude' => '24.7136', 'longitude' => '46.6753'],
                ]);

                Branch::query()->create([
                    'store_id' => $store->id,
                    'name' => ['en' => 'PS — Al Malaz', 'ar' => 'بي إس — الملز'],
                    'address' => ['en' => 'Al Malaz district, Riyadh', 'ar' => 'حي الملز، الرياض'],
                    'delivery_time_from' => 30,
                    'delivery_time_to' => 55,
                    'delivery_fee' => 12.00,
                    'status' => BranchStatusEnum::AVAILABLE,
                    'is_active' => true,
                    'range_of_area_polygon' => null,
                    'location' => ['latitude' => '24.6720', 'longitude' => '46.7330'],
                ]);
            }

            if (Product::query()->where('store_id', $store->id)->where('is_active', true)->doesntExist()) {
                $this->seedMinimalCatalog($store);
            }
        } finally {
            Auth::guard('store')->logout();
        }
    }

    private function seedMinimalCatalog(Store $store): void
    {
        $rows = [
            'Starters & Salads' => [
                ['Hummus & Warm Pita', 18.00, 22.00],
                ['Fattoush Salad', 22.00, 26.00],
                ['Lentil Soup', 16.00, 19.00],
            ],
            'Signature Mains' => [
                ['Grilled Chicken Platter', 45.00, 52.00],
                ['Lamb Kabsa (single)', 55.00, 62.00],
                ['Butter Chicken with Rice', 42.00, 48.00],
            ],
            'Drinks & Desserts' => [
                ['Fresh Lemon Mint', 14.00, 17.00],
                ['Kunafa Slice', 24.00, 28.00],
            ],
        ];

        foreach ($rows as $categoryNameEn => $products) {
            $category = Category::query()->updateOrCreate(
                ['store_id' => $store->id, 'name->en' => $categoryNameEn],
                [
                    'name' => ['en' => $categoryNameEn, 'ar' => $categoryNameEn],
                    'description' => [
                        'en' => "PS Kitchen — {$categoryNameEn}.",
                        'ar' => 'مطبخ بي إس.',
                    ],
                    'is_active' => true,
                ],
            );

            foreach ($products as [$productNameEn, $price, $comparePrice]) {
                Product::query()->updateOrCreate(
                    [
                        'store_id' => $store->id,
                        'category_id' => $category->id,
                        'name->en' => $productNameEn,
                    ],
                    [
                        'name' => ['en' => $productNameEn, 'ar' => $productNameEn],
                        'description' => [
                            'en' => 'Prepared to order for delivery.',
                            'ar' => 'يُحضر عند الطلب.',
                        ],
                        'keywords' => [
                            'en' => [$categoryNameEn, 'ps store'],
                            'ar' => ['بي إس'],
                        ],
                        'price' => $price,
                        'compare_price' => $comparePrice,
                        'is_active' => true,
                        'is_accepted' => true,
                    ],
                );
            }
        }
    }

    /**
     * @return list<Customer>
     */
    private function seedCustomers(): array
    {
        $customers = [];

        for ($i = 0; $i < self::CUSTOMER_COUNT; $i++) {
            $createdAt = $this->randomCustomerSignupTimestamp();

            $customer = new Customer([
                'name' => 'PS Dashboard Customer '.($i + 1),
                'email' => 'c'.$i.'.'.Str::lower(Str::random(8)).'@'.self::CUSTOMER_EMAIL_DOMAIN,
                'password' => 'Password123!',
                'phone_number' => '+9665'.str_pad((string) random_int(0, 99_999_999), 8, '0', STR_PAD_LEFT),
                'is_active' => true,
            ]);

            $customer->created_at = $createdAt;
            $customer->updated_at = $createdAt;
            $customer->save();

            $customers[] = $customer;
        }

        return $customers;
    }

    /**
     * @param  list<Customer>  $customers
     * @return array<int, int> customer_id => address_id
     */
    private function seedAddresses(array $customers): array
    {
        $map = [];

        foreach ($customers as $customer) {
            $address = Address::query()->create([
                'name' => 'Home',
                'customer_id' => $customer->id,
                'fields' => [
                    'city' => ['en' => 'Riyadh', 'ar' => 'الرياض'],
                    'district' => ['en' => 'Al Olaya', 'ar' => 'العلياء'],
                ],
            ]);

            $map[$customer->id] = $address->id;
        }

        return $map;
    }

    private function randomOrderTimestamp(): Carbon
    {
        $day = random_int(0, 70);
        $hour = random_int(8, 23);
        $minute = random_int(0, 59);
        $second = random_int(0, 59);

        return Carbon::now()
            ->subDays($day)
            ->setTime($hour, $minute, $second);
    }

    private function randomCustomerSignupTimestamp(): Carbon
    {
        $day = random_int(0, 65);

        return Carbon::now()
            ->subDays($day)
            ->setTime(random_int(8, 22), random_int(0, 59), random_int(0, 59));
    }

    private function randomOrderStatus(): OrderStatusEnum
    {
        $r = random_int(1, 100);

        return match (true) {
            $r <= 40 => OrderStatusEnum::COMPLETED,
            $r <= 52 => OrderStatusEnum::PENDING,
            $r <= 64 => OrderStatusEnum::PREPARING,
            $r <= 76 => OrderStatusEnum::ON_THE_WAY,
            $r <= 88 => OrderStatusEnum::CANCELLED,
            default => OrderStatusEnum::REJECTED,
        };
    }

    private function paymentStatusFor(OrderStatusEnum $status): PaymentStatusEnum
    {
        return match ($status) {
            OrderStatusEnum::COMPLETED, OrderStatusEnum::ON_THE_WAY, OrderStatusEnum::PREPARING => random_int(1, 100) <= 92
                ? PaymentStatusEnum::PAID
                : PaymentStatusEnum::UNPAID,
            OrderStatusEnum::PENDING => random_int(1, 100) <= 70
                ? PaymentStatusEnum::UNPAID
                : PaymentStatusEnum::PAID,
            OrderStatusEnum::CANCELLED => random_int(1, 100) <= 30
                ? PaymentStatusEnum::REFUNDED
                : PaymentStatusEnum::UNPAID,
            OrderStatusEnum::REJECTED => random_int(1, 100) <= 50
                ? PaymentStatusEnum::REFUNDED
                : PaymentStatusEnum::UNPAID,
        };
    }
}
