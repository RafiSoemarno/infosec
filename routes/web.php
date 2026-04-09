<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DrillController;
use App\Http\Controllers\EducationController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\ProgressDrillController;
use App\Http\Controllers\ResultController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', [AuthController::class, 'showLogin']);
Route::post('/login', [AuthController::class, 'login']);

Route::get('/menu', [MenuController::class, 'index']);
Route::get('/education', [EducationController::class, 'index']);
Route::get('/drill', [DrillController::class, 'index']);
Route::post('/drill/complete', [DrillController::class, 'complete']);
Route::get('/drill/video', [DrillController::class, 'videoPlayer']);
Route::get('/drill/video/stream', [DrillController::class, 'video']);
Route::get('/my-result', [ResultController::class, 'index']);
Route::get('/progress-drill', [ProgressDrillController::class, 'index']);

Route::post('/logout', [AuthController::class, 'logout']);
