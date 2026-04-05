<?php

namespace Database\Seeders;

use App\Enums\BranchStatusEnum;
use App\Enums\SectionEnum;
use App\Enums\SectionItemEnum;
use App\Models\Branch;
use App\Models\Group;
use App\Models\Section;
use App\Models\SectionItem;
use App\Models\Store;
use App\Models\StoreCategory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Seeds one active section per {@see SectionEnum} case for the customer home page (sections with no group).
 *
 * Removes existing ungrouped sections and their items first. Creates demo store categories, a store, a branch,
 * and a group where needed. Attaches placeholder banner images to banner items and store category images.
 *
 * Note: {@see DatabaseSeeder} uses {@see WithoutModelEvents}. When this seeder
 * is invoked via {@code $this->call()}, model events are muted; this class sets {@see Section} order explicitly
 * with {@see Section::withoutEvents()} so display order stays correct. Run alone with
 * {@code php artisan db:seed --class=HomePageSectionsSeeder} if you rely on other creating hooks.
 */
class HomePageSectionsSeeder extends Seeder
{
    /**
     * @return array{en: string, ar: string}
     */
    private function t(string $en, string $ar): array
    {
        return ['en' => $en, 'ar' => $ar];
    }

    public function run(): void
    {
        $this->purgeUngroupedHomeSections();

        $imagePath = $this->ensurePlaceholderBannerPath();

        $categoryFood = $this->seedStoreCategory(
            slug: 'seed-home-food',
            name: $this->t('Food & drinks', 'مأكولات ومشروبات'),
            description: $this->t('Restaurants and cafés', 'مطاعم ومقاهي'),
            imagePath: $imagePath,
        );

        $categoryRetail = $this->seedStoreCategory(
            slug: 'seed-home-retail',
            name: $this->t('Retail', 'تجزئة'),
            description: $this->t('Shops and markets', 'متاجر وأسواق'),
            imagePath: $imagePath,
        );

        $store = $this->seedStore($categoryFood, $imagePath);

        $this->seedBranch($store);

        $group = $this->seedGroup($store);

        $nextOrder = (int) (Section::query()->max('ordered') ?? 0);

        $mkSection = function (array $attributes) use (&$nextOrder): Section {
            $nextOrder++;

            return Section::withoutEvents(function () use ($attributes, $nextOrder): Section {
                return Section::query()->create(array_merge([
                    'group_id' => null,
                    'is_active' => true,
                    'ordered' => $nextOrder,
                ], $attributes));
            });
        };

        // Search (no images)
        $mkSection([
            'title' => $this->t('Search', 'بحث'),
            'description' => $this->t('Find stores and products', 'ابحث عن المتاجر والمنتجات'),
            'type' => SectionEnum::SEARCH,
            'data' => null,
        ]);

        // Main banners — one item per item type, each with image
        $mainBanners = $mkSection([
            'title' => $this->t('Main banners', 'البانرات الرئيسية'),
            'description' => $this->t('Hero carousel', 'سلايدر رئيسي'),
            'type' => SectionEnum::MAIN_BANNERS,
            'data' => null,
        ]);

        $this->createBannerItem($mainBanners, SectionItemEnum::EXTERNAL_LINK, 1, $imagePath, [
            'data' => ['external_link' => 'https://example.com/promo-main'],
        ]);

        $this->createBannerItem($mainBanners, SectionItemEnum::STORE, 2, $imagePath, [
            'store_id' => $store->id,
            'data' => [],
        ]);

        $this->createBannerItem($mainBanners, SectionItemEnum::GROUP, 3, $imagePath, [
            'group_id' => $group->id,
            'data' => [],
        ]);

        $this->createBannerItem($mainBanners, SectionItemEnum::STORE_CATEGORY, 4, $imagePath, [
            'store_category_id' => $categoryRetail->id,
            'data' => [],
        ]);

        // Square banners — titled tiles (image + translatable title in data)
        $squareBanners = $mkSection([
            'title' => $this->t('Square banners', 'بانرات مربعة'),
            'description' => $this->t('Shortcuts', 'اختصارات'),
            'type' => SectionEnum::SQUIRE_BANNERS,
            'data' => null,
        ]);

        $this->createBannerItem($squareBanners, SectionItemEnum::STORE_CATEGORY, 1, $imagePath, [
            'store_category_id' => $categoryFood->id,
            'data' => ['title' => $this->t('Food', 'أكل')],
        ]);

        $this->createBannerItem($squareBanners, SectionItemEnum::EXTERNAL_LINK, 2, $imagePath, [
            'data' => [
                'title' => $this->t('Offers', 'عروض'),
                'external_link' => 'https://example.com/offers',
            ],
        ]);

        // Rectangle banners
        $rectangleBanners = $mkSection([
            'title' => $this->t('Rectangle banners', 'بانرات مستطيلة'),
            'description' => $this->t('Wide promos', 'عروض عريضة'),
            'type' => SectionEnum::RECTANGLE_BANNERS,
            'data' => null,
        ]);

        $this->createBannerItem($rectangleBanners, SectionItemEnum::STORE, 1, $imagePath, [
            'store_id' => $store->id,
            'data' => [],
        ]);

        $this->createBannerItem($rectangleBanners, SectionItemEnum::GROUP, 2, $imagePath, [
            'group_id' => $group->id,
            'data' => [],
        ]);

        // Written banner (styling only, no upload)
        $mkSection([
            'title' => $this->t('Written banner', 'بانر نصي'),
            'description' => $this->t('Headline strip', 'شريط عنوان'),
            'type' => SectionEnum::WRITTEN_BANNER,
            'data' => [
                'name' => $this->t('Free delivery today', 'توصيل مجاني اليوم'),
                'text_color' => '#FFFFFF',
                'background_color' => '#1D4ED8',
            ],
        ]);

        // Store categories block (uses category media)
        $mkSection([
            'title' => $this->t('Shop by category', 'تسوق حسب التصنيف'),
            'description' => $this->t('Browse categories', 'تصفح التصنيفات'),
            'type' => SectionEnum::STORE_CATEGORY,
            'data' => [
                'store_categories' => [$categoryFood->id, $categoryRetail->id],
            ],
        ]);

        // List items — group (branches under group stores)
        $mkSection([
            'title' => $this->t('Featured group', 'مجموعة مميزة'),
            'description' => $this->t('Branches from a curated group', 'فروع من مجموعة مختارة'),
            'type' => SectionEnum::LIST_ITEMS,
            'data' => [
                'type' => SectionItemEnum::GROUP->value,
                'group_id' => $group->id,
            ],
        ]);

        // List items — store category (branches for stores in category)
        $mkSection([
            'title' => $this->t('Nearby food', 'طعام قريب'),
            'description' => $this->t('Branches in this category', 'فروع في هذا التصنيف'),
            'type' => SectionEnum::LIST_ITEMS,
            'data' => [
                'type' => SectionItemEnum::STORE_CATEGORY->value,
                'store_category_id' => $categoryFood->id,
            ],
        ]);

        // Order strips (no images; counts depend on authenticated customer)
        $mkSection([
            'title' => $this->t('Active orders', 'طلبات نشطة'),
            'description' => $this->t('In-progress orders', 'طلبات قيد التنفيذ'),
            'type' => SectionEnum::ACTIVE_ORDERS,
            'data' => null,
        ]);

        $mkSection([
            'title' => $this->t('Unpaid orders', 'طلبات غير مدفوعة'),
            'description' => $this->t('Awaiting payment', 'في انتظار الدفع'),
            'type' => SectionEnum::UN_PAID_ORDERS,
            'data' => null,
        ]);
    }

    private function purgeUngroupedHomeSections(): void
    {
        $sections = Section::query()->withTrashed()->whereNull('group_id')->get();

        foreach ($sections as $section) {
            $items = SectionItem::query()->withTrashed()->where('section_id', $section->id)->get();

            foreach ($items as $item) {
                $item->clearMediaCollection('section-item');
                $item->forceDelete();
            }

            $section->forceDelete();
        }
    }

    private function seedStoreCategory(string $slug, array $name, array $description, string $imagePath): StoreCategory
    {
        $category = StoreCategory::withoutEvents(function () use ($slug, $name, $description): StoreCategory {
            return StoreCategory::query()->updateOrCreate(
                ['slug' => $slug],
                [
                    'slug' => $slug,
                    'name' => $name,
                    'description' => $description,
                ],
            );
        });

        $category->clearMediaCollection('store_categories_images');
        $category->addMedia($imagePath)
            ->preservingOriginal()
            ->usingFileName(Str::slug($slug).'-'.Str::random(6).'.jpg')
            ->toMediaCollection('store_categories_images');

        return $category;
    }

    private function seedStore(StoreCategory $category, string $imagePath): Store
    {
        $email = 'seed-homepage-store@example.test';

        $store = Store::query()->updateOrCreate(
            ['email' => $email],
            [
                'name' => $this->t('Seed Marketplace Store', 'متجر تجريبي'),
                'slug' => 'seed-homepage-store',
                'description' => $this->t('Demo store for home sections', 'متجر تجريبي للصفحة الرئيسية'),
                'keywords' => $this->t('demo, seed', 'تجريبي'),
                'social_media' => [],
                'phone' => '+10000000001',
                'password' => Hash::make('password'),
                'category_id' => $category->id,
                'is_active' => true,
            ],
        );

        if ($store->getMedia('store_images')->isEmpty()) {
            $store->addMedia($imagePath)
                ->preservingOriginal()
                ->usingFileName('seed-store-'.Str::random(6).'.jpg')
                ->toMediaCollection('store_images');
        }

        if ($store->getMedia('store_cover_images')->isEmpty()) {
            $store->addMedia($imagePath)
                ->preservingOriginal()
                ->usingFileName('seed-store-cover-'.Str::random(6).'.jpg')
                ->toMediaCollection('store_cover_images');
        }

        return $store;
    }

    private function seedBranch(Store $store): Branch
    {
        return Branch::query()->updateOrCreate(
            ['store_id' => $store->id],
            [
                'name' => $this->t('Seed Main Branch', 'فرع تجريبي رئيسي'),
                'address' => $this->t('123 Demo Street', '١٢٣ شارع تجريبي'),
                'delivery_time_from' => 30,
                'delivery_time_to' => 60,
                'delivery_fee' => 2.5,
                'status' => BranchStatusEnum::AVAILABLE,
                'is_active' => true,
                'range_of_area_polygon' => null,
                'location' => [
                    'latitude' => '24.7136',
                    'longitude' => '46.6753',
                ],
            ],
        );
    }

    private function seedGroup(Store $store): Group
    {
        return Group::query()->updateOrCreate(
            ['name->en' => 'Seed Home Page Group'],
            [
                'name' => $this->t('Seed Home Page Group', 'مجموعة الصفحة الرئيسية'),
                'stores' => [$store->id],
                'is_active' => true,
            ],
        );
    }

    /**
     * @param  array<string, mixed>  $extra
     */
    private function createBannerItem(Section $section, SectionItemEnum $type, int $ordered, string $imagePath, array $extra): SectionItem
    {
        /** @var SectionItem $item */
        $item = SectionItem::query()->create(array_merge([
            'section_id' => $section->id,
            'type' => $type,
            'ordered' => $ordered,
            'is_active' => true,
        ], $extra));

        $item->addMedia($imagePath)
            ->preservingOriginal()
            ->usingFileName('section-item-'.$section->id.'-'.$ordered.'-'.Str::random(4).'.jpg')
            ->toMediaCollection('section-item');

        return $item;
    }

    private function ensurePlaceholderBannerPath(): string
    {
        $directory = storage_path('app/seeders');
        File::ensureDirectoryExists($directory);

        $path = $directory.'/home-page-banner-placeholder.jpg';

        if (File::exists($path)) {
            return $path;
        }

        if (function_exists('imagecreatetruecolor')) {
            $image = imagecreatetruecolor(800, 450);
            $background = imagecolorallocate($image, 37, 99, 235);
            $accent = imagecolorallocate($image, 147, 197, 253);
            imagefilledrectangle($image, 0, 0, 800, 450, $background);
            imagefilledrectangle($image, 40, 160, 760, 290, $accent);
            imagejpeg($image, $path, 88);
            imagedestroy($image);
        } else {
            $oneByOnePng = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==');
            File::put($path, $oneByOnePng);
        }

        return $path;
    }
}
