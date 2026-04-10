<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Throwable;

class HealthController extends Controller
{
    /**
     * GET /api/health/db
     *
     * Tests both database connections and returns their statuses.
     */
    public function db(): JsonResponse
    {
        return response()->json([
            'mysql'  => $this->checkConnection('mysql'),
            'sqlsrv' => $this->checkConnection('sqlsrv'),
        ]);
    }

    private function checkConnection(string $connection): array
    {
        try {
            DB::connection($connection)->getPdo();

            return ['status' => 'ok'];
        } catch (Throwable $e) {
            return [
                'status'  => 'error',
                'message' => $e->getMessage(),
            ];
        }
    }
}
