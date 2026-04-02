<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class OnboardingSettings extends Settings
{
    public array $steps = [];

    public static function group(): string
    {
        return 'onboarding-settings';
    }
}
