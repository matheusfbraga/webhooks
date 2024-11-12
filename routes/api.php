<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
// Controllers
use App\Http\Controllers\WebhooksController;


/*
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
*/

Route::get('/webhook', [WebhooksController::class,'index']);
Route::post('/webhook', [WebhooksController::class,'webhook']);
