<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    use ApiResponseTrait;

    /**
     * GET /api/v1/activity-logs
     */
    public function index(Request $request): JsonResponse
    {
        if (request()->user()->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized. Only admins can view activity logs.'], 403);
        }

        $logs = ActivityLog::with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return $this->successResponse('Activity logs retrieved successfully', $logs);
    }
}
