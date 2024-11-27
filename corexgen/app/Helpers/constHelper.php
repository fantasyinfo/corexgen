<?php

use App\Helpers\PermissionsHelper;


/**
 * Constant Helpers Inside the APP
 */
!defined('CRMPERMISSIONS') && define('CRMPERMISSIONS', [
    'DASHBOARD' => [
        'name' => 'CRM_DASHBOARD',
        'id' => PermissionsHelper::$PARENT_PERMISSION_IDS['1'],
        'children' => PermissionsHelper::$PERMISSIONS_IDS['DASHBOARD']
    ],
    'ROLE' => [
        'name' => 'CRM_ROLE',
        'id' => PermissionsHelper::$PARENT_PERMISSION_IDS['2'],
        'children' => PermissionsHelper::$PERMISSIONS_IDS['ROLE']
    ],
    'USERS' => [
        'name' => 'CRM_USERS',
        'id' => PermissionsHelper::$PARENT_PERMISSION_IDS['3'],
        'children' => PermissionsHelper::$PERMISSIONS_IDS['USERS']
    ],
    'PERMISSIONS' => [
        'name' => 'CRM_PERMISSIONS',
        'id' => PermissionsHelper::$PARENT_PERMISSION_IDS['4'],
        'children' => PermissionsHelper::$PERMISSIONS_IDS['PERMISSIONS']
    ],
    'SETTINGS' => [
        'name' => 'CRM_SETTINGS',
        'id' => PermissionsHelper::$PARENT_PERMISSION_IDS['5'],
        'children' => PermissionsHelper::$PERMISSIONS_IDS['SETTINGS']
    ],
    'MODULES' => [
        'name' => 'CRM_MODULES',
        'id' => PermissionsHelper::$PARENT_PERMISSION_IDS['6'],
        'children' => PermissionsHelper::$PERMISSIONS_IDS['MODULES']
    ],
]);

!defined('CRM_MENU_ITEMS') && define('CRM_MENU_ITEMS', [
    'Dashboard' => [
        'menu_icon' => 'fa-tachometer-alt',
        'permission_id' => PermissionsHelper::$PARENT_PERMISSION_IDS['1'],
        'children' => [
            'CRM' => [
                'menu_url' => 'home',
                'menu_icon' => 'fa-tachometer-alt',
                'permission_id' => PermissionsHelper::findPermissionKey('DASHBOARD', 'READ_ALL')
            ]
        ]
    ],
    'Roles & Permissions' => [
        'menu_icon' => 'fa-users',
        'permission_id' => PermissionsHelper::$PARENT_PERMISSION_IDS['2'],
        'children' => [
            'Role' => ['menu_url' => 'role.index', 'menu_icon' => 'fa-users', 'permission_id' => PermissionsHelper::findPermissionKey('ROLE', 'READ_ALL')],
            'Permissions' => ['menu_url' => 'permissions.index', 'menu_icon' => 'fa-user', 'permission_id' => PermissionsHelper::findPermissionKey('PERMISSIONS', 'READ_ALL')],
        ]
    ],
    'Users' => [
        'menu_icon' => 'fa-user',
        'permission_id' => PermissionsHelper::$PARENT_PERMISSION_IDS['3'],
        'children' => [
            'Users' => ['menu_url' => 'users.index', 'menu_icon' => 'fa-user', 'permission_id' => PermissionsHelper::findPermissionKey('USERS', 'READ_ALL')],
        ]
    ],
    'Settings' => [
        'menu_icon' => 'fa-cog',
        'permission_id' => PermissionsHelper::$PARENT_PERMISSION_IDS['4'],
        'children' => [
            'Settings' => ['menu_url' => 'settings.index', 'menu_icon' => 'fa-cog', 'permission_id' => PermissionsHelper::findPermissionKey('SETTINGS', 'READ')],
        ]
    ],
    'Modules' => [
        'menu_icon' => 'fa-box',
        'permission_id' => PermissionsHelper::$PARENT_PERMISSION_IDS['5'],
        'children' => [
            'Modules' => ['menu_url' => 'modules.index', 'menu_icon' => 'fa-box', 'permission_id' => PermissionsHelper::findPermissionKey('MODULES', 'READ_ALL')],
        ]
    ],
]);

!defined('CRM_SETTINGS') && define('CRM_SETTINGS', [

    'COMPANY_NAME' => [
        'key' => 'Company Name',
        'value' => 'Core X Gen',
        'is_media_setting' => false,
        'media_id' => null,
        'input_type' => 'text'
    ],
    'COMPANY_TAGLINE' => [
        'key' => 'Company Tagline',
        'value' => 'Next Generation CRM',
        'is_media_setting' => false,
        'media_id' => null,
        'input_type' => 'text'
    ],
    'COMPANY_LOGO' => [
        'key' => 'Company Logo',
        'value' => '/',
        'is_media_setting' => true,
        'media_id' => null,
        'input_type' => 'image'
    ],
    'DATE_FORMAT' => [
        'key' => 'Date Format',
        'value' => 'DD/MM/YYYY',
        'is_media_setting' => false,
        'media_id' => null,
        'input_type' => 'date'
    ],
    'TIME_FORMAT' => [
        'key' => 'Time Format',
        'value' => '12 Hours',
        'is_media_setting' => false,
        'media_id' => null,
        'input_type' => 'time'
    ],



]);

!defined('CRM_STATUS_TYPES') && define('CRM_STATUS_TYPES', [
    'TENANTS' => [
        'TABLE_STATUS' => ['ACTIVE', 'DEACTIVE'],
        'STATUS' => ['ACTIVE' => 'ACTIVE', 'DEACTIVE' => 'DEACTIVE'],
        'BT_CLASSES' => ['ACTIVE' => 'success', 'DEACTIVE' => 'danger'],
    ],
    'COMPANY' => [
        'TABLE_STATUS' => ['ACTIVE', 'DEACTIVE', 'BANNED'],
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

    ],
    'COMPANY_PANEL' => [
        'dashboard' => 'dashboard',
        'role' => 'role',
        'permissions' => 'permissions',
        'settings' => 'settings',
        'users' => 'users',
        'modules' => 'modules'
    ]
]);
