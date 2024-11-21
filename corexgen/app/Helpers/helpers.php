<?php

use App\Models\User;
use App\Models\CRM\CRMRole;
use App\Models\CRM\CRMRolePermissions;
use Illuminate\Support\Facades\DB;



!defined('CRMPERMISSIONS') && define('CRMPERMISSIONS',  [
    'DASHBOARD' => [
        'name' => 'CRM_DASHBOARD',
        'id' => 1,
        'children' => [
            2 => 'READ'
        ]
    ],
    'ROLE' => [
        'name' => 'CRM_ROLE',
        'id' => 20,
        'children' => [
            21 => 'CREATE',
            22 => 'READ',
            23 => 'READ_ALL',
            24 => 'UPDATE',
            25 => 'DELETE',
            26 => 'IMPORT',
            27 => 'EXPORT',
            28 => 'FILTER',
            29 => 'CHANGE_STATUS',
        ]
    ],
    'USERS' => [
        'name' => 'CRM_USERS',
        'id' => 40,
        'children' => [
            41 => 'CREATE',
            42 => 'READ',
            43 => 'READ_ALL',
            44 => 'UPDATE',
            45 => 'DELETE',
            46 => 'IMPORT',
            47 => 'EXPORT',
            48 => 'FILTER',
            49 => 'CHANGE_STATUS',
        ]
    ],
    'PERMISSIONS' => [
        'name' => 'CRM_PERMISSIONS',
        'id' => 60,
        'children' => [
            61 => 'CREATE',
            62 => 'READ',
            63 => 'READ_ALL',
            64 => 'UPDATE',
            65 => 'DELETE',
            66 => 'FILTER',
        ]
    ],
]);

!defined('CRM_MENU_ITEMS') && define('CRM_MENU_ITEMS',[
    'Dashboard' => [
        'menu_icon' => 'feather-airplay',
        'children' => [
            'CRM' => ['menu_url' => 'home', 'menu_icon' => 'feather-corner-down-right']
        ]
    ],
    'Roles & Permissions' => [
        'menu_icon' => 'feather-command',
        'children' => [
            'Role' => ['menu_url' => 'crm.role.index', 'menu_icon' => 'feather-corner-down-right'],
            'Create Role' => ['menu_url' => 'crm.role.create', 'menu_icon' => 'feather-corner-down-right'],
            'Permissions' => ['menu_url' => 'crm.permissions.index', 'menu_icon' => 'feather-corner-down-right'],
            'Create Permissions' => ['menu_url' => 'crm.permissions.create', 'menu_icon' => 'feather-corner-down-right']
        ]
        ],
    'Users' => [
        'menu_icon' => 'feather-user-plus',
        'children' => [
            'Users' => ['menu_url' => 'crm.users.index', 'menu_icon' => 'feather-corner-down-right'],
            'Create Users' => ['menu_url' => 'crm.users.create', 'menu_icon' => 'feather-corner-down-right']
        ]
    ]
]);





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
    return DB::table('crm_menu')->get();
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
      if($userRoleId == 1){
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

