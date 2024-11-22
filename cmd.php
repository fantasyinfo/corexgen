php artisan db:seed --class=UserSeeder
php artisan db:seed --class=CRMPermissionsSeeder
php artisan db:seed --class=CRMMenuSeeder




# change the .env file SESSION_DRIVER=file 
rm -f bootstrap/cache/config.php


php artisan config:clear
php artisan cache:clear
php artisan config:cache
php artisan route:clear
php artisan route:cache
php artisan optimize



// for learning
php artisan migrate:rollback --step=1


u804660301_corexgen
Corexgen!123