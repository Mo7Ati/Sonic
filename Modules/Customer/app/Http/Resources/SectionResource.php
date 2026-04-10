<?php

namespace Modules\Customer\Http\Resources;

use App\Enums\PaymentStatusEnum;
use App\Enums\SectionEnum;
use App\Enums\SectionItemEnum;
use App\Models\Branch;
use App\Models\Group;
use App\Models\Order;
use App\Models\StoreCategory;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class SectionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return array_merge([
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'type' => $this->type(),
            'data' => $this->serializeSectionData(),
        ], $this->serializeForMergeData());
    }

    public function type()
    {
        if ($this->type === SectionEnum::LIST_ITEMS) {
            return "{$this->data['type']}_list_items";
        }

        return $this->type;
    }

    /**
     * Serialize section data based on type
     */
    private function serializeSectionData()
    {
        return match ($this->type?->value) {
            SectionEnum::MAIN_BANNERS->value,
            SectionEnum::SQUIRE_BANNERS->value,
            SectionEnum::RECTANGLE_BANNERS->value => $this->serializeItemsSection(),

            SectionEnum::WRITTEN_BANNER->value => $this->serializeWrittenBanner(),
            SectionEnum::STORE_CATEGORY->value => $this->serializeStoreCategory(),

            SectionEnum::SEARCH->value => $this->serializeSearch(),

            SectionEnum::LIST_ITEMS->value => $this->serializeListItemsSection(),

            SectionEnum::ACTIVE_ORDERS->value => $this->serializeActiveOrders(),
            SectionEnum::UN_PAID_ORDERS->value => $this->serializeUnPaidOrders(),

            default => null,
        };
    }

    public function serializeUnPaidOrders()
    {
        return [
            'orders_count' => Order::query()
                ->where([
                    'customer_id' => auth('customer')->id(),
                    'payment_status' => PaymentStatusEnum::UNPAID->value,
                ])->count(),
        ];
    }

    /**
     * Serialize sections that contain ordered items
     */
    private function serializeItemsSection()
    {
        return $this->items()
            ->ordered()
            ->get()
            ->mapInto(SectionItemResource::class);
    }

    /**
     * Serialize written banner section
     */
    private function serializeWrittenBanner()
    {
        return [
            'name' => $this->data['name'][app()->getLocale()],
            'text_color' => Arr::get($this->data, 'text_color'),
            'background_color' => Arr::get($this->data, 'background_color'),
        ];
    }

    /**
     * Serialize unrated orders section
     */
    private function serializeStoreCategory()
    {
        $storeCategories = StoreCategory::query()
            ->findMany($this->data['store_categories'] ?? [])
            ->mapInto(StoreCategoryResource::class)
            ->toArray();

        return [
            ...$storeCategories,
        ];
    }

    /**
     * Serialize search section
     */
    private function serializeSearch()
    {
        // Search section typically doesn't need additional data
        return null;
    }

    /**
     * Serialize list item section
     */
    private function serializeListItemsSection()
    {
        // Search section typically doesn't need additional data
        return $this->{'serializeFor'.Str::camel($this->data['type'])}();
    }

    public function serializeForStoreCategory()
    {
        $categoryId = $this->data['store_category_id'];

        $branches = Branch::query()
            ->withWhereHas('store', function ($query) use ($categoryId) {
                $query->whereHas('storeCategories', function ($cq) use ($categoryId) {
                    $cq->where('store_categories.id', $categoryId);
                });
            })
            ->get();

        return BranchResource::collection($branches)->resolve();
    }

    public function serializeForGroup()
    {
        $group = Group::query()->select('stores')->find($this->data['group_id']);
        $storeIds = $group->stores ?? [];
        $branches = Branch::query()
            ->with('store')
            ->whereHas('store', function ($query) use ($storeIds) {
                $query->whereIn('id', $storeIds);
            })
            ->get();

        return BranchResource::collection($branches)->resolve();
    }

    public function serializeForMergeData(): array
    {
        if ($this->type->value === SectionEnum::LIST_ITEMS->value) {

            return match ($this->data['type']) {
                SectionItemEnum::STORE_CATEGORY->value => ['store_category_id' => $this->data['store_category_id']],
                SectionItemEnum::GROUP->value => ['group_id' => $this->data['group_id']],
            };
        }

        //        return [
        //            'target' => $this->data['type'],
        //        ];
        return [];
    }

    public function serializeActiveOrders()
    {
        return Order::query()
            ->where('customer_id', auth('customer')->id())
            // ->active()
            ->get()
            ->map(fn ($order) => $order); // OrderResource::make($order)->serializeForActiveOrdersSection());
    }
}
