<?php

use App\Http\Controllers\AksesController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\FasilitasController;
use App\Http\Controllers\KerusakanController;
use App\Http\Controllers\LokasiController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\RoleAksesController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\FCMController;


Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:api');


Route::middleware('auth:api')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);

    Route::post('/fcm-token', [FCMController::class, 'store']);

    Route::prefix('users')->group(function () {
        Route::get('/profile', [UserController::class, 'getProfile']);
        Route::put('/update', [UserController::class, 'updateByUserId']);
    });
});


Route::prefix('kerusakan')->middleware('auth:api')->group(function () {

    Route::post('/', [KerusakanController::class, 'store'])
        ->middleware('akses:create_kerusakan');

    Route::post('/perbaikan', [KerusakanController::class, 'storePerbaikan'])
        ->middleware('akses:create_perbaikan');

    Route::get('/', [KerusakanController::class, 'index'])
        ->middleware('akses:read_kerusakan');

    Route::get('/admin', [KerusakanController::class, 'indexadmin'])
        ->middleware('akses:read_kerusakan');

    Route::put('/{id_kerusakan}/status', [KerusakanController::class, 'updateStatus'])
        ->middleware('akses:update_kerusakan');

    Route::delete('/{id_kerusakan}', [KerusakanController::class, 'destroy'])
        ->middleware('akses:delete_kerusakan');
});

Route::prefix('admin')->middleware('auth:api')->group(function () {

    // Admin
    Route::get('/', [KerusakanController::class, 'indexadmin'])
        ->middleware('akses:admin');
});


Route::prefix('lokasi')->middleware('auth:api')->group(function () {

    // Dropdown ketika membuat kerusakan
    Route::get('/{userId}', [LokasiController::class, 'lokasiDropdown'])
        ->middleware('akses:create_kerusakan');

    Route::get('/', [LokasiController::class, 'index'])
        ->middleware('akses:read_lokasi');

    Route::post('/', [LokasiController::class, 'store'])
        ->middleware('akses:create_lokasi');

    Route::put('/{id}', [LokasiController::class, 'update'])
        ->middleware('akses:update_lokasi');

    Route::delete('/{id}', [LokasiController::class, 'destroy'])
        ->middleware('akses:delete_lokasi');
});



Route::prefix('fasilitas')->middleware('auth:api')->group(function () {

    // Dropdown ketika membuat kerusakan
    Route::get('/{userId}', [FasilitasController::class, 'fasilitasDropdown'])
        ->middleware('akses:create_kerusakan');

    Route::get('/', [FasilitasController::class, 'index'])
        ->middleware('akses:read_fasilitas');

    Route::post('/', [FasilitasController::class, 'store'])
        ->middleware('akses:create_fasilitas');

    Route::put('/{id}', [FasilitasController::class, 'update'])
        ->middleware('akses:update_fasilitas');

    Route::delete('/{id}', [FasilitasController::class, 'destroy'])
        ->middleware('akses:delete_fasilitas');
});


Route::prefix('project')->middleware('auth:api')->group(function () {

    Route::get('/', [ProjectController::class, 'index'])
        ->middleware('akses:read_project');

    Route::post('/', [ProjectController::class, 'store'])
        ->middleware('akses:create_project');

    Route::put('/{id}', [ProjectController::class, 'update'])
        ->middleware('akses:update_project');

    Route::delete('/{id}', [ProjectController::class, 'destroy'])
        ->middleware('akses:delete_project');
});


Route::prefix('akses')->middleware('auth:api')->group(function () {

    Route::get('/', [AksesController::class, 'index'])
        ->middleware('akses:read_akses');
});


Route::prefix('role')->middleware('auth:api')->group(function () {

    Route::get('/', [RoleController::class, 'index'])
        ->middleware('akses:read_role');
});


Route::prefix('roleakses')->middleware('auth:api')->group(function () {

    Route::get('/', [RoleAksesController::class, 'index'])
        ->middleware('akses:read_roleakses');

    Route::put('/{id}', [RoleAksesController::class, 'update'])
        ->middleware('akses:update_roleakses');
});


Route::prefix('user')->middleware('auth:api')->group(function () {

    Route::get('/', [UserController::class, 'index'])
        ->middleware('akses:read_user');

    Route::post('/', [UserController::class, 'store'])
        ->middleware('akses:create_user');

    Route::put('/{id}', [UserController::class, 'update'])
        ->middleware('akses:update_user');

    Route::delete('/{id}', [UserController::class, 'destroy'])
        ->middleware('akses:delete_user');
});