<?php

use App\Http\Controllers\AssetController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\SyncController;
use Illuminate\Support\Facades\Route;

Route::post('/sync', [SyncController::class, 'store']);

Route::get('/assets', [AssetController::class, 'index']);
Route::get('/assets/{asset}', [AssetController::class, 'show']);
Route::delete('/assets/{asset}', [AssetController::class, 'destroy']);

Route::get('/employees', [EmployeeController::class, 'index']);
Route::delete('/employees/{employee}', [EmployeeController::class, 'destroy']);
