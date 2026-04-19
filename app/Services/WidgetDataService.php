<?php

namespace App\Services;

use App\Enums\OrderStatusEnum;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Models\Store;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class WidgetDataService
{
    // ==================== ADMIN METRICS (All Stores) ====================

    public function getAdminRevenueStats(string $period = 'today'): array
    {
        return $this->calculateRevenue(period: $period);
    }

    public function getAdminOrderStats(string $period = 'today'): array
    {
        return $this->calculateOrders(period: $period);
    }

    public function getAdminCustomerStats(string $period = 'today'): array
    {
        return $this->calculateCustomers(period: $period);
    }

    public function getAdminRevenueChart(string $period = 'month'): Collection
    {
        return $this->buildRevenueChart(period: $period);
    }

    public function getAdminOrdersStatusChart(): Collection
    {
        return $this->buildOrdersStatusChart();
    }

    public function getAdminTopProducts(int $limit = 10): Collection
    {
        return $this->buildTopProductsChart(limit: $limit);
    }

    public function getAdminBranchPerformance(): Collection
    {
        return $this->buildBranchPerformanceChart();
    }

    /**
     * Rolling last 30 days vs the prior 30 days (aligned with sparklines).
     *
     * @return array{current: float, previous: float, percentage_change: float, period: string}
     */
    public function getAdminRevenue30dStats(): array
    {
        return $this->calculateRevenue(null, '30d');
    }

    /**
     * @return array{current: int, previous: int, percentage_change: float, pending: int, period: string}
     */
    public function getAdminOrders30dStats(): array
    {
        return $this->calculateOrders(null, '30d');
    }

    /**
     * @return array{current: int, previous: int, percentage_change: float, period: string}
     */
    public function getAdminCustomers30dStats(): array
    {
        return $this->calculateCustomers(null, '30d');
    }

    /**
     * @return array{total: int, new_current: int, new_previous: int, percentage_change: float}
     */
    public function getAdminStoreSnapshot30d(): array
    {
        return $this->calculateStoreSnapshot30d();
    }

    /**
     * @return array{total: int, new_current: int, new_previous: int, percentage_change: float}
     */
    public function getAdminBranchSnapshot30d(): array
    {
        return $this->calculateBranchSnapshot30d();
    }

    /**
     * @return array{total: int, new_current: int, new_previous: int, percentage_change: float}
     */
    public function getAdminProductSnapshot30d(): array
    {
        return $this->calculateProductSnapshot30d();
    }

    /**
     * Daily totals for the last 30 days (oldest → newest), for stat sparklines.
     *
     * @return array<int, float>
     */
    public function getAdminSparklineRevenueLast30Days(): array
    {
        return $this->sparklineOrderRevenueLast30Days(null);
    }

    /**
     * @return array<int, float>
     */
    public function getAdminSparklineOrdersLast30Days(): array
    {
        return $this->sparklineOrderCountsLast30Days(null);
    }

    /**
     * @return array<int, float>
     */
    public function getAdminSparklineCustomersLast30Days(): array
    {
        return $this->sparklineModelCountsLast30Days(Customer::class);
    }

    /**
     * @return array<int, float>
     */
    public function getAdminSparklineStoresLast30Days(): array
    {
        return $this->sparklineModelCountsLast30Days(Store::class);
    }

    /**
     * @return array<int, float>
     */
    public function getAdminSparklineBranchesLast30Days(): array
    {
        return $this->sparklineModelCountsLast30Days(Branch::class);
    }

    /**
     * @return array<int, float>
     */
    public function getAdminSparklineProductsLast30Days(): array
    {
        return $this->sparklineModelCountsLast30Days(Product::class);
    }

    // ==================== STORE METRICS (Store-Specific) ====================

    public function getStoreRevenueStats(int|string $storeId, string $period = 'today'): array
    {
        return $this->calculateRevenue(storeId: $storeId, period: $period);
    }

    public function getStoreOrderStats(int|string $storeId, string $period = 'today'): array
    {
        return $this->calculateOrders(storeId: $storeId, period: $period);
    }

    public function getStoreCustomerStats(int|string $storeId, string $period = 'today'): array
    {
        return $this->calculateCustomers(storeId: $storeId, period: $period);
    }

    public function getStoreProductStats(int|string $storeId): array
    {
        return $this->calculateStoreProducts(storeId: $storeId);
    }

    public function getStoreRevenueChart(int|string $storeId, string $period = 'month'): Collection
    {
        return $this->buildRevenueChart(storeId: $storeId, period: $period);
    }

    public function getStoreOrdersStatusChart(int|string $storeId): Collection
    {
        return $this->buildOrdersStatusChart(storeId: $storeId);
    }

    public function getStoreTopProducts(int|string $storeId, int $limit = 10): Collection
    {
        return $this->buildTopProductsChart(storeId: $storeId, limit: $limit);
    }

    public function getStoreBranchPerformance(int|string $storeId): Collection
    {
        return $this->buildBranchPerformanceChart(storeId: $storeId);
    }

    /**
     * Calculate revenue for a given period with comparison to previous period.
     *
     * @return array{current: float, previous: float, percentage_change: float, period: string}
     */
    private function calculateRevenue(int|string|null $storeId = null, string $period = 'today'): array
    {
        [$dateFrom, $dateTo, $prevFrom, $prevTo] = $this->getDateRanges($period);

        $query = Order::query()
            ->whereBetween('created_at', [$dateFrom, $dateTo]);

        if ($storeId) {
            $query->whereHas('branch', fn ($q) => $q->where('store_id', $storeId));
        }

        $currentRevenue = (float) $query->sum('total');

        $previousQuery = Order::query()
            ->whereBetween('created_at', [$prevFrom, $prevTo]);

        if ($storeId) {
            $previousQuery->whereHas('branch', fn ($q) => $q->where('store_id', $storeId));
        }

        $previousRevenue = (float) $previousQuery->sum('total');

        $percentageChange = $previousRevenue > 0
            ? (($currentRevenue - $previousRevenue) / $previousRevenue) * 100
            : 0;

        return [
            'current' => $currentRevenue,
            'previous' => $previousRevenue,
            'percentage_change' => round($percentageChange, 2),
            'period' => $period,
        ];
    }

    /**
     * Calculate order count and pending orders.
     *
     * @return array{current: int, previous: int, percentage_change: float, pending: int, period: string}
     */
    private function calculateOrders(int|string|null $storeId = null, string $period = 'today'): array
    {
        [$dateFrom, $dateTo, $prevFrom, $prevTo] = $this->getDateRanges($period);

        $query = Order::query()
            ->whereBetween('created_at', [$dateFrom, $dateTo]);

        if ($storeId) {
            $query->whereHas('branch', fn ($q) => $q->where('store_id', $storeId));
        }

        $currentCount = (clone $query)->count();
        $pendingCount = (clone $query)->where('status', 'pending')->count();

        $previousQuery = Order::query()
            ->whereBetween('created_at', [$prevFrom, $prevTo]);

        if ($storeId) {
            $previousQuery->whereHas('branch', fn ($q) => $q->where('store_id', $storeId));
        }

        $previousCount = $previousQuery->count();
        $percentageChange = $previousCount > 0
            ? (($currentCount - $previousCount) / $previousCount) * 100
            : 0;

        return [
            'current' => $currentCount,
            'previous' => $previousCount,
            'percentage_change' => round($percentageChange, 2),
            'pending' => $pendingCount,
            'period' => $period,
        ];
    }

    /**
     * Calculate new customers in a period.
     *
     * @return array{current: int, previous: int, percentage_change: float, period: string}
     */
    private function calculateCustomers(int|string|null $storeId = null, string $period = 'today'): array
    {
        [$dateFrom, $dateTo, $prevFrom, $prevTo] = $this->getDateRanges($period);

        $query = Customer::query()
            ->whereBetween('created_at', [$dateFrom, $dateTo]);

        if ($storeId) {
            $query->whereHas('orders.branch', fn ($q) => $q->where('store_id', $storeId));
        }

        $currentCount = $query->count();

        $previousQuery = Customer::query()
            ->whereBetween('created_at', [$prevFrom, $prevTo]);

        if ($storeId) {
            $previousQuery->whereHas('orders.branch', fn ($q) => $q->where('store_id', $storeId));
        }

        $previousCount = $previousQuery->count();
        $percentageChange = $previousCount > 0
            ? (($currentCount - $previousCount) / $previousCount) * 100
            : 0;

        return [
            'current' => $currentCount,
            'previous' => $previousCount,
            'percentage_change' => round($percentageChange, 2),
            'period' => $period,
        ];
    }

    /**
     * Calculate total products in store catalog.
     *
     * @return array{current: int, total: int, active: int}
     */
    private function calculateStoreProducts(int|string $storeId): array
    {
        $totals = Product::query()
            ->where('store_id', $storeId)
            ->selectRaw('COUNT(*) as total, SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active')
            ->first();

        $total = (int) ($totals->total ?? 0);
        $active = (int) ($totals->active ?? 0);

        return [
            'current' => $total,
            'total' => $total,
            'active' => $active,
        ];
    }

    /**
     * Build revenue trend chart data.
     */
    private function buildRevenueChart(int|string|null $storeId = null, string $period = 'month'): Collection
    {
        [$dateFrom, $dateTo] = $this->getDateRange($period);

        $query = Order::query()
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(total) as total'))
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->groupBy('date')
            ->orderBy('date');

        if ($storeId) {
            $query->whereHas('branch', fn ($q) => $q->where('store_id', $storeId));
        }

        return $query->get()
            ->map(fn ($record) => [
                'label' => Carbon::parse($record->date)
                    ->locale(app()->getLocale())
                    ->translatedFormat(__('widgets.charts.axis_date_format')),
                'value' => (float) $record->total,
            ]);
    }

    /**
     * Build orders by status pie chart.
     */
    private function buildOrdersStatusChart(int|string|null $storeId = null): Collection
    {
        $query = Order::query()
            ->select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status');

        if ($storeId) {
            $query->whereHas('branch', fn ($q) => $q->where('store_id', $storeId));
        }

        return $query->get()
            ->map(fn ($record) => [
                'label' => $this->orderStatusLabel($record->status),
                'value' => (int) $record->count,
            ]);
    }

    /**
     * Build top products bar chart using the order_items pivot table.
     */
    private function buildTopProductsChart(int|string|null $storeId = null, int $limit = 10): Collection
    {
        $query = Product::query()
            ->select('products.id', 'products.name')
            ->selectRaw('COALESCE(SUM(order_items.quantity), 0) as order_count')
            ->leftJoin('order_items', function ($join) {
                $join->on('products.id', '=', 'order_items.product_id')
                    ->whereNull('order_items.deleted_at');
            })
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('order_count')
            ->limit($limit);

        if ($storeId) {
            $query->where('products.store_id', $storeId);
        }

        return $query->get()
            ->map(fn (Product $record) => [
                'label' => $this->translatableToString($record->name),
                'value' => (int) $record->order_count,
            ]);
    }

    /**
     * Build branch performance chart.
     */
    private function buildBranchPerformanceChart(int|string|null $storeId = null): Collection
    {
        $query = Branch::query()
            ->select('branches.id', 'branches.name')
            ->selectRaw('COALESCE(SUM(orders.total), 0) as revenue')
            ->selectRaw('COUNT(orders.id) as order_count')
            ->leftJoin('orders', 'branches.id', '=', 'orders.branch_id')
            ->groupBy('branches.id', 'branches.name')
            ->orderByDesc('revenue');

        if ($storeId) {
            $query->where('branches.store_id', $storeId);
        }

        return $query->get()
            ->map(fn (Branch $record) => [
                'label' => $this->translatableToString($record->name),
                'revenue' => (float) ($record->revenue ?? 0),
                'orders' => (int) ($record->order_count ?? 0),
            ]);
    }

    private function orderStatusLabel(mixed $status): string
    {
        if ($status instanceof OrderStatusEnum) {
            return $status->label();
        }

        if (is_string($status)) {
            $enum = OrderStatusEnum::tryFrom($status);

            return $enum ? $enum->label() : __('enums.order_status.'.$status);
        }

        if ($status instanceof \BackedEnum) {
            $enum = OrderStatusEnum::tryFrom($status->value);

            return $enum ? $enum->label() : (string) $status->value;
        }

        return (string) $status;
    }

    /**
     * Normalise a translatable attribute value to a displayable string.
     */
    private function translatableToString(mixed $value): string
    {
        if (is_string($value)) {
            return $value;
        }

        if (is_array($value)) {
            $locale = app()->getLocale();

            return (string) ($value[$locale] ?? reset($value) ?? '');
        }

        return (string) ($value ?? '');
    }

    /**
     * Get date ranges for period comparison
     */
    private function getDateRanges(string $period): array
    {
        $now = Carbon::now();

        return match ($period) {
            'today' => [
                $now->copy()->startOfDay(),
                $now->copy()->endOfDay(),
                $now->copy()->subDay()->startOfDay(),
                $now->copy()->subDay()->endOfDay(),
            ],
            'week' => [
                $now->copy()->startOfWeek(),
                $now->copy()->endOfWeek(),
                $now->copy()->subWeek()->startOfWeek(),
                $now->copy()->subWeek()->endOfWeek(),
            ],
            'month' => [
                $now->copy()->startOfMonth(),
                $now->copy()->endOfMonth(),
                $now->copy()->subMonth()->startOfMonth(),
                $now->copy()->subMonth()->endOfMonth(),
            ],
            '30d' => [
                $now->copy()->subDays(29)->startOfDay(),
                $now->copy()->endOfDay(),
                $now->copy()->subDays(59)->startOfDay(),
                $now->copy()->subDays(30)->endOfDay(),
            ],
            default => [
                $now->copy()->subDays(30)->startOfDay(),
                $now->copy()->endOfDay(),
                $now->copy()->subDays(60)->startOfDay(),
                $now->copy()->subDays(30)->endOfDay(),
            ],
        };
    }

    /**
     * Get single date range
     */
    private function getDateRange(string $period): array
    {
        $now = Carbon::now();

        return match ($period) {
            'today' => [
                $now->copy()->startOfDay(),
                $now->copy()->endOfDay(),
            ],
            'week' => [
                $now->copy()->startOfWeek(),
                $now->copy()->endOfWeek(),
            ],
            'month' => [
                $now->copy()->startOfMonth(),
                $now->copy()->endOfMonth(),
            ],
            '30d' => [
                $now->copy()->subDays(29)->startOfDay(),
                $now->copy()->endOfDay(),
            ],
            default => [
                $now->copy()->subDays(30)->startOfDay(),
                $now->copy()->endOfDay(),
            ],
        };
    }

    /**
     * @return array{total: int, new_current: int, new_previous: int, percentage_change: float}
     */
    private function calculateStoreSnapshot30d(): array
    {
        $total = (int) Store::query()->where('is_active', true)->count();
        [$currentFrom, $currentTo, $previousFrom, $previousTo] = $this->getDateRanges('30d');

        $newCurrent = (int) Store::query()->whereBetween('created_at', [$currentFrom, $currentTo])->count();
        $newPrevious = (int) Store::query()->whereBetween('created_at', [$previousFrom, $previousTo])->count();
        $pct = $newPrevious > 0
            ? (($newCurrent - $newPrevious) / $newPrevious) * 100
            : ($newCurrent > 0 ? 100.0 : 0.0);

        return [
            'total' => $total,
            'new_current' => $newCurrent,
            'new_previous' => $newPrevious,
            'percentage_change' => round($pct, 1),
        ];
    }

    /**
     * @return array{total: int, new_current: int, new_previous: int, percentage_change: float}
     */
    private function calculateBranchSnapshot30d(): array
    {
        $total = (int) Branch::query()->count();
        [$currentFrom, $currentTo, $previousFrom, $previousTo] = $this->getDateRanges('30d');

        $newCurrent = (int) Branch::query()->whereBetween('created_at', [$currentFrom, $currentTo])->count();
        $newPrevious = (int) Branch::query()->whereBetween('created_at', [$previousFrom, $previousTo])->count();
        $pct = $newPrevious > 0
            ? (($newCurrent - $newPrevious) / $newPrevious) * 100
            : ($newCurrent > 0 ? 100.0 : 0.0);

        return [
            'total' => $total,
            'new_current' => $newCurrent,
            'new_previous' => $newPrevious,
            'percentage_change' => round($pct, 1),
        ];
    }

    /**
     * @return array{total: int, new_current: int, new_previous: int, percentage_change: float}
     */
    private function calculateProductSnapshot30d(): array
    {
        $total = (int) Product::query()->count();
        [$currentFrom, $currentTo, $previousFrom, $previousTo] = $this->getDateRanges('30d');

        $newCurrent = (int) Product::query()->whereBetween('created_at', [$currentFrom, $currentTo])->count();
        $newPrevious = (int) Product::query()->whereBetween('created_at', [$previousFrom, $previousTo])->count();
        $pct = $newPrevious > 0
            ? (($newCurrent - $newPrevious) / $newPrevious) * 100
            : ($newCurrent > 0 ? 100.0 : 0.0);

        return [
            'total' => $total,
            'new_current' => $newCurrent,
            'new_previous' => $newPrevious,
            'percentage_change' => round($pct, 1),
        ];
    }

    /**
     * @return array{0: Carbon, 1: Carbon}
     */
    private function last30DaysBounds(): array
    {
        $end = Carbon::now()->endOfDay();
        $start = Carbon::now()->copy()->subDays(29)->startOfDay();

        return [$start, $end];
    }

    /**
     * @param  Collection<string|int, mixed>  $valuesByDay
     * @return array<int, float>
     */
    private function buildNumericSeriesFromDaily(Collection $valuesByDay, int $days = 30): array
    {
        $start = Carbon::now()->copy()->subDays($days - 1)->startOfDay();
        $out = [];
        for ($i = 0; $i < $days; $i++) {
            $d = $start->copy()->addDays($i)->toDateString();
            $out[] = (float) ($valuesByDay->get($d) ?? 0);
        }

        return $out;
    }

    /**
     * @return array<int, float>
     */
    private function sparklineOrderCountsLast30Days(int|string|null $storeId): array
    {
        [$start, $end] = $this->last30DaysBounds();

        $query = Order::query()
            ->whereBetween('created_at', [$start, $end]);

        if ($storeId) {
            $query->whereHas('branch', fn ($q) => $q->where('store_id', $storeId));
        }

        $raw = $query
            ->selectRaw('DATE(created_at) as day, COUNT(*) as c')
            ->groupBy('day')
            ->pluck('c', 'day');

        return $this->buildNumericSeriesFromDaily($raw, 30);
    }

    /**
     * @return array<int, float>
     */
    private function sparklineOrderRevenueLast30Days(int|string|null $storeId): array
    {
        [$start, $end] = $this->last30DaysBounds();

        $query = Order::query()
            ->whereBetween('created_at', [$start, $end]);

        if ($storeId) {
            $query->whereHas('branch', fn ($q) => $q->where('store_id', $storeId));
        }

        $raw = $query
            ->selectRaw('DATE(created_at) as day, SUM(total) as total')
            ->groupBy('day')
            ->pluck('total', 'day');

        return $this->buildNumericSeriesFromDaily($raw, 30);
    }

    /**
     * @param  class-string<Model>  $modelClass
     * @return array<int, float>
     */
    private function sparklineModelCountsLast30Days(string $modelClass): array
    {
        [$start, $end] = $this->last30DaysBounds();

        $raw = $modelClass::query()
            ->whereBetween('created_at', [$start, $end])
            ->selectRaw('DATE(created_at) as day, COUNT(*) as c')
            ->groupBy('day')
            ->pluck('c', 'day');

        return $this->buildNumericSeriesFromDaily($raw, 30);
    }
}
