<?php

// crm routes
use App\Http\Controllers\AppUpdateController;
use App\Http\Controllers\AttachmentController;
use App\Http\Controllers\AuditController;
use App\Http\Controllers\BackupController;
use App\Http\Controllers\CommentsController;
use App\Http\Controllers\CompanyOnboardingController;
use App\Http\Controllers\CompanyRegisterController;
use App\Http\Controllers\ContractsController;
use App\Http\Controllers\CompaniesController;
use App\Http\Controllers\CRM\ClientsController;
use App\Http\Controllers\CRM\LeadsController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\CustomFieldController;
use App\Http\Controllers\DownloadController;
use App\Http\Controllers\EstimatesController;
use App\Http\Controllers\ProductServicesController;
use App\Http\Controllers\ProposalController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\RolePermissionsController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\ModuleController;

use App\Http\Controllers\PaymentGatewayController;
use App\Http\Controllers\PlansController;
use App\Http\Controllers\PlansPaymentTransaction;
use App\Http\Controllers\PlanUpgrade;
use App\Http\Controllers\SystemInstallerController;
use App\Http\Controllers\TasksController;
use App\Http\Controllers\TemplatesController;
use App\Http\Controllers\UserController;
use App\Models\City;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;
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



Route::prefix('installer')->group(function () {
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



// home route of applying it to a group of routes
Route::middleware(['check.installation'])->group(function () {
    // All routes that require the installation to be completed
    Route::get('/', function () {
        if (Auth::check()) {
            if (Auth::user()->is_tenant) {
                return redirect()->route(getPanelUrl(PANEL_TYPES['SUPER_PANEL']) . '.home');
            } else if (Auth::user()->company_id != null) {
                return redirect()->route(getPanelUrl(PANEL_TYPES['COMPANY_PANEL']) . '.home');
            }
        } else {
            return view('landing.index');
        }
    })->name('home');


    Route::get('/company/register', [CompanyRegisterController::class, 'register'])->name('compnay.landing-register');
    Route::post('/company/register', [CompanyRegisterController::class, 'store'])->name('company.register');


    Route::get('/login', function () {
        return view('auth.login');
    })->name('login');
});


// company onboarding
Route::middleware(['auth'])->group(function () {
    Route::get('/onboarding', [CompanyOnboardingController::class, 'showOnboardingForm'])
        ->name('onboarding.index');

    Route::post('/onboarding/address', [CompanyOnboardingController::class, 'saveAddress'])
        ->name('onboarding.address');

    Route::post('/onboarding/currency', [CompanyOnboardingController::class, 'saveCurrency'])
        ->name('onboarding.currency');

    Route::post('/onboarding/timezone', [CompanyOnboardingController::class, 'saveTimezone'])
        ->name('onboarding.timezone');

    Route::post('/onboarding/plan', [CompanyOnboardingController::class, 'savePlan'])
        ->name('onboarding.plan');

    Route::post('/onboarding/payment', [CompanyOnboardingController::class, 'processPayment'])
        ->name('onboarding.payment');

    Route::post('/onboarding/complete', [CompanyOnboardingController::class, 'completeOnboarding'])
        ->name('onboarding.complete');
});

// payment gateway routes
Route::prefix('payments')->group(function () {
    Route::post(
        '/initiate/{gateway?}',
        [PaymentGatewayController::class, 'initiate']
    )
        ->name('payment.initiate');

    Route::get(
        '/success/{gateway}',
        [PaymentGatewayController::class, 'handleSuccess']
    )
        ->name('payment.success')->middleware('payment.debug');

    Route::get(
        '/cancel/{gateway}',
        [PaymentGatewayController::class, 'handleCancel']
    )
        ->name('payment.cancel');
});


// proposal view and signed
Route::get('/proposal/view/{id}', [ProposalController::class, 'viewOpen'])->name('proposal.viewOpen');
Route::get('/proposal/print/{id}', [ProposalController::class, 'print'])->name('proposal.print');
Route::post('/proposal/accept', [ProposalController::class, 'accept'])->name('proposal.accept');


// estimates view and signed
Route::get('/estimate/view/{id}', [EstimatesController::class, 'viewOpen'])->name('estimate.viewOpen');
Route::get('/estimate/print/{id}', [EstimatesController::class, 'print'])->name('estimate.print');
Route::post('/estimate/accept', [EstimatesController::class, 'accept'])->name('estimate.accept');


// contracts
Route::get('/contract/view/{id}', [ContractsController::class, 'viewOpen'])->name('contract.viewOpen');
Route::get('/contract/print/{id}', [ContractsController::class, 'print'])->name('contract.print');
Route::post('/contract/accept', [ContractsController::class, 'accept'])->name('contract.accept');
Route::post('/contract/acceptCompany', [ContractsController::class, 'acceptCompany'])->name('contract.acceptCompany');




// register
Route::get('/register', function () {
    // return view('auth.register', );
    // disable registration redirect to login page
    return redirect()->route('login');
})->name('register');

// open routes for download
Route::prefix('download')->as('download.')->group(function () {
    Route::get('/countries', [DownloadController::class, 'countries'])->name('countries');
    Route::get('/ctg/{type}/{relation}', [DownloadController::class, 'cgt'])->name('cgt')->middleware('auth:sanctum');
});




Route::get('/super-admin-login', function () {
    return view('auth.login', ['is_tenant' => true, 'path' => 'super-admin-login']);
})->name('super.panel.login');

Route::get('/direct-logout', function () {
    Auth::guard('web')->logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();

    return redirect('/'); // Redirect to home or login page after logout
})->name('direct.logout');


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
    'company.onboarding',
    // config('jetstream.auth_session'),
    'check.installation',
    'company.timezone'
])->prefix(getPanelUrl(PANEL_TYPES['COMPANY_PANEL']))->as(getPanelUrl(PANEL_TYPES['COMPANY_PANEL']) . '.')->group(function () {

    Route::get('/', function () {
        // dd('Company Panel Home');
        return redirect()->route(getPanelUrl(PANEL_TYPES['COMPANY_PANEL']) . '.role.index');
    })->name('home');





    // role routes
    Route::prefix(PANEL_MODULES['COMPANY_PANEL']['role'])->as(PANEL_MODULES['COMPANY_PANEL']['role'] . '.')->group(function () {
        // role for fetch, store, update
        Route::get('/', [RoleController::class, 'index'])->name('index')->middleware('check.permission:ROLE.READ_ALL');
        Route::post('/', [RoleController::class, 'store'])->name('store')->middleware('check.permission:ROLE.CREATE');
        Route::put('/', [RoleController::class, 'update'])->name('update')->middleware('check.permission:ROLE.UPDATE');

        // create, edit, change status, delete
        Route::get('/create', [RoleController::class, 'create'])->name('create')->middleware('check.permission:ROLE.CREATE');
        Route::get('/edit/{id}', [RoleController::class, 'edit'])->name('edit')->middleware('check.permission:ROLE.UPDATE');
        Route::get(
            '/changeStatus/{id}/{status}',
            [RoleController::class, 'changeStatus']
        )->name('changeStatus')->middleware('check.permission:ROLE.CHANGE_STATUS');
        Route::delete('/destroy/{id}', [RoleController::class, 'destroy'])->name('destroy')->middleware('check.permission:ROLE.DELETE');

        // validate, export, import
        Route::get('/export', [RoleController::class, 'export'])->name('export')->middleware('check.permission:ROLE.EXPORT');
        Route::get('/import', [RoleController::class, 'importView'])->name('importView')->middleware('check.permission:ROLE.IMPORT');
        Route::post('/import', [RoleController::class, 'import'])->name('import')->middleware('check.permission:ROLE.IMPORT');
        Route::post('/bulkDelete', [RoleController::class, 'bulkDelete'])->name('bulkDelete')->middleware('check.permission:ROLE.BULK_DELETE');
    });

    // users routes
    Route::prefix(PANEL_MODULES['COMPANY_PANEL']['users'])->as(PANEL_MODULES['COMPANY_PANEL']['users'] . '.')->group(function () {
        // role for fetch, store, update
        Route::get('/', [UserController::class, 'index'])->name('index')->middleware('check.permission:USERS.READ_ALL');
        Route::post('/', [UserController::class, 'store'])->name('store')->middleware('check.permission:USERS.CREATE');
        Route::put('/', [UserController::class, 'update'])->name('update')->middleware('check.permission:USERS.UPDATE');

        // create, edit, change status, delete
        Route::get('/create', [UserController::class, 'create'])->name('create')->middleware('check.permission:USERS.CREATE');
        Route::get('/edit/{id}', [UserController::class, 'edit'])->name('edit')->middleware('check.permission:USERS.UPDATE');
        Route::get('/changeStatus/{id}/{status}', [UserController::class, 'changeStatus'])->name('changeStatus')->middleware('check.permission:USERS.CHANGE_STATUS');
        Route::delete('/destroy/{id}', [UserController::class, 'destroy'])->name('destroy')->middleware('check.permission:USERS.DELETE');

        // validate, export, import
        Route::get('/export', [UserController::class, 'export'])->name('export')->middleware('check.permission:USERS.EXPORT');
        Route::get('/import', [UserController::class, 'importView'])->name('importView')->middleware('check.permission:USERS.IMPORT');
        Route::post('/import', [UserController::class, 'import'])->name('import')->middleware('check.permission:USERS.IMPORT');
        Route::post('/bulkDelete', [UserController::class, 'bulkDelete'])->name('bulkDelete')->middleware('check.permission:USERS.BULK_DELETE');
        Route::post('/changePassword', [UserController::class, 'changePassword'])->name('changePassword')->middleware('check.permission:USERS.CHANGE_PASSWORD');
        Route::post('/updatePassword', [UserController::class, 'updatePassword'])->name('updatePassword');
        Route::get('/view/{id}', [UserController::class, 'view'])->name('view')->middleware('check.permission:USERS.VIEW');
        Route::get('/profile', [UserController::class, 'profile'])->name('profile');


        // login back
        Route::get('/login-back', [CompaniesController::class, 'loginback'])->name('loginback');

        Route::get('/loginas/{userid}', [UserController::class, 'loginas'])->name('loginas')->middleware('check.permission:USERS.LOGIN_AS');
    });

    // products_services routes
    Route::prefix(PANEL_MODULES['COMPANY_PANEL']['products_services'])->as(PANEL_MODULES['COMPANY_PANEL']['products_services'] . '.')->group(function () {
        // role for fetch, store, update
        Route::get('/', [ProductServicesController::class, 'index'])->name('index')->middleware('check.permission:PRODUCTS_SERVICES.READ_ALL');
        Route::post('/', [ProductServicesController::class, 'store'])->name('store')->middleware('check.permission:PRODUCTS_SERVICES.CREATE');
        Route::put('/', [ProductServicesController::class, 'update'])->name('update')->middleware('check.permission:PRODUCTS_SERVICES.UPDATE');

        // create, edit, change status, delete
        Route::get('/create', [ProductServicesController::class, 'create'])->name('create')->middleware('check.permission:PRODUCTS_SERVICES.CREATE');
        Route::get('/edit/{id}', [ProductServicesController::class, 'edit'])->name('edit')->middleware('check.permission:PRODUCTS_SERVICES.UPDATE');
        Route::get('/changeStatus/{id}/{status}', [ProductServicesController::class, 'changeStatus'])->name('changeStatus')->middleware('check.permission:PRODUCTS_SERVICES.CHANGE_STATUS');
        Route::delete('/destroy/{id}', [ProductServicesController::class, 'destroy'])->name('destroy')->middleware('check.permission:PRODUCTS_SERVICES.DELETE');


        Route::post('/bulkDelete', [ProductServicesController::class, 'bulkDelete'])->name('bulkDelete')->middleware('check.permission:PRODUCTS_SERVICES.BULK_DELETE');

        Route::get('/view/{id}', [ProductServicesController::class, 'view'])->name('view')->middleware('check.permission:PRODUCTS_SERVICES.VIEW');

    });



    // permissions routes
    Route::prefix(PANEL_MODULES['COMPANY_PANEL']['permissions'])->as(PANEL_MODULES['COMPANY_PANEL']['permissions'] . '.')->group(function () {
        // role for fetch, store, update
        Route::get('/', [RolePermissionsController::class, 'index'])->name('index')->middleware('check.permission:PERMISSIONS.READ_ALL');
        Route::post('/', [RolePermissionsController::class, 'store'])->name('store')->middleware('check.permission:PERMISSIONS.CREATE');
        Route::put('/', [RolePermissionsController::class, 'update'])->name('update')->middleware('check.permission:PERMISSIONS.UPDATE');

        // create, edit, change status, delete
        Route::get('/create', [RolePermissionsController::class, 'create'])->name('create')->middleware('check.permission:PERMISSIONS.CREATE');
        Route::get('/edit/{id}', [RolePermissionsController::class, 'edit'])->name('edit')->middleware('check.permission:PERMISSIONS.UPDATE');

        Route::delete('/destroy/{id}', [RolePermissionsController::class, 'destroy'])->name('destroy')->middleware('check.permission:PERMISSIONS.DELETE');
    });




    // settings routes
    Route::prefix(PANEL_MODULES['COMPANY_PANEL']['settings'])->as(PANEL_MODULES['COMPANY_PANEL']['settings'] . '.')->group(function () {
        // role for fetch, store, update
        Route::get('/general', [SettingsController::class, 'general'])->name('general')->middleware('check.permission:SETTINGS_GENERAL.READ_ALL');
        Route::put('/general', [SettingsController::class, 'generalUpdate'])->name('generalUpdate')->middleware('check.permission:SETTINGS_GENERAL.UPDATE');



        Route::get('/mail', [SettingsController::class, 'mail'])->name('mail')->middleware('check.permission:SETTINGS_MAIL.READ_ALL');
        Route::put('/mail', [SettingsController::class, 'mailUpdate'])->name('mailUpdate')->middleware('check.permission:SETTINGS_MAIL.UPDATE');
        Route::post('/test-connection', [SettingsController::class, 'testMailConnection'])->name('test-connection')->middleware('check.permission:SETTINGS_MAIL.READ_ALL');


        Route::get('/oneWord', [SettingsController::class, 'oneWord'])->name('oneWord')->middleware('check.permission:SETTINGS_ONEWORD.READ_ALL');
        Route::put('/oneWord', [SettingsController::class, 'oneWordUpdate'])->name('oneWordUpdate')->middleware('check.permission:SETTINGS_ONEWORD.UPDATE');
    });
    // upgrade routes
    Route::prefix(PANEL_MODULES['COMPANY_PANEL']['planupgrade'])->as(PANEL_MODULES['COMPANY_PANEL']['planupgrade'] . '.')->group(function () {
        // role for fetch, store, update
        Route::get('/', [PlanUpgrade::class, 'index'])->name('index')->middleware('check.permission:PLANUPGRADE.READ_ALL');

        Route::put('/', [PlanUpgrade::class, 'upgrade'])->name('upgrade')->middleware('check.permission:PLANUPGRADE.UPGRADE');
    });


    // modules routes
    Route::prefix(PANEL_MODULES['COMPANY_PANEL']['modules'])->as(PANEL_MODULES['COMPANY_PANEL']['modules'] . '.')->group(function () {
        Route::get('/', [ModuleController::class, 'index'])->name('index')->middleware('check.permission:MODULES.READ_ALL');
        Route::post('/', [ModuleController::class, 'create'])->name('create')->middleware('check.permission:MODULES.CREATE');
        Route::delete('/destroy/{module}', [ModuleController::class, 'destroy'])->name('destroy')->middleware('check.permission:MODULES.DELETE');
    });




    // audits routes
    Route::prefix('audit')->as('audit.')->group(function () {
        Route::get('/', [AuditController::class, 'index'])->name('index')->middleware('check.permission:EVENTS_AUDIT_LOG.READ_ALL');
        Route::get('/bulk-import', [AuditController::class, 'bulkimport'])->name('bulkimport')->middleware('check.permission:BULK_IMPORT_STATUS.READ_ALL');
    });


    // clients routes
    Route::prefix(PANEL_MODULES['COMPANY_PANEL']['clients'])->as(PANEL_MODULES['COMPANY_PANEL']['clients'] . '.')->group(function () {
        // role for fetch, store, update
        Route::get('/', [ClientsController::class, 'index'])->name('index')->middleware('check.permission:CLIENTS.READ_ALL');
        Route::post('/', [ClientsController::class, 'store'])->name('store')->middleware('check.permission:CLIENTS.CREATE');
        Route::put('/', [ClientsController::class, 'update'])->name('update')->middleware('check.permission:CLIENTS.UPDATE');

        // create, edit, change status, delete
        Route::get('/create', [ClientsController::class, 'create'])->name('create')->middleware('check.permission:CLIENTS.CREATE');
        Route::get('/edit/{id}', [ClientsController::class, 'edit'])->name('edit')->middleware('check.permission:CLIENTS.UPDATE');
        Route::get('/changeStatus/{id}/{status}', [ClientsController::class, 'changeStatus'])->name('changeStatus')->middleware('check.permission:CLIENTS.CHANGE_STATUS');
        Route::delete('/destroy/{id}', [ClientsController::class, 'destroy'])->name('destroy')->middleware('check.permission:CLIENTS.DELETE');

        // validate, export, import
        Route::get('/export', [ClientsController::class, 'export'])->name('export')->middleware('check.permission:CLIENTS.EXPORT');
        Route::get('/import', [ClientsController::class, 'importView'])->name('importView')->middleware('check.permission:CLIENTS.IMPORT');
        Route::post('/import', [ClientsController::class, 'import'])->name('import')->middleware('check.permission:CLIENTS.IMPORT');

        Route::post('/bulkDelete', [ClientsController::class, 'bulkDelete'])->name('bulkDelete')->middleware('check.permission:CLIENTS.BULK_DELETE');

        Route::get('/view/{id}', [ClientsController::class, 'view'])->name('view')->middleware('check.permission:CLIENTS.VIEW');
        Route::get('/profile', [ClientsController::class, 'profile'])->name('profile');

        // comments routes...
        Route::post('/comment', [CommentsController::class, 'addClientsComment'])->name('comment.create')->middleware('check.permission:CLIENTS.CREATE');
        Route::delete('/comment/destroy/{id}', [CommentsController::class, 'destroyClientsComment'])->name('comment.destroy')->middleware('check.permission:CLIENTS.DELETE');



        // attachments routes

        Route::post('/attachment', [AttachmentController::class, 'addClientsAttachment'])->name('attachment.create')->middleware('check.permission:CLIENTS.CREATE');
        Route::delete('/attachment/destroy/{id}', [AttachmentController::class, 'destroyClientsAttachment'])->name('attachment.destroy')->middleware('check.permission:CLIENTS.DELETE');
    });

    //proposals
    Route::prefix(PANEL_MODULES['COMPANY_PANEL']['proposals'])->as(PANEL_MODULES['COMPANY_PANEL']['proposals'] . '.')->group(function () {
        // role for fetch, store, update
        Route::get('/', [ProposalController::class, 'index'])->name('index')->middleware('check.permission:PROPOSALS.READ_ALL');
        Route::post('/', [ProposalController::class, 'store'])->name('store')->middleware('check.permission:PROPOSALS.CREATE');
        Route::put('/', [ProposalController::class, 'update'])->name('update')->middleware('check.permission:PROPOSALS.UPDATE');

        // create, edit, change status, delete
        Route::get('/create', [ProposalController::class, 'create'])->name('create')->middleware('check.permission:PROPOSALS.CREATE');
        Route::get('/edit/{id}', [ProposalController::class, 'edit'])->name('edit')->middleware('check.permission:PROPOSALS.UPDATE');

        Route::get('/changeStatusAction/{id}/{action}', [ProposalController::class, 'changeStatusAction'])->name('changeStatusAction')->middleware('check.permission:PROPOSALS.CHANGE_STATUS');

        Route::get('/sendProposal/{id}', [ProposalController::class, 'sendProposal'])->name('sendProposal')->middleware('check.permission:PROPOSALS.CHANGE_STATUS');

        Route::delete('/destroy/{id}', [ProposalController::class, 'destroy'])->name('destroy')->middleware('check.permission:PROPOSALS.DELETE');


        Route::post('/bulkDelete', [ProposalController::class, 'bulkDelete'])->name('bulkDelete')->middleware('check.permission:PROPOSALS.BULK_DELETE');

        Route::get('/view/{id}', [ProposalController::class, 'view'])->name('view')->middleware('check.permission:PROPOSALS.VIEW');




        // template
        Route::get('/templates', [TemplatesController::class, 'indexProposals'])->name('indexProposals')->middleware('check.permission:PROPOSALS.READ_ALL');
        Route::post('/templates', [TemplatesController::class, 'storeProposals'])->name('storeProposals')->middleware('check.permission:PROPOSALS.CREATE');
        Route::put('/templates', [TemplatesController::class, 'updateProposals'])->name('updateProposals')->middleware('check.permission:PROPOSALS.UPDATE');

        // create, edit, change status, delete
        Route::get('templates/create', [TemplatesController::class, 'createProposals'])->name('createProposals')->middleware('check.permission:PROPOSALS.CREATE');
        Route::get('templates/edit/{id}', [TemplatesController::class, 'editProposals'])->name('editProposals')->middleware('check.permission:PROPOSALS.UPDATE');
        Route::get('templates/view/{id}', [TemplatesController::class, 'viewProposals'])->name('viewProposals')->middleware('check.permission:PROPOSALS.READ');
        Route::delete('templates/destroy/{id}', [TemplatesController::class, 'destroyProposals'])->name('destroyProposals')->middleware('check.permission:PROPOSALS.DELETE');

    });


    // estimates
    Route::prefix(PANEL_MODULES['COMPANY_PANEL']['estimates'])->as(PANEL_MODULES['COMPANY_PANEL']['estimates'] . '.')->group(function () {
        // role for fetch, store, update
        Route::get('/', [EstimatesController::class, 'index'])->name('index')->middleware('check.permission:ESTIMATES.READ_ALL');
        Route::post('/', [EstimatesController::class, 'store'])->name('store')->middleware('check.permission:ESTIMATES.CREATE');
        Route::put('/', [EstimatesController::class, 'update'])->name('update')->middleware('check.permission:ESTIMATES.UPDATE');

        // create, edit, change status, delete
        Route::get('/create', [EstimatesController::class, 'create'])->name('create')->middleware('check.permission:ESTIMATES.CREATE');
        Route::get('/edit/{id}', [EstimatesController::class, 'edit'])->name('edit')->middleware('check.permission:ESTIMATES.UPDATE');

        Route::get('/changeStatusAction/{id}/{action}', [EstimatesController::class, 'changeStatusAction'])->name('changeStatusAction')->middleware('check.permission:ESTIMATES.CHANGE_STATUS');

        Route::get('/sendEstimate/{id}', [EstimatesController::class, 'sendEstimate'])->name('sendEstimate')->middleware('check.permission:ESTIMATES.CHANGE_STATUS');

        Route::delete('/destroy/{id}', [EstimatesController::class, 'destroy'])->name('destroy')->middleware('check.permission:ESTIMATES.DELETE');


        Route::post('/bulkDelete', [EstimatesController::class, 'bulkDelete'])->name('bulkDelete')->middleware('check.permission:ESTIMATES.BULK_DELETE');

        Route::get('/view/{id}', [EstimatesController::class, 'view'])->name('view')->middleware('check.permission:ESTIMATES.VIEW');




        // template
        Route::get('/templates', [TemplatesController::class, 'indexEstimates'])->name('indexEstimates')->middleware('check.permission:ESTIMATES.READ_ALL');
        Route::post('/templates', [TemplatesController::class, 'storeEstimates'])->name('storeEstimates')->middleware('check.permission:ESTIMATES.CREATE');
        Route::put('/templates', [TemplatesController::class, 'updateEstimates'])->name('updateEstimates')->middleware('check.permission:ESTIMATES.UPDATE');

        // create, edit, change status, delete
        Route::get('templates/create', [TemplatesController::class, 'createEstimates'])->name('createEstimates')->middleware('check.permission:ESTIMATES.CREATE');
        Route::get('templates/edit/{id}', [TemplatesController::class, 'editEstimates'])->name('editEstimates')->middleware('check.permission:ESTIMATES.UPDATE');
        Route::get('templates/view/{id}', [TemplatesController::class, 'viewEstimates'])->name('viewEstimates')->middleware('check.permission:ESTIMATES.READ');
        Route::delete('templates/destroy/{id}', [TemplatesController::class, 'destroyEstimates'])->name('destroyEstimates')->middleware('check.permission:ESTIMATES.DELETE');

    });


    // contracts

    Route::prefix(PANEL_MODULES['COMPANY_PANEL']['contracts'])->as(PANEL_MODULES['COMPANY_PANEL']['contracts'] . '.')->group(function () {
        // role for fetch, store, update
        Route::get('/', [ContractsController::class, 'index'])->name('index')->middleware('check.permission:CONTRACTS.READ_ALL');
        Route::post('/', [ContractsController::class, 'store'])->name('store')->middleware('check.permission:CONTRACTS.CREATE');
        Route::put('/', [ContractsController::class, 'update'])->name('update')->middleware('check.permission:CONTRACTS.UPDATE');

        // create, edit, change status, delete
        Route::get('/create', [ContractsController::class, 'create'])->name('create')->middleware('check.permission:CONTRACTS.CREATE');
        Route::get('/edit/{id}', [ContractsController::class, 'edit'])->name('edit')->middleware('check.permission:CONTRACTS.UPDATE');

        Route::get('/changeStatusAction/{id}/{action}', [ContractsController::class, 'changeStatusAction'])->name('changeStatusAction')->middleware('check.permission:CONTRACTS.CHANGE_STATUS');

        Route::get('/sendContract/{id}', [ContractsController::class, 'sendContract'])->name('sendContract')->middleware('check.permission:CONTRACTS.CHANGE_STATUS');

        Route::delete('/destroy/{id}', [ContractsController::class, 'destroy'])->name('destroy')->middleware('check.permission:CONTRACTS.DELETE');


        Route::post('/bulkDelete', [ContractsController::class, 'bulkDelete'])->name('bulkDelete')->middleware('check.permission:CONTRACTS.BULK_DELETE');

        Route::get('/view/{id}', [ContractsController::class, 'view'])->name('view')->middleware('check.permission:CONTRACTS.VIEW');




        // template
        Route::get('/templates', [TemplatesController::class, 'indexContracts'])->name('indexContracts')->middleware('check.permission:CONTRACTS.READ_ALL');
        Route::post('/templates', [TemplatesController::class, 'storeContracts'])->name('storeContracts')->middleware('check.permission:CONTRACTS.CREATE');
        Route::put('/templates', [TemplatesController::class, 'updateContracts'])->name('updateContracts')->middleware('check.permission:CONTRACTS.UPDATE');

        // create, edit, change status, delete
        Route::get('templates/create', [TemplatesController::class, 'createContracts'])->name('createContracts')->middleware('check.permission:CONTRACTS.CREATE');
        Route::get('templates/edit/{id}', [TemplatesController::class, 'editContracts'])->name('editContracts')->middleware('check.permission:CONTRACTS.UPDATE');
        Route::get('templates/view/{id}', [TemplatesController::class, 'viewContracts'])->name('viewContracts')->middleware('check.permission:CONTRACTS.READ');
        Route::delete('templates/destroy/{id}', [TemplatesController::class, 'destroyContracts'])->name('destroyContracts')->middleware('check.permission:CONTRACTS.DELETE');

    });



    // leads routes
    Route::prefix(PANEL_MODULES['COMPANY_PANEL']['leads'])->as(PANEL_MODULES['COMPANY_PANEL']['leads'] . '.')->group(function () {
        // role for fetch, store, update
        Route::get('/', [LeadsController::class, 'index'])->name('index')->middleware('check.permission:LEADS.READ_ALL');
        Route::post('/', [LeadsController::class, 'store'])->name('store')->middleware('check.permission:LEADS.CREATE');
        Route::put('/', [LeadsController::class, 'update'])->name('update')->middleware('check.permission:LEADS.UPDATE');

        // create, edit, change status, delete
        Route::get('/create', [LeadsController::class, 'create'])->name('create')->middleware('check.permission:LEADS.CREATE');
        Route::get('/edit/{id}', [LeadsController::class, 'edit'])->name('edit')->middleware('check.permission:LEADS.UPDATE');
        Route::get('/changeStatus/{id}/{status}', [LeadsController::class, 'changeStatus'])->name('changeStatus')->middleware('check.permission:LEADS.CHANGE_STATUS');
        Route::delete('/destroy/{id}', [LeadsController::class, 'destroy'])->name('destroy')->middleware('check.permission:LEADS.DELETE');

        // validate, export, import
        Route::get('/export', [LeadsController::class, 'export'])->name('export')->middleware('check.permission:LEADS.EXPORT');
        Route::get('/import', [LeadsController::class, 'importView'])->name('importView')->middleware('check.permission:LEADS.IMPORT');
        Route::post('/import', [LeadsController::class, 'import'])->name('import')->middleware('check.permission:LEADS.IMPORT');

        Route::post('/bulkDelete', [LeadsController::class, 'bulkDelete'])->name('bulkDelete')->middleware('check.permission:LEADS.BULK_DELETE');

        Route::get('/view/{id}', [LeadsController::class, 'view'])->name('view')->middleware('check.permission:LEADS.VIEW');
        Route::get('/profile', [LeadsController::class, 'profile'])->name('profile');



        // kanban board routes.

        Route::get('/kanban', [LeadsController::class, 'kanban'])->name('kanban')->middleware('check.permission:LEADS.KANBAN_BOARD');

        Route::get('/kanbanEdit/{id}', [LeadsController::class, 'kanbanEdit'])->name('kanbanEdit')->middleware('check.permission:LEADS.UPDATE');

        Route::get('/kanbanView/{id}', [LeadsController::class, 'kanbanView'])->name('kanbanView')->middleware('check.permission:LEADS.READ');

        Route::get('/kanbanLoad', [LeadsController::class, 'kanbanLoad'])->name('kanbanLoad')->middleware('check.permission:LEADS.KANBAN_BOARD');

        Route::get('/changeStage/{leadid}/{stageid}', [LeadsController::class, 'changeStage'])->name('changeStage')->middleware('check.permission:LEADS.CHANGE_STATUS');



        // comments routes...
        Route::post('/comment', [CommentsController::class, 'addLeadsComment'])->name('comment.create')->middleware('check.permission:LEADS.CREATE');
        Route::delete('/comment/destroy/{id}', [CommentsController::class, 'destroyLeadsComment'])->name('comment.destroy')->middleware('check.permission:LEADS.DELETE');



        // attachments routes

        Route::post('/attachment', [AttachmentController::class, 'addLeadsAttachment'])->name('attachment.create')->middleware('check.permission:LEADS.CREATE');
        Route::delete('/attachment/destroy/{id}', [AttachmentController::class, 'destroyLeadsAttachment'])->name('attachment.destroy')->middleware('check.permission:LEADS.DELETE');

    });


    // custom fields routes
    Route::prefix(PANEL_MODULES['COMPANY_PANEL']['customfields'])->as(PANEL_MODULES['COMPANY_PANEL']['customfields'] . '.')->group(function () {
        // role for fetch, store, update
        Route::get('/', [CustomFieldController::class, 'index'])->name('index')->middleware('check.permission:CUSTOM_FIELDS.READ_ALL');
        Route::post('/', [CustomFieldController::class, 'store'])->name('store')->middleware('check.permission:CUSTOM_FIELDS.CREATE');
        Route::put('/', [CustomFieldController::class, 'update'])->name('update')->middleware('check.permission:CUSTOM_FIELDS.UPDATE');

        // create, edit, change status, delete
        Route::get('/create', [CustomFieldController::class, 'create'])->name('create')->middleware('check.permission:CUSTOM_FIELDS.CREATE');
        Route::get('/edit/{id}', [CustomFieldController::class, 'edit'])->name('edit')->middleware('check.permission:CUSTOM_FIELDS.UPDATE');
        Route::get('/changeStatus/{id}/{status}', [CustomFieldController::class, 'changeStatus'])->name('changeStatus')->middleware('check.permission:CUSTOM_FIELDS.CHANGE_STATUS');
        Route::delete('/destroy/{id}', [CustomFieldController::class, 'destroy'])->name('destroy')->middleware('check.permission:CUSTOM_FIELDS.DELETE');

        // validate, export, import
        Route::get('/export', [CustomFieldController::class, 'export'])->name('export')->middleware('check.permission:CUSTOM_FIELDS.EXPORT');
        Route::get('/import', [CustomFieldController::class, 'importView'])->name('importView')->middleware('check.permission:CUSTOM_FIELDS.IMPORT');
        Route::post('/import', [CustomFieldController::class, 'import'])->name('import')->middleware('check.permission:CUSTOM_FIELDS.IMPORT');

        Route::post('/bulkDelete', [CustomFieldController::class, 'bulkDelete'])->name('bulkDelete')->middleware('check.permission:CUSTOM_FIELDS.BULK_DELETE');

        Route::get('/view/{id}', [CustomFieldController::class, 'view'])->name('view')->middleware('check.permission:CUSTOM_FIELDS.VIEW');
        Route::get('/profile', [CustomFieldController::class, 'profile'])->name('profile');
    });


     // projects routes
     Route::prefix(PANEL_MODULES['COMPANY_PANEL']['projects'])->as(PANEL_MODULES['COMPANY_PANEL']['projects'] . '.')->group(function () {
        // role for fetch, store, update
        Route::get('/', [ProjectController::class, 'index'])->name('index')->middleware('check.permission:PROJECTS.READ_ALL');
        Route::post('/', [ProjectController::class, 'store'])->name('store')->middleware('check.permission:PROJECTS.CREATE');
        Route::put('/', [ProjectController::class, 'update'])->name('update')->middleware('check.permission:PROJECTS.UPDATE');

        // add assignee
        Route::post('/add-assignee', [ProjectController::class, 'addAssignee'])->name('addAssignee')->middleware('check.permission:PROJECTS.UPDATE');

        // create, edit, change status, delete
        Route::get('/create', [ProjectController::class, 'create'])->name('create')->middleware('check.permission:PROJECTS.CREATE');
        Route::get('/edit/{id}', [ProjectController::class, 'edit'])->name('edit')->middleware('check.permission:PROJECTS.UPDATE');
        Route::get('/changeStatus/{id}/{status}', [ProjectController::class, 'changeStatus'])->name('changeStatus')->middleware('check.permission:PROJECTS.CHANGE_STATUS');
        Route::delete('/destroy/{id}', [ProjectController::class, 'destroy'])->name('destroy')->middleware('check.permission:PROJECTS.DELETE');

        // validate, export, import
        Route::get('/export', [ProjectController::class, 'export'])->name('export')->middleware('check.permission:PROJECTS.EXPORT');
        Route::get('/import', [ProjectController::class, 'importView'])->name('importView')->middleware('check.permission:PROJECTS.IMPORT');
        Route::post('/import', [ProjectController::class, 'import'])->name('import')->middleware('check.permission:PROJECTS.IMPORT');

        Route::post('/bulkDelete', [ProjectController::class, 'bulkDelete'])->name('bulkDelete')->middleware('check.permission:PROJECTS.BULK_DELETE');

        Route::get('/view/{id}', [ProjectController::class, 'view'])->name('view')->middleware('check.permission:PROJECTS.VIEW');
        Route::get('/profile', [ProjectController::class, 'profile'])->name('profile');


        // comments routes...
        Route::post('/comment', [CommentsController::class, 'addProjectsComment'])->name('comment.create')->middleware('check.permission:PROJECTS.CREATE');
        Route::delete('/comment/destroy/{id}', [CommentsController::class, 'destroyProjectsComment'])->name('comment.destroy')->middleware('check.permission:PROJECTS.DELETE');



        // attachments routes

        Route::post('/attachment', [AttachmentController::class, 'addProjectsAttachment'])->name('attachment.create')->middleware('check.permission:PROJECTS.CREATE');
        Route::delete('/attachment/destroy/{id}', [AttachmentController::class, 'destroyProjectsAttachment'])->name('attachment.destroy')->middleware('check.permission:PROJECTS.DELETE');

    });

      // tasks routes
      Route::prefix(PANEL_MODULES['COMPANY_PANEL']['tasks'])->as(PANEL_MODULES['COMPANY_PANEL']['tasks'] . '.')->group(function () {
        // role for fetch, store, update
        Route::get('/', [TasksController::class, 'index'])->name('index')->middleware('check.permission:TASKS.READ_ALL');
        Route::post('/', [TasksController::class, 'store'])->name('store')->middleware('check.permission:TASKS.CREATE');
        Route::put('/', [TasksController::class, 'update'])->name('update')->middleware('check.permission:TASKS.UPDATE');

        // create, edit, change status, delete
        Route::get('/create', [TasksController::class, 'create'])->name('create')->middleware('check.permission:TASKS.CREATE');
        Route::get('/edit/{id}', [TasksController::class, 'edit'])->name('edit')->middleware('check.permission:TASKS.UPDATE');
        Route::get('/changeStatus/{id}/{status}', [TasksController::class, 'changeStatus'])->name('changeStatus')->middleware('check.permission:TASKS.CHANGE_STATUS');
        Route::delete('/destroy/{id}', [TasksController::class, 'destroy'])->name('destroy')->middleware('check.permission:TASKS.DELETE');

        Route::post('/bulkDelete', [TasksController::class, 'bulkDelete'])->name('bulkDelete')->middleware('check.permission:TASKS.BULK_DELETE');

        Route::get('/view/{id}', [TasksController::class, 'view'])->name('view')->middleware('check.permission:TASKS.VIEW');
        Route::get('/profile', [TasksController::class, 'profile'])->name('profile');



        // kanban board routes.

        Route::get('/kanban', [TasksController::class, 'kanban'])->name('kanban')->middleware('check.permission:TASKS.KANBAN_BOARD');

        Route::get('/kanbanEdit/{id}', [TasksController::class, 'kanbanEdit'])->name('kanbanEdit')->middleware('check.permission:TASKS.UPDATE');

        Route::get('/kanbanView/{id}', [TasksController::class, 'kanbanView'])->name('kanbanView')->middleware('check.permission:TASKS.READ');

        Route::get('/kanbanLoad', [TasksController::class, 'kanbanLoad'])->name('kanbanLoad')->middleware('check.permission:TASKS.KANBAN_BOARD');

        Route::get('/changeStage/{leadid}/{stageid}', [TasksController::class, 'changeStage'])->name('changeStage')->middleware('check.permission:TASKS.CHANGE_STATUS');



        // comments routes...
        Route::post('/comment', [CommentsController::class, 'addLeadsComment'])->name('comment.create')->middleware('check.permission:TASKS.CREATE');
        Route::delete('/comment/destroy/{id}', [CommentsController::class, 'destroyLeadsComment'])->name('comment.destroy')->middleware('check.permission:TASKS.DELETE');



        // attachments routes

        Route::post('/attachment', [AttachmentController::class, 'addLeadsAttachment'])->name('attachment.create')->middleware('check.permission:TASKS.CREATE');
        Route::delete('/attachment/destroy/{id}', [AttachmentController::class, 'destroyLeadsAttachment'])->name('attachment.destroy')->middleware('check.permission:TASKS.DELETE');

    });
});


// super-panel routes

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
    'check.installation'
])->prefix(getPanelUrl(PANEL_TYPES['SUPER_PANEL']))->as(getPanelUrl(PANEL_TYPES['SUPER_PANEL']) . '.')->group(function () {

    Route::get('/', function () {
        // dd('Super Panel Home');
        return redirect()->route(getPanelUrl(PANEL_TYPES['SUPER_PANEL']) . '.companies.index');
    })->name('home');
    // role routes
    Route::prefix('role')->as('role.')->group(function () {
        // role for fetch, store, update
        Route::get('/', [RoleController::class, 'index'])->name('index')->middleware('check.permission:ROLE.READ_ALL');
        Route::post('/', [RoleController::class, 'store'])->name('store')->middleware('check.permission:ROLE.CREATE');
        Route::put('/', [RoleController::class, 'update'])->name('update')->middleware('check.permission:ROLE.UPDATE');

        // create, edit, change status, delete
        Route::get('/create', [RoleController::class, 'create'])->name('create')->middleware('check.permission:ROLE.CREATE');
        Route::get('/edit/{id}', [RoleController::class, 'edit'])->name('edit')->middleware('check.permission:ROLE.UPDATE');
        Route::get(
            '/changeStatus/{id}/{status}',
            [RoleController::class, 'changeStatus']
        )->name('changeStatus')->middleware('check.permission:ROLE.CHANGE_STATUS');
        Route::delete('/destroy/{id}', [RoleController::class, 'destroy'])->name('destroy')->middleware('check.permission:ROLE.DELETE');

        // validate, export, import
        Route::get('/export', [RoleController::class, 'export'])->name('export')->middleware('check.permission:ROLE.EXPORT');
        Route::get('/import', [RoleController::class, 'importView'])->name('importView')->middleware('check.permission:ROLE.IMPORT');
        Route::post('/import', [RoleController::class, 'import'])->name('import')->middleware('check.permission:ROLE.IMPORT');
        Route::post('/bulkDelete', [RoleController::class, 'bulkDelete'])->name('bulkDelete')->middleware('check.permission:ROLE.BULK_DELETE');
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
        Route::get('/changeStatus/{id}/{status}', [UserController::class, 'changeStatus'])->name('changeStatus')->middleware('check.permission:USERS.CHANGE_STATUS');
        Route::delete('/destroy/{id}', [UserController::class, 'destroy'])->name('destroy')->middleware('check.permission:USERS.DELETE');

        // validate, export, import
        Route::get('/export', [UserController::class, 'export'])->name('export')->middleware('check.permission:USERS.EXPORT');
        Route::get('/import', [UserController::class, 'importView'])->name('importView')->middleware('check.permission:USERS.IMPORT');
        Route::post('/import', [UserController::class, 'import'])->name('import')->middleware('check.permission:USERS.IMPORT');
        Route::post('/bulkDelete', [UserController::class, 'bulkDelete'])->name('bulkDelete')->middleware('check.permission:USERS.BULK_DELETE');
        Route::post('/changePassword', [UserController::class, 'changePassword'])->name('changePassword')->middleware('check.permission:USERS.CHANGE_PASSWORD');
        Route::post('/updatePassword', [UserController::class, 'updatePassword'])->name('updatePassword');

        Route::get('/view/{id}', [UserController::class, 'view'])->name('view')->middleware('check.permission:USERS.VIEW');
        Route::get('/profile', [UserController::class, 'profile'])->name('profile');

        Route::get('/loginas/{userid}', [UserController::class, 'loginas'])->name('loginas')->middleware('check.permission:USERS.LOGIN_AS');
    });



    // companies routes
    Route::prefix('companies')->as('companies.')->group(function () {
        // role for fetch, store, update
        Route::get('/', [CompaniesController::class, 'index'])->name('index')->middleware('check.permission:COMPANIES.READ_ALL');
        Route::post('/', [CompaniesController::class, 'store'])->name('store')->middleware('check.permission:COMPANIES.CREATE');
        Route::put('/', [CompaniesController::class, 'update'])->name('update')->middleware('check.permission:COMPANIES.UPDATE');

        // create, edit, change status, delete
        Route::get('/create', [CompaniesController::class, 'create'])->name('create')->middleware('check.permission:COMPANIES.CREATE');
        Route::get('/edit/{id}', [CompaniesController::class, 'edit'])->name('edit')->middleware('check.permission:COMPANIES.UPDATE');
        Route::get('/changeStatus/{id}/{status}', [CompaniesController::class, 'changeStatus'])->name('changeStatus')->middleware('check.permission:COMPANIES.CHANGE_STATUS');
        Route::delete('/destroy/{id}', [CompaniesController::class, 'destroy'])->name('destroy')->middleware('check.permission:COMPANIES.DELETE');

        // validate, export, import
        Route::get('/export', [CompaniesController::class, 'export'])->name('export')->middleware('check.permission:COMPANIES.EXPORT');
        Route::get('/import', [CompaniesController::class, 'importView'])->name('importView')->middleware('check.permission:COMPANIES.IMPORT');
        Route::post('/import', [CompaniesController::class, 'import'])->name('import')->middleware('check.permission:COMPANIES.IMPORT');

        // view compnay login as company
        Route::get('/loginas/{companyid}', [CompaniesController::class, 'loginas'])->name('loginas')->middleware('check.permission:COMPANIES.LOGIN_AS');
        Route::post('/bulkDelete', [CompaniesController::class, 'bulkDelete'])->name('bulkDelete')->middleware('check.permission:COMPANIES.BULK_DELETE');
        Route::post('/changePassword', [CompaniesController::class, 'changePassword'])->name('changePassword')->middleware('check.permission:COMPANIES.CHANGE_PASSWORD');
        Route::get('/view/{id}', [CompaniesController::class, 'view'])->name('view')->middleware('check.permission:COMPANIES.VIEW');
    });

    // permissions routes
    Route::prefix('permissions')->as('permissions.')->group(function () {
        // role for fetch, store, update
        Route::get('/', [RolePermissionsController::class, 'index'])->name('index')->middleware('check.permission:PERMISSIONS.READ_ALL');
        Route::post('/', [RolePermissionsController::class, 'store'])->name('store')->middleware('check.permission:PERMISSIONS.CREATE');
        Route::put('/', [RolePermissionsController::class, 'update'])->name('update')->middleware('check.permission:PERMISSIONS.UPDATE');

        // create, edit, change status, delete
        Route::get('/create', [RolePermissionsController::class, 'create'])->name('create')->middleware('check.permission:PERMISSIONS.CREATE');
        Route::get('/edit/{id}', [RolePermissionsController::class, 'edit'])->name('edit')->middleware('check.permission:PERMISSIONS.UPDATE');

        Route::delete('/destroy/{id}', [RolePermissionsController::class, 'destroy'])->name('destroy')->middleware('check.permission:PERMISSIONS.DELETE');
    });





    // plans routes
    Route::prefix('plans')->as('plans.')->group(function () {
        // role for fetch, store, update
        Route::get('/', [PlansController::class, 'index'])->name('index')->middleware('check.permission:PLANS.READ_ALL');
        Route::post('/', [PlansController::class, 'store'])->name('store')->middleware('check.permission:PLANS.CREATE');
        Route::put('/', [PlansController::class, 'update'])->name('update')->middleware('check.permission:PLANS.UPDATE');

        // create, edit, change status, delete
        Route::get('/create', [PlansController::class, 'create'])->name('create')->middleware('check.permission:PLANS.CREATE');
        Route::get('/edit/{id}', [PlansController::class, 'edit'])->name('edit')->middleware('check.permission:PLANS.UPDATE');
        Route::get(
            '/changeStatus/{id}/{status}',
            [PlansController::class, 'changeStatus']
        )->name('changeStatus')->middleware('check.permission:PLANS.CHANGE_STATUS');
        Route::delete('/destroy/{id}', [PlansController::class, 'destroy'])->name('destroy')->middleware('check.permission:PLANS.DELETE');
    });

    // planPaymentTransaction routes
    Route::prefix('planPaymentTransaction')->as('planPaymentTransaction.')->group(function () {
        // role for fetch, store, update
        Route::get('/', [PlansPaymentTransaction::class, 'index'])->name('index')->middleware('check.permission:PAYMENTSTRANSACTIONS.READ_ALL');
    });


    // subscriptions routes
    Route::prefix('subscriptions')->as('subscriptions.')->group(function () {
        Route::get('/', [PlansPaymentTransaction::class, 'subscriptions'])->name('index')->middleware('check.permission:SUBSCRIPTIONS.READ_ALL');
    });
    // audits routes
    Route::prefix('audit')->as('audit.')->group(function () {
        Route::get('/', [AuditController::class, 'index'])->name('index')->middleware('check.permission:EVENTS_AUDIT_LOG.READ_ALL');
        Route::get('/bulk-import', [AuditController::class, 'bulkimport'])->name('bulkimport')->middleware('check.permission:BULK_IMPORT_STATUS.READ_ALL');
    });
    // audits routes
    Route::prefix('backup')->as('backup.')->group(function () {
        Route::get('/', [BackupController::class, 'index'])->name('index')->middleware('check.permission:DOWNLOAD_BACKUP.READ_ALL');
        Route::post('/', [BackupController::class, 'createBackup'])->name('createBackup')->middleware('check.permission:DOWNLOAD_BACKUP.CREATE');
        Route::get('/download', [BackupController::class, 'downloadBackup'])
            ->name('download')
            ->middleware(['throttle:5,1', 'check.permission:DOWNLOAD_BACKUP.DOWNLOAD']);
    });

    // payment gateways routes

    Route::prefix('paymentGateway')->as('paymentGateway.')->group(function () {
        // role for fetch, store, update
        Route::get('/', [PaymentGatewayController::class, 'index'])->name('index')->middleware('check.permission:PAYMENTGATEWAYS.READ_ALL');

        Route::put('/', [PaymentGatewayController::class, 'update'])->name('update')->middleware('check.permission:PAYMENTGATEWAYS.UPDATE');

        //  edit, change status, 

        Route::get('/edit/{id}', [PaymentGatewayController::class, 'edit'])->name('edit')->middleware('check.permission:PAYMENTGATEWAYS.UPDATE');
        Route::get(
            '/changeStatus/{id}/{status}',
            [PaymentGatewayController::class, 'changeStatus']
        )->name('changeStatus')->middleware('check.permission:PAYMENTGATEWAYS.CHANGE_STATUS');
    });
    // settings routes
    Route::prefix('settings')->as('settings.')->group(function () {
        // role for fetch, store, update
        Route::get('/general', [SettingsController::class, 'general'])->name('general')->middleware('check.permission:SETTINGS_GENERAL.READ_ALL');
        Route::put('/general', [SettingsController::class, 'generalUpdate'])->name('generalUpdate')->middleware('check.permission:SETTINGS_GENERAL.UPDATE');



        Route::get('/mail', [SettingsController::class, 'mail'])->name('mail')->middleware('check.permission:SETTINGS_MAIL.READ_ALL');
        Route::put('/mail', [SettingsController::class, 'mailUpdate'])->name('mailUpdate')->middleware('check.permission:SETTINGS_MAIL.UPDATE');
        Route::post('/test-connection', [SettingsController::class, 'testMailConnection'])->name('test-connection')->middleware('check.permission:SETTINGS_MAIL.READ_ALL');


        Route::get('/cron', [SettingsController::class, 'cron'])->name('cron')->middleware('check.permission:SETTINGS_CRON.READ_ALL');
    });


    // modules routes
    Route::prefix('modules')->as('modules.')->group(function () {
        Route::get('/', [ModuleController::class, 'index'])->name('index')->middleware('check.permission:MODULES.READ_ALL');
        Route::post('/', [ModuleController::class, 'create'])->name('create')->middleware('check.permission:MODULES.CREATE');
        Route::delete('/destroy/{module}', [ModuleController::class, 'destroy'])->name('destroy')->middleware('check.permission:MODULES.DELETE');
    });


    // appupdates routes
    Route::prefix('appupdates')->as('appupdates.')->group(function () {
        Route::get('/', [AppUpdateController::class, 'index'])->name('index')->middleware('check.permission:APPUPDATES.READ_ALL');
        Route::post('/', [AppUpdateController::class, 'create'])->name('create')->middleware('check.permission:APPUPDATES.CREATE');
    });
});
