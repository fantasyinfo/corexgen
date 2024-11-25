<?php

// crm routes
use App\Http\Controllers\CountryCitySeederController;
use App\Http\Controllers\CRM\CRMRoleController;
use App\Http\Controllers\CRM\CRMRolePermissionsController;
use App\Http\Controllers\CRM\CRMSettingsController;
use App\Http\Controllers\ModuleController;
use App\Http\Controllers\SystemInstallerController;
use App\Http\Controllers\UserController;
use App\Models\CRM\CRMRole;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;
use Laravel\Fortify\Features;
use Illuminate\Support\Facades\File;
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



Route::middleware(['web', 'auth'])->prefix('admin')->group(function () {
    Route::get('/modules', [ModuleController::class, 'index'])->name('admin.modules.index');
    Route::post('/modules/upload', [ModuleController::class, 'upload'])->name('admin.modules.upload');
    Route::post('/modules/{module}/enable', [ModuleController::class, 'enable'])->name('admin.modules.enable');
    Route::post('/modules/{module}/disable', [ModuleController::class, 'disable'])->name('admin.modules.disable');
    Route::delete('/modules/{module}', [ModuleController::class, 'destroy'])->name('admin.modules.destroy');
});



Route::prefix('installer')->group(function() {
    Route::get('/install', [SystemInstallerController::class, 'showInstaller'])
    ->name('installer.index');
    Route::get('/requirements', [SystemInstallerController::class, 'checkSystemRequirements']);
    Route::post('/verify-purchase', [SystemInstallerController::class, 'verifyPurchaseCodeEndpoint']);
    Route::post('/test-database', [SystemInstallerController::class, 'testDatabaseConnection']);
    Route::post('/test-smtp', [SystemInstallerController::class, 'testSmtpConnection']);
    Route::post('/install', [SystemInstallerController::class, 'installApplication']);
    Route::get('/status', function () {
        if (File::exists(storage_path('installed.lock'))) {
            return response()->json(['status' => 'success']);
        }
        return response()->json(['status' => 'pending']);
    });
});



// Example of applying it to a group of routes
Route::middleware(['check.installation'])->group(function () {
    // All routes that require the installation to be completed
    Route::get('/', function () {
        if (Auth::check()) {
            return view('welcome');
        } else {
            return redirect()->route('login');
        }
    })->name('home');


    Route::get('/login', function(){
        return view('auth.login');
    })->name('login');

});


// register
Route::get('/register', function () {
    // return view('auth.register', );
    // disable registration redirect to login page
    return redirect()->route('login');
})->name('register');



Route::get('/super-admin-login',function(){
    return view('auth.login',['is_tenant' => true]);
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
Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
    'check.user.role',
    'check.installation'
])->prefix(getPanelUrl(PANEL_TYPES['COMPANY_PANEL']))->as(getPanelUrl(PANEL_TYPES['COMPANY_PANEL']) . '.')->group(function () {
    
    Route::get('/',function(){ dd('Company Panel Home');})->name('home');

    // role routes
    Route::prefix('role')->as('role.')->group(function () {
        // role for fetch, store, update
        Route::get('/', [CRMRoleController::class, 'index'])->name('index')->middleware('check.permission:ROLE.READ_ALL');
        Route::post('/', [CRMRoleController::class, 'store'])->name('store')->middleware('check.permission:ROLE.CREATE');
        Route::put('/', [CRMRoleController::class, 'update'])->name('update')->middleware('check.permission:ROLE.UPDATE');

        // create, edit, change status, delete
        Route::get('/create', [CRMRoleController::class, 'create'])->name('create')->middleware('check.permission:ROLE.CREATE');
        Route::get('/edit/{id}', [CRMRoleController::class, 'edit'])->name('edit')->middleware('check.permission:ROLE.UPDATE');
        Route::get('/changeStatus/{id}', [CRMRoleController::class, 'changeStatus'])->name('changeStatus')->middleware('check.permission:ROLE.CHANGE_STATUS');
        Route::delete('/destroy/{id}', [CRMRoleController::class, 'destroy'])->name('destroy')->middleware('check.permission:ROLE.DELETE');

        // validate, export, import
        Route::get('/export', [CRMRoleController::class, 'export'])->name('export')->middleware('check.permission:ROLE.EXPORT');
        Route::post('/import', [CRMRoleController::class, 'import'])->name('import')->middleware('check.permission:ROLE.IMPORT');
        Route::post('/validate-field', [CRMRoleController::class, 'validateField'])->name('validate-field');
    });

    // users routes
    Route::prefix('users')->as('users.')->group(function () {
        // role for fetch, store, update
        Route::get('/', [UserController::class, 'index'])->name('index')->middleware('check.permission:USERS.READ_ALL');
        Route::post('/', [UserController::class, 'store'])->name('store')->middleware('check.permission:USERS.CREATE');
        Route::put('/', [UserController::class, 'update'])->name('update')->middleware('check.permission:USERS.UPDATE');

        // create, edit, change status, delete
        Route::get('/create', [UserController::class, 'create'])->name('create')->middleware('check.permission:USERS.CREATE');
        Route::get('/edit/{id}', [UserController::class, 'edit'])->name('edit')->middleware('check.permission:USERS.UPDATE');
        Route::get('/changeStatus/{id}', [UserController::class, 'changeStatus'])->name('changeStatus')->middleware('check.permission:USERS.CHANGE_STATUS');
        Route::delete('/destroy/{id}', [UserController::class, 'destroy'])->name('destroy')->middleware('check.permission:USERS.DELETE');

        // validate, export, import
        Route::get('/export', [UserController::class, 'export'])->name('export')->middleware('check.permission:USERS.EXPORT');
        Route::post('/import', [UserController::class, 'import'])->name('import')->middleware('check.permission:USERS.IMPORT');
      
    });



        // permissions routes
        Route::prefix('permissions')->as('permissions.')->group(function () {
            // role for fetch, store, update
            Route::get('/', [CRMRolePermissionsController::class, 'index'])->name('index')->middleware('check.permission:PERMISSIONS.READ_ALL');
            Route::post('/', [CRMRolePermissionsController::class, 'store'])->name('store')->middleware('check.permission:PERMISSIONS.CREATE');
            Route::put('/', [CRMRolePermissionsController::class, 'update'])->name('update')->middleware('check.permission:PERMISSIONS.UPDATE');
    
            // create, edit, change status, delete
            Route::get('/create', [CRMRolePermissionsController::class, 'create'])->name('create')->middleware('check.permission:PERMISSIONS.CREATE');
            Route::get('/edit/{id}', [CRMRolePermissionsController::class, 'edit'])->name('edit')->middleware('check.permission:PERMISSIONS.UPDATE');
        
            Route::delete('/destroy/{id}', [CRMRolePermissionsController::class, 'destroy'])->name('destroy')->middleware('check.permission:PERMISSIONS.DELETE');
    
      
          
        });




    // settings routes
    Route::prefix('settings')->as('settings.')->group(function () {
        // role for fetch, store, update
        Route::get('/', [CRMSettingsController::class, 'index'])->name('index')->middleware('check.permission:SETTINGS.READ_ALL');
        Route::put('/', [CRMSettingsController::class, 'update'])->name('update')->middleware('check.permission:SETTINGS.UPDATE');
    });


    // modules routes
    Route::prefix('modules')->as('modules.')->group(function () {
        Route::get('/', [ModuleController::class, 'index'])->name('index')->middleware('check.permission:MODULES.READ_ALL');
        Route::post('/', [ModuleController::class, 'create'])->name('create')->middleware('check.permission:MODULES.CREATE');
        Route::delete('/destroy/{module}', [ModuleController::class, 'destroy'])->name('destroy')->middleware('check.permission:MODULES.DELETE');
    });

    



    // add country, city tables in bg
    Route::get('/add-default-countries-cities', [CountryCitySeederController::class, 'runSeeder']);


});


// super-panel routes

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
    'check.installation'
])->prefix(getPanelUrl(PANEL_TYPES['SUPER_PANEL']))->as(getPanelUrl(PANEL_TYPES['SUPER_PANEL']) . '.')->group(function () {
    
    Route::get('/',function(){ dd('Super Panel Home');})->name('home');
    // role routes
    Route::prefix('role')->as('role.')->group(function () {
        // role for fetch, store, update
        Route::get('/', [CRMRoleController::class, 'index'])->name('index')->middleware('check.permission:ROLE.READ_ALL');
        Route::post('/', [CRMRoleController::class, 'store'])->name('store')->middleware('check.permission:ROLE.CREATE');
        Route::put('/', [CRMRoleController::class, 'update'])->name('update')->middleware('check.permission:ROLE.UPDATE');

        // create, edit, change status, delete
        Route::get('/create', [CRMRoleController::class, 'create'])->name('create')->middleware('check.permission:ROLE.CREATE');
        Route::get('/edit/{id}', [CRMRoleController::class, 'edit'])->name('edit')->middleware('check.permission:ROLE.UPDATE');
        Route::get('/changeStatus/{id}', [CRMRoleController::class, 'changeStatus'])->name('changeStatus')->middleware('check.permission:ROLE.CHANGE_STATUS');
        Route::delete('/destroy/{id}', [CRMRoleController::class, 'destroy'])->name('destroy')->middleware('check.permission:ROLE.DELETE');

        // validate, export, import
        Route::get('/export', [CRMRoleController::class, 'export'])->name('export')->middleware('check.permission:ROLE.EXPORT');
        Route::post('/import', [CRMRoleController::class, 'import'])->name('import')->middleware('check.permission:ROLE.IMPORT');
        Route::post('/validate-field', [CRMRoleController::class, 'validateField'])->name('validate-field');
    });

    // users routes
    Route::prefix('users')->as('users.')->group(function () {
        // role for fetch, store, update
        Route::get('/', [UserController::class, 'index'])->name('index')->middleware('check.permission:USERS.READ_ALL');
        Route::post('/', [UserController::class, 'store'])->name('store')->middleware('check.permission:USERS.CREATE');
        Route::put('/', [UserController::class, 'update'])->name('update')->middleware('check.permission:USERS.UPDATE');

        // create, edit, change status, delete
        Route::get('/create', [UserController::class, 'create'])->name('create')->middleware('check.permission:USERS.CREATE');
        Route::get('/edit/{id}', [UserController::class, 'edit'])->name('edit')->middleware('check.permission:USERS.UPDATE');
        Route::get('/changeStatus/{id}', [UserController::class, 'changeStatus'])->name('changeStatus')->middleware('check.permission:USERS.CHANGE_STATUS');
        Route::delete('/destroy/{id}', [UserController::class, 'destroy'])->name('destroy')->middleware('check.permission:USERS.DELETE');

        // validate, export, import
        Route::get('/export', [UserController::class, 'export'])->name('export')->middleware('check.permission:USERS.EXPORT');
        Route::post('/import', [UserController::class, 'import'])->name('import')->middleware('check.permission:USERS.IMPORT');
      
    });



        // permissions routes
        Route::prefix('permissions')->as('permissions.')->group(function () {
            // role for fetch, store, update
            Route::get('/', [CRMRolePermissionsController::class, 'index'])->name('index')->middleware('check.permission:PERMISSIONS.READ_ALL');
            Route::post('/', [CRMRolePermissionsController::class, 'store'])->name('store')->middleware('check.permission:PERMISSIONS.CREATE');
            Route::put('/', [CRMRolePermissionsController::class, 'update'])->name('update')->middleware('check.permission:PERMISSIONS.UPDATE');
    
            // create, edit, change status, delete
            Route::get('/create', [CRMRolePermissionsController::class, 'create'])->name('create')->middleware('check.permission:PERMISSIONS.CREATE');
            Route::get('/edit/{id}', [CRMRolePermissionsController::class, 'edit'])->name('edit')->middleware('check.permission:PERMISSIONS.UPDATE');
        
            Route::delete('/destroy/{id}', [CRMRolePermissionsController::class, 'destroy'])->name('destroy')->middleware('check.permission:PERMISSIONS.DELETE');
    
      
          
        });




    // settings routes
    Route::prefix('settings')->as('settings.')->group(function () {
        // role for fetch, store, update
        Route::get('/', [CRMSettingsController::class, 'index'])->name('index')->middleware('check.permission:SETTINGS.READ_ALL');
        Route::put('/', [CRMSettingsController::class, 'update'])->name('update')->middleware('check.permission:SETTINGS.UPDATE');
    });


    // modules routes
    Route::prefix('modules')->as('modules.')->group(function () {
        Route::get('/', [ModuleController::class, 'index'])->name('index')->middleware('check.permission:MODULES.READ_ALL');
        Route::post('/', [ModuleController::class, 'create'])->name('create')->middleware('check.permission:MODULES.CREATE');
        Route::delete('/destroy/{module}', [ModuleController::class, 'destroy'])->name('destroy')->middleware('check.permission:MODULES.DELETE');
    });



});