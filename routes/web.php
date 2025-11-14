<?php

use App\Http\Controllers\Api\AuthController;
use Illuminate\Support\Facades\Route;
use L5Swagger\Http\Controllers\SwaggerController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/docs', function () {
    return view('vendor.l5-swagger.index');
});
Route::get('/api/documentation', [SwaggerController::class,
    'api'
])->name('l5-swagger.default.api');


