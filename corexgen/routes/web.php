<?php

// crm routes
use App\Http\Controllers\CRM\CRMRoleController;
use App\Http\Controllers\UserController;
use App\Models\CRM\CRMRole;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;
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
    if (Auth::check()) {
        return view('welcome');
    } else {
        return redirect()->route('login');
    }
});

// login
Route::get('/login', function () {
    return view('auth.login', );
})->name('login');


// register
Route::get('/register', function () {
    return view('auth.register', );
})->name('register');




// language set
Route::get('/setlang/{locale}', function (string $locale) {

    if (in_array($locale, ['en', 'es', 'fr', 'hi'])) { // Add supported languages here
        Session::put('locale', $locale); // Store locale in session
        App::setLocale($locale); // Set locale for the current request
    }
    return redirect()->back()->with('success', 'Language Changed Successfully!');
});

// crm routes
Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
    'check.user.role'
])->prefix('crm')->as('crm.')->group(function () {
    
    // role routes
    Route::prefix('role')->as('role.')->group(function () {
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

    // users routes
    Route::prefix('users')->as('users.')->group(function () {
        // role for fetch, store, update
        Route::get('/', [UserController::class, 'index'])->name('index');
        Route::post('/', [UserController::class, 'store'])->name('store');
        Route::patch('/', [UserController::class, 'update'])->name('update');

        // create, edit, change status, delete
        Route::get('/create', [UserController::class, 'create'])->name('create');
        Route::get('/edit/{id}', [UserController::class, 'edit'])->name('edit');
        Route::get('/changeStatus/{id}', [UserController::class, 'changeStatus'])->name('changeStatus');
        Route::delete('/destroy/{id}', [UserController::class, 'destroy'])->name('destroy');

        // validate, export, import
        Route::get('/export', [UserController::class, 'export'])->name('export');
        Route::post('/import', [UserController::class, 'import'])->name('import');
      
    });
});
