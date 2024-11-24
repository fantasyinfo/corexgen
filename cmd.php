php artisan db:seed --class=UserSeeder
php artisan db:seed --class=CRMPermissionsSeeder
php artisan db:seed --class=CRMMenuSeeder
php artisan db:seed --class=CRMSettingsSeeder




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


/usr/local/bin/php /home/your-username/public_html/artisan queue:work --daemon

php artisan queue:work


u804660301_corexgen
Corexgen!123