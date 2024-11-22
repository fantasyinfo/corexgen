<?php

use App\Models\User;
use App\Models\CRM\CRMRole;
use App\Models\CRM\CRMRolePermissions;
use Illuminate\Support\Facades\DB;
use App\Models\Media;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;


final class PermissionsIds
{
    public static $PERMISSIONS_IDS = [
        'DASHBOARD' => [501 => 'READ', 502 => 'READ_ALL'],
        'ROLE' => [551 => 'CREATE', 552 => 'READ', 553 => 'READ_ALL', 554 => 'UPDATE', 555 => 'DELETE', 556 => 'IMPORT', 557 => 'EXPORT', 558 => 'FILTER', 559 => 'CHANGE_STATUS',],
        'USERS' => [601 => 'CREATE', 602 => 'READ', 103 => 'READ_ALL', 604 => 'UPDATE', 605 => 'DELETE', 606 => 'IMPORT', 607 => 'EXPORT', 608 => 'FILTER', 609 => 'CHANGE_STATUS',],
        'PERMISSIONS' => [651 => 'CREATE', 652 => 'READ', 653 => 'READ_ALL', 654 => 'UPDATE', 655 => 'DELETE', 656 => 'FILTER',]

    ];

    // 500 reserved for featueres
    public static $PARENT_PERMISSION_IDS = [
        1 => 1,
        2 => 2,
        3 => 3,
        4 => 4,
        5 => 5,
        6 => 6,
        7 => 7,
        8 => 8,
        9 => 9,
        10 => 10,

    ];

    // Method to find the key for a given value in a specific category
    public static function findPermissionKey($category, $permission)
    {
        if (isset(self::$PERMISSIONS_IDS[$category])) {
            return array_search($permission, self::$PERMISSIONS_IDS[$category], true);
        }
        return null; // Return null if category or permission not found
    }
}

!defined('CRMPERMISSIONS') && define('CRMPERMISSIONS', [
    'DASHBOARD' => [
        'name' => 'CRM_DASHBOARD',
        'id' => PermissionsIds::$PARENT_PERMISSION_IDS['1'],
        'children' => PermissionsIds::$PERMISSIONS_IDS['DASHBOARD']
    ],
    'ROLE' => [
        'name' => 'CRM_ROLE',
        'id' => PermissionsIds::$PARENT_PERMISSION_IDS['2'],
        'children' => PermissionsIds::$PERMISSIONS_IDS['ROLE']
    ],
    'USERS' => [
        'name' => 'CRM_USERS',
        'id' => PermissionsIds::$PARENT_PERMISSION_IDS['3'],
        'children' => PermissionsIds::$PERMISSIONS_IDS['USERS']
    ],
    'PERMISSIONS' => [
        'name' => 'CRM_PERMISSIONS',
        'id' => PermissionsIds::$PARENT_PERMISSION_IDS['4'],
        'children' => PermissionsIds::$PERMISSIONS_IDS['PERMISSIONS']
    ],
]);

!defined('CRM_MENU_ITEMS') && define('CRM_MENU_ITEMS', [
    'Dashboard' => [
        'menu_icon' => 'feather-airplay',
        'permission_id' => PermissionsIds::$PARENT_PERMISSION_IDS['1'],
        'children' => [
            'CRM' => [
                'menu_url' => 'home',
                'menu_icon' => 'feather-corner-down-right',
                'permission_id' => PermissionsIds::findPermissionKey('DASHBOARD', 'READ_ALL')
            ]
        ]
    ],
    'Roles & Permissions' => [
        'menu_icon' => 'feather-command',
        'permission_id' => PermissionsIds::$PARENT_PERMISSION_IDS['2'],
        'children' => [
            'Role' => ['menu_url' => 'crm.role.index', 'menu_icon' => 'feather-corner-down-right', 'permission_id' => PermissionsIds::findPermissionKey('ROLE', 'READ_ALL')],
            'Permissions' => ['menu_url' => 'crm.permissions.index', 'menu_icon' => 'feather-corner-down-right', 'permission_id' => PermissionsIds::findPermissionKey('PERMISSIONS', 'READ_ALL')],
        ]
    ],
    'Users' => [
        'menu_icon' => 'feather-user-plus',
        'permission_id' => PermissionsIds::$PARENT_PERMISSION_IDS['3'],
        'children' => [
            'Users' => ['menu_url' => 'crm.users.index', 'menu_icon' => 'feather-corner-down-right', 'permission_id' => PermissionsIds::findPermissionKey('USERS', 'READ_ALL')],
        ]
    ]
]);





!defined('CRM_SETTINGS') && define('CRM_SETTINGS', [
    'GENERAL_SETTINGS' => [
        'COMPANY_NAME' => [
            'key' => 'Company Name',
            'value' => 'Core X Gen',
            'is_media_setting' => false,
            'media_id' => null,
        ],
        'COMPANY_TAGLINE' => [
            'key' => 'Company Tagline',
            'value' => 'Next Generation CRM',
            'is_media_setting' => false,
            'media_id' => null,
        ],
        'COMPANY_LOGO' => [
            'key' => 'Company Logo',
            'value' => '/',
            'is_media_setting' => true,
            'media_id' => null,
        ],
        'DATE_FORMAT' => [
            'key' => 'Date Format',
            'value' => 'DD/MM/YYYY',
            'is_media_setting' => false,
            'media_id' => null,
        ],
        'TIME_FORMAT' => [
            'key' => 'Time Format',
            'value' => '12 Hours',
            'is_media_setting' => false,
            'media_id' => null,
        ],
        'TIME_ZONE' => [
            'key' => 'Time Zone',
            'value' => 'Asia/Kolkata',
            'is_media_setting' => false,
            'media_id' => null,
        ],

    ],
]);





function createMedia(UploadedFile $file)
{
    // Validate or process the file as needed

    $mediaDirectory = storage_path('app/public/media');
    if (!file_exists($mediaDirectory)) {
        mkdir($mediaDirectory, 0775, true);
    }

    // Store the file in the 'media' directory (can be customized to use any disk)
    $filePath = $file->store('media', 'public');

    // Create the media record in the database
    $media = Media::create([
        'file_name' => $file->getClientOriginalName(),
        'file_path' => $filePath,
        'file_type' => $file->getMimeType(),
        'file_extension' => $file->getClientOriginalExtension(),
        'size' => $file->getSize(),
        'buyer_id' =>  auth()->user()->buyer_id,
        'is_super_user' => auth()->user()->is_super_user,
        'created_by' => auth()->user()->id,
        'updated_by' => auth()->user()->id,
        'status' => 'active',
    ]);

    // Return the created media record
    return $media;
}














function getUserName($id = null)
{
    if ($id == null) {
        return '';
    } else {
        return User::findOrFail($id)->name;
    }
}



// crm/roles
function filterRolesDetails($roles)
{
    $roleData = [];
    foreach ($roles as $role) {
        $tmp = [];
        $tmp['id'] = $role->id;
        $tmp['role_name'] = $role->role_name;
        $tmp['role_desc'] = $role->role_desc;
        $tmp['created_at'] = $role->created_at;
        $tmp['updated_at'] = $role->updated_at;
        $tmp['status'] = $role->status;
        $tmp['created_by'] = getUserName($role->created_by);
        $tmp['updated_by'] = getUserName($role->updated_by);
        $roleData[] = $tmp;
    }

    return $roleData;
}

function filterRoleDetails($role)
{

    return [
        'id' => $role->id,
        'role_name' => $role->role_name,
        'role_desc' => $role->role_desc,
        'created_at' => $role->created_at,
        'updated_at' => $role->updated_at,
        'status' => $role->status,
        'created_by' => getUserName($role->created_by),
        'updated_by' => getUserName($role->updated_by),
    ];
}

// users
function filterUsersDetails($users)
{
    $userData = [];
    foreach ($users as $user) {
        $tmp = [];
        $tmp['id'] = $user->id;
        $tmp['name'] = $user->name;
        $tmp['email'] = $user->email;
        $tmp['role_name'] = getRoleName($user->role_id);
        $tmp['created_at'] = $user->created_at;
        $tmp['updated_at'] = $user->updated_at;
        $tmp['status'] = $user->status;
        $userData[] = $tmp;
    }

    return $userData;
}

function getRoleName($role_id = null)
{

    if ($role_id == null) {
        return '';
    } else {
        return CRMRole::findOrFail($role_id)->role_name;
    }
}

function filerUserDetails($user)
{

    return [
        'id' => $user->id,
        'name' => $user->name,
        'email' => $user->email,
        'role_name' => getRoleName($user->role_id),
        'created_at' => $user->created_at,
        'updated_at' => $user->updated_at,
        'status' => $user->status,
    ];
}


function prePrintR($arr)
{
    echo '<pre>';
    print_r($arr);
    echo '</pre>';
}



function getCRMMenus()
{
    return DB::table('crm_menu')->where('buyer_id', 1)->get();
}

/**
 * Check if the current user has specific permission
 *
 * @param string $permissionKey The permission key in format 'MODULE.PERMISSION'
 * @return bool
 */
function hasPermission($permissionKey)
{

    // Get the current user's role ID
    $userRoleId = auth()->user()->role_id;

    // return true for superadmin or role id 1
    if ($userRoleId == 1) {
        return true;
    }
    // Split the permission key into module and permission type
    $parts = explode('.', $permissionKey);

    // Validate the permission key format
    if (count($parts) !== 2) {
        \Log::warning("Invalid permission key format: {$permissionKey}. Use 'MODULE.PERMISSION'.");
        return false;
    }

    // Extract module and permission type
    list($module, $permissionType) = $parts;

    // Validate the module exists in CRMPERMISSIONS
    if (!isset(CRMPERMISSIONS[$module])) {
        \Log::warning("Module not found in CRMPERMISSIONS: {$module}");
        return false;
    }

    // Get the module's parent permission ID
    $modulePermissionId = CRMPERMISSIONS[$module]['id'];

    // Check if the specific child permission exists
    $childPermissions = CRMPERMISSIONS[$module]['children'];
    $childPermissionId = null;

    // Find the child permission ID
    foreach ($childPermissions as $id => $name) {
        if (strtoupper($name) === strtoupper($permissionType)) {
            $childPermissionId = $id;
            break;
        }
    }

    // If no matching child permission found, return false
    if ($childPermissionId === null) {
        \Log::warning("Permission type not found for module {$module}: {$permissionType}");
        return false;
    }



    // Check if the specific child permission exists for the user's role
    $specificPermissionExists = CRMRolePermissions::where('role_id', $userRoleId)
        ->where('permission_id', $childPermissionId)
        ->exists();

    if ($specificPermissionExists) {
        return true;
    }

    // Check if the parent module permission exists
    $modulePermissionExists = CRMRolePermissions::where('role_id', $userRoleId)
        ->where('permission_id', $modulePermissionId)
        ->exists();

    return $modulePermissionExists;
}



function hasMenuPermission($permissionId = null)
{
    // for superadmins
    $userRoleId = auth()->user()->role_id;

    if ($userRoleId == 1) return true;

    if ($permissionId == null)
        return false;

    if (!$userRoleId)
        return false;

    $permissionExists = DB::table('crm_role_permissions')
        ->where('permission_id', $permissionId)
        ->where('role_id', $userRoleId)
        ->exists();

    return $permissionExists;
}
