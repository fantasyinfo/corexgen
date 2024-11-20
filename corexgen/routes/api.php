<?php

use App\Http\Controllers\API\CRM\CRMRoleAPIController;
use App\Http\Controllers\API\UserAPIController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::group(['prefix' => 'v1'], function () {

    Route::post('auth/login', [UserAPIController::class, 'login']);
    Route::post('auth/forgot-password', [UserAPIController::class, 'forgotPassword']);
    Route::post('auth/reset-password', [UserAPIController::class, 'resetPassword']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('auth/logout', [UserAPIController::class, 'logout']);


        // crm api routes
        Route::prefix('crm')->as('crm.')->group(function () {

            // roles routes
            Route::prefix('role')->as('role.')->group(function () {
                // role for fetch, store, update
                Route::get('/', [CRMRoleAPIController::class, 'index'])->name('index');
                Route::get('/show/{id}', [CRMRoleAPIController::class, 'show'])->name('show');
                Route::post('/create', [CRMRoleAPIController::class, 'store'])->name('store');
                Route::patch('/update/{id}', [CRMRoleAPIController::class, 'update'])->name('update');

                Route::get('/changeStatus/{id}', [CRMRoleAPIController::class, 'toggleStatus'])->name('toggleStatus');
                Route::delete('/destroy/{id}', [CRMRoleAPIController::class, 'destroy'])->name('destroy');

            });
        });
    });
});
