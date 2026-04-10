<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\DrillLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DrillLogController extends Controller
{
    public function __construct(private DrillLogService $service) {}

    /**
     * GET /api/drill-logs
     *
     * Query parameters:
     *   from           – inclusive start datetime (e.g. 2024-01-01 or 2024-01-01T00:00:00)
     *   to             – inclusive end datetime
     *   computer_name  – partial match
     *   user           – partial match
     *   limit          – records per page (default 25)
     *   page           – page number (default 1)
     */
    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['from', 'to', 'computer_name', 'user']);
        $limit   = max(1, (int) $request->query('limit', 25));
        $page    = max(1, (int) $request->query('page', 1));

        $result = $this->service->search($filters, $limit, $page);

        return response()->json($result);
    }
}
