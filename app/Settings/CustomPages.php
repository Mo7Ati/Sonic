<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class CustomPages extends Settings
{
    public array $pages = [];

    public static function group(): string
    {
        return 'custom-pages-settings';
    }
}
