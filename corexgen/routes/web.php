<?php

// crm routes
use App\Http\Controllers\CRM\CRMRoleController;







use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// home route
Route::get('/', function () {
    return view('welcome');
});


// language set
Route::get('/setlang/{locale}', function (string $locale) {

    if (in_array($locale, ['en', 'es', 'fr', 'hi'])) { // Add supported languages here
        Session::put('locale', $locale); // Store locale in session
        App::setLocale($locale); // Set locale for the current request
    }
    return redirect()->back()->with('success', 'Language Changed Successfully!');
});

// crm routes
Route::group(['prefix' => 'crm', 'as' => 'crm.'], function () {
    Route::get('/role', [CRMRoleController::class, 'index'])->name('role.index');
    Route::get('/role/create', [CRMRoleController::class, 'create'])->name('role.create');
    Route::get('/role/edit', [CRMRoleController::class, 'edit'])->name('role.edit');
    Route::get('/role/destroy', [CRMRoleController::class, 'destroy'])->name('role.destroy');
    Route::get('/role/export', [CRMRoleController::class, 'export'])->name('role.export');
    Route::post('/role', [CRMRoleController::class, 'store'])->name('role.store'); 
});


