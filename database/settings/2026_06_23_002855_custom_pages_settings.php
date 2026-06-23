<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('custom-pages-settings.pages', []);
    }

    public function down(): void
    {
        $this->migrator->delete('custom-pages-settings.pages');
    }
};
