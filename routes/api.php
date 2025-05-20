<?php

use App\Http\Controllers\OfficeController;
use Illuminate\Support\Facades\Route;

Route::apiResource('offices', OfficeController::class);