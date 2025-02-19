<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AuthController;




Route::post('/register', [AuthController::class, 'register']); 
Route::post('/login', [AuthController::class, 'login']);

Route::middleware(['auth:sanctum', 'auth.admin'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']); 
});