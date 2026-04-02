<?php

namespace App\Enums;

enum SectionEnum: string
{
    case MAIN_BANNERS = 'main_banners';
    case SQUIRE_BANNERS = 'square_banners';
    case RECTANGLE_BANNERS = 'rectangle_banners';
    case SEARCH = 'search';
    case STORE_CATEGORY = 'store_categories';
    case WRITTEN_BANNER = 'written_banner';
    case LIST_ITEMS = 'list_items';
    case ACTIVE_ORDERS = 'active_orders';
    case UN_PAID_ORDERS = 'un_paid_orders';

    public function getLabel(): string
    {
        return __('forms.section.types.'.$this->value);
    }

    public static function getOptions(): array
    {
        return collect(self::cases())->mapWithKeys(function ($case) {
            return [$case->value => $case->getLabel()];
        })->toArray();
    }
}
