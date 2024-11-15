<?php

use App\Models\Student;
use App\Http\Controllers\Api\LiveServiceAuthenticationController;
use App\Http\Controllers\Api\MQTTAuthenticationController;
use App\Http\Resources\VehicleCollection;
use App\Http\Resources\VehicleResource;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Resources\StudentResource;
use App\Http\Resources\StudentCollection;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('/v1')->group(function () {
    Route::prefix('/mqtt')->group(function () {
        Route::post('/authenticate', [MQTTAuthenticationController::class, 'authenticate'])->name('mqttauthentication.authenticate');
    });
});

Route::get('/live-ws-url', function () {
    return response()->json(config('app.LIVE_SERVICE_WS_URL'));
})->name('live-ws-url');

Route::get('/students', function () {
    return new StudentCollection(Student::all());
});


Route::get('/kendaraan', function() {
    return new VehicleCollection(Vehicle::paginate());
})->middleware('auth:sanctum');
