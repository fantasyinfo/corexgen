php artisan make:controller CRM/CRMRoleController
php artisan make:model CRM/CRMRole
php artisan make:migration create_crm_roles_table



php artisan db:seed --class=UserSeeder