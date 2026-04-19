<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class AddressSettings extends Settings
{
    public array $fields = [];

    public static function group(): string
    {
        return 'address-settings';
    }
}
