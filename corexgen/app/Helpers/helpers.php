<?php

use App\Models\CRM\CRMMenu;
use App\Models\User;
use App\Models\CRM\CRMRole;
use App\Models\CRM\CRMRolePermissions;
use Illuminate\Support\Facades\DB;
use App\Models\Media;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;







/**
 * Method createMedia
 *
 * @param UploadedFile $file [for creating the media files]
 *
 * @return void
 */
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
        'buyer_id' => auth()->user()->buyer_id,
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
    $user = Auth::user();

    $menus = [];

    if ($user->is_tenant && session('panelAccess') === PANEL_TYPES['SUPER_PANEL']) {

        $menus = CRMMenu::where('panel_type', PANEL_TYPES['SUPER_PANEL'])->distinct()->get();
        return $menus;
    }

    // echo PANEL_TYPES['COMPANY_PANEL'];
    $menus = CRMMenu::where('panel_type', PANEL_TYPES['COMPANY_PANEL'])->distinct()->get();

    // \dd(session('panelAccess'));
    return $menus;

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
    $user = Auth::user();


    $userRoleId = $user->role_id;


    // return true for superadmin where role id null
    if ($user->is_tenant && $userRoleId === null) {
        return true;
    }

    // return true for comoany admins where role is null

    if (!$user->is_tenant && $userRoleId == null && $user->company_id != null) {
        return true;
    }

    // Split the permission key into module and permission type
    $parts = explode('.', $permissionKey);


    // dd($parts);
    // Validate the permission key format
    if (count($parts) != 2) {
        \Log::warning("Invalid permission key format: {  $permissionKey} ",[$parts]);
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
    if ($childPermissionId == null) {
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
    // Get the current user's role ID
    $user = Auth::user();


    $userRoleId = $user->role_id;


    // return true for superadmin or role id 1
    if ($user->is_tenant && $userRoleId === null) {
        return true;
    }

    if ($permissionId == null)
        return false;


    if ($userRoleId == null && $user->company_id == null) {
       // \Log::error('Role & Compnay Id Not Found.', [$user, $userRoleId, $user->company_id]);
        return false;
    } else if ($userRoleId == null && $user->company_id != null) {
        // its a company admin user
        $permissionExists = DB::table('crm_role_permissions')
            ->where('permission_id', $permissionId)
            ->where('role_id', null)
            ->where('company_id', $user->company_id)
            ->exists();
        //\Log::info('Compnay Id Found but not role id.', [$user, $userRoleId, $user->comapany_id]);
        return true;
    }

    $permissionExists = DB::table('crm_role_permissions')
        ->where('permission_id', $permissionId)
        ->where('role_id', $userRoleId)
        ->exists();

    return $permissionExists;
}



function panelAccess()
{
    $user = Auth::user();

    if (isset($user->is_tenant) && $user->is_tenant) {
        return PANEL_TYPES['SUPER_PANEL'];
    }
    return PANEL_TYPES['COMPANY_PANEL'];
}

function getPanelUrl($string)
{
    return strtolower(str_replace('_', '-', $string));
}

function getPanelRoutes($route)
{
    return getPanelUrl(panelAccess()) . '.' . $route;
}


function getComponentsDirFilePath($filename)
{
    return 'layout.components.' . $filename;
}