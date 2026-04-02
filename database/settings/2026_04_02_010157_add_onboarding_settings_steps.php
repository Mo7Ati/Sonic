<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('onboarding-settings.steps', []);
    }

    public function down(): void
    {
        $this->migrator->delete('onboarding-settings.steps');
    }
};
