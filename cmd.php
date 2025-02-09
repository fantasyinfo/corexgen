php artisan db:seed --class=UserSeeder
php artisan db:seed --class=CRMPermissionsSeeder
php artisan db:seed --class=CRMMenuSeeder
php artisan db:seed --class=CRMSettingsSeeder
php artisan db:seed --class=PlansSeeder
php artisan db:seed --class=PaymentGatewaySeeder
php artisan db:seed --class=FrontendSeeder



# remove cache/config.php file
# include .env and .htaccess file (if zip if created via mackbook)
# remove the installer.lock file from storage dir 
# change the .env SESSION_DRIVER=database to SESSION_DRIVER=file


# change the .env file SESSION_DRIVER=file
rm -f bootstrap/cache/config.php


php artisan config:clear
php artisan cache:clear
php artisan config:cache
php artisan route:clear
php artisan route:cache
php artisan optimize



php artisan tinker

app()->getLoadedProviders();

// for learning
php artisan migrate:rollback --step=1
php artisan make:job SeedCountriesCities


// steps for making a crud
create migrations
create model
create controller 
create request
create repository
create service



/usr/local/bin/php /home/your-username/public_html/artisan queue:work --daemon

php artisan queue:work


// migrate specific table
php artisan migrate --path=/database/migrations/2013_11_25_150421_create_crm_roles_table.php


u804660301_corexgen
Corexgen!123

@extends('layout.app')
@push('style')
@endpush
@section('content')
@endsection
@push('scripts')
@endpush



public function index(){
        
    }
    public function store(){

    }
    public function create(){

    }

    public function update(){

    }
    public function edit(){

    }
    public function destroy(){

    }
    public function export(){

    }
    public function import(){

    }
    public function bulkDelete(){

    }
    public function view(){

    }
    public function profile(){

    }