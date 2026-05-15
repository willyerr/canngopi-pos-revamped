<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Import Controllers
use App\Http\Controllers\MenuController;
use App\Http\Controllers\SelfOrderController;

// For production
Route::middleware('verify.self.order.token')->group(function () {
    Route::get('/menus', [MenuController::class, 'index']);
    Route::get('/menus/{id}', [MenuController::class, 'show']);
});
Route::post('self-order', [SelfOrderController::class, 'store'])->middleware('verify.self.order.token');