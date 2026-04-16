<?php

use Illuminate\Http\Request;
use App\Http\Controllers\Api\DrillLogController;
use App\Http\Controllers\Api\EquipmentController;
use App\Http\Controllers\Api\HealthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
Route::get('/equipment',   [EquipmentController::class, 'index']);
Route::get('/drill-logs',  [DrillLogController::class, 'index']);
Route::get('/health/db',   [HealthController::class, 'db']);

