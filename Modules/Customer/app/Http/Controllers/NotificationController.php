<?php

namespace Modules\Customer\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Customer\Http\Resources\NotificationResource;

class NotificationController extends Controller
{
    /**
     * Paginated list of the customer's notifications, newest first.
     */
    public function index(Request $request): JsonResponse
    {
        $notifications = $request->user()
            ->notifications()
            ->paginate($request->integer('per_page', 20));

        return successResponse(
            NotificationResource::collection($notifications)->response()->getData(true),
            __('messages.data_retrieved_successfully'),
        );
    }

    /**
     * Number of unread notifications, for the bell badge.
     */
    public function unreadCount(Request $request): JsonResponse
    {
        return successResponse(
            ['count' => $request->user()->unreadNotifications()->count()],
            __('messages.data_retrieved_successfully'),
        );
    }

    /**
     * Mark a single notification as read.
     */
    public function markAsRead(Request $request, string $id): JsonResponse
    {
        $notification = $request->user()->notifications()->findOrFail($id);

        $notification->markAsRead();

        return successResponse(null, __('messages.data_updated_successfully'));
    }

    /**
     * Mark every unread notification as read.
     */
    public function markAllAsRead(Request $request): JsonResponse
    {
        $request->user()->unreadNotifications->markAsRead();

        return successResponse(null, __('messages.data_updated_successfully'));
    }
}
