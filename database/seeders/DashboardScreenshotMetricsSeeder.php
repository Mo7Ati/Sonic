<?php

namespace Database\Seeders;

use App\Enums\BranchStatusEnum;
use App\Enums\OrderStatusEnum;
use App\Enums\PaymentStatusEnum;
use App\Models\Address;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Models\Store;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Seeds a large volume of orders and customers spread across dates/times so Filament
 * dashboard widgets (revenue, orders, status pie, top products, etc.) look good in screenshots.
 *
 * Prerequisites: run {@see DemoStoreWithInventorySeeder} first so at least one store has
 * branches and products.
 *
 *     php artisan db:seed --class=DashboardScreenshotMetricsSeeder
 */
class DashboardScreenshotMetricsSeeder extends Seeder
{
    private const string ORDER_NOTE_TAG = 'SEED_DASHBOARD_METRICS';

    private const string CUSTOMER_EMAIL_DOMAIN = 'metrics.seed.sonic';

    /** Total orders to create (adjust for heavier/lighter charts). */
    private const int ORDER_COUNT = 1_200;

    /** Customers to create (orders randomly reuse them). */
    private const int CUSTOMER_COUNT = 280;

    /** Extra demo stores for admin “active stores” / branch stats (optional). */
    private const int EXTRA_STORES = 5;

    /** Extra branches on the primary seeded store (recent “new” branches). */
    private const int EXTRA_BRANCHES = 6;

    public function run(): void
    {
        $this->cleanupPreviousSeed();

        $primaryStore = Store::query()
            ->whereHas('products', fn ($q) => $q->where('is_active', true))
            ->with(['branches' => fn ($q) => $q->orderBy('id')])
            ->first();

        if ($primaryStore === null) {
            $this->command?->error('No store with active products found. Run DemoStoreWithInventorySeeder first.');

            return;
        }

        $branches = Branch::query()
            ->where('store_id', $primaryStore->id)
            ->where('is_active', true)
            ->get();

        if ($branches->isEmpty()) {
            $this->command?->error('No active branches for the demo store. Add branches, then re-run.');

            return;
        }

        $products = Product::query()
            ->where('store_id', $primaryStore->id)
            ->where('is_active', true)
            ->get();

        if ($products->isEmpty()) {
            $this->command?->error('No active products for the demo store.');

            return;
        }

        $this->seedExtraStores();
        $this->seedExtraBranches($primaryStore);

        $branches = Branch::query()
            ->where('store_id', $primaryStore->id)
            ->where('is_active', true)
            ->get();

        $customers = $this->seedCustomers();
        $addressesByCustomer = $this->seedAddresses($customers);

        $this->command?->info('Seeding '.self::ORDER_COUNT.' orders…');

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

                    // `order_items` has no timestamp columns; avoid Eloquent timestamps here.
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

        $this->command?->info('Dashboard metrics seed complete.');
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

        Branch::query()
            ->where('name->en', 'like', 'Screenshot Branch%')
            ->forceDelete();

        $storeIds = Store::query()
            ->where('email', 'like', 'screenshot.store.%@'.self::CUSTOMER_EMAIL_DOMAIN)
            ->pluck('id');

        if ($storeIds->isNotEmpty()) {
            Branch::query()->whereIn('store_id', $storeIds)->forceDelete();

            Store::query()->whereIn('id', $storeIds)->forceDelete();
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
                'name' => 'Screenshot Customer '.($i + 1),
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

    private function seedExtraStores(): void
    {
        for ($i = 0; $i < self::EXTRA_STORES; $i++) {
            $createdAt = Carbon::now()
                ->subDays(random_int(5, 75))
                ->setTime(random_int(9, 20), random_int(0, 59), random_int(0, 59));

            $store = new Store([
                'name' => [
                    'en' => 'Screenshot Store '.($i + 1),
                    'ar' => 'متجر لقطة '.($i + 1),
                ],
                'description' => [
                    'en' => 'Seeded for dashboard screenshots.',
                    'ar' => 'بيانات تجريبية للوحة التحكم.',
                ],
                'keywords' => ['en' => ['demo'], 'ar' => ['تجريبي']],
                'social_media' => [],
                'email' => 'screenshot.store.'.$i.'@'.self::CUSTOMER_EMAIL_DOMAIN,
                'phone' => '+9665'.str_pad((string) random_int(0, 99_999_999), 8, '0', STR_PAD_LEFT),
                'password' => Hash::make('Password123!'),
                'is_active' => true,
            ]);

            $store->created_at = $createdAt;
            $store->updated_at = $createdAt;
            $store->save();
        }
    }

    private function seedExtraBranches(Store $store): void
    {
        for ($i = 0; $i < self::EXTRA_BRANCHES; $i++) {
            $createdAt = Carbon::now()
                ->subDays(random_int(1, 28))
                ->setTime(random_int(10, 19), random_int(0, 59), random_int(0, 59));

            $branch = new Branch([
                'store_id' => $store->id,
                'name' => [
                    'en' => 'Screenshot Branch '.($i + 1),
                    'ar' => 'فرع لقطة '.($i + 1),
                ],
                'address' => [
                    'en' => 'Riyadh, KSA',
                    'ar' => 'الرياض',
                ],
                'delivery_time_from' => 20,
                'delivery_time_to' => 45,
                'delivery_fee' => 10,
                'status' => BranchStatusEnum::AVAILABLE,
                'is_active' => true,
                'range_of_area_polygon' => null,
                'location' => ['latitude' => '24.71', 'longitude' => '46.67'],
            ]);

            $branch->created_at = $createdAt;
            $branch->updated_at = $createdAt;
            $branch->save();
        }
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
