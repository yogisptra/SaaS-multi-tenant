<?php

namespace App\Http\Controllers;

use App\Http\Resources\NotificationResource;
use App\Models\Notification;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    use ApiResponseTrait;

    /**
     * GET /api/v1/notifications
     * Ambil notifikasi milik user yang login.
     */
    public function index(Request $request): JsonResponse
    {
        $notifications = Notification::where('user_id', auth('api')->id())
            ->orderByDesc('created_at')
            ->paginate($request->get('per_page', 15));

        return $this->successResponse(
            'Notifications retrieved successfully',
            NotificationResource::collection($notifications)->response()->getData(true)
        );
    }

    /**
     * PATCH /api/v1/notifications/{id}/read
     * Tandai notifikasi sebagai sudah dibaca.
     */
    public function markAsRead(int $id): JsonResponse
    {
        $notification = Notification::where('user_id', auth('api')->id())
            ->where('id', $id)
            ->first();

        if (!$notification) {
            return $this->errorResponse('Notification not found', 404);
        }

        $notification->update(['is_read' => true]);

        return $this->successResponse(
            'Notification marked as read',
            new NotificationResource($notification)
        );
    }

    /**
     * PATCH /api/v1/notifications/read-all
     * Tandai semua notifikasi sebagai sudah dibaca.
     */
    public function markAllAsRead(): JsonResponse
    {
        Notification::where('user_id', auth('api')->id())
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return $this->successResponse('All notifications marked as read');
    }
}
