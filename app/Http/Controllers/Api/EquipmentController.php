<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\EquipmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EquipmentController extends Controller
{
    public function __construct(private EquipmentService $service) {}

    /**
     * GET /api/equipment
     *
     * Query parameters:
     *   computer_name  – partial match
     *   serial_number  – partial match
     *   user           – partial match
     *   company        – partial match
     *   plant          – partial match
     *   limit          – records per page (default 25)
     *   page           – page number (default 1)
     */
    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['computer_name', 'serial_number', 'user', 'company', 'plant']);
        $limit   = max(1, (int) $request->query('limit', 25));
        $page    = max(1, (int) $request->query('page', 1));

        $result = $this->service->search($filters, $limit, $page);

        return response()->json($result);
    }
}
