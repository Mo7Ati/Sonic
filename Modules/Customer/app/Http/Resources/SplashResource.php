<?php

namespace Modules\Customer\Http\Resources;

use App\Models\Address;
use App\Models\Customer;
use App\Settings\AddressSettings;
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

        [$addresses, $lastUsedAddress] = $customer
            ? $this->forCustomer($customer)
            : $this->forGuest($request->header('X-Session-Id'));

        return [
            'customer' => CustomerResource::make($customer),
            'addresses' => AddressResource::collection($addresses),
            // 'lastUsedAddress' => AddressResource::make($lastUsedAddress),
            'platformAddressFields' => app(AddressSettings::class)->fields,
        ];
    }

    /**
     * @return array{0: Collection, 1: ?Address}
     */
    private function forCustomer(Customer $customer): array
    {
        $customer->load(['addresses' => fn($q) => $q->latest(), 'lastUsedAddress']);

        return [
            $customer->addresses,
            $customer->lastUsedAddress ?? $customer->addresses->first(),
        ];
    }

    /**
     * @return array{0: Collection|SupportCollection, 1: ?Address}
     */
    private function forGuest(?string $sessionId): array
    {
        if (!$sessionId) {
            return [collect(), null];
        }

        $addresses = Address::where('session_id', $sessionId)->latest()->get();

        return [$addresses, $addresses->first()];
    }
}
