<?php

use App\Models\User;
use App\Models\CRM\CRMRole;
use Illuminate\Support\Facades\DB;


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
            26 => 'BULK_UPLOAD',
            27 => 'EXPORT'
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
            46 => 'BULK_UPLOAD',
            47 => 'EXPORT'
        ]
    ],
]);
