<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    // use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        // Optional (slow, ~1–2 min): php artisan db:seed --class=DashboardScreenshotMetricsSeeder
        // Store panel (store@ps.com): php artisan db:seed --class=PsStoreDashboardMetricsSeeder
        $this->call([
            DemoStoreWithInventorySeeder::class,
            HomePageSectionsSeeder::class,
        ]);
    }
}
