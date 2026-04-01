<?php

namespace App\Enums;

enum HomePageSectionsType: string
{
    case HERO = 'hero';
    case FEATURES = 'features';
    case PRODUCTS = 'products';
    case CATEGORIES = 'categories';
    case STORES = 'stores';
    case VENDOR_CTA = 'vendor_cta';

    public function getLabel(): string
    {
        return match ($this) {
            self::HERO => 'Hero',
            self::FEATURES => 'Features',
            self::PRODUCTS => 'Products',
            self::CATEGORIES => 'Categories',
            self::STORES => 'Stores',
            self::VENDOR_CTA => 'Vendor CTA',
        };
    }

    public static function getOptions(): array
    {
        return collect(self::cases())->mapWithKeys(function ($case) {
            return [$case->value => $case->getLabel()];
        })->toArray();
    }
}
