<?php

namespace App\Enums;

enum SectionEnum: string
{
    case MAIN_BANNERS = 'main_banners';
    case STORE_CATEGORIES = 'store_categories';
    case PRODUCTS = 'products';
    case STORES = 'stores';
    case FEATURES = 'features';

    public function getLabel(): string
    {
        return __('forms.section.types.' . $this->value);
    }

    public static function getOptions(): array
    {
        return collect(self::cases())->mapWithKeys(function ($case) {
            return [$case->value => $case->getLabel()];
        })->toArray();
    }
}
