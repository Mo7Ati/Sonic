<?php

namespace Modules\Cashier\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Cashier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Modules\Cashier\Http\Requests\Auth\LoginRequest;
use Modules\Cashier\Http\Resources\CashierResource;

class AuthController extends Controller
{
    public function login(LoginRequest $request): JsonResponse
    {
        $cashier = Cashier::where('email', $request->email)->first();

        if (! $cashier || ! Hash::check($request->password, $cashier->password)) {
            return errorResponse(__('auth.failed'), 401);
        }

        Auth::guard('cashier')->login($cashier);
        $request->session()->regenerate();

        return successResponse(
            new CashierResource($cashier->load('branch')),
            'Login successful.'
        );
    }

    public function logout(Request $request): JsonResponse
    {
        Auth::guard('cashier')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return successResponse(null, 'Logged out successfully.');
    }

    public function user(Request $request): JsonResponse
    {
        $cashier = Auth::guard('cashier')->user();
        $cashier->load('branch');

        return successResponse(new CashierResource($cashier));
    }
}
