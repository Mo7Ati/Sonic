<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration {
    public function up(): void
    {
        $this->migrator->add('platform-settings.social_media', []);
    }

    public function down(): void
    {
        $this->migrator->delete('platform-settings.social_media');
    }
};
