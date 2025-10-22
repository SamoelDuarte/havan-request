<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HavanRequestController;

Route::post('/endpoint', [HavanRequestController::class, 'handle']);
