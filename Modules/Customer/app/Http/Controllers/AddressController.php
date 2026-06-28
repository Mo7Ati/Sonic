<?php

namespace Modules\Customer\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Settings\AddressSettings;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Customer\Http\Requests\Address\StoreAddressRequest;
use Modules\Customer\Http\Requests\Address\UpdateAddressRequest;
use Modules\Customer\Http\Resources\AddressResource;

class AddressController extends Controller
{
    /**
     * The platform-defined address field templates used to render the address form.
     */
    public function fields(): JsonResponse
    {
        return successResponse(
            app(AddressSettings::class)->fields,
            __('messages.data_retrieved_successfully'),
        );
    }

    /**
     * List all addresses for the current customer or guest session.
     */
    public function index(Request $request): JsonResponse
    {
        $addresses = Address::resolveQueryFor($request)->latest()->get();

        return successResponse(
            AddressResource::collection($addresses),
            __('messages.data_retrieved_successfully'),
        );
    }

    /**
     * Show a single address.
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $address = Address::resolveQueryFor($request)->findOrFail($id);

        return successResponse(
            AddressResource::make($address),
            __('messages.data_retrieved_successfully'),
        );
    }

    /**
     * Store a new address.
     */
    public function store(StoreAddressRequest $request): JsonResponse
    {
        $data = [
            'name' => $request->input('name'),
            'fields' => $request->addressFields(),
        ];

        if ($request->user('sanctum')) {
            $data['customer_id'] = $request->user('sanctum')->id;
        } else {
            $data['session_id'] = $request->header('X-Session-Id');
        }

        $address = Address::create($data);

        return successResponse(
            AddressResource::make($address),
            __('messages.address_created_successfully'),
            201,
        );
    }

    /**
     * Update an existing address.
     */
    public function update(UpdateAddressRequest $request, int $id): JsonResponse
    {
        $address = Address::resolveQueryFor($request)->findOrFail($id);

        $address->update([
            'name' => $request->input('name'),
            'fields' => $request->addressFields(),
        ]);

        return successResponse(
            AddressResource::make($address),
            __('messages.address_updated_successfully'),
        );
    }

    /**
     * Delete an address.
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $address = Address::resolveQueryFor($request)->findOrFail($id);
        $address->delete();

        return successResponse(null, __('messages.address_deleted_successfully'));
    }
}
