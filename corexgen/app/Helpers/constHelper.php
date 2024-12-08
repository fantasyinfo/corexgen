<?php

use App\Helpers\PermissionsHelper;


/**
 * Constant Helpers Inside the APP
 */




// initilize the permissions with matching keys for plans and permissions of menus
PermissionsHelper::initializePermissions();


!defined('CRMPERMISSIONS') && define('CRMPERMISSIONS', [
    PermissionsHelper::$plansPermissionsKeys['DASHBOARD'] => [
        'name' => 'DASHBOARD',
        'id' => PermissionsHelper::getParentPermissionId('1'),
        'children' => PermissionsHelper::$PERMISSIONS_IDS['DASHBOARD']
    ],
    PermissionsHelper::$plansPermissionsKeys['ROLE'] => [
        'name' => 'ROLE',
        'id' => PermissionsHelper::getParentPermissionId('2'),
        'children' => PermissionsHelper::$PERMISSIONS_IDS['ROLE']
    ],
    PermissionsHelper::$plansPermissionsKeys['USERS'] => [
        'name' => 'USERS',
        'id' => PermissionsHelper::getParentPermissionId('3'),
        'children' => PermissionsHelper::$PERMISSIONS_IDS['USERS']
    ],
    PermissionsHelper::$plansPermissionsKeys['PERMISSIONS'] => [
        'name' => 'PERMISSIONS',
        'id' => PermissionsHelper::getParentPermissionId('4'),
        'children' => PermissionsHelper::$PERMISSIONS_IDS['PERMISSIONS']
    ],
    PermissionsHelper::$plansPermissionsKeys['SETTINGS'] => [
        'name' => 'SETTINGS',
        'id' => PermissionsHelper::getParentPermissionId('5'),
        'children' => PermissionsHelper::$PERMISSIONS_IDS['SETTINGS']
    ],
    PermissionsHelper::$plansPermissionsKeys['PLANS'] => [
        'name' => 'PLANS',
        'id' => PermissionsHelper::getParentPermissionId('6'),
        'children' => PermissionsHelper::$PERMISSIONS_IDS['PLANS']
    ],
    PermissionsHelper::$plansPermissionsKeys['MODULES'] => [
        'name' => 'MODULES',
        'id' => PermissionsHelper::getParentPermissionId('7'),
        'children' => PermissionsHelper::$PERMISSIONS_IDS['MODULES']
    ],
    PermissionsHelper::$plansPermissionsKeys['APPUPDATES'] => [
        'name' => 'APPUPDATES',
        'id' => PermissionsHelper::getParentPermissionId('8'),
        'children' => PermissionsHelper::$PERMISSIONS_IDS['APPUPDATES']
    ],
    PermissionsHelper::$plansPermissionsKeys['COMPANIES'] => [
        'name' => 'COMPANIES',
        'id' => PermissionsHelper::getParentPermissionId('9'),
        'children' => PermissionsHelper::$PERMISSIONS_IDS['COMPANIES']
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
    'Compaines' => [
        'menu_icon' => 'fa-building',
        'permission_id' => PermissionsHelper::getParentPermissionId('4'),
        'children' => [
            'Compaines' => ['menu_url' => 'companies.index', 'menu_icon' => 'fa-user', 'permission_id' => PermissionsHelper::findPermissionKey(PermissionsHelper::$plansPermissionsKeys['COMPANIES'], 'READ_ALL')],
        ]
    ],
    'Plans' => [
        'menu_icon' => 'fa-clipboard-list',
        'permission_id' => PermissionsHelper::getParentPermissionId('5'),
        'children' => [
            'Plans' => ['menu_url' => 'plans.index', 'menu_icon' => 'fa-user', 'permission_id' => PermissionsHelper::findPermissionKey(PermissionsHelper::$plansPermissionsKeys['PLANS'], 'READ_ALL')],

        ]
    ],
    'Settings' => [
        'menu_icon' => 'fa-cog',
        'permission_id' => PermissionsHelper::getParentPermissionId('6'),
        'children' => [
            'Settings' => ['menu_url' => 'settings.index', 'menu_icon' => 'fa-cog', 'permission_id' => PermissionsHelper::findPermissionKey(PermissionsHelper::$plansPermissionsKeys['SETTINGS'], 'READ')],
        ]
    ],
    'Modules' => [
        'menu_icon' => 'fa-box',
        'permission_id' => PermissionsHelper::getParentPermissionId('7'),
        'children' => [
            'Modules' => ['menu_url' => 'modules.index', 'menu_icon' => 'fa-box', 'permission_id' => PermissionsHelper::findPermissionKey(PermissionsHelper::$plansPermissionsKeys['MODULES'], 'READ_ALL')],
            'AppUpdates' => ['menu_url' => 'appupdates.index', 'menu_icon' => 'fa-box', 'permission_id' => PermissionsHelper::findPermissionKey(PermissionsHelper::$plansPermissionsKeys['APPUPDATES'], 'READ_ALL')],
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
            'Settings' => ['menu_url' => 'settings.index', 'menu_icon' => 'fa-cog', 'permission_id' => PermissionsHelper::findPermissionKey(PermissionsHelper::$plansPermissionsKeys['SETTINGS'], 'READ')],
        ]
    ],
    'Roles & Permissions' => [
        'menu_icon' => 'fa-users',
        'permission_plan' => PermissionsHelper::$plansPermissionsKeys['ROLE'],
        'permission_id' => PermissionsHelper::getParentPermissionId('2'),
        'children' => [
            'Role' => ['menu_url' => 'role.index', 'menu_icon' => 'fa-users', 'permission_id' => PermissionsHelper::findPermissionKey(PermissionsHelper::$plansPermissionsKeys['ROLE'], 'READ_ALL')],
            'Permissions' => ['menu_url' => 'permissions.index', 'menu_icon' => 'fa-user', 'permission_id' => PermissionsHelper::findPermissionKey(PermissionsHelper::$plansPermissionsKeys['PERMISSIONS'], 'READ_ALL')],
        ]
    ],
    'Users & Employees' => [
        'menu_icon' => 'fa-user',
        'permission_plan' => PermissionsHelper::$plansPermissionsKeys['USERS'],
        'permission_id' => PermissionsHelper::getParentPermissionId('3'),
        'children' => [
            'Users' => ['menu_url' => 'users.index', 'menu_icon' => 'fa-user', 'permission_id' => PermissionsHelper::findPermissionKey(PermissionsHelper::$plansPermissionsKeys['USERS'], 'READ_ALL')],
        ]
    ],

]);

!defined('CRM_TENANT_SETTINGS') && define('CRM_TENANT_SETTINGS', [

    'COMPANY_NAME' => [
        'key' => 'Company Name',
        'value' => 'Core X Gen',
        'is_media_setting' => false,
        'media_id' => null,
        'input_type' => 'string',
        'is_tenant' => true,
        'company_id' => null
    ],
    'COMPANY_TAGLINE' => [
        'key' => 'Company Tagline',
        'value' => 'Next Generation CRM',
        'is_media_setting' => false,
        'media_id' => null,
        'input_type' => 'string',
        'is_tenant' => true,
        'company_id' => null
    ],
    'COMPANY_LOGO' => [
        'key' => 'Company Logo',
        'value' => '/',
        'is_media_setting' => true,
        'media_id' => null,
        'input_type' => 'image',
        'is_tenant' => true,
        'company_id' => null
    ],
    'DATE_FORMAT' => [
        'key' => 'Date Format',
        'value' => 'DD/MM/YYYY',
        'is_media_setting' => false,
        'media_id' => null,
        'input_type' => 'string',
        'is_tenant' => true,
        'company_id' => null
    ],
    'TIME_ZONE' => [
        'key' => 'Time Zone',
        'value' => 'Asia/Kolkata',
        'is_media_setting' => false,
        'media_id' => null,
        'input_type' => 'string',
        'is_tenant' => true,
        'company_id' => null
    ],
    'CURRENCY_SYMBOL' => [
        'key' => 'Currency Symbol',
        'value' => '$',
        'is_media_setting' => false,
        'media_id' => null,
        'input_type' => 'string',
        'is_tenant' => true,
        'company_id' => null
    ],
    'CURRENCY_CODE' => [
        'key' => 'Currency Code',
        'value' => 'USD',
        'is_media_setting' => false,
        'media_id' => null,
        'input_type' => 'string',
        'is_tenant' => true,
        'company_id' => null
    ],
    'SMTP' => [
        'key' => 'SMTP',
        'value' => json_encode([]),
        'is_media_setting' => false,
        'media_id' => null,
        'input_type' => 'json',
        'is_tenant' => true,
        'company_id' => null
    ]


]);

!defined('CRM_STATUS_TYPES') && define('CRM_STATUS_TYPES', [
    'TENANTS' => [
        'TABLE_STATUS' => ['ACTIVE', 'DEACTIVE'],
        'STATUS' => ['ACTIVE' => 'ACTIVE', 'DEACTIVE' => 'DEACTIVE'],
        'BT_CLASSES' => ['ACTIVE' => 'success', 'DEACTIVE' => 'danger'],
    ],
    'COMPANIES' => [
        'TABLE_STATUS' => ['ACTIVE', 'DEACTIVE', 'BANNED'],
        'STATUS' => ['ACTIVE' => 'ACTIVE', 'DEACTIVE' => 'DEACTIVE', 'BANNED' => 'BANNED'],
        'BT_CLASSES' => ['ACTIVE' => 'success', 'DEACTIVE' => 'danger', 'BANNED' => 'warning'],
    ],
    'USERS' => [
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
    'TRANSACTIONS' => [
        'TABLE_STATUS' => ['SUCCESS', 'FAILED', 'PENDING'],
        'STATUS' => ['SUCCESS' => 'SUCCESS', 'FAILED' => 'FAILED', 'PENDING' => 'PENDING'],
        'BT_CLASSES' => ['SUCCESS' => 'success', 'FAILED' => 'danger', 'PENDING' => 'warning'],
    ],

]);

!defined('PANEL_TYPES') && define('PANEL_TYPES', [
    'SUPER_PANEL' => 'SUPER_PANEL',
    'COMPANY_PANEL' => 'COMPANY_PANEL'
]);

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


    ],
    'COMPANY_PANEL' => [
        'dashboard' => 'dashboard',
        'role' => 'role',
        'permissions' => 'permissions',
        'settings' => 'settings',
        'users' => 'users',
        'modules' => 'modules',
        'appupdates' => 'appupdates',
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