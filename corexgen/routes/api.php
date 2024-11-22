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


            // api naming
            Route::group(['as' => 'api.'],function(){

          
                // roles routes
                Route::prefix('role')->as('role.')->group(function () {
                    // role for fetch, store, update
                    Route::get('/', [CRMRoleAPIController::class, 'index'])->name('index')->middleware('check.permission:ROLE.READ_ALL');
                    Route::get('/show/{id}', [CRMRoleAPIController::class, 'show'])->name('show')->middleware('check.permission:ROLE.READ');
                    Route::post('/create', [CRMRoleAPIController::class, 'store'])->name('store')->middleware('check.permission:ROLE.CREATE');
                    Route::put('/update/{id}', [CRMRoleAPIController::class, 'update'])->name('update')->middleware('check.permission:ROLE.UPDATE');

                    Route::get('/changeStatus/{id}', [CRMRoleAPIController::class, 'toggleStatus'])->name('toggleStatus')->middleware('check.permission:ROLE.CHANGE_STATUS');
                    Route::delete('/destroy/{id}', [CRMRoleAPIController::class, 'destroy'])->name('destroy')->middleware('check.permission:ROLE.DELETE');

                });



                // user routes
                Route::prefix('users')->as('users.')->group(function () {
                    // role for fetch, store, update
                    Route::get('/', [UserAPIController::class, 'index'])->name('index')->middleware('check.permission:USERS.READ_ALL');
                    Route::get('/show/{id}', [UserAPIController::class, 'show'])->name('show')->middleware('check.permission:USERS.READ');
                    Route::post('/create', [UserAPIController::class, 'store'])->name('store')->middleware('check.permission:USERS.CREATE');
                    Route::put('/update/{id}', [UserAPIController::class, 'update'])->name('update')->middleware('check.permission:USERS.UPDATE');

                    Route::get('/changeStatus/{id}', [UserAPIController::class, 'toggleStatus'])->name('toggleStatus')->middleware('check.permission:USERS.CHANGE_STATUS');
                    Route::delete('/destroy/{id}', [UserAPIController::class, 'destroy'])->name('destroy')->middleware('check.permission:USERS.DELETE');

                });

            });




        });
    });
});
