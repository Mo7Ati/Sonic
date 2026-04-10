<?php

namespace Database\Seeders;

use App\Enums\BranchStatusEnum;
use App\Models\Branch;
use App\Models\Store;
use Illuminate\Database\Seeder;
use RuntimeException;

/**
 * Seeds 50 branches for the store with email {@see self::STORE_EMAIL}.
 *
 * Re-running soft-deletes all branches for this store, then inserts these 50 rows again.
 */
class PsStoreBranchesSeeder extends Seeder
{
    private const string STORE_EMAIL = 'store@ps.com';

    /**
     * Base coordinates (Riyadh); each branch offsets slightly for distinct map pins.
     */
    private const string BASE_LATITUDE = '24.7136';

    private const string BASE_LONGITUDE = '46.6753';

    public function run(): void
    {
        $store = Store::query()->where('email', self::STORE_EMAIL)->first();

        if ($store === null) {
            throw new RuntimeException('No store found with email '.self::STORE_EMAIL.'. Create the store first.');
        }

        Branch::query()->where('store_id', $store->id)->delete();

        foreach (self::branchDefinitions() as $index => $def) {
            $offset = ($index + 1) * 0.002;

            Branch::query()->create([
                'store_id' => $store->id,
                'name' => ['en' => $def['en'], 'ar' => $def['ar']],
                'address' => ['en' => $def['address_en'], 'ar' => $def['address_ar']],
                'delivery_time_from' => 25 + ($index % 20),
                'delivery_time_to' => 50 + ($index % 25),
                'delivery_fee' => round(1.5 + ($index % 10) * 0.5, 2),
                'status' => BranchStatusEnum::AVAILABLE,
                'is_active' => true,
                'range_of_area_polygon' => null,
                'location' => [
                    'latitude' => (string) round((float) self::BASE_LATITUDE + $offset * 0.01, 6),
                    'longitude' => (string) round((float) self::BASE_LONGITUDE + $offset * 0.01, 6),
                ],
            ]);
        }
    }

    /**
     * @return list<array{en: string, ar: string, address_en: string, address_ar: string}>
     */
    private static function branchDefinitions(): array
    {
        return [
            ['en' => 'Al Olaya — flagship branch', 'ar' => 'العلياء — الفرع الرئيسي', 'address_en' => 'King Fahd Rd, Al Olaya', 'address_ar' => 'طريق الملك فهد، العلياء'],
            ['en' => 'Al Malaz — city branch', 'ar' => 'الملز — فرع المدينة', 'address_en' => 'Al Malaz district, Riyadh', 'address_ar' => 'حي الملز، الرياض'],
            ['en' => 'Diplomatic Quarter — express', 'ar' => 'الحي الدبلوماسي — سريع', 'address_en' => 'DQ, Riyadh', 'address_ar' => 'الحي الدبلوماسي، الرياض'],
            ['en' => 'Al Muhammadiyah — showroom', 'ar' => 'المحمدية — معرض', 'address_en' => 'Al Muhammadiyah', 'address_ar' => 'حي المحمدية'],
            ['en' => 'Al Sahafah — pickup point', 'ar' => 'الصحافة — نقطة استلام', 'address_en' => 'Al Sahafah', 'address_ar' => 'حي الصحافة'],
            ['en' => 'Al Yasmin — mall front', 'ar' => 'الياسمين — واجهة المجمع', 'address_en' => 'Al Yasmin', 'address_ar' => 'حي الياسمين'],
            ['en' => 'Al Muruj — corner unit', 'ar' => 'المروج — زاوية', 'address_en' => 'Al Muruj', 'address_ar' => 'حي المروج'],
            ['en' => 'Al Rabwah — drive service', 'ar' => 'الربوة — خدمة السيارات', 'address_en' => 'Al Rabwah', 'address_ar' => 'حي الربوة'],
            ['en' => 'Al Rawdah — family hall', 'ar' => 'الروضة — قاعة العائلات', 'address_en' => 'Al Rawdah', 'address_ar' => 'حي الروضة'],
            ['en' => 'Al Wurud — garden side', 'ar' => 'الورود — جهة الحديقة', 'address_en' => 'Al Wurud', 'address_ar' => 'حي الورود'],
            ['en' => 'An Narjis — north outlet', 'ar' => 'النرجس — منفذ الشمال', 'address_en' => 'An Narjis', 'address_ar' => 'حي النرجس'],
            ['en' => 'Hittin — boulevard', 'ar' => 'حطين — الشارع الرئيسي', 'address_en' => 'Hittin', 'address_ar' => 'حي حطين'],
            ['en' => 'Ar Rabi — district hub', 'ar' => 'الربيع — مركز الحي', 'address_en' => 'Ar Rabi', 'address_ar' => 'حي الربيع'],
            ['en' => 'Qurtubah — east wing', 'ar' => 'قرطبة — الجناح الشرقي', 'address_en' => 'Qurtubah', 'address_ar' => 'حي قرطبة'],
            ['en' => 'Ishbiliyah — plaza level', 'ar' => 'إشبيلية — مستوى المجمع', 'address_en' => 'Ishbiliyah', 'address_ar' => 'حي إشبيلية'],
            ['en' => 'Al Izdihar — clinic row', 'ar' => 'الازدهار — صف العيادات', 'address_en' => 'Al Izdihar', 'address_ar' => 'حي الازدهار'],
            ['en' => 'Granada — retail strip', 'ar' => 'الجراندة — شريط تجاري', 'address_en' => 'Granada, Riyadh', 'address_ar' => 'الجراندة، الرياض'],
            ['en' => 'An Nakheel — kiosk A', 'ar' => 'النخيل — كشك أ', 'address_en' => 'An Nakheel', 'address_ar' => 'حي النخيل'],
            ['en' => 'Takhassusi — medical city', 'ar' => 'التخصصي — المدينة الطبية', 'address_en' => 'Takhassusi St', 'address_ar' => 'شارع التخصصي'],
            ['en' => 'KAFD — tower lobby', 'ar' => 'كافد — بهو البرج', 'address_en' => 'King Abdullah Financial District', 'address_ar' => 'حي الملك عبدالله المالي'],
            ['en' => 'Digital City — tech park', 'ar' => 'المدينة الرقمية — الحديقة التقنية', 'address_en' => 'Digital City', 'address_ar' => 'المدينة الرقمية'],
            ['en' => 'Airport Road — cargo gate', 'ar' => 'طريق المطار — بوابة الشحن', 'address_en' => 'Airport Rd service lane', 'address_ar' => 'طريق المطار، مسار الخدمة'],
            ['en' => 'Southern Ring — depot', 'ar' => 'الدائري الجنوبي — مستودع', 'address_en' => 'Southern Ring Rd access', 'address_ar' => 'مدخل الدائري الجنوبي'],
            ['en' => 'Northern Ring — hub', 'ar' => 'الدائري الشمالي — مركز', 'address_en' => 'Northern Ring Rd hub', 'address_ar' => 'مركز الدائري الشمالي'],
            ['en' => 'Eastern Ring — express lane', 'ar' => 'الدائري الشرقي — مسار سريع', 'address_en' => 'Eastern Ring Rd', 'address_ar' => 'الدائري الشرقي'],
            ['en' => 'Exit 5 — service road', 'ar' => 'مخرج 5 — طريق الخدمة', 'address_en' => 'Near exit 5', 'address_ar' => 'قرب مخرج 5'],
            ['en' => 'Exit 8 — strip mall', 'ar' => 'مخرج 8 — مجمع تجاري', 'address_en' => 'Exit 8 retail strip', 'address_ar' => 'مخرج 8، شريط تجاري'],
            ['en' => 'Exit 10 — community plaza', 'ar' => 'مخرج 10 — ساحة الحي', 'address_en' => 'Exit 10 plaza', 'address_ar' => 'مخرج 10، الساحة'],
            ['en' => 'Al Sulaymaniyah — café row', 'ar' => 'السليمانية — صف المقاهي', 'address_en' => 'Al Sulaymaniyah', 'address_ar' => 'حي السليمانية'],
            ['en' => 'Al Maathar — office tower', 'ar' => 'المعذر — برج مكاتب', 'address_en' => 'Al Maathar', 'address_ar' => 'حي المعذر'],
            ['en' => 'Al Faisaliyah — basement level', 'ar' => 'الفيصلية — المستوى السفلي', 'address_en' => 'Near Al Faisaliyah Center', 'address_ar' => 'قرب برج الفيصلية'],
            ['en' => 'Al Batha — souk entrance', 'ar' => 'البطحاء — مدخل السوق', 'address_en' => 'Al Batha market area', 'address_ar' => 'منطقة سوق البطحاء'],
            ['en' => 'Al Dirah — heritage block', 'ar' => 'الديرة — بلوك التراث', 'address_en' => 'Al Dirah old town', 'address_ar' => 'الديرة، المدينة القديمة'],
            ['en' => 'Al Marqab — station front', 'ar' => 'المرقب — أمام المحطة', 'address_en' => 'Al Marqab', 'address_ar' => 'حي المرقب'],
            ['en' => 'Al Futah — central market', 'ar' => 'الفوطة — السوق المركزي', 'address_en' => 'Al Futah', 'address_ar' => 'حي الفوطة'],
            ['en' => 'Al Salam — family seating', 'ar' => 'السلام — جلوس عائلي', 'address_en' => 'Al Salam district', 'address_ar' => 'حي السلام'],
            ['en' => 'Al Taawun — community hub', 'ar' => 'التعاون — مركز الحي', 'address_en' => 'Al Taawun', 'address_ar' => 'حي التعاون'],
            ['en' => 'Al Khaleej — coastal style walk', 'ar' => 'الخليج — ممشى', 'address_en' => 'Al Khaleej', 'address_ar' => 'حي الخليج'],
            ['en' => 'Al Rehab — residential cluster', 'ar' => 'الرحاب — مجموعة سكنية', 'address_en' => 'Al Rehab', 'address_ar' => 'حي الرحاب'],
            ['en' => 'Al Aqiq — garden district', 'ar' => 'العقيق — حي الحدائق', 'address_en' => 'Al Aqiq', 'address_ar' => 'حي العقيق'],
            ['en' => 'Al Ghadeer — lake walk', 'ar' => 'الغدير — ممشى البحيرة', 'address_en' => 'Al Ghadeer', 'address_ar' => 'حي الغدير'],
            ['en' => 'Al Nada — sports complex', 'ar' => 'الندى — المجمع الرياضي', 'address_en' => 'Al Nada', 'address_ar' => 'حي الندى'],
            ['en' => 'Al Janadriyah — seasonal tent', 'ar' => 'الجنادرية — خيمة موسمية', 'address_en' => 'Al Janadriyah festival zone', 'address_ar' => 'منطقة مهرجان الجنادرية'],
            ['en' => 'Al Bandariyah — marina view', 'ar' => 'البندرية — إطلالة المارينا', 'address_en' => 'Al Bandariyah', 'address_ar' => 'حي البندرية'],
            ['en' => 'Al Manar — lighthouse plaza', 'ar' => 'المنار — ساحة المنارة', 'address_en' => 'Al Manar', 'address_ar' => 'حي المنار'],
            ['en' => 'Al Khozama — VIP lounge', 'ar' => 'الخزامى — صالة كبار', 'address_en' => 'Al Khozama', 'address_ar' => 'حي الخزامى'],
            ['en' => 'Corporate HQ — scheduled pickup', 'ar' => 'المقر الرئيسي — استلام مجدول', 'address_en' => 'PS corporate HQ, Riyadh', 'address_ar' => 'مقر الشركة، الرياض'],
            ['en' => 'Warehouse district — bulk orders', 'ar' => 'منطقة المستودعات — طلبات بالجملة', 'address_en' => 'Industrial service rd, Riyadh', 'address_ar' => 'طريق الخدمات الصناعية'],
            ['en' => 'City center — 24h counter', 'ar' => 'وسط المدينة — كاونتر 24 ساعة', 'address_en' => 'Downtown Riyadh', 'address_ar' => 'وسط الرياض'],
            ['en' => 'University district — student lane', 'ar' => 'حي الجامعات — ممر الطلاب', 'address_en' => 'Near university ring', 'address_ar' => 'قرب حلقة الجامعات'],
        ];
    }
}
