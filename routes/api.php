<?php

declare(strict_types=1);

use App\Http\Controllers\Api\DealershipController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function (): void {
    Route::get('/user', fn (Request $request) => $request->user());
});

Route::get('/dealerships', [DealershipController::class, 'index'])
    ->name('api.dealerships.index');
