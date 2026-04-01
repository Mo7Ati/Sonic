<?php

namespace App\Enums;

enum SectionItemEnum: string
{
    case GROUP = 'group';
    case STORE = 'store';
    case STORE_CATEGORY = 'store_category';
    case EXTERNAL_LINK = 'external_link';

    public function getLabel(): string
    {
        return __('forms.section_item.types.' . $this->value);
    }

    public static function getOptions(): array
    {
        return collect(self::cases())->mapWithKeys(function ($case) {
            return [$case->value => $case->getLabel()];
        })->toArray();
    }
}
