<?php

use App\Http\Controllers\API\CRM\CRMRoleAPIController;
use App\Http\Controllers\API\UserAPIController;
use App\Http\Controllers\CRM\LeadsController;
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

// leads to web api
Route::post('/leads/create', [LeadsController::class, 'leadsCreateAPI'])->name('leadsCreateAPI');


