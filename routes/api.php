<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth.token')->get('/secret/get', [\App\Http\Controllers\SecretController::class, 'get']);
Route::middleware('auth.token')->post('/secret/post', [\App\Http\Controllers\SecretController::class, 'post']);
