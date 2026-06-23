<?php

namespace Modules\Customer\Http\Resources;

use App\Models\Address;
use App\Models\Customer;
use App\Settings\AddressSettings;
use App\Settings\CustomPages;
use App\Settings\OnboardingSettings;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection as SupportCollection;
use Illuminate\Support\Facades\Auth;

class SplashResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $customer = Auth::guard('sanctum')->user();

        [$addresses] = $customer
            ? $this->forCustomer($customer)
            : $this->forGuest($request->header('X-Session-Id'));

        $customPages = app(CustomPages::class)->pages ?? [];

        return [
            'customer' => CustomerResource::make($customer),
            'addresses' => AddressResource::collection($addresses),
            'platformAddressFields' => app(AddressSettings::class)->fields,
            'onboardingSlides' => OnboardingResource::collection(app(OnboardingSettings::class)->steps),
            'customPages' => CustomPagesResource::collection($customPages),
        ];
    }

    /**
     * @return array{0: Collection, 1: ?Address}
     */
    private function forCustomer(Customer $customer): array
    {
        $customer->load(['addresses' => fn ($q) => $q->latest()]);

        return [
            $customer->addresses,
        ];
    }

    /**
     * @return array{0: Collection|SupportCollection}
     */
    private function forGuest(?string $sessionId): array
    {
        if (! $sessionId) {
            return [collect(), null];
        }

        $addresses = Address::where('session_id', $sessionId)->latest()->get();

        return [$addresses];
    }
}
