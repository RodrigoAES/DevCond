<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BilletController;
use App\Http\Controllers\DocController;
use App\Http\Controllers\LostAndFoundController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WallController;
use App\Http\Controllers\WarningController;

Route::get('ping', function(){
    return ['pong' => true];
});

Route::get('401', function() {
    return response()->json(['error' => 'unauthorized'], 401);
})->name('401');

Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/register', [AuthController::class, 'register']);

Route::middleware('auth:api')->group(function(){
    Route::post('/auth/validate', [AuthController::class, 'validateToken']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    // Mural de avisos
    Route::get('/walls', [WallController::class, 'getAll']);
    Route::post('/wall/{id}/like', [WallController::class, 'like']);

    // Documentos 
    Route::get('/docs', [DocController::class, 'getAll']);

    // Livro de ocorrencias
    Route::get('/warnings', [WarningController::class, 'getMyWarnings']);
    Route::post('/warning', [WarningController::class, 'setWarning']);
    Route::post('/warning/file', [WarningController::class, 'addWarningFile']);

    // Boletos
    Route::get('/billets', [BilletController::class, 'getAll']);

    // Achados e perdiidos
    Route::get('/lostandfound', [LostAndFoundController::class, 'getAll']);
    Route::post('/lostandfound', [LostAndFoundController::class, 'insert']);
    Route::put('/lostandfound/{id}', [LostAndFoundController::class, 'update']);

    // Unidades
    Route::get('/unit/{id}', [UnitController::class, 'getInfo']);
    Route::post('/unit/{id}/resident', [UnitController::class, 'addResident']);
    Route::delete('/unit/{id}/resident', [UnitController::class, 'removeResident']);
    Route::post('/unit/{id}/vehicle', [UnitController::class, 'addVehicle']);
    Route::delete('/unit/{id}/vehicle', [UnitController::class, 'removeVehicle']);
    Route::post('/unit/{id}/pet', [UnitController::class, 'addPet']);
    Route::delete('/unit/{id}/pet', [UnitController::class, 'removePet']);

    // Reservas
    Route::get('/reservations', [ReservationController::class, 'getAll']);
    Route::get('/myreservations', [ReservationController::class, 'userReservations']);

    Route::get('/reservation/{areaId}/disabledates', [ReservationController::class, 'getDisabledDays']);
    Route::get('/reservation/{areaId}/times', [ReservationController::class, 'getTimes']);
    Route::get('/reservation/{areaId}/avaliable', [ReservationController::class, 'getAvaliable']);

    Route::post('/reservation/{areaId}', [ReservationController::class, 'addReservation']);
    Route::delete('/myreservation/{id}', [ReservationController::class, 'removeReservation']);


});