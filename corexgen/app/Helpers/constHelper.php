<?php

use App\Helpers\PermissionsHelper;


/**
 * Constant Helpers Inside the APP
 */

/**
 * cache default hours to cache the items
 */
!defined('CACHE_DEFAULT_HOURS') && define('CACHE_DEFAULT_HOURS', 24);

/**
 * csv max file upload size
 */
!defined('BULK_CSV_UPLOAD_FILE_SIZE') && define('BULK_CSV_UPLOAD_FILE_SIZE', 2048);

// initilize the permissions with matching keys for plans and permissions of menus
PermissionsHelper::initializePermissions();


/**
 * permissions values of app
 */

!defined('CRMPERMISSIONS') && define('CRMPERMISSIONS', [
    // Core Settings - Starting from 1
    PermissionsHelper::$plansPermissionsKeys['DASHBOARD'] => [
        'name' => 'DASHBOARD',
        'id' => PermissionsHelper::getParentPermissionId('1'),
        'children' => PermissionsHelper::$PERMISSIONS_IDS['DASHBOARD'],
        'for' => 'both',
        'is_feature' => false
    ],
    PermissionsHelper::$plansPermissionsKeys['COMPANIES'] => [
        'name' => 'COMPANIES',
        'id' => PermissionsHelper::getParentPermissionId('2'),
        'children' => PermissionsHelper::$PERMISSIONS_IDS['COMPANIES'],
        'for' => 'tenant',
        'is_feature' => false
    ],
    PermissionsHelper::$plansPermissionsKeys['PLANS'] => [
        'name' => 'PLANS',
        'id' => PermissionsHelper::getParentPermissionId('3'),
        'children' => PermissionsHelper::$PERMISSIONS_IDS['PLANS'],
        'for' => 'tenant',
        'is_feature' => false
    ],
    PermissionsHelper::$plansPermissionsKeys['PAYMENTSTRANSACTIONS'] => [
        'name' => 'PAYMENTSTRANSACTIONS',
        'id' => PermissionsHelper::getParentPermissionId('4'),
        'children' => PermissionsHelper::$PERMISSIONS_IDS['PAYMENTSTRANSACTIONS'],
        'for' => 'both',
        'is_feature' => false
    ],
    PermissionsHelper::$plansPermissionsKeys['PAYMENTGATEWAYS'] => [
        'name' => 'PAYMENTGATEWAYS',
        'id' => PermissionsHelper::getParentPermissionId('5'),
        'children' => PermissionsHelper::$PERMISSIONS_IDS['PAYMENTGATEWAYS'],
        'for' => 'both',
        'is_feature' => false
    ],
    PermissionsHelper::$plansPermissionsKeys['SUBSCRIPTIONS'] => [
        'name' => 'SUBSCRIPTIONS',
        'id' => PermissionsHelper::getParentPermissionId('6'),
        'children' => PermissionsHelper::$PERMISSIONS_IDS['SUBSCRIPTIONS'],
        'for' => 'tenant',
        'is_feature' => false
    ],

    // User Management - Starting from 10
    PermissionsHelper::$plansPermissionsKeys['USERS'] => [
        'name' => 'USERS',
        'id' => PermissionsHelper::getParentPermissionId('200'),
        'children' => PermissionsHelper::$PERMISSIONS_IDS['USERS'],
        'for' => 'both',
        'is_feature' => true
    ],
    PermissionsHelper::$plansPermissionsKeys['ROLE'] => [
        'name' => 'ROLE',
        'id' => PermissionsHelper::getParentPermissionId('201'),
        'children' => PermissionsHelper::$PERMISSIONS_IDS['ROLE'],
        'for' => 'both',
        'is_feature' => true
    ],
    PermissionsHelper::$plansPermissionsKeys['PERMISSIONS'] => [
        'name' => 'PERMISSIONS',
        'id' => PermissionsHelper::getParentPermissionId('202'),
        'children' => PermissionsHelper::$PERMISSIONS_IDS['PERMISSIONS'],
        'for' => 'both',
        'is_feature' => false
    ],

    // System Settings - Starting from 20
    PermissionsHelper::$plansPermissionsKeys['MODULES'] => [
        'name' => 'MODULES',
        'id' => PermissionsHelper::getParentPermissionId('203'),
        'children' => PermissionsHelper::$PERMISSIONS_IDS['MODULES'],
        'for' => 'tenant',
        'is_feature' => false
    ],
    PermissionsHelper::$plansPermissionsKeys['APPUPDATES'] => [
        'name' => 'APPUPDATES',
        'id' => PermissionsHelper::getParentPermissionId('21'),
        'children' => PermissionsHelper::$PERMISSIONS_IDS['APPUPDATES'],
        'for' => 'tenant',
        'is_feature' => false
    ],
    PermissionsHelper::$plansPermissionsKeys['DOWNLOAD_BACKUP'] => [
        'name' => 'DOWNLOAD_BACKUP',
        'id' => PermissionsHelper::getParentPermissionId('22'),
        'children' => PermissionsHelper::$PERMISSIONS_IDS['DOWNLOAD_BACKUP'],
        'for' => 'tenant',
        'is_feature' => false
    ],

    // General Settings - Starting from 30
    PermissionsHelper::$plansPermissionsKeys['SETTINGS'] => [
        'name' => 'SETTINGS',
        'id' => PermissionsHelper::getParentPermissionId('204'),
        'children' => PermissionsHelper::$PERMISSIONS_IDS['SETTINGS'],
        'for' => 'both',
        'is_feature' => false
    ],
    PermissionsHelper::$plansPermissionsKeys['SETTINGS_GENERAL'] => [
        'name' => 'SETTINGS_GENERAL',
        'id' => PermissionsHelper::getParentPermissionId('31'),
        'children' => PermissionsHelper::$PERMISSIONS_IDS['SETTINGS_GENERAL'],
        'for' => 'both',
        'is_feature' => false
    ],
    PermissionsHelper::$plansPermissionsKeys['SETTINGS_MAIL'] => [
        'name' => 'SETTINGS_MAIL',
        'id' => PermissionsHelper::getParentPermissionId('32'),
        'children' => PermissionsHelper::$PERMISSIONS_IDS['SETTINGS_MAIL'],
        'for' => 'both',
        'is_feature' => false
    ],
    PermissionsHelper::$plansPermissionsKeys['SETTINGS_CRON'] => [
        'name' => 'SETTINGS_CRON',
        'id' => PermissionsHelper::getParentPermissionId('33'),
        'children' => PermissionsHelper::$PERMISSIONS_IDS['SETTINGS_CRON'],
        'for' => 'both',
        'is_feature' => false
    ],
    PermissionsHelper::$plansPermissionsKeys['SETTINGS_ONEWORD'] => [
        'name' => 'SETTINGS_ONEWORD',
        'id' => PermissionsHelper::getParentPermissionId('34'),
        'children' => PermissionsHelper::$PERMISSIONS_IDS['SETTINGS_ONEWORD'],
        'for' => 'company',
        'is_feature' => false
    ],
    PermissionsHelper::$plansPermissionsKeys['SETTINGS_CTG'] => [
        'name' => 'SETTINGS_CTG',
        'id' => PermissionsHelper::getParentPermissionId('35'),
        'children' => PermissionsHelper::$PERMISSIONS_IDS['SETTINGS_CTG'],
        'for' => 'company',
        'is_feature' => false
    ],
    PermissionsHelper::$plansPermissionsKeys['SETTINGS_LEADFORM'] => [
        'name' => 'SETTINGS_LEADFORM',
        'id' => PermissionsHelper::getParentPermissionId('37'),
        'children' => PermissionsHelper::$PERMISSIONS_IDS['SETTINGS_LEADFORM'],
        'for' => 'company',
        'is_feature' => false
    ],
    PermissionsHelper::$plansPermissionsKeys['LANDING_PAGE_SETTINGS'] => [
        'name' => 'LANDING_PAGE_SETTINGS',
        'id' => PermissionsHelper::getParentPermissionId('36'),
        'children' => PermissionsHelper::$PERMISSIONS_IDS['LANDING_PAGE_SETTINGS'],
        'for' => 'tenant',
        'is_feature' => false
    ],

    // CRM Core Features - Starting from 100
    PermissionsHelper::$plansPermissionsKeys['CLIENTS'] => [
        'name' => 'CLIENTS',
        'id' => PermissionsHelper::getParentPermissionId('100'),
        'children' => PermissionsHelper::$PERMISSIONS_IDS['CLIENTS'],
        'for' => 'company',
        'is_feature' => true
    ],
    PermissionsHelper::$plansPermissionsKeys['LEADS'] => [
        'name' => 'LEADS',
        'id' => PermissionsHelper::getParentPermissionId('101'),
        'children' => PermissionsHelper::$PERMISSIONS_IDS['LEADS'],
        'for' => 'company',
        'is_feature' => true
    ],

    // Sales Features - Starting from 110
    PermissionsHelper::$plansPermissionsKeys['PROPOSALS'] => [
        'name' => 'PROPOSALS',
        'id' => PermissionsHelper::getParentPermissionId('110'),
        'children' => PermissionsHelper::$PERMISSIONS_IDS['PROPOSALS'],
        'for' => 'company',
        'is_feature' => true
    ],
    PermissionsHelper::$plansPermissionsKeys['ESTIMATES'] => [
        'name' => 'ESTIMATES',
        'id' => PermissionsHelper::getParentPermissionId('111'),
        'children' => PermissionsHelper::$PERMISSIONS_IDS['ESTIMATES'],
        'for' => 'company',
        'is_feature' => true
    ],
    PermissionsHelper::$plansPermissionsKeys['CONTRACTS'] => [
        'name' => 'CONTRACTS',
        'id' => PermissionsHelper::getParentPermissionId('112'),
        'children' => PermissionsHelper::$PERMISSIONS_IDS['CONTRACTS'],
        'for' => 'company',
        'is_feature' => true
    ],
    PermissionsHelper::$plansPermissionsKeys['PRODUCTS_SERVICES'] => [
        'name' => 'PRODUCTS_SERVICES',
        'id' => PermissionsHelper::getParentPermissionId('113'),
        'children' => PermissionsHelper::$PERMISSIONS_IDS['PRODUCTS_SERVICES'],
        'for' => 'company',
        'is_feature' => true
    ],
    PermissionsHelper::$plansPermissionsKeys['INVOICES'] => [
        'name' => 'INVOICES',
        'id' => PermissionsHelper::getParentPermissionId('114'),
        'children' => PermissionsHelper::$PERMISSIONS_IDS['INVOICES'],
        'for' => 'company',
        'is_feature' => true
    ],

    // Project Management - Starting from 120
    PermissionsHelper::$plansPermissionsKeys['PROJECTS'] => [
        'name' => 'PROJECTS',
        'id' => PermissionsHelper::getParentPermissionId('120'),
        'children' => PermissionsHelper::$PERMISSIONS_IDS['PROJECTS'],
        'for' => 'company',
        'is_feature' => true
    ],
    PermissionsHelper::$plansPermissionsKeys['TASKS'] => [
        'name' => 'TASKS',
        'id' => PermissionsHelper::getParentPermissionId('121'),
        'children' => PermissionsHelper::$PERMISSIONS_IDS['TASKS'],
        'for' => 'company',
        'is_feature' => true
    ],
    PermissionsHelper::$plansPermissionsKeys['MILESTONES'] => [
        'name' => 'MILESTONES',
        'id' => PermissionsHelper::getParentPermissionId('122'),
        'children' => PermissionsHelper::$PERMISSIONS_IDS['MILESTONES'],
        'for' => 'company',
        'is_feature' => true
    ],
    PermissionsHelper::$plansPermissionsKeys['TIMESHEETS'] => [
        'name' => 'TIMESHEETS',
        'id' => PermissionsHelper::getParentPermissionId('123'),
        'children' => PermissionsHelper::$PERMISSIONS_IDS['TIMESHEETS'],
        'for' => 'company',
        'is_feature' => false
    ],

    // Additional Features - Starting from 130
    PermissionsHelper::$plansPermissionsKeys['CUSTOM_FIELDS'] => [
        'name' => 'CUSTOM_FIELDS',
        'id' => PermissionsHelper::getParentPermissionId('130'),
        'children' => PermissionsHelper::$PERMISSIONS_IDS['CUSTOM_FIELDS'],
        'for' => 'company',
        'is_feature' => true
    ],
    PermissionsHelper::$plansPermissionsKeys['CALENDER'] => [
        'name' => 'CALENDER',
        'id' => PermissionsHelper::getParentPermissionId('131'),
        'children' => PermissionsHelper::$PERMISSIONS_IDS['CALENDER'],
        'for' => 'company',
        'is_feature' => true
    ],

    // Templates - Starting from 140
    PermissionsHelper::$plansPermissionsKeys['PROPOSALS_TEMPLATES'] => [
        'name' => 'PROPOSALS_TEMPLATES',
        'id' => PermissionsHelper::getParentPermissionId('140'),
        'children' => PermissionsHelper::$PERMISSIONS_IDS['PROPOSALS_TEMPLATES'],
        'for' => 'company',
        'is_feature' => false
    ],
    PermissionsHelper::$plansPermissionsKeys['ESTIMATES_TEMPLATES'] => [
        'name' => 'ESTIMATES_TEMPLATES',
        'id' => PermissionsHelper::getParentPermissionId('141'),
        'children' => PermissionsHelper::$PERMISSIONS_IDS['ESTIMATES_TEMPLATES'],
        'for' => 'company',
        'is_feature' => false
    ],
    PermissionsHelper::$plansPermissionsKeys['CONTRACTS_TEMPLATES'] => [
        'name' => 'CONTRACTS_TEMPLATES',
        'id' => PermissionsHelper::getParentPermissionId('142'),
        'children' => PermissionsHelper::$PERMISSIONS_IDS['CONTRACTS_TEMPLATES'],
        'for' => 'company',
        'is_feature' => false
    ],

    // Miscellaneous - Starting from 150
    PermissionsHelper::$plansPermissionsKeys['PLANUPGRADE'] => [
        'name' => 'PLANUPGRADE',
        'id' => PermissionsHelper::getParentPermissionId('150'),
        'children' => PermissionsHelper::$PERMISSIONS_IDS['PLANUPGRADE'],
        'for' => 'company',
        'is_feature' => false
    ],

    // Logs and Events - Starting from 160
    PermissionsHelper::$plansPermissionsKeys['EVENTS_AUDIT_LOG'] => [
        'name' => 'EVENTS_AUDIT_LOG',
        'id' => PermissionsHelper::getParentPermissionId('160'),
        'children' => PermissionsHelper::$PERMISSIONS_IDS['EVENTS_AUDIT_LOG'],
        'for' => 'both',
        'is_feature' => false
    ],
    PermissionsHelper::$plansPermissionsKeys['BULK_IMPORT_STATUS'] => [
        'name' => 'BULK_IMPORT_STATUS',
        'id' => PermissionsHelper::getParentPermissionId('161'),
        'children' => PermissionsHelper::$PERMISSIONS_IDS['BULK_IMPORT_STATUS'],
        'for' => 'both',
        'is_feature' => false
    ],
    PermissionsHelper::$plansPermissionsKeys['THEME_CUSTOMIZE'] => [
        'name' => 'THEME_CUSTOMIZE',
        'id' => PermissionsHelper::getParentPermissionId('162'),
        'children' => PermissionsHelper::$PERMISSIONS_IDS['THEME_CUSTOMIZE'],
        'for' => 'both',
        'is_feature' => false
    ]
]);

// super panel menus
!defined('CRM_MENU_ITEMS_TENANT') && define('CRM_MENU_ITEMS_TENANT', [
    'Dashboard' => [
        'menu_icon' => 'fa-tachometer-alt',
        'permission_id' => PermissionsHelper::getParentPermissionId('1'),
        'menu_url' => 'home',
        'children' => [
            'CRM' => [
                'menu_url' => 'home',
                'menu_icon' => 'fa-tachometer-alt',
                'permission_id' => PermissionsHelper::findPermissionKey(PermissionsHelper::$plansPermissionsKeys['DASHBOARD'], 'READ_ALL')
            ]
        ]
    ],
    'Companies' => [
        'menu_icon' => 'fa-building',
        'menu_url' => 'companies',
        'permission_id' => PermissionsHelper::getParentPermissionId('2'),
        'children' => [
            'Companies' => ['menu_url' => 'companies.index', 'menu_icon' => 'fa-building', 'permission_id' => PermissionsHelper::findPermissionKey(PermissionsHelper::$plansPermissionsKeys['COMPANIES'], 'READ_ALL')],
        ]
    ],
    'Plans' => [
        'menu_icon' => 'fa-clipboard-list',
        'permission_id' => PermissionsHelper::getParentPermissionId('3'),
        'menu_url' => 'plans',
        'children' => [
            'Plans' => ['menu_url' => 'plans.index', 'menu_icon' => 'fa-clipboard-list', 'permission_id' => PermissionsHelper::findPermissionKey(PermissionsHelper::$plansPermissionsKeys['PLANS'], 'READ_ALL')],
        ]
    ],
    'Gateway & Transactions' => [
        'menu_icon' => 'fas fa-file-invoice-dollar',
        'permission_id' => PermissionsHelper::getParentPermissionId('4'),
        'menu_url' => 'paymentGateway',
        'children' => [
            'Gateways' => ['menu_url' => 'paymentGateway.index', 'menu_icon' => 'fa-credit-card', 'permission_id' => PermissionsHelper::findPermissionKey(PermissionsHelper::$plansPermissionsKeys['PAYMENTGATEWAYS'], 'READ_ALL')],
            'Transactions' => ['menu_url' => 'planPaymentTransaction.index', 'menu_icon' => 'fa-money-bill-wave', 'permission_id' => PermissionsHelper::findPermissionKey(PermissionsHelper::$plansPermissionsKeys['PAYMENTSTRANSACTIONS'], 'READ_ALL')],
            'Subscriptions' => ['menu_url' => 'subscriptions.index', 'menu_icon' => 'fa-sync', 'permission_id' => PermissionsHelper::findPermissionKey(PermissionsHelper::$plansPermissionsKeys['SUBSCRIPTIONS'], 'READ_ALL')],
        ]
    ],
    'Users & Employees' => [
        'menu_icon' => 'fa-users',
        'permission_id' => PermissionsHelper::getParentPermissionId('200'),
        'menu_url' => 'users',
        'children' => [
            'Users' => ['menu_url' => 'users.index', 'menu_icon' => 'fa-user', 'permission_id' => PermissionsHelper::findPermissionKey(PermissionsHelper::$plansPermissionsKeys['USERS'], 'READ_ALL')],
        ]
    ],
    'Roles & Permissions' => [
        'menu_icon' => 'fa-user-shield',
        'permission_id' => PermissionsHelper::getParentPermissionId('201'),
        'menu_url' => 'role',
        'children' => [
            'Role' => ['menu_url' => 'role.index', 'menu_icon' => 'fa-user-tag', 'permission_id' => PermissionsHelper::findPermissionKey(PermissionsHelper::$plansPermissionsKeys['ROLE'], 'READ_ALL')],
            'Permissions' => ['menu_url' => 'permissions.index', 'menu_icon' => 'fa-key', 'permission_id' => PermissionsHelper::findPermissionKey(PermissionsHelper::$plansPermissionsKeys['PERMISSIONS'], 'READ_ALL')],
        ]
    ],
    'System Settings' => [
        'menu_icon' => 'fa-box',
        'permission_id' => PermissionsHelper::getParentPermissionId('203'),
        'menu_url' => 'modules',
        'children' => [
            'Modules' => ['menu_url' => 'modules.index', 'menu_icon' => 'fa-puzzle-piece', 'permission_id' => PermissionsHelper::findPermissionKey(PermissionsHelper::$plansPermissionsKeys['MODULES'], 'READ_ALL')],
            'AppUpdates' => ['menu_url' => 'appupdates.index', 'menu_icon' => 'fa-sync', 'permission_id' => PermissionsHelper::findPermissionKey(PermissionsHelper::$plansPermissionsKeys['APPUPDATES'], 'READ_ALL')],
            'Backups' => ['menu_url' => 'backup.index', 'menu_icon' => 'fa-database', 'permission_id' => PermissionsHelper::findPermissionKey(PermissionsHelper::$plansPermissionsKeys['DOWNLOAD_BACKUP'], 'READ_ALL')],
        ]
    ],
    'Settings' => [
        'menu_icon' => 'fa-cog',
        'permission_id' => PermissionsHelper::getParentPermissionId('204'),
        'menu_url' => 'settings',
        'children' => [
            'General' => ['menu_url' => 'settings.general', 'menu_icon' => 'fa-sliders-h', 'permission_id' => PermissionsHelper::findPermissionKey(PermissionsHelper::$plansPermissionsKeys['SETTINGS_GENERAL'], 'READ')],
            'Mail' => ['menu_url' => 'settings.mail', 'menu_icon' => 'fa-envelope', 'permission_id' => PermissionsHelper::findPermissionKey(PermissionsHelper::$plansPermissionsKeys['SETTINGS_MAIL'], 'READ')],
            'Cron' => ['menu_url' => 'settings.cron', 'menu_icon' => 'fa-clock', 'permission_id' => PermissionsHelper::findPermissionKey(PermissionsHelper::$plansPermissionsKeys['SETTINGS_CRON'], 'READ')],
            'Theme Customize' => ['menu_url' => 'settings.theme', 'menu_icon' => 'fa-palette', 'permission_id' => PermissionsHelper::findPermissionKey(PermissionsHelper::$plansPermissionsKeys['THEME_CUSTOMIZE'], 'READ')],
            'Front End' => ['menu_url' => 'settings.frontend', 'menu_icon' => 'fa-clock', 'permission_id' => PermissionsHelper::findPermissionKey(PermissionsHelper::$plansPermissionsKeys['LANDING_PAGE_SETTINGS'], 'READ')],

        ]
    ],
    'Logs & Events' => [
        'menu_icon' => 'fa-list-ul',
        'permission_id' => PermissionsHelper::getParentPermissionId('160'),
        'menu_url' => 'audit',
        'children' => [
            'Audit' => ['menu_url' => 'audit.index', 'menu_icon' => 'fa-history', 'permission_id' => PermissionsHelper::findPermissionKey(PermissionsHelper::$plansPermissionsKeys['EVENTS_AUDIT_LOG'], 'READ_ALL')],
            'Bulk Import' => ['menu_url' => 'audit.bulkimport', 'menu_icon' => 'fa-box', 'permission_id' => PermissionsHelper::findPermissionKey(PermissionsHelper::$plansPermissionsKeys['BULK_IMPORT_STATUS'], 'READ_ALL')],
        ]
    ],
]);

// company panel menus
!defined('CRM_MENU_ITEMS_COMPANY') && define('CRM_MENU_ITEMS_COMPANY', [
    'Dashboard' => [
        'menu_icon' => 'fa-tachometer-alt',
        'permission_id' => PermissionsHelper::getParentPermissionId('1'),
        'is_default' => true,
        'menu_url' => 'home',
        'children' => [
            'CRM' => [
                'menu_url' => 'home',
                'menu_icon' => 'fa-tachometer-alt',
                'permission_id' => PermissionsHelper::findPermissionKey(PermissionsHelper::$plansPermissionsKeys['DASHBOARD'], 'READ_ALL')
            ]
        ]
    ],
    'System Settings' => [
        'menu_icon' => 'fa-box',
        'permission_id' => PermissionsHelper::getParentPermissionId('203'),
        'menu_url' => 'modules',
        'panelModule' => 'company',
        'children' => [
            'Modules' => ['menu_url' => 'modules.index', 'menu_icon' => 'fa-puzzle-piece', 'permission_id' => PermissionsHelper::findPermissionKey(PermissionsHelper::$plansPermissionsKeys['MODULES'], 'READ_ALL')],
            'AppUpdates' => ['menu_url' => 'appupdates.index', 'menu_icon' => 'fa-sync', 'permission_id' => PermissionsHelper::findPermissionKey(PermissionsHelper::$plansPermissionsKeys['APPUPDATES'], 'READ_ALL')],
            
            'Backups' => ['menu_url' => 'backup.index', 'menu_icon' => 'fa-database', 'permission_id' => PermissionsHelper::findPermissionKey(PermissionsHelper::$plansPermissionsKeys['DOWNLOAD_BACKUP'], 'READ_ALL')],

            'Cron' => ['menu_url' => 'settings.cron', 'menu_icon' => 'fa-clock', 'permission_id' => PermissionsHelper::findPermissionKey(PermissionsHelper::$plansPermissionsKeys['SETTINGS_CRON'], 'READ')],
        ]
    ],
    'Settings' => [
        'menu_icon' => 'fa-cog',
        'is_default' => true,
        'permission_id' => PermissionsHelper::getParentPermissionId('204'),
        'menu_url' => 'settings',
        'children' => [
            'General' => ['menu_url' => 'settings.general', 'menu_icon' => 'fa-cog', 'permission_id' => PermissionsHelper::findPermissionKey(PermissionsHelper::$plansPermissionsKeys['SETTINGS'], 'READ')],
            'Mail' => ['menu_url' => 'settings.mail', 'menu_icon' => 'fa-cog', 'permission_id' => PermissionsHelper::findPermissionKey(PermissionsHelper::$plansPermissionsKeys['SETTINGS_MAIL'], 'READ')],
            'One Word' => ['menu_url' => 'settings.oneWord', 'menu_icon' => 'fa-cog', 'permission_id' => PermissionsHelper::findPermissionKey(PermissionsHelper::$plansPermissionsKeys['SETTINGS_ONEWORD'], 'READ')],
            'Web to Lead' => ['menu_url' => 'settings.leadFormSetting', 'menu_icon' => 'fa-clock', 'permission_id' => PermissionsHelper::findPermissionKey(PermissionsHelper::$plansPermissionsKeys['SETTINGS_LEADFORM'], 'READ')],
            'Theme Customize' => ['menu_url' => 'settings.theme', 'menu_icon' => 'fa-palette', 'permission_id' => PermissionsHelper::findPermissionKey(PermissionsHelper::$plansPermissionsKeys['THEME_CUSTOMIZE'], 'READ')],
            'Clients Category' => ['menu_url' => 'cgt.indexClientCategory', 'menu_icon' => 'fa-cog', 'permission_id' => 2000],
            'Leads Groups' => ['menu_url' => 'cgt.indexLeadsGroups', 'menu_icon' => 'fa-cog', 'permission_id' => 2001],
            'Leads Status' => ['menu_url' => 'cgt.indexLeadsStatus', 'menu_icon' => 'fa-cog', 'permission_id' => 2002],
            'Leads Sources' => ['menu_url' => 'cgt.indexLeadsSources', 'menu_icon' => 'fa-cog', 'permission_id' => 2003],
            'Product Categories' => ['menu_url' => 'cgt.indexProductCategories', 'menu_icon' => 'fa-cog', 'permission_id' => 2004],
            'Product Taxes' => ['menu_url' => 'cgt.indexProductTaxes', 'menu_icon' => 'fa-cog', 'permission_id' => 2005],
            'Tasks Status' => ['menu_url' => 'cgt.indexTasksStatus', 'menu_icon' => 'fa-cog', 'permission_id' => 2006],
        ]
    ],
    'Gateway & Transactions' => [
        'menu_icon' => 'fas fa-file-invoice-dollar',
        'permission_id' => PermissionsHelper::getParentPermissionId('4'),
        'menu_url' => 'paymentGateway',
        'children' => [
            'Gateways' => ['menu_url' => 'paymentGateway.index', 'menu_icon' => 'fa-credit-card', 'permission_id' => PermissionsHelper::findPermissionKey(PermissionsHelper::$plansPermissionsKeys['PAYMENTGATEWAYS'], 'READ_ALL')],
            'Transactions' => ['menu_url' => 'planPaymentTransaction.index', 'menu_icon' => 'fa-money-bill-wave', 'permission_id' => PermissionsHelper::findPermissionKey(PermissionsHelper::$plansPermissionsKeys['PAYMENTSTRANSACTIONS'], 'READ_ALL')],
        ]
    ],
    'Roles & Permissions' => [
        'menu_icon' => 'fa-users',
        'feature_type' => PermissionsHelper::$plansPermissionsKeys['ROLE'], // this need to match the PLANS_FEATURES key
        'permission_plan' => PermissionsHelper::$plansPermissionsKeys['ROLE'],
        'permission_id' => PermissionsHelper::getParentPermissionId('201'),
        'menu_url' => 'role',
        'children' => [
            'Role' => ['menu_url' => 'role.index', 'menu_icon' => 'fa-users', 'permission_id' => PermissionsHelper::findPermissionKey(PermissionsHelper::$plansPermissionsKeys['ROLE'], 'READ_ALL')],
            'Permissions' => ['menu_url' => 'permissions.index', 'menu_icon' => 'fa-user', 'permission_id' => PermissionsHelper::findPermissionKey(PermissionsHelper::$plansPermissionsKeys['PERMISSIONS'], 'READ_ALL')],
        ]
    ],
    'Users & Employees' => [
        'menu_icon' => 'fa-user',
        'feature_type' => PermissionsHelper::$plansPermissionsKeys['USERS'], // this need to match the PLANS_FEATURES key
        'permission_plan' => PermissionsHelper::$plansPermissionsKeys['USERS'],
        'permission_id' => PermissionsHelper::getParentPermissionId('200'),
        'menu_url' => 'users',
        'children' => [
            'Users' => ['menu_url' => 'users.index', 'menu_icon' => 'fa-user', 'permission_id' => PermissionsHelper::findPermissionKey(PermissionsHelper::$plansPermissionsKeys['USERS'], 'READ_ALL')],
        ]
    ],
    'Membership' => [
        'menu_icon' => 'fa-paper-plane',
        'is_default' => true,
        'permission_plan' => PermissionsHelper::$plansPermissionsKeys['PLANUPGRADE'],
        'permission_id' => PermissionsHelper::getParentPermissionId('150'),
        'menu_url' => 'planupgrade',
        'panelModule' => 'saas',
        'children' => [
            'Membership' => ['menu_url' => 'planupgrade.index', 'menu_icon' => 'fa-user', 'permission_id' => PermissionsHelper::findPermissionKey(PermissionsHelper::$plansPermissionsKeys['PLANUPGRADE'], 'READ_ALL')],
        ]
    ],
    'Logs & Events' => [
        'menu_icon' => 'fa-list-ul',
        'is_default' => true,
        'permission_id' => PermissionsHelper::getParentPermissionId('160'),
        'menu_url' => 'audit',
        'children' => [
            'Audit' => ['menu_url' => 'audit.index', 'menu_icon' => 'fa-box', 'permission_id' => PermissionsHelper::findPermissionKey(PermissionsHelper::$plansPermissionsKeys['EVENTS_AUDIT_LOG'], 'READ_ALL')],
            'Bulk Import' => ['menu_url' => 'audit.bulkimport', 'menu_icon' => 'fa-box', 'permission_id' => PermissionsHelper::findPermissionKey(PermissionsHelper::$plansPermissionsKeys['BULK_IMPORT_STATUS'], 'READ_ALL')],

        ]
    ],

    'Clients' => [
        'menu_icon' => 'fa-users',
        'feature_type' => PermissionsHelper::$plansPermissionsKeys['CLIENTS'], // this need to match the PLANS_FEATURES key
        'permission_plan' => PermissionsHelper::$plansPermissionsKeys['CLIENTS'],
        'permission_id' => PermissionsHelper::getParentPermissionId('100'),
        'menu_url' => 'clients',
        'children' => [
            'Clients' => ['menu_url' => 'clients.index', 'menu_icon' => 'fa-user', 'permission_id' => PermissionsHelper::findPermissionKey(PermissionsHelper::$plansPermissionsKeys['CLIENTS'], 'READ_ALL')],
        ]
    ],
    'Leads' => [
        'menu_icon' => 'fa-phone-volume',
        'feature_type' => PermissionsHelper::$plansPermissionsKeys['LEADS'], // this need to match the PLANS_FEATURES key
        'permission_plan' => PermissionsHelper::$plansPermissionsKeys['LEADS'],
        'permission_id' => PermissionsHelper::getParentPermissionId('101'),
        'menu_url' => 'leads',
        'children' => [
            'Leads' => ['menu_url' => 'leads.index', 'menu_icon' => 'fa-user', 'permission_id' => PermissionsHelper::findPermissionKey(PermissionsHelper::$plansPermissionsKeys['LEADS'], 'READ_ALL')],
        ]
    ],
    'Projects' => [
        'menu_icon' => 'fa-folder-plus',
        'feature_type' => PermissionsHelper::$plansPermissionsKeys['PROJECTS'], // this need to match the PLANS_FEATURES key
        'permission_plan' => PermissionsHelper::$plansPermissionsKeys['PROJECTS'],
        'permission_id' => PermissionsHelper::getParentPermissionId('120'),
        'menu_url' => 'projects',
        'children' => [
            'Projects' => ['menu_url' => 'projects.index', 'menu_icon' => 'fa-user', 'permission_id' => PermissionsHelper::findPermissionKey(PermissionsHelper::$plansPermissionsKeys['PROJECTS'], 'READ_ALL')],
        ]
    ],
    'Tasks' => [
        'menu_icon' => 'fa-tasks',
        'feature_type' => PermissionsHelper::$plansPermissionsKeys['TASKS'], // this need to match the PLANS_FEATURES key
        'permission_plan' => PermissionsHelper::$plansPermissionsKeys['TASKS'],
        'permission_id' => PermissionsHelper::getParentPermissionId('121'),
        'menu_url' => 'tasks',
        'children' => [
            'Tasks' => ['menu_url' => 'tasks.index', 'menu_icon' => 'fa-user', 'permission_id' => PermissionsHelper::findPermissionKey(PermissionsHelper::$plansPermissionsKeys['TASKS'], 'READ_ALL')],
        ]
    ],
    'Proposals' => [
        'menu_icon' => 'fa-flag',
        'feature_type' => PermissionsHelper::$plansPermissionsKeys['PROPOSALS'], // this need to match the PLANS_FEATURES key
        'permission_plan' => PermissionsHelper::$plansPermissionsKeys['PROPOSALS'],
        'permission_id' => PermissionsHelper::getParentPermissionId('110'),
        'menu_url' => 'proposals',
        'children' => [
            'Proposals' => ['menu_url' => 'proposals.index', 'menu_icon' => 'fa-user', 'permission_id' => PermissionsHelper::findPermissionKey(PermissionsHelper::$plansPermissionsKeys['PROPOSALS'], 'READ_ALL')],
            'Templates' => ['menu_url' => 'proposals.indexProposals', 'menu_icon' => 'fa-user', 'permission_id' => PermissionsHelper::findPermissionKey(PermissionsHelper::$plansPermissionsKeys['PROPOSALS_TEMPLATES'], 'READ_ALL')],
        ]
    ],
    'Estimates' => [
        'menu_icon' => 'fa-file-signature',
        'feature_type' => PermissionsHelper::$plansPermissionsKeys['ESTIMATES'], // this need to match the PLANS_FEATURES key
        'permission_plan' => PermissionsHelper::$plansPermissionsKeys['ESTIMATES'],
        'permission_id' => PermissionsHelper::getParentPermissionId('111'),
        'menu_url' => 'estimates',
        'children' => [
            'Estimates' => ['menu_url' => 'estimates.index', 'menu_icon' => 'fa-user', 'permission_id' => PermissionsHelper::findPermissionKey(PermissionsHelper::$plansPermissionsKeys['ESTIMATES'], 'READ_ALL')],
            'Templates' => ['menu_url' => 'estimates.indexEstimates', 'menu_icon' => 'fa-user', 'permission_id' => PermissionsHelper::findPermissionKey(PermissionsHelper::$plansPermissionsKeys['ESTIMATES_TEMPLATES'], 'READ_ALL')],
        ]
    ],
    'Contracts' => [
        'menu_icon' => 'fa-file-contract',
        'feature_type' => PermissionsHelper::$plansPermissionsKeys['CONTRACTS'], // this need to match the PLANS_FEATURES key
        'permission_plan' => PermissionsHelper::$plansPermissionsKeys['CONTRACTS'],
        'permission_id' => PermissionsHelper::getParentPermissionId('112'),
        'menu_url' => 'contracts',
        'children' => [
            'Contracts' => ['menu_url' => 'contracts.index', 'menu_icon' => 'fa-user', 'permission_id' => PermissionsHelper::findPermissionKey(PermissionsHelper::$plansPermissionsKeys['CONTRACTS'], 'READ_ALL')],
            'Templates' => ['menu_url' => 'contracts.indexContracts', 'menu_icon' => 'fa-user', 'permission_id' => PermissionsHelper::findPermissionKey(PermissionsHelper::$plansPermissionsKeys['CONTRACTS_TEMPLATES'], 'READ_ALL')],
        ]
    ],
    'Invoices' => [
        'menu_icon' => 'fa-receipt',
        'feature_type' => PermissionsHelper::$plansPermissionsKeys['INVOICES'], // this need to match the PLANS_FEATURES key
        'permission_plan' => PermissionsHelper::$plansPermissionsKeys['INVOICES'],
        'permission_id' => PermissionsHelper::getParentPermissionId('114'),
        'menu_url' => 'invoices',
        'children' => [
            'Invoice' => ['menu_url' => 'invoices.index', 'menu_icon' => 'fa-user', 'permission_id' => PermissionsHelper::findPermissionKey(PermissionsHelper::$plansPermissionsKeys['INVOICES'], 'READ_ALL')],
        ]
    ],
    'Products & Services' => [
        'menu_icon' => 'fa-boxes',
        'feature_type' => PermissionsHelper::$plansPermissionsKeys['PRODUCTS_SERVICES'], // this need to match the PLANS_FEATURES key
        'permission_plan' => PermissionsHelper::$plansPermissionsKeys['PRODUCTS_SERVICES'],
        'permission_id' => PermissionsHelper::getParentPermissionId('113'),
        'menu_url' => 'products_services',
        'children' => [
            'Products & Services' => ['menu_url' => 'products_services.index', 'menu_icon' => 'fa-user', 'permission_id' => PermissionsHelper::findPermissionKey(PermissionsHelper::$plansPermissionsKeys['PRODUCTS_SERVICES'], 'READ_ALL')],
        ]
    ],
    'Custom Fields' => [
        'menu_icon' => 'fa-plus-square',
        'feature_type' => PermissionsHelper::$plansPermissionsKeys['CUSTOM_FIELDS'], // this need to match the PLANS_FEATURES key
        'permission_plan' => PermissionsHelper::$plansPermissionsKeys['CUSTOM_FIELDS'],
        'permission_id' => PermissionsHelper::getParentPermissionId('130'),
        'menu_url' => 'customfields',
        'children' => [
            'Custom Fields' => ['menu_url' => 'customfields.index', 'menu_icon' => 'fa-user', 'permission_id' => PermissionsHelper::findPermissionKey(PermissionsHelper::$plansPermissionsKeys['CUSTOM_FIELDS'], 'READ_ALL')],
        ]
    ],
    'Calender' => [
        'menu_icon' => 'fa-calendar-week',
        'feature_type' => PermissionsHelper::$plansPermissionsKeys['CALENDER'], // this need to match the PLANS_FEATURES key
        'permission_plan' => PermissionsHelper::$plansPermissionsKeys['CALENDER'],
        'permission_id' => PermissionsHelper::getParentPermissionId('131'),
        'menu_url' => 'calender',
        'children' => [
            'Calender' => ['menu_url' => 'calender.index', 'menu_icon' => 'fa-user', 'permission_id' => PermissionsHelper::findPermissionKey(PermissionsHelper::$plansPermissionsKeys['CALENDER'], 'READ_ALL')],
        ]
    ],
]);


/**
 * status of all the tables inside the software
 */
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
        'TABLE_STATUS' => ['ACTIVE', 'DEACTIVE',],
        'STATUS' => ['ACTIVE' => 'ACTIVE', 'DEACTIVE' => 'DEACTIVE',],
        'BT_CLASSES' => ['ACTIVE' => 'success', 'DEACTIVE' => 'danger',],
    ],
    'LEADS' => [
        'TABLE_STATUS' => ['ACTIVE', 'DEACTIVE',],
        'STATUS' => ['ACTIVE' => 'ACTIVE', 'DEACTIVE' => 'DEACTIVE',],
        'BT_CLASSES' => ['ACTIVE' => 'success', 'DEACTIVE' => 'danger',],
    ],
    'PRODUCTS_SERVICES' => [
        'TABLE_STATUS' => ['ACTIVE', 'DEACTIVE'],
        'STATUS' => ['ACTIVE' => 'ACTIVE', 'DEACTIVE' => 'DEACTIVE'],
        'BT_CLASSES' => ['ACTIVE' => 'success', 'DEACTIVE' => 'danger'],
    ],
    'PROPOSALS' => [
        'TABLE_STATUS' => ['DRAFT', 'SENT', 'OPEN', 'DECLINED', 'ACCEPTED', 'EXPIRED', 'REVISED'],
        'STATUS' => ['DRAFT' => 'DRAFT', 'SENT' => 'SENT', 'OPEN' => 'OPEN', 'DECLINED' => 'DECLINED', 'ACCEPTED' => 'ACCEPTED', 'EXPIRED' => 'EXPIRED', 'REVISED' => 'REVISED'],
        'BT_CLASSES' => ['DRAFT' => 'dark', 'SENT' => 'info', 'OPEN' => 'light', 'ACCEPTED' => 'success', 'DECLINED' => 'danger', 'EXPIRED' => 'warning', 'REVISED' => 'info'],
    ],
    'CONTRACTS' => [
        'TABLE_STATUS' => ['DRAFT', 'SENT', 'OPEN', 'DECLINED', 'ACCEPTED', 'EXPIRED', 'REVISED'],
        'STATUS' => ['DRAFT' => 'DRAFT', 'SENT' => 'SENT', 'OPEN' => 'OPEN', 'DECLINED' => 'DECLINED', 'ACCEPTED' => 'ACCEPTED', 'EXPIRED' => 'EXPIRED', 'REVISED' => 'REVISED'],
        'BT_CLASSES' => ['DRAFT' => 'dark', 'SENT' => 'info', 'OPEN' => 'light', 'ACCEPTED' => 'success', 'DECLINED' => 'danger', 'EXPIRED' => 'warning', 'REVISED' => 'info'],
    ],
    'ESTIMATES' => [
        'TABLE_STATUS' => ['DRAFT', 'SENT', 'OPEN', 'DECLINED', 'ACCEPTED', 'EXPIRED', 'REVISED'],
        'STATUS' => ['DRAFT' => 'DRAFT', 'SENT' => 'SENT', 'OPEN' => 'OPEN', 'DECLINED' => 'DECLINED', 'ACCEPTED' => 'ACCEPTED', 'EXPIRED' => 'EXPIRED', 'REVISED' => 'REVISED'],
        'BT_CLASSES' => ['DRAFT' => 'dark', 'SENT' => 'info', 'OPEN' => 'light', 'ACCEPTED' => 'success', 'DECLINED' => 'danger', 'EXPIRED' => 'warning', 'REVISED' => 'info'],
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
    'INVOICES' => [
        'TABLE_STATUS' => ['SENT', 'SUCCESS', 'OVERDUE', 'PENDING'],
        'STATUS' => ['SENT' => 'SENT', 'SUCCESS' => 'PAID', 'OVERDUE' => 'OVERDUE', 'PENDING' => 'PENDING'],
        'BT_CLASSES' => ['SENT' => 'info', 'SUCCESS' => 'success', 'OVERDUE' => 'danger', 'PENDING' => 'warning'],
    ],

    'PROJECTS' => [
        'TABLE_STATUS' => ['ACTIVE', 'COMPLETED', 'ON_HOLD', 'CANCELED'],
        'STATUS' => ['ACTIVE' => 'ACTIVE', 'COMPLETED' => 'COMPLETED', 'ON_HOLD' => 'ON_HOLD', 'CANCELED' => 'CANCELED'],
        'BT_CLASSES' => ['ACTIVE' => 'success', 'COMPLETED' => 'info', 'ON_HOLD' => 'dark', 'CANCELED' => 'danger'],
    ],
    'MILESTONES' => [
        'TABLE_STATUS' => ['PENDING', 'COMPLETED', 'CANCELED'],
        'STATUS' => ['PENDING' => 'PENDING', 'COMPLETED' => 'COMPLETED', 'CANCELED' => 'CANCELED'],
        'BT_CLASSES' => ['PENDING' => 'warning', 'COMPLETED' => 'success', 'CANCELED' => 'danger'],
    ],
]);

/**
 * admin panel types
 */

!defined('PANEL_TYPES') && define('PANEL_TYPES', [
    'SUPER_PANEL' => 'SUPER_PANEL',
    'COMPANY_PANEL' => 'COMPANY_PANEL'
]);

// web.php file prefix for routes
/**
 * panel modules should match the web.php file to handle the routes dynamically
 */
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
        'customfields' => 'customfields',
        'leads' => 'leads',
        'proposals' => 'proposals',
        'products_services' => 'products_services',
        'estimates' => 'estimates',
        'contracts' => 'contracts',
        'projects' => 'projects',
        'tasks' => 'tasks',
        'milestones' => 'milestones',
        'invoices' => 'invoices',
        'cgt' => 'cgt',
        'calender' => 'calender',
        'planPaymentTransaction' => 'planPaymentTransaction',
        'paymentGateway' => 'paymentGateway',
        'backup' => 'backup',

    ]
]);

/**
 * plans billing cycles
 */
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
/**
 * plans features
 */
!defined('PLANS_FEATURES') && define('PLANS_FEATURES', [
    PermissionsHelper::$plansPermissionsKeys['USERS'] => PermissionsHelper::$plansPermissionsKeys['USERS'],
    PermissionsHelper::$plansPermissionsKeys['ROLE'] => PermissionsHelper::$plansPermissionsKeys['ROLE'],
    PermissionsHelper::$plansPermissionsKeys['CLIENTS'] => PermissionsHelper::$plansPermissionsKeys['CLIENTS'],
    PermissionsHelper::$plansPermissionsKeys['CUSTOM_FIELDS'] => PermissionsHelper::$plansPermissionsKeys['CUSTOM_FIELDS'],
    PermissionsHelper::$plansPermissionsKeys['LEADS'] => PermissionsHelper::$plansPermissionsKeys['LEADS'],
    PermissionsHelper::$plansPermissionsKeys['PROPOSALS'] => PermissionsHelper::$plansPermissionsKeys['PROPOSALS'],
    PermissionsHelper::$plansPermissionsKeys['ESTIMATES'] => PermissionsHelper::$plansPermissionsKeys['ESTIMATES'],
    PermissionsHelper::$plansPermissionsKeys['CONTRACTS'] => PermissionsHelper::$plansPermissionsKeys['CONTRACTS'],
    PermissionsHelper::$plansPermissionsKeys['PRODUCTS_SERVICES'] => PermissionsHelper::$plansPermissionsKeys['PRODUCTS_SERVICES'],
    PermissionsHelper::$plansPermissionsKeys['PROJECTS'] => PermissionsHelper::$plansPermissionsKeys['PROJECTS'],
    PermissionsHelper::$plansPermissionsKeys['TASKS'] => PermissionsHelper::$plansPermissionsKeys['TASKS'],
    PermissionsHelper::$plansPermissionsKeys['MILESTONES'] => PermissionsHelper::$plansPermissionsKeys['MILESTONES'],
    PermissionsHelper::$plansPermissionsKeys['INVOICES'] => PermissionsHelper::$plansPermissionsKeys['INVOICES'],
    PermissionsHelper::$plansPermissionsKeys['CALENDER'] => PermissionsHelper::$plansPermissionsKeys['CALENDER'],
]);


/**
 * address types to store
 */
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

/**
 * default payment gateways
 */
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

/**
 * settings menu items
 */
!defined('SETTINGS_MENU_ITEMS') && define('SETTINGS_MENU_ITEMS', [
    'General' => [
        'name' => 'General',
        'link' => 'general',
        'icon' => 'fa-cog',
        'for' => 'both',
        'module' => 'settings',
    ],
    'Mail' => [
        'name' => 'Mail',
        'link' => 'mail',
        'icon' => 'fa-envelope',
        'for' => 'both',
        'module' => 'settings',
    ],
    'Cron' => [
        'name' => 'Cron Job',
        'link' => 'cron',
        'icon' => 'fa-hourglass-half',
        'for' => 'tenant',
        'module' => 'settings',
    ],
    'One Word' => [
        'name' => 'One Word',
        'link' => 'oneWord',
        'icon' => 'fa-file-word',
        'for' => 'company',
        'module' => 'settings',
    ],

    'Clients Category' => [
        'name' => 'Clients Category',
        'link' => 'indexClientCategory',
        'icon' => 'fa-tags',
        'for' => 'company',
        'module' => 'cgt',
    ],
    'Leads Groups' => [
        'name' => 'Leads Groups',
        'link' => 'indexLeadsGroups',
        'icon' => 'fa-tags',
        'for' => 'company',
        'module' => 'cgt',
    ],
    'Leads Status' => [
        'name' => 'Leads Status',
        'link' => 'indexLeadsStatus',
        'icon' => 'fa-tags',
        'for' => 'company',
        'module' => 'cgt',
    ],
    'Leads Sources' => [
        'name' => 'Leads Sources',
        'link' => 'indexLeadsSources',
        'icon' => 'fa-tags',
        'for' => 'company',
        'module' => 'cgt',
    ],
    'Products Categories' => [
        'name' => 'Products Categories',
        'link' => 'indexProductCategories',
        'icon' => 'fa-tags',
        'for' => 'company',
        'module' => 'cgt',
    ],
    'Products Taxes' => [
        'name' => 'Products Taxes',
        'link' => 'indexProductTaxes',
        'icon' => 'fa-tags',
        'for' => 'company',
        'module' => 'cgt',
    ],
    'Tasks Status' => [
        'name' => 'Tasks Status',
        'link' => 'indexTasksStatus',
        'icon' => 'fa-tags',
        'for' => 'company',
        'module' => 'cgt',
    ],
    'Web to Lead' => [
        'name' => 'Web to Lead',
        'link' => 'leadFormSetting',
        'icon' => 'fa-phone-volume',
        'for' => 'company',
        'module' => 'settings',
    ],
    'Theme Customize' => [
        'name' => 'Theme Customize',
        'link' => 'theme',
        'icon' => 'fa-palette',
        'for' => 'both',
        'module' => 'settings',
    ],
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
        'value' => 'UTC',
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
        'value' => '$',
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
        'value' => 'USD',
        'is_media_setting' => false,
        'media_id' => null,
        'value_type' => 'string',
        'input_type' => 'text',
        'is_tenant' => true,
        'placeholder' => 'USD',
        'name' => 'tenant_company_currency_code'
    ],



]);
/**
 * company general settings
 */
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
        'value' => 'UTC',
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
        'value' => '$',
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
        'value' => 'USD',
        'is_media_setting' => false,
        'media_id' => null,
        'value_type' => 'string',
        'input_type' => 'text',
        'is_tenant' => false,
        'placeholder' => 'USD',
        'name' => 'client_company_currency_code'
    ],

    'STREET_ADDRESS' => [
        'key' => 'Street Address',
        'value' => 'Street 1st, Twins Tower Building, Office No 007',
        'is_media_setting' => false,
        'media_id' => null,
        'value_type' => 'string',
        'input_type' => 'textarea',
        'is_tenant' => false,
        'placeholder' => 'Street Address',
        'name' => 'client_company_address_street_address'
    ],
    'CITY' => [
        'key' => 'City',
        'value' => 'New Delhi',
        'is_media_setting' => false,
        'media_id' => null,
        'value_type' => 'string',
        'input_type' => 'text',
        'is_tenant' => false,
        'placeholder' => 'City name',
        'name' => 'client_company_address_city_name'
    ],
    'PINCODE' => [
        'key' => 'Pincode',
        'value' => '110006',
        'is_media_setting' => false,
        'media_id' => null,
        'value_type' => 'string',
        'input_type' => 'text',
        'is_tenant' => false,
        'placeholder' => 'Pincode',
        'name' => 'client_company_address_pincode'
    ],
    'COUNTRY' => [
        'key' => 'Country',
        'value' => 'India',
        'is_media_setting' => false,
        'media_id' => null,
        'value_type' => 'string',
        'input_type' => 'dropdown',
        'is_tenant' => false,
        'placeholder' => 'Country',
        'name' => 'client_company_address_country_id'
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

/**
 * company mail settings
 */
!defined('CRM_COMPANY_MAIL_SETTINGS') && define('CRM_COMPANY_MAIL_SETTINGS', [

    'MAIL_PROVIDER' => [
        'key' => 'Mail Provider',
        'value' => 'default',
        'is_media_setting' => false,
        'media_id' => null,
        'value_type' => 'string',
        'input_type' => 'text',
        'is_tenant' => false,
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
        'is_tenant' => false,
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
        'is_tenant' => false,
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
        'is_tenant' => false,
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
        'is_tenant' => false,
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
        'is_tenant' => false,
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
        'is_tenant' => false,
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
        'is_tenant' => false,
        'placeholder' => 'Josh Doe',
        'name' => 'client_mail_from_name'
    ],
]);



/**
 * Tenant Theme Light Colors Settings
 */
!defined('CRM_TENANT_THEME_LIGHT_SETTINGS') && define('CRM_TENANT_THEME_LIGHT_SETTINGS', [
    'PRIMARY_COLOR' => [
        'key' => 'Panel Primary Color Light',
        'value' => '#673DE6',
        'is_media_setting' => false,
        'media_id' => null,
        'value_type' => 'string',
        'input_type' => 'color',
        'is_tenant' => true,
        'placeholder' => '#673DE6',
        'name' => 'primary-color'
    ],
    'PRIMARY_COLOR_HOVER' => [
        'key' => 'Panel Primary Color Hover Light',
        'value' => '#5025d1',
        'is_media_setting' => false,
        'media_id' => null,
        'value_type' => 'string',
        'input_type' => 'color',
        'is_tenant' => true,
        'placeholder' => '#5025d1',
        'name' => 'primary-hover'
    ],
    'SECONDARY_COLOR' => [
        'key' => 'Panel Secondary Color Light',
        'value' => '#2F1C6A',
        'is_media_setting' => false,
        'media_id' => null,
        'value_type' => 'string',
        'input_type' => 'color',
        'is_tenant' => true,
        'placeholder' => '#2F1C6A',
        'name' => 'secondary-color'
    ],
    'SUCCESS_COLOR' => [
        'key' => 'Panel Success Color Light',
        'value' => '#00B090',
        'is_media_setting' => false,
        'media_id' => null,
        'value_type' => 'string',
        'input_type' => 'color',
        'is_tenant' => true,
        'placeholder' => '#00B090',
        'name' => 'success-color'
    ],
    'DANGER_COLOR' => [
        'key' => 'Panel Danger Color Light',
        'value' => '#FF3C5C',
        'is_media_setting' => false,
        'media_id' => null,
        'value_type' => 'string',
        'input_type' => 'color',
        'is_tenant' => true,
        'placeholder' => '#FF3C5C',
        'name' => 'danger-color'
    ],
    'WARNING_COLOR' => [
        'key' => 'Panel Warning Color Light',
        'value' => '#FFB800',
        'is_media_setting' => false,
        'media_id' => null,
        'value_type' => 'string',
        'input_type' => 'color',
        'is_tenant' => true,
        'placeholder' => '#FFB800',
        'name' => 'warning-color'
    ],
    'INFO_COLOR' => [
        'key' => 'Panel Info Color Light',
        'value' => '#2C5CC5',
        'is_media_setting' => false,
        'media_id' => null,
        'value_type' => 'string',
        'input_type' => 'color',
        'is_tenant' => true,
        'placeholder' => '#2C5CC5',
        'name' => 'info-color'
    ],
    'LIGHT_COLOR' => [
        'key' => 'Panel Light Color Light',
        'value' => '#F8F9FA',
        'is_media_setting' => false,
        'media_id' => null,
        'value_type' => 'string',
        'input_type' => 'color',
        'is_tenant' => true,
        'placeholder' => '#F8F9FA',
        'name' => 'light-color'
    ],
    'DARK_COLOR' => [
        'key' => 'Panel Dark Color Light',
        'value' => '#1D1E20',
        'is_media_setting' => false,
        'media_id' => null,
        'value_type' => 'string',
        'input_type' => 'color',
        'is_tenant' => true,
        'placeholder' => '#1D1E20',
        'name' => 'dark-color'
    ],
    'BODY_BG_COLOR' => [
        'key' => 'Panel Body BG Color Light',
        'value' => '#F4F4F5',
        'is_media_setting' => false,
        'media_id' => null,
        'value_type' => 'string',
        'input_type' => 'color',
        'is_tenant' => true,
        'placeholder' => '#F4F4F5',
        'name' => 'body-bg'
    ],
    'BODY_COLOR' => [
        'key' => 'Panel Body Color Light',
        'value' => '#1D1E20',
        'is_media_setting' => false,
        'media_id' => null,
        'value_type' => 'string',
        'input_type' => 'color',
        'is_tenant' => true,
        'placeholder' => '#1D1E20',
        'name' => 'body-color'
    ],
    'BORDER_COLOR' => [
        'key' => 'Panel Border Color Light',
        'value' => '#E6E6E6',
        'is_media_setting' => false,
        'media_id' => null,
        'value_type' => 'string',
        'input_type' => 'color',
        'is_tenant' => true,
        'placeholder' => '#E6E6E6',
        'name' => 'border-color'
    ],
    'CARD_BG_COLOR' => [
        'key' => 'Panel Card BG Color Light',
        'value' => '#ffffff',
        'is_media_setting' => false,
        'media_id' => null,
        'value_type' => 'string',
        'input_type' => 'color',
        'is_tenant' => true,
        'placeholder' => '#ffffff',
        'name' => 'card-bg'
    ],
    'INPUT_BG_COLOR' => [
        'key' => 'Panel Input BG Color Light',
        'value' => '#ffffff',
        'is_media_setting' => false,
        'media_id' => null,
        'value_type' => 'string',
        'input_type' => 'color',
        'is_tenant' => true,
        'placeholder' => '#ffffff',
        'name' => 'input-bg'
    ],
    'INPUT_BORDER_COLOR' => [
        'key' => 'Panel Input Border Color Light',
        'value' => '#E6E6E6',
        'is_media_setting' => false,
        'media_id' => null,
        'value_type' => 'string',
        'input_type' => 'color',
        'is_tenant' => true,
        'placeholder' => '#E6E6E6',
        'name' => 'input-border'
    ],
    'NEUTRAL_GRAY_COLOR' => [
        'key' => 'Panel Neutral Gray Color Light',
        'value' => '#727586',
        'is_media_setting' => false,
        'media_id' => null,
        'value_type' => 'string',
        'input_type' => 'color',
        'is_tenant' => true,
        'placeholder' => '#727586',
        'name' => 'neutral-gray'
    ],
    'SIDEBAR_BG' => [
        'key' => 'Panel Sidebar BG Color Light',
        'value' => '#ffffff',
        'is_media_setting' => false,
        'media_id' => null,
        'value_type' => 'string',
        'input_type' => 'color',
        'is_tenant' => true,
        'placeholder' => '#ffffff',
        'name' => 'sidebar-bg'
    ],
    'SIDEBAR_DIFF_BG' => [
        'key' => 'Panel Sidebar Diff BG Light',
        'value' => 'rgba(237, 232, 252,0.9)',
        'is_media_setting' => false,
        'media_id' => null,
        'value_type' => 'string',
        'input_type' => 'color',
        'is_tenant' => true,
        'placeholder' => 'rgba(237, 232, 252,0.9)',
        'name' => 'sidebar-diff-bg'
    ],
    'TASK_COL_CARD_BORDER' => [
        'key' => 'Panel Tasks Col Card Border Light',
        'value' => '#cfd3d7',
        'is_media_setting' => false,
        'media_id' => null,
        'value_type' => 'string',
        'input_type' => 'color',
        'is_tenant' => true,
        'placeholder' => '#cfd3d7',
        'name' => 'task-columns-cards-border'
    ],
 
]);

/**
 * Tenant Theme Dark Colors Settings
 */
!defined('CRM_TENANT_THEME_DARK_SETTINGS') && define('CRM_TENANT_THEME_DARK_SETTINGS', [
    'PRIMARY_COLOR' => [
        'key' => 'Panel Primary Color Dark',
        'value' => '#673DE6',
        'is_media_setting' => false,
        'media_id' => null,
        'value_type' => 'string',
        'input_type' => 'color',
        'is_tenant' => true,
        'placeholder' => '#673DE6',
        'name' => 'primary-color-d'
    ],
    'PRIMARY_COLOR_HOVER' => [
        'key' => 'Panel Primary Color Hover Dark',
        'value' => '#5025d1',
        'is_media_setting' => false,
        'media_id' => null,
        'value_type' => 'string',
        'input_type' => 'color',
        'is_tenant' => true,
        'placeholder' => '#5025d1',
        'name' => 'primary-hover-d'
    ],
    'SECONDARY_COLOR' => [
        'key' => 'Panel Secondary Color Dark',
        'value' => '#9B8AFB',
        'is_media_setting' => false,
        'media_id' => null,
        'value_type' => 'string',
        'input_type' => 'color',
        'is_tenant' => true,
        'placeholder' => '#9B8AFB',
        'name' => 'secondary-color-d'
    ],
    'SUCCESS_COLOR' => [
        'key' => 'Panel Success Color Dark',
        'value' => '#00D1AB',
        'is_media_setting' => false,
        'media_id' => null,
        'value_type' => 'string',
        'input_type' => 'color',
        'is_tenant' => true,
        'placeholder' => '#00D1AB',
        'name' => 'success-color-d'
    ],
    'DANGER_COLOR' => [
        'key' => 'Panel Danger Color Dark',
        'value' => '#FF5C7C',
        'is_media_setting' => false,
        'media_id' => null,
        'value_type' => 'string',
        'input_type' => 'color',
        'is_tenant' => true,
        'placeholder' => '#FF5C7C',
        'name' => 'danger-color-d'
    ],
    'WARNING_COLOR' => [
        'key' => 'Panel Warning Color Dark',
        'value' => '#FFD033',
        'is_media_setting' => false,
        'media_id' => null,
        'value_type' => 'string',
        'input_type' => 'color',
        'is_tenant' => true,
        'placeholder' => '#FFD033',
        'name' => 'warning-color-d'
    ],
    'INFO_COLOR' => [
        'key' => 'Panel Info Color Dark',
        'value' => '#4B7BE5',
        'is_media_setting' => false,
        'media_id' => null,
        'value_type' => 'string',
        'input_type' => 'color',
        'is_tenant' => true,
        'placeholder' => '#4B7BE5',
        'name' => 'info-color-d'
    ],
    'LIGHT_COLOR' => [
        'key' => 'Panel Light Color Dark',
        'value' => '#2D2D2D',
        'is_media_setting' => false,
        'media_id' => null,
        'value_type' => 'string',
        'input_type' => 'color',
        'is_tenant' => true,
        'placeholder' => '#2D2D2D',
        'name' => 'light-color-d'
    ],
    'DARK_COLOR' => [
        'key' => 'Panel Dark Color Dark',
        'value' => '#1A1A1A',
        'is_media_setting' => false,
        'media_id' => null,
        'value_type' => 'string',
        'input_type' => 'color',
        'is_tenant' => true,
        'placeholder' => '#1A1A1A',
        'name' => 'dark-color-d'
    ],
    'BODY_BG_COLOR' => [
        'key' => 'Panel Body BG Color Dark',
        'value' => '#1A1A1A',
        'is_media_setting' => false,
        'media_id' => null,
        'value_type' => 'string',
        'input_type' => 'color',
        'is_tenant' => true,
        'placeholder' => '#1A1A1A',
        'name' => 'body-bg-d'
    ],
    'BODY_COLOR' => [
        'key' => 'Panel Body Color Dark',
        'value' => '#ffffff',
        'is_media_setting' => false,
        'media_id' => null,
        'value_type' => 'string',
        'input_type' => 'color',
        'is_tenant' => true,
        'placeholder' => '#ffffff',
        'name' => 'body-color-d'
    ],
    'BORDER_COLOR' => [
        'key' => 'Panel Border Color Dark',
        'value' => '#2D2D2D',
        'is_media_setting' => false,
        'media_id' => null,
        'value_type' => 'string',
        'input_type' => 'color',
        'is_tenant' => true,
        'placeholder' => '#2D2D2D',
        'name' => 'border-color-d'
    ],
    'CARD_BG_COLOR' => [
        'key' => 'Panel Card BG Color Dark',
        'value' => '#2D2D2D',
        'is_media_setting' => false,
        'media_id' => null,
        'value_type' => 'string',
        'input_type' => 'color',
        'is_tenant' => true,
        'placeholder' => '#2D2D2D',
        'name' => 'card-bg-d'
    ],
    'INPUT_BG_COLOR' => [
        'key' => 'Panel Input BG Color Dark',
        'value' => '#2D2D2D',
        'is_media_setting' => false,
        'media_id' => null,
        'value_type' => 'string',
        'input_type' => 'color',
        'is_tenant' => true,
        'placeholder' => '#2D2D2D',
        'name' => 'input-bg-d'
    ],
    'INPUT_BORDER_COLOR' => [
        'key' => 'Panel Input Border Color Dark',
        'value' => '#3D3D3D',
        'is_media_setting' => false,
        'media_id' => null,
        'value_type' => 'string',
        'input_type' => 'color',
        'is_tenant' => true,
        'placeholder' => '#3D3D3D',
        'name' => 'input-border-d'
    ],
    'NEUTRAL_GRAY_COLOR' => [
        'key' => 'Panel Neutral Gray Color Dark',
        'value' => '#A0A0A0',
        'is_media_setting' => false,
        'media_id' => null,
        'value_type' => 'string',
        'input_type' => 'color',
        'is_tenant' => true,
        'placeholder' => '#A0A0A0',
        'name' => 'neutral-gray-d'
    ],
    'SIDEBAR_BG' => [
        'key' => 'Panel Sidebar BG Color Dark',
        'value' => '#2D2D2D',
        'is_media_setting' => false,
        'media_id' => null,
        'value_type' => 'string',
        'input_type' => 'color',
        'is_tenant' => true,
        'placeholder' => '#2D2D2D',
        'name' => 'sidebar-bg-d'
    ],
    'SIDEBAR_DIFF_BG' => [
        'key' => 'Panel Sidebar Diff BG Dark',
        'value' => '#0e0d0d',
        'is_media_setting' => false,
        'media_id' => null,
        'value_type' => 'string',
        'input_type' => 'color',
        'is_tenant' => true,
        'placeholder' => '#0e0d0d',
        'name' => 'sidebar-diff-bg-d'
    ],
    'TASK_COL_CARD_BORDER' => [
        'key' => 'Panel Tasks Col Card Border Dark',
        'value' => '#30363d',
        'is_media_setting' => false,
        'media_id' => null,
        'value_type' => 'string',
        'input_type' => 'color',
        'is_tenant' => true,
        'placeholder' => '#30363d',
        'name' => 'task-columns-cards-border-d'
    ],
 
]);



/**
 * Company Theme Light Colors Settings
 */
!defined('CRM_COMPANY_THEME_LIGHT_SETTINGS') && define('CRM_COMPANY_THEME_LIGHT_SETTINGS', [
    'PRIMARY_COLOR' => [
        'key' => 'Primary Color Light',
        'value' => '#673DE6',
        'is_media_setting' => false,
        'media_id' => null,
        'value_type' => 'string',
        'input_type' => 'color',
        'is_tenant' => true,
        'placeholder' => '#673DE6',
        'name' => 'primary-color-company'
    ],
    'PRIMARY_COLOR_HOVER' => [
        'key' => 'Primary Color Hover Light',
        'value' => '#5025d1',
        'is_media_setting' => false,
        'media_id' => null,
        'value_type' => 'string',
        'input_type' => 'color',
        'is_tenant' => true,
        'placeholder' => '#5025d1',
        'name' => 'primary-hover-company'
    ],
    'SECONDARY_COLOR' => [
        'key' => 'Secondary Color Light',
        'value' => '#2F1C6A',
        'is_media_setting' => false,
        'media_id' => null,
        'value_type' => 'string',
        'input_type' => 'color',
        'is_tenant' => true,
        'placeholder' => '#2F1C6A',
        'name' => 'secondary-color-company'
    ],
    'SUCCESS_COLOR' => [
        'key' => 'Success Color Light',
        'value' => '#00B090',
        'is_media_setting' => false,
        'media_id' => null,
        'value_type' => 'string',
        'input_type' => 'color',
        'is_tenant' => true,
        'placeholder' => '#00B090',
        'name' => 'success-color-company'
    ],
    'DANGER_COLOR' => [
        'key' => 'Danger Color Light',
        'value' => '#FF3C5C',
        'is_media_setting' => false,
        'media_id' => null,
        'value_type' => 'string',
        'input_type' => 'color',
        'is_tenant' => true,
        'placeholder' => '#FF3C5C',
        'name' => 'danger-color-company'
    ],
    'WARNING_COLOR' => [
        'key' => 'Warning Color Light',
        'value' => '#FFB800',
        'is_media_setting' => false,
        'media_id' => null,
        'value_type' => 'string',
        'input_type' => 'color',
        'is_tenant' => true,
        'placeholder' => '#FFB800',
        'name' => 'warning-color-company'
    ],
    'INFO_COLOR' => [
        'key' => 'Info Color Light',
        'value' => '#2C5CC5',
        'is_media_setting' => false,
        'media_id' => null,
        'value_type' => 'string',
        'input_type' => 'color',
        'is_tenant' => true,
        'placeholder' => '#2C5CC5',
        'name' => 'info-color-company'
    ],
    'LIGHT_COLOR' => [
        'key' => 'Light Color Light',
        'value' => '#F8F9FA',
        'is_media_setting' => false,
        'media_id' => null,
        'value_type' => 'string',
        'input_type' => 'color',
        'is_tenant' => true,
        'placeholder' => '#F8F9FA',
        'name' => 'light-color-company'
    ],
    'DARK_COLOR' => [
        'key' => 'Dark Color Light',
        'value' => '#1D1E20',
        'is_media_setting' => false,
        'media_id' => null,
        'value_type' => 'string',
        'input_type' => 'color',
        'is_tenant' => true,
        'placeholder' => '#1D1E20',
        'name' => 'dark-color-company'
    ],
    'BODY_BG_COLOR' => [
        'key' => 'Body BG Color Light',
        'value' => '#F4F4F5',
        'is_media_setting' => false,
        'media_id' => null,
        'value_type' => 'string',
        'input_type' => 'color',
        'is_tenant' => true,
        'placeholder' => '#F4F4F5',
        'name' => 'body-bg-company'
    ],
    'BODY_COLOR' => [
        'key' => 'Body Color Light',
        'value' => '#1D1E20',
        'is_media_setting' => false,
        'media_id' => null,
        'value_type' => 'string',
        'input_type' => 'color',
        'is_tenant' => true,
        'placeholder' => '#1D1E20',
        'name' => 'body-color-company'
    ],
    'BORDER_COLOR' => [
        'key' => 'Border Color Light',
        'value' => '#E6E6E6',
        'is_media_setting' => false,
        'media_id' => null,
        'value_type' => 'string',
        'input_type' => 'color',
        'is_tenant' => true,
        'placeholder' => '#E6E6E6',
        'name' => 'border-color-company'
    ],
    'CARD_BG_COLOR' => [
        'key' => 'Card BG Color Light',
        'value' => '#ffffff',
        'is_media_setting' => false,
        'media_id' => null,
        'value_type' => 'string',
        'input_type' => 'color',
        'is_tenant' => true,
        'placeholder' => '#ffffff',
        'name' => 'card-bg-company'
    ],
    'INPUT_BG_COLOR' => [
        'key' => 'Input BG Color Light',
        'value' => '#ffffff',
        'is_media_setting' => false,
        'media_id' => null,
        'value_type' => 'string',
        'input_type' => 'color',
        'is_tenant' => true,
        'placeholder' => '#ffffff',
        'name' => 'input-bg-company'
    ],
    'INPUT_BORDER_COLOR' => [
        'key' => 'Input Border Color Light',
        'value' => '#E6E6E6',
        'is_media_setting' => false,
        'media_id' => null,
        'value_type' => 'string',
        'input_type' => 'color',
        'is_tenant' => true,
        'placeholder' => '#E6E6E6',
        'name' => 'input-border-company'
    ],
    'NEUTRAL_GRAY_COLOR' => [
        'key' => 'Neutral Gray Color Light',
        'value' => '#727586',
        'is_media_setting' => false,
        'media_id' => null,
        'value_type' => 'string',
        'input_type' => 'color',
        'is_tenant' => true,
        'placeholder' => '#727586',
        'name' => 'neutral-gray-company'
    ],
    'SIDEBAR_BG' => [
        'key' => 'Sidebar BG Color Light',
        'value' => '#ffffff',
        'is_media_setting' => false,
        'media_id' => null,
        'value_type' => 'string',
        'input_type' => 'color',
        'is_tenant' => true,
        'placeholder' => '#ffffff',
        'name' => 'sidebar-bg-company'
    ],
    'SIDEBAR_DIFF_BG' => [
        'key' => 'Sidebar Diff BG Light',
        'value' => 'rgba(237, 232, 252,0.9)',
        'is_media_setting' => false,
        'media_id' => null,
        'value_type' => 'string',
        'input_type' => 'color',
        'is_tenant' => true,
        'placeholder' => 'rgba(237, 232, 252,0.9)',
        'name' => 'sidebar-diff-bg-company'
    ],
    'TASK_COL_CARD_BORDER' => [
        'key' => 'Tasks Col Card Border Light',
        'value' => '#cfd3d7',
        'is_media_setting' => false,
        'media_id' => null,
        'value_type' => 'string',
        'input_type' => 'color',
        'is_tenant' => true,
        'placeholder' => '#cfd3d7',
        'name' => 'task-columns-cards-border-company'
    ],
 
]);


/**
 * Company Theme Dark Colors Settings
 */
!defined('CRM_COMPANY_THEME_DARK_SETTINGS') && define('CRM_COMPANY_THEME_DARK_SETTINGS', [
    'PRIMARY_COLOR' => [
        'key' => 'Primary Color Dark',
        'value' => '#673DE6',
        'is_media_setting' => false,
        'media_id' => null,
        'value_type' => 'string',
        'input_type' => 'color',
        'is_tenant' => true,
        'placeholder' => '#673DE6',
        'name' => 'primary-color-d-company'
    ],
    'PRIMARY_COLOR_HOVER' => [
        'key' => 'Primary Color Hover Dark',
        'value' => '#5025d1',
        'is_media_setting' => false,
        'media_id' => null,
        'value_type' => 'string',
        'input_type' => 'color',
        'is_tenant' => true,
        'placeholder' => '#5025d1',
        'name' => 'primary-hover-d-company'
    ],
    'SECONDARY_COLOR' => [
        'key' => 'Secondary Color Dark',
        'value' => '#9B8AFB',
        'is_media_setting' => false,
        'media_id' => null,
        'value_type' => 'string',
        'input_type' => 'color',
        'is_tenant' => true,
        'placeholder' => '#9B8AFB',
        'name' => 'secondary-color-d-company'
    ],
    'SUCCESS_COLOR' => [
        'key' => 'Success Color Dark',
        'value' => '#00D1AB',
        'is_media_setting' => false,
        'media_id' => null,
        'value_type' => 'string',
        'input_type' => 'color',
        'is_tenant' => true,
        'placeholder' => '#00D1AB',
        'name' => 'success-color-d-company'
    ],
    'DANGER_COLOR' => [
        'key' => 'Danger Color Dark',
        'value' => '#FF5C7C',
        'is_media_setting' => false,
        'media_id' => null,
        'value_type' => 'string',
        'input_type' => 'color',
        'is_tenant' => true,
        'placeholder' => '#FF5C7C',
        'name' => 'danger-color-d-company'
    ],
    'WARNING_COLOR' => [
        'key' => 'Warning Color Dark',
        'value' => '#FFD033',
        'is_media_setting' => false,
        'media_id' => null,
        'value_type' => 'string',
        'input_type' => 'color',
        'is_tenant' => true,
        'placeholder' => '#FFD033',
        'name' => 'warning-color-d-company'
    ],
    'INFO_COLOR' => [
        'key' => 'Info Color Dark',
        'value' => '#4B7BE5',
        'is_media_setting' => false,
        'media_id' => null,
        'value_type' => 'string',
        'input_type' => 'color',
        'is_tenant' => true,
        'placeholder' => '#4B7BE5',
        'name' => 'info-color-d-company'
    ],
    'LIGHT_COLOR' => [
        'key' => 'Light Color Dark',
        'value' => '#2D2D2D',
        'is_media_setting' => false,
        'media_id' => null,
        'value_type' => 'string',
        'input_type' => 'color',
        'is_tenant' => true,
        'placeholder' => '#2D2D2D',
        'name' => 'light-color-d-company'
    ],
    'DARK_COLOR' => [
        'key' => 'Dark Color Dark',
        'value' => '#1A1A1A',
        'is_media_setting' => false,
        'media_id' => null,
        'value_type' => 'string',
        'input_type' => 'color',
        'is_tenant' => true,
        'placeholder' => '#1A1A1A',
        'name' => 'dark-color-d-company'
    ],
    'BODY_BG_COLOR' => [
        'key' => 'Body BG Color Dark',
        'value' => '#1A1A1A',
        'is_media_setting' => false,
        'media_id' => null,
        'value_type' => 'string',
        'input_type' => 'color',
        'is_tenant' => true,
        'placeholder' => '#1A1A1A',
        'name' => 'body-bg-d-company'
    ],
    'BODY_COLOR' => [
        'key' => 'Body Color Dark',
        'value' => '#ffffff',
        'is_media_setting' => false,
        'media_id' => null,
        'value_type' => 'string',
        'input_type' => 'color',
        'is_tenant' => true,
        'placeholder' => '#ffffff',
        'name' => 'body-color-d-company'
    ],
    'BORDER_COLOR' => [
        'key' => 'Border Color Dark',
        'value' => '#2D2D2D',
        'is_media_setting' => false,
        'media_id' => null,
        'value_type' => 'string',
        'input_type' => 'color',
        'is_tenant' => true,
        'placeholder' => '#2D2D2D',
        'name' => 'border-color-d-company'
    ],
    'CARD_BG_COLOR' => [
        'key' => 'Card BG Color Dark',
        'value' => '#2D2D2D',
        'is_media_setting' => false,
        'media_id' => null,
        'value_type' => 'string',
        'input_type' => 'color',
        'is_tenant' => true,
        'placeholder' => '#2D2D2D',
        'name' => 'card-bg-d-company'
    ],
    'INPUT_BG_COLOR' => [
        'key' => 'Input BG Color Dark',
        'value' => '#2D2D2D',
        'is_media_setting' => false,
        'media_id' => null,
        'value_type' => 'string',
        'input_type' => 'color',
        'is_tenant' => true,
        'placeholder' => '#2D2D2D',
        'name' => 'input-bg-d-company'
    ],
    'INPUT_BORDER_COLOR' => [
        'key' => 'Input Border Color Dark',
        'value' => '#3D3D3D',
        'is_media_setting' => false,
        'media_id' => null,
        'value_type' => 'string',
        'input_type' => 'color',
        'is_tenant' => true,
        'placeholder' => '#3D3D3D',
        'name' => 'input-border-d-company'
    ],
    'NEUTRAL_GRAY_COLOR' => [
        'key' => 'Neutral Gray Color Dark',
        'value' => '#A0A0A0',
        'is_media_setting' => false,
        'media_id' => null,
        'value_type' => 'string',
        'input_type' => 'color',
        'is_tenant' => true,
        'placeholder' => '#A0A0A0',
        'name' => 'neutral-gray-d-company'
    ],
    'SIDEBAR_BG' => [
        'key' => 'Sidebar BG Color Dark',
        'value' => '#2D2D2D',
        'is_media_setting' => false,
        'media_id' => null,
        'value_type' => 'string',
        'input_type' => 'color',
        'is_tenant' => true,
        'placeholder' => '#2D2D2D',
        'name' => 'sidebar-bg-d-company'
    ],
    'SIDEBAR_DIFF_BG' => [
        'key' => 'Sidebar Diff BG Dark',
        'value' => '#0e0d0d',
        'is_media_setting' => false,
        'media_id' => null,
        'value_type' => 'string',
        'input_type' => 'color',
        'is_tenant' => true,
        'placeholder' => '#0e0d0d',
        'name' => 'sidebar-diff-bg-d-company'
    ],
    'TASK_COL_CARD_BORDER' => [
        'key' => 'Tasks Col Card Border Dark',
        'value' => '#30363d',
        'is_media_setting' => false,
        'media_id' => null,
        'value_type' => 'string',
        'input_type' => 'color',
        'is_tenant' => true,
        'placeholder' => '#30363d',
        'name' => 'task-columns-cards-border-d-company'
    ],
 
]);





// One Word Settings

!defined('CRM_COMPANY_ONE_WORD_SETTINGS') && define('CRM_COMPANY_ONE_WORD_SETTINGS', [

    'PROPOSAL_PREFIX' => [
        'key' => 'Proposal Prefix',
        'value' => 'PRO',
        'is_media_setting' => false,
        'media_id' => null,
        'value_type' => 'string',
        'input_type' => 'text',
        'is_tenant' => false,
        'placeholder' => 'PRO',
        'name' => 'client_proposal_prefix'
    ],
    'CONTRACT_PREFIX' => [
        'key' => 'Contract Prefix',
        'value' => 'CONT',
        'is_media_setting' => false,
        'media_id' => null,
        'value_type' => 'string',
        'input_type' => 'text',
        'is_tenant' => false,
        'placeholder' => 'CONT',
        'name' => 'client_contract_prefix'
    ],
    'ESTIMATE_PREFIX' => [
        'key' => 'Estimate Prefix',
        'value' => 'EST',
        'is_media_setting' => false,
        'media_id' => null,
        'value_type' => 'string',
        'input_type' => 'text',
        'is_tenant' => false,
        'placeholder' => 'EST',
        'name' => 'client_estimate_prefix'
    ],
    'INVOICE_PREFIX' => [
        'key' => 'Invoice Prefix',
        'value' => 'INV',
        'is_media_setting' => false,
        'media_id' => null,
        'value_type' => 'string',
        'input_type' => 'text',
        'is_tenant' => false,
        'placeholder' => 'INV',
        'name' => 'client_invoice_prefix'
    ],

]);


/**
 *  custom fields input types
 */
!defined('CUSTOM_FIELDS_INPUT_TYPES') && define('CUSTOM_FIELDS_INPUT_TYPES', [

    'text' => 'Text',
    'number' => 'Number',
    'select' => 'Dropdown',
    'date' => 'Date',
    'time' => 'Time',
    'textarea' => 'TeaxArea',
    'checkbox' => 'Checkbox',

]);



// kyes must match the model name in lowercase
/**
 * custom fields relation types
 */
!defined('CUSTOM_FIELDS_RELATION_TYPES') && define('CUSTOM_FIELDS_RELATION_TYPES', [
    'KEYS' => ['crmclients' => 'crmclients', 'user' => 'user', 'crmleads' => 'crmleads', 'productsservices' => 'productsservices', 'project' => 'project', 'tasks' => 'tasks'],
    'VALUES' => ['crmclients' => 'Clients', 'user' => 'Users & Employees', 'crmleads' => 'Leads', 'productsservices' => 'Products & Services', 'project' => 'Projects', 'tasks' => 'Tasks'],
]);

/**
 * category group tags types
 */
!defined('CATEGORY_GROUP_TAGS_TYPES') && define('CATEGORY_GROUP_TAGS_TYPES', [
    'TABLE_STATUS' => [
        'categories',
        'groups',
        'tags',
        'leads_groups',
        'leads_sources',
        'leads_status',
        'products_categories',
        'products_taxs',
        'tasks_status'
    ],
    'STATUS' => [
        'categories' => 'Categories',
        'groups' => 'Groups',
        'tags' => 'Tags',
        'leads_groups' => 'Leads Groups',
        'leads_sources' => 'Leads Sources',
        'leads_status' => 'Leads Status',
        'products_categories' => 'Products Categories',
        'products_taxs' => 'Products Tags',
        'tasks_status' => 'Tasks Status',
    ],
    'KEY' => [
        'categories' => 'categories',
        'groups' => 'groups',
        'tags' => 'tags',
        'leads_groups' => 'leads_groups',
        'leads_sources' => 'leads_sources',
        'leads_status' => 'leads_status',
        'products_categories' => 'products_categories',
        'products_taxs' => 'products_taxs',
        'tasks_status' => 'tasks_status',
    ],
]);

/**
 * category groups tags relationships
 */
!defined('CATEGORY_GROUP_TAGS_RELATIONS') && define('CATEGORY_GROUP_TAGS_RELATIONS', [
    'TABLE_STATUS' => [
        'clients',
        'leads',
        'products_services',
        'tasks'
    ],
    'STATUS' => [
        'clients' => 'Clients',
        'leads' => 'Leads',
        'products_services' => 'Products & Services',
        'tasks' => 'Tasks',
    ],
    'KEY' => [
        'clients' => 'clients',
        'leads' => 'leads',
        'products_services' => 'products_services',
        'tasks' => 'tasks',
    ],
]);

/**
 * tasks related to
 */
!defined('TASKS_RELATED_TO') && define('TASKS_RELATED_TO', [
    'TABLE_STATUS' => [
        'project',
        'clients',
        'leads',
        'estimate',
        'proposal',
        'contract',
        'ticket',
    ],
    'STATUS' => [
        'project' => 'Projects',
        'clients' => 'Clients',
        'leads' => 'Leads',
        'estimate' => 'Estimate',
        'proposal' => 'Proposal',
        'contract' => 'Contract',
        'ticket' => 'Ticket',
    ],
    'KEY' => [
        'project' => 'project',
        'clients' => 'clients',
        'leads' => 'leads',
        'estimate' => 'estimate',
        'proposal' => 'proposal',
        'contract' => 'contract',
        'ticket' => 'ticket',
    ],
]);

/**
 * quick create menus
 */
!defined('QUICK_CREATE_MENU') && define('QUICK_CREATE_MENU', [
    'SUPER_PANEL' => [
        'MENUS' => [
            [
                'name' => 'User',
                'route' => 'users.create',
            ],
            [
                'name' => 'Company',
                'route' => 'companies.create',
            ],
            [
                'name' => 'Plans',
                'route' => 'plans.create',
            ],
            [
                'name' => 'Role',
                'route' => 'role.create',
            ],
        ]
    ],
    'COMPANY_PANEL' => [
        'MENUS' => [
            [
                'name' => 'Client',
                'route' => 'clients.create',
            ],
            [
                'name' => 'Lead',
                'route' => 'leads.create',
            ],
            [
                'name' => 'Proposal',
                'route' => 'proposals.create',
            ],
            [
                'name' => 'Estimate',
                'route' => 'estimates.create',
            ],
            [
                'name' => 'Contract',
                'route' => 'contracts.create',
            ],
            [
                'name' => 'Product',
                'route' => 'products_services.create',
            ],
            [
                'name' => 'Invoice',
                'route' => 'invoices.create',
            ],
            [
                'name' => 'Project',
                'route' => 'projects.create',
            ],
            [
                'name' => 'Task',
                'route' => 'tasks.create',
            ],
            [
                'name' => 'Custom Field',
                'route' => 'customfields.create',
            ],
            [
                'name' => 'User',
                'route' => 'users.create',
            ],
            [
                'name' => 'Role',
                'route' => 'role.create',
            ],
        ]
    ],
]);

/**
 * frontend default settings
 */
!defined('FRONT_END_DEFAULT_SETTINGS') && define('FRONT_END_DEFAULT_SETTINGS', [
    'HERO' => [
        'Heading' => 'Streamline Your Business with CoreXGen CRM',
        'SubHeading' => "Intelligent Customer Relationship Management that transforms how you connect, engage, and grow your business.",
    ],
    "FEATURES" => [
        'Heading' => 'Powerful Features for Modern Businesses',
        'SubHeading' => "CoreXGen offers comprehensive tools to manage your customer relationships",
        "Options" => [
            [
                "Heading" => "Tasks Pipeline",
                "SubHeading" => "Visualize and optimize your tasks process with intuitive pipeline management."
            ],
            [
                "Heading" => "Customer Insights",
                "SubHeading" => "Deep analytics and 360-degree customer view to understand your clients better."
            ],
            [
                "Heading" => "Capture Leads",
                "SubHeading" => "Web to lead capture online in one click, don't miss out your single opportunity."
            ],
        ]
    ],
    "SOLUTIONS" => [
        'Heading' => 'Custom Solutions for Every Business',
        'SubHeading' => "CoreXGen adapts to your unique business needs with flexible, scalable solutions.",
        "Options" => [
            "Leads Management",
            "Clients Management",
            "Proposal Management",
            "Estimates Management",
            "Contracts Management",
            "Products & Services Management",
            "Invoices Management",
            "Projects Management",
            "Tasks Management",
            "Custom Fields",
            "Event Calendar",
            "Users & Employees Managment",
            "Roles & Permissions Managment",
            "Much More...",
        ]
    ],
    'PLANS' => [
        'Heading' => 'Simple, Transparent Pricing',
        'SubHeading' => "Choose a plan that grows with your business.",
    ],
    "TESTIMONIALS" => [
        'Heading' => 'What Our Customers Say',
        "Options" => [
            [
                "Message" => "CoreXGen transformed our sales process. The insights are incredible!",
                "Customer Name" => "Sarah Johnson",
                "Position" => "CEO",
                "Company" => "TechStartup Inc.",
                "LOGO" => "/img/100.svg"
            ],
            [
                "Message" => "Seamless integrations and powerful automation. A game-changer for our business!",
                "Customer Name" => "Mike Rodriguez",
                "Position" => "Sales Director",
                "Company" => "GlobalSales Co.",
                "LOGO" => "/img/100.svg"
            ],
            [
                "Message" => "The customer insights feature has dramatically improved our customer relationships",
                "Customer Name" => "Emily Chen",
                "Position" => "Marketing Director",
                "Company" => "InnovateNow",
                "LOGO" => "/img/100.svg"
            ],
        ]
    ],
    'FOOTER' => [
        'Heading' => 'Intelligent CRM that helps businesses grow and succeed.',
        'SubHeading' => "All Rights Reserved.",
    ],
]);
