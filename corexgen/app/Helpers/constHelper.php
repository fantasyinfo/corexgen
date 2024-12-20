<?php

use App\Helpers\PermissionsHelper;


/**
 * Constant Helpers Inside the APP
 */


!defined('CACHE_DEFAULT_HOURS') && define('CACHE_DEFAULT_HOURS', 24);

!defined('BULK_CSV_UPLOAD_FILE_SIZE') && define('BULK_CSV_UPLOAD_FILE_SIZE', 2048);

// initilize the permissions with matching keys for plans and permissions of menus
PermissionsHelper::initializePermissions();



!defined('CRMPERMISSIONS') && define('CRMPERMISSIONS', [
    PermissionsHelper::$plansPermissionsKeys['DASHBOARD'] => [
        'name' => 'DASHBOARD',
        'id' => PermissionsHelper::getParentPermissionId('1'),
        'children' => PermissionsHelper::$PERMISSIONS_IDS['DASHBOARD'],
        'for' => 'both',
        'is_feature' => false
    ],
    PermissionsHelper::$plansPermissionsKeys['ROLE'] => [
        'name' => 'ROLE',
        'id' => PermissionsHelper::getParentPermissionId('2'),
        'children' => PermissionsHelper::$PERMISSIONS_IDS['ROLE'],
        'for' => 'both',
        'is_feature' => true
    ],
    PermissionsHelper::$plansPermissionsKeys['USERS'] => [
        'name' => 'USERS',
        'id' => PermissionsHelper::getParentPermissionId('3'),
        'children' => PermissionsHelper::$PERMISSIONS_IDS['USERS'],
        'for' => 'both',
        'is_feature' => true
    ],
    PermissionsHelper::$plansPermissionsKeys['PERMISSIONS'] => [
        'name' => 'PERMISSIONS',
        'id' => PermissionsHelper::getParentPermissionId('4'),
        'children' => PermissionsHelper::$PERMISSIONS_IDS['PERMISSIONS'],
        'for' => 'both',
        'is_feature' => false
    ],
    PermissionsHelper::$plansPermissionsKeys['SETTINGS'] => [
        'name' => 'SETTINGS',
        'id' => PermissionsHelper::getParentPermissionId('5'),
        'children' => PermissionsHelper::$PERMISSIONS_IDS['SETTINGS'],
        'for' => 'both',
        'is_feature' => false
    ],
    PermissionsHelper::$plansPermissionsKeys['PLANS'] => [
        'name' => 'PLANS',
        'id' => PermissionsHelper::getParentPermissionId('6'),
        'children' => PermissionsHelper::$PERMISSIONS_IDS['PLANS'],
        'for' => 'tenant',
        'is_feature' => false
    ],
    PermissionsHelper::$plansPermissionsKeys['MODULES'] => [
        'name' => 'MODULES',
        'id' => PermissionsHelper::getParentPermissionId('7'),
        'children' => PermissionsHelper::$PERMISSIONS_IDS['MODULES'],
        'for' => 'tenant',
        'is_feature' => false
    ],
    PermissionsHelper::$plansPermissionsKeys['APPUPDATES'] => [
        'name' => 'APPUPDATES',
        'id' => PermissionsHelper::getParentPermissionId('8'),
        'children' => PermissionsHelper::$PERMISSIONS_IDS['APPUPDATES'],
        'for' => 'tenant',
        'is_feature' => false
    ],
    PermissionsHelper::$plansPermissionsKeys['COMPANIES'] => [
        'name' => 'COMPANIES',
        'id' => PermissionsHelper::getParentPermissionId('9'),
        'children' => PermissionsHelper::$PERMISSIONS_IDS['COMPANIES'],
        'for' => 'tenant',
        'is_feature' => false
    ],
    PermissionsHelper::$plansPermissionsKeys['PAYMENTSTRANSACTIONS'] => [
        'name' => 'PAYMENTSTRANSACTIONS',
        'id' => PermissionsHelper::getParentPermissionId('10'),
        'children' => PermissionsHelper::$PERMISSIONS_IDS['PAYMENTSTRANSACTIONS'],
        'for' => 'tenant',
        'is_feature' => false
    ],
    PermissionsHelper::$plansPermissionsKeys['PLANUPGRADE'] => [
        'name' => 'PLANUPGRADE',
        'id' => PermissionsHelper::getParentPermissionId('11'),
        'children' => PermissionsHelper::$PERMISSIONS_IDS['PLANUPGRADE'],
        'for' => 'company',
        'is_feature' => false
    ],
    PermissionsHelper::$plansPermissionsKeys['PAYMENTGATEWAYS'] => [
        'name' => 'PAYMENTGATEWAYS',
        'id' => PermissionsHelper::getParentPermissionId('12'),
        'children' => PermissionsHelper::$PERMISSIONS_IDS['PAYMENTGATEWAYS'],
        'for' => 'tenant',
        'is_feature' => false
    ],
    PermissionsHelper::$plansPermissionsKeys['SUBSCRIPTIONS'] => [
        'name' => 'SUBSCRIPTIONS',
        'id' => PermissionsHelper::getParentPermissionId('13'),
        'children' => PermissionsHelper::$PERMISSIONS_IDS['SUBSCRIPTIONS'],
        'for' => 'tenant',
        'is_feature' => false
    ],
    PermissionsHelper::$plansPermissionsKeys['SETTINGS_GENERAL'] => [
        'name' => 'SETTINGS_GENERAL',
        'id' => PermissionsHelper::getParentPermissionId('14'),
        'children' => PermissionsHelper::$PERMISSIONS_IDS['SETTINGS_GENERAL'],
        'for' => 'both',
        'is_feature' => false
    ],
    PermissionsHelper::$plansPermissionsKeys['SETTINGS_MAIL'] => [
        'name' => 'SETTINGS_MAIL',
        'id' => PermissionsHelper::getParentPermissionId('15'),
        'children' => PermissionsHelper::$PERMISSIONS_IDS['SETTINGS_MAIL'],
        'for' => 'both',
        'is_feature' => false
    ],
    PermissionsHelper::$plansPermissionsKeys['SETTINGS_CRON'] => [
        'name' => 'SETTINGS_CRON',
        'id' => PermissionsHelper::getParentPermissionId('19'),
        'children' => PermissionsHelper::$PERMISSIONS_IDS['SETTINGS_CRON'],
        'for' => 'tenant',
        'is_feature' => false
    ],
    PermissionsHelper::$plansPermissionsKeys['EVENTS_AUDIT_LOG'] => [
        'name' => 'EVENTS_AUDIT_LOG',
        'id' => PermissionsHelper::getParentPermissionId('16'),
        'children' => PermissionsHelper::$PERMISSIONS_IDS['EVENTS_AUDIT_LOG'],
        'for' => 'both',
        'is_feature' => false
    ],
    PermissionsHelper::$plansPermissionsKeys['DOWNLOAD_BACKUP'] => [
        'name' => 'DOWNLOAD_BACKUP',
        'id' => PermissionsHelper::getParentPermissionId('17'),
        'children' => PermissionsHelper::$PERMISSIONS_IDS['DOWNLOAD_BACKUP'],
        'for' => 'tenant',
        'is_feature' => false
    ],

    // starting from 100
    PermissionsHelper::$plansPermissionsKeys['CLIENTS'] => [
        'name' => 'CLIENTS',
        'id' => PermissionsHelper::getParentPermissionId('100'),
        'children' => PermissionsHelper::$PERMISSIONS_IDS['CLIENTS'],
        'for' => 'company',
        'is_feature' => true
    ],
]);

// super panel menus
!defined('CRM_MENU_ITEMS_TENANT') && define('CRM_MENU_ITEMS_TENANT', [
    'Dashboard' => [
        'menu_icon' => 'fa-tachometer-alt',
        'permission_id' => PermissionsHelper::getParentPermissionId('1'),
        'children' => [
            'CRM' => [
                'menu_url' => 'home',
                'menu_icon' => 'fa-tachometer-alt',
                'permission_id' => PermissionsHelper::findPermissionKey(PermissionsHelper::$plansPermissionsKeys['DASHBOARD'], 'READ_ALL')
            ]
        ]
    ],
    'Roles & Permissions' => [
        'menu_icon' => 'fa-users',
        'permission_id' => PermissionsHelper::getParentPermissionId('2'),
        'children' => [
            'Role' => ['menu_url' => 'role.index', 'menu_icon' => 'fa-users', 'permission_id' => PermissionsHelper::findPermissionKey(PermissionsHelper::$plansPermissionsKeys['ROLE'], 'READ_ALL')],
            'Permissions' => ['menu_url' => 'permissions.index', 'menu_icon' => 'fa-user', 'permission_id' => PermissionsHelper::findPermissionKey(PermissionsHelper::$plansPermissionsKeys['PERMISSIONS'], 'READ_ALL')],
        ]
    ],
    'Users & Employees' => [
        'menu_icon' => 'fa-user',
        'permission_id' => PermissionsHelper::getParentPermissionId('3'),
        'children' => [
            'Users' => ['menu_url' => 'users.index', 'menu_icon' => 'fa-user', 'permission_id' => PermissionsHelper::findPermissionKey(PermissionsHelper::$plansPermissionsKeys['USERS'], 'READ_ALL')],
        ]
    ],
    'Companies' => [
        'menu_icon' => 'fa-building',
        'permission_id' => PermissionsHelper::getParentPermissionId('4'),
        'children' => [
            'Companies' => ['menu_url' => 'companies.index', 'menu_icon' => 'fa-user', 'permission_id' => PermissionsHelper::findPermissionKey(PermissionsHelper::$plansPermissionsKeys['COMPANIES'], 'READ_ALL')],
        ]
    ],
    'Plans' => [
        'menu_icon' => 'fa-clipboard-list',
        'permission_id' => PermissionsHelper::getParentPermissionId('5'),
        'children' => [
            'Plans' => ['menu_url' => 'plans.index', 'menu_icon' => 'fa-user', 'permission_id' => PermissionsHelper::findPermissionKey(PermissionsHelper::$plansPermissionsKeys['PLANS'], 'READ_ALL')],

        ]
    ],
    'Gateway & Transactions' => [
        'menu_icon' => 'fas fa-file-invoice-dollar',
        'permission_id' => PermissionsHelper::getParentPermissionId('10'),
        'children' => [
            'Gateways' => ['menu_url' => 'paymentGateway.index', 'menu_icon' => 'fa-user', 'permission_id' => PermissionsHelper::findPermissionKey(PermissionsHelper::$plansPermissionsKeys['PAYMENTGATEWAYS'], 'READ_ALL')],
            'Transactions' => ['menu_url' => 'planPaymentTransaction.index', 'menu_icon' => 'fa-user', 'permission_id' => PermissionsHelper::findPermissionKey(PermissionsHelper::$plansPermissionsKeys['PAYMENTSTRANSACTIONS'], 'READ_ALL')],
            'Subscriptions' => ['menu_url' => 'subscriptions.index', 'menu_icon' => 'fa-user', 'permission_id' => PermissionsHelper::findPermissionKey(PermissionsHelper::$plansPermissionsKeys['SUBSCRIPTIONS'], 'READ_ALL')],

        ]
    ],
    'Settings' => [
        'menu_icon' => 'fa-cog',
        'permission_id' => PermissionsHelper::getParentPermissionId('6'),
        'children' => [
            'General' => ['menu_url' => 'settings.general', 'menu_icon' => 'fa-cog', 'permission_id' => PermissionsHelper::findPermissionKey(PermissionsHelper::$plansPermissionsKeys['SETTINGS_GENERAL'], 'READ')],
            'Mail' => ['menu_url' => 'settings.mail', 'menu_icon' => 'fa-cog', 'permission_id' => PermissionsHelper::findPermissionKey(PermissionsHelper::$plansPermissionsKeys['SETTINGS_MAIL'], 'READ')],
            'Cron' => ['menu_url' => 'settings.cron', 'menu_icon' => 'fa-cog', 'permission_id' => PermissionsHelper::findPermissionKey(PermissionsHelper::$plansPermissionsKeys['SETTINGS_CRON'], 'READ')],
        ]
    ],
    'System Settings' => [
        'menu_icon' => 'fa-box',
        'permission_id' => PermissionsHelper::getParentPermissionId('7'),
        'children' => [
            'Modules' => ['menu_url' => 'modules.index', 'menu_icon' => 'fa-box', 'permission_id' => PermissionsHelper::findPermissionKey(PermissionsHelper::$plansPermissionsKeys['MODULES'], 'READ_ALL')],
            'AppUpdates' => ['menu_url' => 'appupdates.index', 'menu_icon' => 'fa-box', 'permission_id' => PermissionsHelper::findPermissionKey(PermissionsHelper::$plansPermissionsKeys['APPUPDATES'], 'READ_ALL')],
            'Backups' => ['menu_url' => 'backup.index', 'menu_icon' => 'fa-box', 'permission_id' => PermissionsHelper::findPermissionKey(PermissionsHelper::$plansPermissionsKeys['DOWNLOAD_BACKUP'], 'READ_ALL')],
        ]
    ],
    'Logs & Events' => [
        'menu_icon' => 'fa-list-ul',
        'permission_id' => PermissionsHelper::getParentPermissionId('16'),
        'children' => [
            'Audit' => ['menu_url' => 'audit.index', 'menu_icon' => 'fa-box', 'permission_id' => PermissionsHelper::findPermissionKey(PermissionsHelper::$plansPermissionsKeys['EVENTS_AUDIT_LOG'], 'READ_ALL')],

        ]
    ],
]);

// company panel menus
!defined('CRM_MENU_ITEMS_COMPANY') && define('CRM_MENU_ITEMS_COMPANY', [
    'Dashboard' => [
        'menu_icon' => 'fa-tachometer-alt',
        'permission_id' => PermissionsHelper::getParentPermissionId('1'),
        'is_default' => true,
        'children' => [
            'CRM' => [
                'menu_url' => 'home',
                'menu_icon' => 'fa-tachometer-alt',
                'permission_id' => PermissionsHelper::findPermissionKey(PermissionsHelper::$plansPermissionsKeys['DASHBOARD'], 'READ_ALL')
            ]
        ]
    ],
    'Settings' => [
        'menu_icon' => 'fa-cog',
        'is_default' => true,
        'permission_id' => PermissionsHelper::getParentPermissionId('6'),
        'children' => [
            'General' => ['menu_url' => 'settings.general', 'menu_icon' => 'fa-cog', 'permission_id' => PermissionsHelper::findPermissionKey(PermissionsHelper::$plansPermissionsKeys['SETTINGS'], 'READ')],
            'Mail' => ['menu_url' => 'settings.mail', 'menu_icon' => 'fa-cog', 'permission_id' => PermissionsHelper::findPermissionKey(PermissionsHelper::$plansPermissionsKeys['SETTINGS_MAIL'], 'READ')],
        ]
    ],
    'Roles & Permissions' => [
        'menu_icon' => 'fa-users',
        'feature_type' => PermissionsHelper::$plansPermissionsKeys['ROLE'], // this need to match the PLANS_FEATURES key
        'permission_plan' => PermissionsHelper::$plansPermissionsKeys['ROLE'],
        'permission_id' => PermissionsHelper::getParentPermissionId('2'),
        'children' => [
            'Role' => ['menu_url' => 'role.index', 'menu_icon' => 'fa-users', 'permission_id' => PermissionsHelper::findPermissionKey(PermissionsHelper::$plansPermissionsKeys['ROLE'], 'READ_ALL')],
            'Permissions' => ['menu_url' => 'permissions.index', 'menu_icon' => 'fa-user', 'permission_id' => PermissionsHelper::findPermissionKey(PermissionsHelper::$plansPermissionsKeys['PERMISSIONS'], 'READ_ALL')],
        ]
    ],
    'Users & Employees' => [
        'menu_icon' => 'fa-user',
        'feature_type' => PermissionsHelper::$plansPermissionsKeys['USERS'], // this need to match the PLANS_FEATURES key
        'permission_plan' => PermissionsHelper::$plansPermissionsKeys['USERS'],
        'permission_id' => PermissionsHelper::getParentPermissionId('3'),
        'children' => [
            'Users' => ['menu_url' => 'users.index', 'menu_icon' => 'fa-user', 'permission_id' => PermissionsHelper::findPermissionKey(PermissionsHelper::$plansPermissionsKeys['USERS'], 'READ_ALL')],
        ]
    ],
    'Membership' => [
        'menu_icon' => 'fa-paper-plane',
        'is_default' => true,
        'permission_plan' => PermissionsHelper::$plansPermissionsKeys['PLANUPGRADE'],
        'permission_id' => PermissionsHelper::getParentPermissionId('11'),
        'children' => [
            'Membership' => ['menu_url' => 'planupgrade.index', 'menu_icon' => 'fa-user', 'permission_id' => PermissionsHelper::findPermissionKey(PermissionsHelper::$plansPermissionsKeys['PLANUPGRADE'], 'READ_ALL')],
        ]
    ],
    'Logs & Events' => [
        'menu_icon' => 'fa-list-ul',
        'is_default' => true,
        'permission_id' => PermissionsHelper::getParentPermissionId('16'),
        'children' => [
            'Audit' => ['menu_url' => 'audit.index', 'menu_icon' => 'fa-box', 'permission_id' => PermissionsHelper::findPermissionKey(PermissionsHelper::$plansPermissionsKeys['EVENTS_AUDIT_LOG'], 'READ_ALL')],

        ]
    ],

    'Clients' => [
        'menu_icon' => 'fa-users',
        'feature_type' => PermissionsHelper::$plansPermissionsKeys['CLIENTS'], // this need to match the PLANS_FEATURES key
        'permission_plan' => PermissionsHelper::$plansPermissionsKeys['CLIENTS'],
        'permission_id' => PermissionsHelper::getParentPermissionId('100'),
        'children' => [
            'Clients' => ['menu_url' => 'clients.index', 'menu_icon' => 'fa-user', 'permission_id' => PermissionsHelper::findPermissionKey(PermissionsHelper::$plansPermissionsKeys['CLIENTS'], 'READ_ALL')],
        ]
    ],
]);



!defined('CRM_STATUS_TYPES') && define('CRM_STATUS_TYPES', [
    'TENANTS' => [
        'TABLE_STATUS' => ['ACTIVE', 'DEACTIVE'],
        'STATUS' => ['ACTIVE' => 'ACTIVE', 'DEACTIVE' => 'DEACTIVE'],
        'BT_CLASSES' => ['ACTIVE' => 'success', 'DEACTIVE' => 'danger'],
    ],
    'COMPANIES' => [
        'TABLE_STATUS' => ['ACTIVE', 'DEACTIVE', 'BANNED', 'ONBOARDING'],
        'STATUS' => ['ACTIVE' => 'ACTIVE', 'DEACTIVE' => 'DEACTIVE', 'BANNED' => 'BANNED', 'ONBOARDING' => 'ONBOARDING'],
        'BT_CLASSES' => ['ACTIVE' => 'success', 'DEACTIVE' => 'danger', 'BANNED' => 'warning', 'ONBOARDING' => 'info'],
    ],

    'COMPANIES_ONBORDING' => [
        'TABLE_STATUS' => ['NOT_STARTED', 'IN_PROGRESS', 'ADDRESS_CAPTURED', 'CURRENCY_CAPTURED', 'TIMEZONE_CAPTURED', 'PLAN_CAPTURED', 'PAYMENT_PENDING', 'COMPLETE'],
        'STATUS' => [
            'NOT_STARTED' => 'NOT_STARTED',
            'IN_PROGRESS' => 'IN_PROGRESS',
            'ADDRESS_CAPTURED' => 'ADDRESS_CAPTURED',
            'CURRENCY_CAPTURED' => 'CURRENCY_CAPTURED',
            'TIMEZONE_CAPTURED' => 'TIMEZONE_CAPTURED',
            'PLAN_CAPTURED' => 'PLAN_CAPTURED',
            'PAYMENT_PENDING' => 'PAYMENT_PENDING',
            'COMPLETE' => 'COMPLETE',
        ],
        'BT_CLASSES' => ['ACTIVE' => 'success', 'DEACTIVE' => 'danger', 'BANNED' => 'warning', 'ONBOARDING' => 'info'],
    ],
    'USERS' => [
        'TABLE_STATUS' => ['ACTIVE', 'DEACTIVE', 'BANNED'],
        'STATUS' => ['ACTIVE' => 'ACTIVE', 'DEACTIVE' => 'DEACTIVE', 'BANNED' => 'BANNED'],
        'BT_CLASSES' => ['ACTIVE' => 'success', 'DEACTIVE' => 'danger', 'BANNED' => 'warning'],
    ],
    'CLIENTS' => [
        'TABLE_STATUS' => ['ACTIVE', 'DEACTIVE', 'BANNED'],
        'STATUS' => ['ACTIVE' => 'ACTIVE', 'DEACTIVE' => 'DEACTIVE', 'BANNED' => 'BANNED'],
        'BT_CLASSES' => ['ACTIVE' => 'success', 'DEACTIVE' => 'danger', 'BANNED' => 'warning'],
    ],
    'CRM_ROLES' => [
        'TABLE_STATUS' => ['ACTIVE', 'DEACTIVE'],
        'STATUS' => ['ACTIVE' => 'ACTIVE', 'DEACTIVE' => 'DEACTIVE'],
        'BT_CLASSES' => ['ACTIVE' => 'success', 'DEACTIVE' => 'danger'],
    ],
    'TAX_RATES' => [
        'TABLE_STATUS' => ['ACTIVE', 'DEACTIVE'],
        'STATUS' => ['ACTIVE' => 'ACTIVE', 'DEACTIVE' => 'DEACTIVE'],
        'BT_CLASSES' => ['ACTIVE' => 'success', 'DEACTIVE' => 'danger'],
    ],
    'PLANS' => [
        'TABLE_STATUS' => ['ACTIVE', 'DEACTIVE'],
        'STATUS' => ['ACTIVE' => 'ACTIVE', 'DEACTIVE' => 'DEACTIVE'],
        'BT_CLASSES' => ['ACTIVE' => 'success', 'DEACTIVE' => 'danger'],
    ],
    'SUBSCRIPTION' => [
        'TABLE_STATUS' => ['ACTIVE', 'DEACTIVE'],
        'STATUS' => ['ACTIVE' => 'ACTIVE', 'DEACTIVE' => 'DEACTIVE'],
        'BT_CLASSES' => ['ACTIVE' => 'success', 'DEACTIVE' => 'danger'],
    ],
    'PAYMENTSTRANSACTIONS' => [
        'TABLE_STATUS' => ['SUCCESS', 'FAILED', 'PENDING'],
        'STATUS' => ['SUCCESS' => 'SUCCESS', 'FAILED' => 'FAILED', 'PENDING' => 'PENDING'],
        'BT_CLASSES' => ['SUCCESS' => 'success', 'FAILED' => 'danger', 'PENDING' => 'warning'],
    ],

]);

!defined('PANEL_TYPES') && define('PANEL_TYPES', [
    'SUPER_PANEL' => 'SUPER_PANEL',
    'COMPANY_PANEL' => 'COMPANY_PANEL'
]);

// web.php file prefix for routes
!defined('PANEL_MODULES') && define('PANEL_MODULES', [
    'SUPER_PANEL' => [
        'dashboard' => 'dashboard',
        'role' => 'role',
        'permissions' => 'permissions',
        'settings' => 'settings',
        'users' => 'users',
        'modules' => 'modules',
        'appupdates' => 'appupdates',
        'companies' => 'companies',
        'plans' => 'plans',
        'planPaymentTransaction' => 'planPaymentTransaction',
        'paymentGateway' => 'paymentGateway',
        'subscriptions' => 'subscriptions',
        'audit' => 'audit',
        'backup' => 'backup',


    ],
    'COMPANY_PANEL' => [
        'dashboard' => 'dashboard',
        'role' => 'role',
        'permissions' => 'permissions',
        'settings' => 'settings',
        'users' => 'users',
        'modules' => 'modules',
        'appupdates' => 'appupdates',
        'planupgrade' => 'planupgrade',
        'audit' => 'audit',
        'clients' => 'clients',
    ]
]);


!defined('PLANS_BILLING_CYCLES') && define('PLANS_BILLING_CYCLES', [
    'BILLINGS_TABLE' => ['1 MONTH', '3 MONTHS', '6 MONTHS', '1 YEAR', 'UNLIMITED'],
    'BILLINGS' => [
        '1 MONTH' => '1 MONTH',
        '3 MONTHS' => '3 MONTHS',
        '6 MONTHS' => '6 MONTHS',
        '1 YEAR' => '1 YEAR',
        'UNLIMITED' => 'UNLIMITED'
    ],
]);



// these values must match with permissionHelper::PERMISSIONS_IDS keys otherwise permission not working properly
!defined('PLANS_FEATURES') && define('PLANS_FEATURES', [
    PermissionsHelper::$plansPermissionsKeys['USERS'] => PermissionsHelper::$plansPermissionsKeys['USERS'],
    PermissionsHelper::$plansPermissionsKeys['ROLE'] => PermissionsHelper::$plansPermissionsKeys['ROLE'],
    PermissionsHelper::$plansPermissionsKeys['CLIENTS'] => PermissionsHelper::$plansPermissionsKeys['CLIENTS'],
]);

!defined('ADDRESS_TYPES') && define('ADDRESS_TYPES', [
    'COMPANY' => [
        'TABLE' => ['HOME', 'OFFICE'],
        'SHOW' => ['HOME' => 'HOME', 'OFFICE' => 'OFFICE'],
    ],
    'USER' => [
        'TABLE' => ['HOME', 'OFFICE'],
        'SHOW' => ['HOME' => 'HOME', 'OFFICE' => 'OFFICE'],
    ],
]);

!defined('PAYMENT_GATEWAYS') && define('PAYMENT_GATEWAYS', [
    'STRIPE' => [
        'name' => 'Stripe',
        'official_website' => 'stripe.com',
        'logo' => 'stripe.png',
        'type' => 'International',
        'config_key' => 'pk_test_o6ScAy3rikKa1jKhsNZ9HwLn00HIrNEESf',
        'config_value' => 'sk_test_ZpiCxEOHseka5xDnfwoRoG0700L2MOuJkS',
        'mode' => 'LIVE',
        'status' => 'Active'
    ]
]);


!defined('SETTINGS_MENU_ITEMS') && define('SETTINGS_MENU_ITEMS', [
    'General' => [
        'name' => 'General',
        'link' => 'general',
        'icon' => 'fa-cog',
        'for' => 'both',
    ],
    'Mail' => [
        'name' => 'Mail',
        'link' => 'mail',
        'icon' => 'fa-envelope',
        'for' => 'both',
    ],
    'Cron' => [
        'name' => 'Cron Job',
        'link' => 'cron',
        'icon' => 'fa-hourglass-half',
        'for' => 'tenant',
    ]
]);

// general settings 
!defined('CRM_TENANT_GENERAL_SETTINGS') && define('CRM_TENANT_GENERAL_SETTINGS', [

    'COMPANY_NAME' => [
        'key' => 'Panel Company Name',
        'value' => 'Core X Gen',
        'is_media_setting' => false,
        'media_id' => null,
        'value_type' => 'string',
        'input_type' => 'text',
        'is_tenant' => true,
        'placeholder' => 'Digital Pvt Ltd',
        'name' => 'tenant_company_name'
    ],
    'COMPANY_TAGLINE' => [
        'key' => 'Panel Company Tagline',
        'value' => 'Next Generation CRM',
        'is_media_setting' => false,
        'media_id' => null,
        'value_type' => 'string',
        'input_type' => 'text',
        'is_tenant' => true,
        'placeholder' => 'Best Company in the country.',
        'name' => 'tenant_company_tagline'
    ],
    'COMPANY_LOGO' => [
        'key' => 'Panel Company Logo',
        'value' => 'logos/logo.png',
        'is_media_setting' => true,
        'media_id' => null,
        'value_type' => 'image',
        'input_type' => 'image',
        'is_tenant' => true,
        'name' => 'tenant_company_logo'
    ],
    'DATE_FORMAT' => [
        'key' => 'Panel Date Format',
        'value' => 'YY-MM-DD',
        'is_media_setting' => false,
        'media_id' => null,
        'value_type' => 'string',
        'input_type' => 'dropdown',
        'is_tenant' => true,
        'placeholder' => 'YY-MM-DD',
        'name' => 'tenant_company_date_format'
    ],
    'TIME_ZONE' => [
        'key' => 'Panel Time Zone',
        'value' => 'default',
        'is_media_setting' => false,
        'media_id' => null,
        'value_type' => 'string',
        'input_type' => 'dropdown',
        'is_tenant' => true,
        'placeholder' => 'Asia/Kolkata',
        'name' => 'tenant_company_time_zone',
    ],
    'CURRENCY_SYMBOL' => [
        'key' => 'Panel Currency Symbol',
        'value' => 'default',
        'is_media_setting' => false,
        'media_id' => null,
        'value_type' => 'string',
        'input_type' => 'text',
        'is_tenant' => true,
        'placeholder' => '$',
        'name' => 'tenant_company_currency_symbol'
    ],
    'CURRENCY_CODE' => [
        'key' => 'Panel Currency Code',
        'value' => 'default',
        'is_media_setting' => false,
        'media_id' => null,
        'value_type' => 'string',
        'input_type' => 'text',
        'is_tenant' => true,
        'placeholder' => 'USD',
        'name' => 'tenant_company_currency_code'
    ],



]);

!defined('CRM_COMPANY_GENERAL_SETTINGS') && define('CRM_COMPANY_GENERAL_SETTINGS', [

    'COMPANY_NAME' => [
        'key' => 'Company Name',
        'value' => 'Core X Gen',
        'is_media_setting' => false,
        'media_id' => null,
        'value_type' => 'string',
        'input_type' => 'text',
        'is_tenant' => false,
        'placeholder' => 'Digital Pvt Ltd',
        'name' => 'client_company_name'
    ],
    'COMPANY_TAGLINE' => [
        'key' => 'Company Tagline',
        'value' => 'Next Generation CRM',
        'is_media_setting' => false,
        'media_id' => null,
        'value_type' => 'string',
        'input_type' => 'text',
        'is_tenant' => false,
        'placeholder' => 'Best Company in the country.',
        'name' => 'client_company_tagline'
    ],
    'COMPANY_LOGO' => [
        'key' => 'Company Logo',
        'value' => 'logos/logo.png',
        'is_media_setting' => true,
        'media_id' => null,
        'value_type' => 'image',
        'input_type' => 'image',
        'is_tenant' => false,
        'name' => 'client_company_logo'
    ],
    'DATE_FORMAT' => [
        'key' => 'Date Format',
        'value' => 'YY-MM-DD',
        'is_media_setting' => false,
        'media_id' => null,
        'value_type' => 'string',
        'input_type' => 'dropdown',
        'is_tenant' => false,
        'placeholder' => 'YY-MM-DD',
        'name' => 'client_company_date_format'
    ],
    'TIME_ZONE' => [
        'key' => 'Time Zone',
        'value' => 'default',
        'is_media_setting' => false,
        'media_id' => null,
        'value_type' => 'string',
        'input_type' => 'dropdown',
        'is_tenant' => false,
        'placeholder' => 'Asia/Kolkata',
        'name' => 'client_company_time_zone',
    ],
    'CURRENCY_SYMBOL' => [
        'key' => 'Currency Symbol',
        'value' => 'default',
        'is_media_setting' => false,
        'media_id' => null,
        'value_type' => 'string',
        'input_type' => 'text',
        'is_tenant' => false,
        'placeholder' => '$',
        'name' => 'client_company_currency_symbol'
    ],
    'CURRENCY_CODE' => [
        'key' => 'Currency Code',
        'value' => 'default',
        'is_media_setting' => false,
        'media_id' => null,
        'value_type' => 'string',
        'input_type' => 'text',
        'is_tenant' => false,
        'placeholder' => 'USD',
        'name' => 'client_company_currency_code'
    ],



]);

// mail settings
!defined('CRM_TENANT_MAIL_SETTINGS') && define('CRM_TENANT_MAIL_SETTINGS', [

    'MAIL_PROVIDER' => [
        'key' => 'Panel Mail Provider',
        'value' => 'default',
        'is_media_setting' => false,
        'media_id' => null,
        'value_type' => 'string',
        'input_type' => 'text',
        'is_tenant' => true,
        'placeholder' => 'smtp',
        'name' => 'tenant_mail_provider'
    ],
    'MAIL_HOST' => [
        'key' => 'Panel Mail Host',
        'value' => 'default',
        'is_media_setting' => false,
        'media_id' => null,
        'value_type' => 'string',
        'input_type' => 'text',
        'is_tenant' => true,
        'placeholder' => 'smtp.gmail.com',
        'name' => 'tenant_mail_host'
    ],
    'MAIL_PORT' => [
        'key' => 'Panel Mail Port',
        'value' => 'default',
        'is_media_setting' => false,
        'media_id' => null,
        'value_type' => 'string',
        'input_type' => 'number',
        'is_tenant' => true,
        'placeholder' => '25,465,587',
        'name' => 'tenant_mail_port'
    ],
    'MAIL_USERNAME' => [
        'key' => 'Panel Mail Username',
        'value' => 'default',
        'is_media_setting' => false,
        'media_id' => null,
        'value_type' => 'string',
        'input_type' => 'text',
        'is_tenant' => true,
        'placeholder' => 'admin / admin@gmail.com',
        'name' => 'tenant_mail_username'
    ],
    'MAIL_PASSWORD' => [
        'key' => 'Panel Mail Password',
        'value' => 'default',
        'is_media_setting' => false,
        'media_id' => null,
        'value_type' => 'string',
        'input_type' => 'password',
        'is_tenant' => true,
        'placeholder' => 'admin / admin@gmail.com',
        'name' => 'tenant_mail_password'
    ],
    'MAIL_ENCRYPTION' => [
        'key' => 'Panel Mail Encryption',
        'value' => 'default',
        'is_media_setting' => false,
        'media_id' => null,
        'value_type' => 'string',
        'input_type' => 'dropdown',
        'is_tenant' => true,
        'placeholder' => 'SSL / TLS / No / None',
        'name' => 'tenant_mail_encryption'
    ],
    'MAIL_FROM_ADDRESS' => [
        'key' => 'Panel Mail From Address',
        'value' => 'default',
        'is_media_setting' => false,
        'media_id' => null,
        'value_type' => 'string',
        'input_type' => 'email',
        'is_tenant' => true,
        'placeholder' => 'sales@gmail.com',
        'name' => 'tenant_mail_from_address'
    ],
    'MAIL_FROM_NAME' => [
        'key' => 'Panel Mail From Name',
        'value' => 'default',
        'is_media_setting' => false,
        'media_id' => null,
        'value_type' => 'string',
        'input_type' => 'text',
        'is_tenant' => true,
        'placeholder' => 'Josh Doe',
        'name' => 'tenant_mail_from_name'
    ],
]);

!defined('CRM_COMPANY_MAIL_SETTINGS') && define('CRM_COMPANY_MAIL_SETTINGS', [

    'MAIL_PROVIDER' => [
        'key' => 'Mail Provider',
        'value' => 'default',
        'is_media_setting' => false,
        'media_id' => null,
        'value_type' => 'string',
        'input_type' => 'text',
        'is_tenant' => true,
        'placeholder' => 'smtp',
        'name' => 'client_mail_provider'
    ],
    'MAIL_HOST' => [
        'key' => 'Mail Host',
        'value' => 'default',
        'is_media_setting' => false,
        'media_id' => null,
        'value_type' => 'string',
        'input_type' => 'text',
        'is_tenant' => true,
        'placeholder' => 'smtp.gmail.com',
        'name' => 'client_mail_host'
    ],
    'MAIL_PORT' => [
        'key' => 'Mail Port',
        'value' => 'default',
        'is_media_setting' => false,
        'media_id' => null,
        'value_type' => 'string',
        'input_type' => 'number',
        'is_tenant' => true,
        'placeholder' => '25,465,587',
        'name' => 'client_mail_port'
    ],
    'MAIL_USERNAME' => [
        'key' => 'Mail Username',
        'value' => 'default',
        'is_media_setting' => false,
        'media_id' => null,
        'value_type' => 'string',
        'input_type' => 'text',
        'is_tenant' => true,
        'placeholder' => 'admin / admin@gmail.com',
        'name' => 'client_mail_username'
    ],
    'MAIL_PASSWORD' => [
        'key' => 'Mail Password',
        'value' => 'default',
        'is_media_setting' => false,
        'media_id' => null,
        'value_type' => 'string',
        'input_type' => 'password',
        'is_tenant' => true,
        'placeholder' => 'admin / admin@gmail.com',
        'name' => 'client_mail_password'
    ],
    'MAIL_ENCRYPTION' => [
        'key' => 'Mail Encryption',
        'value' => 'default',
        'is_media_setting' => false,
        'media_id' => null,
        'value_type' => 'string',
        'input_type' => 'dropdown',
        'is_tenant' => true,
        'placeholder' => 'SSL / TLS / No / None',
        'name' => 'client_mail_encryption'
    ],
    'MAIL_FROM_ADDRESS' => [
        'key' => 'Mail From Address',
        'value' => 'default',
        'is_media_setting' => false,
        'media_id' => null,
        'value_type' => 'string',
        'input_type' => 'email',
        'is_tenant' => true,
        'placeholder' => 'sales@gmail.com',
        'name' => 'client_mail_from_address'
    ],
    'MAIL_FROM_NAME' => [
        'key' => 'Mail From Name',
        'value' => 'default',
        'is_media_setting' => false,
        'media_id' => null,
        'value_type' => 'string',
        'input_type' => 'text',
        'is_tenant' => true,
        'placeholder' => 'Josh Doe',
        'name' => 'client_mail_from_name'
    ],
]);


!defined('CLIENTS_CATEGORY_TYPES') && define('CLIENTS_CATEGORY_TYPES', [
    'TABLE_STATUS' => ['VIP', 'Normal', 'High Budget', 'Low Budget'],
    'STATUS' => ['VIP' => 'VIP', 'Normal' => 'Normal', 'High Budget' => 'High Budget', 'Low Budget' => 'Low Budget'],
]);



