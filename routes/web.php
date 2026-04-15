<?php

use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\HistoryController;
use App\Http\Controllers\LinkController;
use App\Http\Controllers\RedirectController;
use Illuminate\Support\Facades\Route;

Route::get('/', [LinkController::class, 'index']);
Route::post('/shorten', [LinkController::class, 'store']);

Route::get('/analytics', [AnalyticsController::class, 'index']);
Route::get('/history', [HistoryController::class, 'index']);

Route::delete('/links/{link}', [LinkController::class, 'destroy']);
Route::put('/links/{link}', [LinkController::class, 'update']);

// Must be last to avoid conflicting with named routes above
Route::get('/{slug}', [RedirectController::class, 'redirect']);
