<?php

use App\Models\Company;
use App\Models\CRM\CRMMenu;
use App\Models\User;
use App\Models\CRM\CRMRole;
use App\Models\CRM\CRMRolePermissions;
use App\Models\CRM\CRMSettings;
use Illuminate\Support\Facades\DB;
use App\Models\Media;
use App\Models\Plans;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;






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



function isFeatureEnable($module)
{
    if (!Auth::user() || Auth::user()->company_id == null) {
        return true;
    }


    $planFeatuers = Company::with(['plans.planFeatures' => fn($q) => $q->where('module_name', strtolower($module))])
        ->where('id', Auth::user()->company_id)
        ->first()->toArray();

    if (@$planFeatuers['plans']['plan_features'][0]['value'] == 0) {
        return false;
    }

    return true;
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

    // Determine the cache key based on user and panel type
    $panelType = panelAccess() === PANEL_TYPES['SUPER_PANEL'] && $user->is_tenant 
        ? PANEL_TYPES['SUPER_PANEL'] 
        : PANEL_TYPES['COMPANY_PANEL'];

    $cacheKey = "crm_menus_{$user->id}_{$panelType}";

    // Try to retrieve from cache first
    return Cache::remember($cacheKey, now()->addHours(CACHE_DEFAULT_HOURS), function () use ($panelType) {
        return CRMMenu::where('panel_type', $panelType)->distinct()->get();
    });
}


function hasPermission($permissionKey)
{
    $user = Auth::user();
    
    // Quick returns for admin users
    if ($user->is_tenant && $user->role_id === null) {
        return true;
    }

    if (!$user->is_tenant && $user->role_id === null && $user->company_id !== null) {
        return true;
    }

    // Create a unique cache key
    $cacheKey = "permission_{$user->id}_{$permissionKey}";

    return Cache::remember($cacheKey, now()->addHours(CACHE_DEFAULT_HOURS), function () use ($user, $permissionKey) {
        // Existing permission check logic remains the same
        $parts = explode('.', $permissionKey);

        if (count($parts) != 2) {
            \Log::warning("Invalid permission key format: {$permissionKey}", [$parts]);
            return false;
        }

        list($module, $permissionType) = $parts;

        if (!isset(CRMPERMISSIONS[$module])) {
            \Log::warning("Module not found in CRMPERMISSIONS: {$module}");
            return false;
        }

        $modulePermissionId = CRMPERMISSIONS[$module]['id'];
        $childPermissions = CRMPERMISSIONS[$module]['children'];
        $childPermissionId = null;

        foreach ($childPermissions as $id => $name) {
            if (strtoupper($name) === strtoupper($permissionType)) {
                $childPermissionId = $id;
                break;
            }
        }

        if ($childPermissionId === null) {
            \Log::warning("Permission type not found for module {$module}: {$permissionType}");
            return false;
        }

        $specificPermissionExists = CRMRolePermissions::where('role_id', $user->role_id)
            ->where('permission_id', $childPermissionId)
            ->exists();

        if ($specificPermissionExists) {
            return true;
        }

        return CRMRolePermissions::where('role_id', $user->role_id)
            ->where('permission_id', $modulePermissionId)
            ->exists();
    });
}


/**
 * Check if the current user has specific permission
 *
 * @param string $permissionKey The permission key in format 'MODULE.PERMISSION'
 * @return bool
 */
function hasMenuPermission($permissionId = null)
{
    $user = Auth::user();

    // Quick returns for admin users
    if ($user->is_tenant && $user->role_id === null) {
        return true;
    }

    if ($permissionId === null) {
        return false;
    }

    if ($user->role_id === null && $user->company_id === null) {
        return false;
    }

    // Create a unique cache key
    $cacheKey = "menu_permission_{$user->id}_{$permissionId}";

    return Cache::remember($cacheKey, now()->addHours(CACHE_DEFAULT_HOURS), function () use ($user, $permissionId) {
        // Company admin case
        if ($user->role_id === null && $user->company_id !== null) {
            return DB::table('crm_role_permissions')
                ->where('permission_id', $permissionId)
                ->where('role_id', null)
                ->where('company_id', $user->company_id)
                ->exists();
        }

        // Regular role-based permission check
        return DB::table('crm_role_permissions')
            ->where('permission_id', $permissionId)
            ->where('role_id', $user->role_id)
            ->exists();
    });
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




if (!function_exists('getSettingValue')) {

    /**
     * Retrieve the value of a setting by key.
     *
     * @param string $key The key of the setting.
     * @return mixed|null The value of the setting or null if not found.
     */
    function getSettingValue(string $key, string $isTenantSetting = '0')
    {

        $query = CRMSettings::where('key', $key);

        if ($user = Auth::user()) {
            if ($user->is_tenant) {
                $query->where('is_tenant', '1');
            } elseif (!is_null($user->company_id)) {
                if ($isTenantSetting == '1') {
                    $query->where('is_tenant', 1);
                } else {

                    $query->where('company_id', $user->company_id);
                }
            }
        }

        return $query->value('value'); // Return the value column directly

    }
}


function getLogoPath()
{

    $query = CRMSettings::with('media');
    if ($user = Auth::user()) {
        if ($user->is_tenant) {
            $query->where('is_tenant', 1)->where('name', 'tenant_company_logo');
        } elseif (!is_null($user->company_id)) {
            $query->where('company_id', $user->company_id)->where('name', 'client_company_logo');
        }
        $data = $query->first();
        return $data?->media?->file_path;
    }

    return '/img/logo.png';


}


function countriesList()
{
    return [
        "AF" => "Afghanistan",
        "AL" => "Albania",
        "DZ" => "Algeria",
        "AS" => "American Samoa",
        "AD" => "Andorra",
        "AO" => "Angola",
        "AI" => "Anguilla",
        "AQ" => "Antarctica",
        "AG" => "Antigua and Barbuda",
        "AR" => "Argentina",
        "AM" => "Armenia",
        "AW" => "Aruba",
        "AU" => "Australia",
        "AT" => "Austria",
        "AZ" => "Azerbaijan",
        "BS" => "Bahamas",
        "BH" => "Bahrain",
        "BD" => "Bangladesh",
        "BB" => "Barbados",
        "BY" => "Belarus",
        "BE" => "Belgium",
        "BZ" => "Belize",
        "BJ" => "Benin",
        "BM" => "Bermuda",
        "BT" => "Bhutan",
        "BO" => "Bolivia",
        "BA" => "Bosnia and Herzegovina",
        "BW" => "Botswana",
        "BV" => "Bouvet Island",
        "BR" => "Brazil",
        "BQ" => "British Antarctic Territory",
        "IO" => "British Indian Ocean Territory",
        "VG" => "British Virgin Islands",
        "BN" => "Brunei",
        "BG" => "Bulgaria",
        "BF" => "Burkina Faso",
        "BI" => "Burundi",
        "KH" => "Cambodia",
        "CM" => "Cameroon",
        "CA" => "Canada",
        "CT" => "Canton and Enderbury Islands",
        "CV" => "Cape Verde",
        "KY" => "Cayman Islands",
        "CF" => "Central African Republic",
        "TD" => "Chad",
        "CL" => "Chile",
        "CN" => "China",
        "CX" => "Christmas Island",
        "CC" => "Cocos [Keeling] Islands",
        "CO" => "Colombia",
        "KM" => "Comoros",
        "CG" => "Congo - Brazzaville",
        "CD" => "Congo - Kinshasa",
        "CK" => "Cook Islands",
        "CR" => "Costa Rica",
        "HR" => "Croatia",
        "CU" => "Cuba",
        "CY" => "Cyprus",
        "CZ" => "Czech Republic",
        "CI" => "Côte d’Ivoire",
        "DK" => "Denmark",
        "DJ" => "Djibouti",
        "DM" => "Dominica",
        "DO" => "Dominican Republic",
        "NQ" => "Dronning Maud Land",
        "DD" => "East Germany",
        "EC" => "Ecuador",
        "EG" => "Egypt",
        "SV" => "El Salvador",
        "GQ" => "Equatorial Guinea",
        "ER" => "Eritrea",
        "EE" => "Estonia",
        "ET" => "Ethiopia",
        "FK" => "Falkland Islands",
        "FO" => "Faroe Islands",
        "FJ" => "Fiji",
        "FI" => "Finland",
        "FR" => "France",
        "GF" => "French Guiana",
        "PF" => "French Polynesia",
        "TF" => "French Southern Territories",
        "FQ" => "French Southern and Antarctic Territories",
        "GA" => "Gabon",
        "GM" => "Gambia",
        "GE" => "Georgia",
        "DE" => "Germany",
        "GH" => "Ghana",
        "GI" => "Gibraltar",
        "GR" => "Greece",
        "GL" => "Greenland",
        "GD" => "Grenada",
        "GP" => "Guadeloupe",
        "GU" => "Guam",
        "GT" => "Guatemala",
        "GG" => "Guernsey",
        "GN" => "Guinea",
        "GW" => "Guinea-Bissau",
        "GY" => "Guyana",
        "HT" => "Haiti",
        "HM" => "Heard Island and McDonald Islands",
        "HN" => "Honduras",
        "HK" => "Hong Kong SAR China",
        "HU" => "Hungary",
        "IS" => "Iceland",
        "IN" => "India",
        "ID" => "Indonesia",
        "IR" => "Iran",
        "IQ" => "Iraq",
        "IE" => "Ireland",
        "IM" => "Isle of Man",
        "IL" => "Israel",
        "IT" => "Italy",
        "JM" => "Jamaica",
        "JP" => "Japan",
        "JE" => "Jersey",
        "JT" => "Johnston Island",
        "JO" => "Jordan",
        "KZ" => "Kazakhstan",
        "KE" => "Kenya",
        "KI" => "Kiribati",
        "KW" => "Kuwait",
        "KG" => "Kyrgyzstan",
        "LA" => "Laos",
        "LV" => "Latvia",
        "LB" => "Lebanon",
        "LS" => "Lesotho",
        "LR" => "Liberia",
        "LY" => "Libya",
        "LI" => "Liechtenstein",
        "LT" => "Lithuania",
        "LU" => "Luxembourg",
        "MO" => "Macau SAR China",
        "MK" => "Macedonia",
        "MG" => "Madagascar",
        "MW" => "Malawi",
        "MY" => "Malaysia",
        "MV" => "Maldives",
        "ML" => "Mali",
        "MT" => "Malta",
        "MH" => "Marshall Islands",
        "MQ" => "Martinique",
        "MR" => "Mauritania",
        "MU" => "Mauritius",
        "YT" => "Mayotte",
        "FX" => "Metropolitan France",
        "MX" => "Mexico",
        "FM" => "Micronesia",
        "MI" => "Midway Islands",
        "MD" => "Moldova",
        "MC" => "Monaco",
        "MN" => "Mongolia",
        "ME" => "Montenegro",
        "MS" => "Montserrat",
        "MA" => "Morocco",
        "MZ" => "Mozambique",
        "MM" => "Myanmar [Burma]",
        "NA" => "Namibia",
        "NR" => "Nauru",
        "NP" => "Nepal",
        "NL" => "Netherlands",
        "AN" => "Netherlands Antilles",
        "NT" => "Neutral Zone",
        "NC" => "New Caledonia",
        "NZ" => "New Zealand",
        "NI" => "Nicaragua",
        "NE" => "Niger",
        "NG" => "Nigeria",
        "NU" => "Niue",
        "NF" => "Norfolk Island",
        "KP" => "North Korea",
        "VD" => "North Vietnam",
        "MP" => "Northern Mariana Islands",
        "NO" => "Norway",
        "OM" => "Oman",
        "PC" => "Pacific Islands Trust Territory",
        "PK" => "Pakistan",
        "PW" => "Palau",
        "PS" => "Palestinian Territories",
        "PA" => "Panama",
        "PZ" => "Panama Canal Zone",
        "PG" => "Papua New Guinea",
        "PY" => "Paraguay",
        "YD" => "People's Democratic Republic of Yemen",
        "PE" => "Peru",
        "PH" => "Philippines",
        "PN" => "Pitcairn Islands",
        "PL" => "Poland",
        "PT" => "Portugal",
        "PR" => "Puerto Rico",
        "QA" => "Qatar",
        "RO" => "Romania",
        "RU" => "Russia",
        "RW" => "Rwanda",
        "RE" => "Réunion",
        "BL" => "Saint Barthélemy",
        "SH" => "Saint Helena",
        "KN" => "Saint Kitts and Nevis",
        "LC" => "Saint Lucia",
        "MF" => "Saint Martin",
        "PM" => "Saint Pierre and Miquelon",
        "VC" => "Saint Vincent and the Grenadines",
        "WS" => "Samoa",
        "SM" => "San Marino",
        "SA" => "Saudi Arabia",
        "SN" => "Senegal",
        "RS" => "Serbia",
        "CS" => "Serbia and Montenegro",
        "SC" => "Seychelles",
        "SL" => "Sierra Leone",
        "SG" => "Singapore",
        "SK" => "Slovakia",
        "SI" => "Slovenia",
        "SB" => "Solomon Islands",
        "SO" => "Somalia",
        "ZA" => "South Africa",
        "GS" => "South Georgia and the South Sandwich Islands",
        "KR" => "South Korea",
        "ES" => "Spain",
        "LK" => "Sri Lanka",
        "SD" => "Sudan",
        "SR" => "Suriname",
        "SJ" => "Svalbard and Jan Mayen",
        "SZ" => "Swaziland",
        "SE" => "Sweden",
        "CH" => "Switzerland",
        "SY" => "Syria",
        "ST" => "São Tomé and Príncipe",
        "TW" => "Taiwan",
        "TJ" => "Tajikistan",
        "TZ" => "Tanzania",
        "TH" => "Thailand",
        "TL" => "Timor-Leste",
        "TG" => "Togo",
        "TK" => "Tokelau",
        "TO" => "Tonga",
        "TT" => "Trinidad and Tobago",
        "TN" => "Tunisia",
        "TR" => "Turkey",
        "TM" => "Turkmenistan",
        "TC" => "Turks and Caicos Islands",
        "TV" => "Tuvalu",
        "UM" => "U.S. Minor Outlying Islands",
        "PU" => "U.S. Miscellaneous Pacific Islands",
        "VI" => "U.S. Virgin Islands",
        "UG" => "Uganda",
        "UA" => "Ukraine",
        "SU" => "Union of Soviet Socialist Republics",
        "AE" => "United Arab Emirates",
        "GB" => "United Kingdom",
        "US" => "United States",
        "ZZ" => "Unknown or Invalid Region",
        "UY" => "Uruguay",
        "UZ" => "Uzbekistan",
        "VU" => "Vanuatu",
        "VA" => "Vatican City",
        "VE" => "Venezuela",
        "VN" => "Vietnam",
        "WK" => "Wake Island",
        "WF" => "Wallis and Futuna",
        "EH" => "Western Sahara",
        "YE" => "Yemen",
        "ZM" => "Zambia",
        "ZW" => "Zimbabwe",
        "AX" => "Åland Islands",
    ];
}