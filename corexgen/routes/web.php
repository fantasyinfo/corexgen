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


    // role routes
    Route::group(['prefix' => 'role', 'as' => 'role.'], function () {
        // role for fetch, store, update
        Route::get('/', [CRMRoleController::class, 'index'])->name('index');
        Route::post('/', [CRMRoleController::class, 'store'])->name('store');
        Route::patch('/', [CRMRoleController::class, 'update'])->name('update');


        // create, edit, change status, delete
        Route::get('/create', [CRMRoleController::class, 'create'])->name('create');
        Route::get('/edit/{id}', [CRMRoleController::class, 'edit'])->name('edit');
        Route::get('/changeStatus/{id}', [CRMRoleController::class, 'changeStatus'])->name('changeStatus');
        Route::delete('/destroy/{id}', [CRMRoleController::class, 'destroy'])->name('destroy');


        // validate, export, import
        Route::get('/export', [CRMRoleController::class, 'export'])->name('export');
        Route::post('/import', [CRMRoleController::class, 'import'])->name('import');
        Route::post('/validate-field', [CRMRoleController::class, 'validateField'])->name('validate-field');
    });
});
