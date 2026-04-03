<?php

namespace Modules\Customer\app\Http\Resources;

use App\Enums\SectionEnum;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Request;
use App\Enums\SectionItemEnum;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class SectionItemResource extends JsonResource
{
    /**
     * Cache for serialized data to avoid repeated processing
     */
    private ?array $serializedData = null;

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'data' => array_merge($this->getSerializedItemData(), $this->mergedData()),
        ];
    }

    public function mergedData(): array
    {
        if ($this->section->type === SectionEnum::SQUIRE_BANNERS)
            return ['name' => $this->data['title'][app()->getLocale() ?? 'ar']];
        return [];
    }

    /**
     * Get serialized item data with caching
     */
    private function getSerializedItemData(): ?array
    {
        if ($this->serializedData === null) {
            $this->serializedData = $this->serializeItemData();
        }

        return $this->serializedData;
    }

    /**
     * Serialize item data based on type
     */
    private function serializeItemData(): ?array
    {
        if (!$this->type) {
            return null;
        }

        return match ($this->type->value) {
            SectionItemEnum::GROUP->value => $this->serializeGroup(),
            SectionItemEnum::STORE->value => $this->serializeStore(),
            SectionItemEnum::STORE_CATEGORY->value => $this->serializeStoreCategory(),
            SectionItemEnum::EXTERNAL_LINK->value => $this->serializeExternalLink(),
            default => null,
        };
    }

    /**
     * Get common image data
     */
    private function getImageData(): array
    {
        $mediaUrl = $this->getFirstMediaUrl('section-item') ?: $this->getFirstMediaUrl();
        $dataImage = Arr::get($this->data, 'image');

        return [
            'image' => $mediaUrl ?: ($dataImage),
        ];
    }

    public function serializeCooperative(): array
    {
        return array_merge($this->getImageData(), [
            'cooperative_id' => $this->cooperative_id,
            'name' => $this->cooperative?->name
        ]);
    }

    public function serializeCooperatives(): array
    {
        return array_merge($this->getImageData(), [
            'governorate_id' => $this->governorate_id,
            'name' => $this->governorate
                ? __('forms.section_item.cooperatives_of_governorate', ['governorate' => $this->governorate->name])
                : __('forms.section_item.all_cooperatives'),
        ]);
    }

    /**
     * Serialize group item
     */
    private function serializeGroup(): array
    {
        return array_merge($this->getImageData(), [
            'group_id' => $this->group_id,
            'name' => $this->group?->name
        ]);
    }

    /**
     * Serialize store item
     */
    private function serializeStore(): array
    {
        return array_merge($this->getImageData(), [
            'store_id' => $this->store_id,
            'name' => $this->store?->name
        ]);
    }

    /**
     * Serialize store category
     */
    private function serializeStoreCategory(): array
    {
        return array_merge($this->getImageData(), [
            'store_category_id' => $this->store_category_id,
            'name' => $this->storeCategory?->name
        ]);
    }

    /**
     * Serialize external link item
     */
    private function serializeExternalLink(): array
    {
        return array_merge($this->getImageData(), [
            'external_link' => Arr::get($this->data, 'external_link'),
        ]);
    }
}
