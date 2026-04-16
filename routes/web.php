<?php

<<<<<<< HEAD
use App\Http\Controllers\AdminDrillController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DrillController;
use App\Http\Controllers\EducationController;
use App\Http\Controllers\EducationMaterialController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\ProgressDrillController;
use App\Http\Controllers\ResultController;
use App\Http\Controllers\SummaryReportController;
=======
>>>>>>> rafi
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/
<<<<<<< HEAD
Route::get('/', [AuthController::class, 'showLogin']);
Route::post('/login', [AuthController::class, 'login']);

Route::get('/menu', [MenuController::class, 'index']);
Route::get('/education', [EducationController::class, 'index']);
Route::post('/education/materials', [EducationMaterialController::class, 'store']);
Route::delete('/education/materials/{id}', [EducationMaterialController::class, 'destroy']);
Route::get('/drill', [DrillController::class, 'index']);
Route::post('/drill/complete', [DrillController::class, 'complete']);
Route::get('/drill/video', [DrillController::class, 'videoPlayer']);
Route::get('/drill/video/stream', [DrillController::class, 'video']);
Route::get('/my-result', [ResultController::class, 'index']);
Route::get('/progress-drill', [ProgressDrillController::class, 'index']);

// Admin drill scheduling
Route::get('/admin/drill', [AdminDrillController::class, 'index']);
Route::get('/admin/summary-report', [SummaryReportController::class, 'index']);
Route::post('/admin/drill/self-service', [AdminDrillController::class, 'saveSelfService']);
Route::post('/admin/drill/schedule', [AdminDrillController::class, 'saveScheduleDrill']);
Route::post('/admin/drill/drills/{id}', [AdminDrillController::class, 'updateDrill']);
Route::post('/admin/drill/drills/{id}/delete', [AdminDrillController::class, 'destroyDrill']);

Route::post('/logout', [AuthController::class, 'logout']);
=======

Route::get('/', function () {
    return response()->json(['status' => 'ok', 'message' => 'infosec API is running.']);
});

>>>>>>> rafi
