<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('address-settings.fields', []);
    }

    public function down(): void
    {
        $this->migrator->delete('address-settings.fields');
    }
};
