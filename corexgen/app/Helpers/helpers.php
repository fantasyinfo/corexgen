<?php

use App\Models\Company;
use App\Models\CRM\CRMMenu;
use App\Models\Tenant;
use App\Models\User;
use App\Models\CRM\CRMRole;
use App\Models\CRM\CRMRolePermissions;
use App\Models\CRM\CRMSettings;
use Illuminate\Support\Facades\DB;
use App\Models\Media;
use App\Models\Plans;
use Carbon\Carbon;
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





// Cache storage using static variables
$GLOBALS['feature_cache'] = null;
$GLOBALS['default_user_cache'] = null;

/**
 * is feature enable ck
 */
function isFeatureEnabled($module)
{
 
    // Check if user is not logged in or has no company_id
    if (isDefaultUser() === true) {
        return true;
    }

    // Load all features once
    $features = getAllFeatures();

    // Convert module name to lowercase for comparison
    $moduleName = strtolower($module);

    // Check if feature exists and is enabled
    return !isset($features[$moduleName]) || $features[$moduleName] !== 0;
}

/**
 * check if is default user
 */
function isDefaultUser()
{
    if ($GLOBALS['default_user_cache'] === null) {
        $GLOBALS['default_user_cache'] = !Auth::user() || Auth::user()->company_id === null;
    }
    return $GLOBALS['default_user_cache'];
}
/**
 * get all features
 */
function getAllFeatures()
{
    if ($GLOBALS['feature_cache'] === null) {
        $company = Company::with(['plans.planFeatures'])
            ->where('id', Auth::user()->company_id)
            ->first();

        $GLOBALS['feature_cache'] = [];

        if ($company && $company->plans && $company->plans->planFeatures) {
            foreach ($company->plans->planFeatures as $feature) {
                $GLOBALS['feature_cache'][strtolower($feature->module_name)] = $feature->value;
            }
        }
    }

    return $GLOBALS['feature_cache'];
}

// Function to clear cache if needed (e.g., after plan changes)
function clearFeatureCache()
{
    $GLOBALS['feature_cache'] = null;
    $GLOBALS['default_user_cache'] = null;
}

/**
 * replace underscore with spaces
 */

function replaceUnderscoreWithSpace(string $str): string
{
    return str_replace('_', " ", $str);
}







/**
 * get user name
 */
function getUserName($id = null)
{
    if ($id == null) {
        return '';
    } else {
        return User::findOrFail($id)->name;
    }
}



/**
 * filter roles details
 */
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

/**
 * filter role details
 */
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

/**
 * filter users details
 */
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

/**
 * get user role name
 */
function getRoleName($role_id = null)
{

    if ($role_id == null) {
        return '';
    } else {
        return CRMRole::findOrFail($role_id)->role_name;
    }
}
/**
 * filter user details
 */
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
/**
 * debugging pre print r
 */

function prePrintR($arr)
{
    echo '<pre>';
    print_r($arr);
    echo '</pre>';
}

/**
 * get menu items in cache
 */

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

/**
 * check if user has valid permission
 */
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
    $cacheKey = "permission_cache";


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
}


/**
 * Check if the current user has specific permission
 *
 * @param string $permissionKey The permission key in format 'MODULE.PERMISSION'
 * @return bool
 */
function hasMenuPermission($permissionId = null)
{
    // Return true if permissions are not required
    $user = Auth::user();

    // Quick returns for admin users
    if ($user->is_tenant && $user->role_id === null) {
        return true;
    }

    if ($permissionId === null) {
        return false;
    }

    if ($user->role_id == null && $user->company_id == null) {
        return false;
    }

    // Company admin case
    if ($user->role_id === null && $user->company_id !== null) {
        return true;
    }

    // Fetch and cache all permissions for the user's role
    static $permissionsCache = null;

    if ($permissionsCache === null) {
        $permissionsCache = DB::table('crm_role_permissions')
            ->where('role_id', $user->role_id)
            ->pluck('permission_id')
            ->toArray();
    }

    // Check if the requested permission exists in the cached permissions
    return in_array($permissionId, $permissionsCache);
}



/**
 * get panel access
 */
function panelAccess()
{
    $user = Auth::user();

    if (isset($user->is_tenant) && $user->is_tenant) {
        return PANEL_TYPES['SUPER_PANEL'];
    }
    return PANEL_TYPES['COMPANY_PANEL'];
}

/**
 * get panel url
 */
function getPanelUrl($string)
{
    return strtolower(str_replace('_', '-', $string));
}

/**
 * get panel routes with panel access
 */
function getPanelRoutes($route)
{
    return getPanelUrl(panelAccess()) . '.' . $route;
}

/**
 * get component dir path dynamicaly
 */
function getComponentsDirFilePath($filename)
{
    return 'layout.components.' . $filename;
}





const SETTINGS_CACHE_KEY = 'crm_settings';
const SETTINGS_CACHE_TTL = 3600; // 1 hour in seconds

/**
 * Load all settings into cache
 *
 * @return array
 */
function loadAllSettings(): array
{
    $settings = [];

    if ($user = Auth::user()) {
        $query = CRMSettings::query();

        if ($user->is_tenant) {
            $query->where('is_tenant', '1');
        } elseif (!is_null($user->company_id)) {
            $query->where(function ($q) use ($user) {
                $q->where('company_id', $user->company_id)
                    ->orWhere('is_tenant', '1');
            });
        }

        $settings = $query->get(['key', 'value', 'is_tenant'])
            ->groupBy('key')
            ->map(function ($group) {
                // If multiple settings exist, prioritize company-specific over tenant
                return $group->count() > 1
                    ? $group->where('is_tenant', '0')->first()->value ?? $group->first()->value
                    : $group->first()->value;
            })
            ->toArray();
    }

    Cache::put(SETTINGS_CACHE_KEY . '_' . ($user->company_id ?? 'tenant'), $settings, SETTINGS_CACHE_TTL);

    return $settings;
}

/**
 * Retrieve the value of a setting by key using cache
 *
 * @param string $key The key of the setting
 * @param string $isTenantSetting Whether to force tenant setting retrieval
 * @return mixed|null The value of the setting or null if not found
 */
function getSettingValue(string $key, string $isTenantSetting = '0')
{
    $user = Auth::user();
    $cacheKey = SETTINGS_CACHE_KEY . '_' . ($user->company_id ?? 'tenant');

    $settings = Cache::remember($cacheKey, SETTINGS_CACHE_TTL, function () {
        return loadAllSettings();
    });

    return $settings[$key] ?? null;
}

/**
 * Clear settings cache
 *
 * @return void
 */
function clearSettingsCache(): void
{
    $user = Auth::user();
    Cache::forget(SETTINGS_CACHE_KEY . '_' . ($user->company_id ?? 'tenant'));
}

/**
 * get logo path
 */
function getLogoPath()
{
    return Cache::remember('tenant_company_logo', now()->addHours(CACHE_DEFAULT_HOURS), function () {
        if ($user = Auth::user()) {
            $query = CRMSettings::with('media');

            if ($user->is_tenant) {
                $data = $query->where('is_tenant', 1)
                    ->where('name', 'tenant_company_logo')
                    ->first();
            } elseif (!is_null($user->company_id)) {
                $data = $query->where('company_id', $user->company_id)
                    ->where('name', 'client_company_logo')
                    ->first();
            }

            return $data?->media?->file_path ?? '/img/logo.png';
        }

        return '/img/logo.png';
    });
}

/**
 * countries lists
 */
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

/**
 * find php path for cron job
 */
function find_php_paths()
{
    $paths = [];
    $isWindows = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';

    // Common Unix/Linux locations
    $unixLocations = [
        '/usr/bin/php*',
        '/usr/local/bin/php*',
        '/usr/local/php*/bin/php',
        '/usr/local/opt/php*/bin/php',
        '/opt/plesk/php/*/bin/php',
        '/opt/cpanel/ea-php*/root/usr/bin/php',
        '/opt/alt/php*/usr/bin/php',
        '/usr/local/www/php*/bin/php',
        '/usr/pkg/php*/bin/php',
        '/opt/homebrew/bin/php*',
        '/opt/homebrew/opt/php*/bin/php',
        '/opt/remi/php*/root/usr/bin/php',
        '/opt/lampp/bin/php*',
        '/xampp/php/php*',
        '/usr/local/Cellar/php*/*/bin/php',
        '/usr/local/php5/bin/php*',
        '/usr/local/php7/bin/php*',
        '/usr/local/php8/bin/php*',
        '/opt/bitnami/php/bin/php*',
        '/opt/conda/bin/php*'
    ];

    // Common Windows locations
    $windowsLocations = [
        'C:\\xampp\\php\\php.exe',
        'C:\\wamp\\bin\\php\\php*\\php.exe',
        'C:\\wamp64\\bin\\php\\php*\\php.exe',
        'C:\\laragon\\bin\\php\\php*\\php.exe',
        'C:\\Program Files\\PHP\\php.exe',
        'C:\\Program Files (x86)\\PHP\\php.exe',
        'C:\\ProgramData\\chocolatey\\bin\\php.exe',
        'C:\\tools\\php\\php.exe',
        'C:\\php\\php.exe'
    ];

    // Get server software information
    $serverSoftware = isset($_SERVER['SERVER_SOFTWARE']) ? strtolower($_SERVER['SERVER_SOFTWARE']) : '';

    // Function to check if a path is valid and get PHP version
    $validatePath = function ($path) {
        if (!file_exists($path) || !is_file($path)) {
            return false;
        }

        // Get PHP version using the binary
        try {
            $versionCommand = escapeshellcmd($path) . ' -v';
            $versionOutput = shell_exec($versionCommand);

            if (preg_match('/PHP ([0-9]+\.[0-9]+\.[0-9]+)/', $versionOutput, $matches)) {
                return [
                    'path' => $path,
                    'version' => $matches[1],
                    'version_output' => trim($versionOutput),
                    'cli_available' => true,
                    'last_modified' => filemtime($path),
                    'size' => filesize($path),
                    'permissions' => substr(sprintf('%o', fileperms($path)), -4)
                ];
            }
        } catch (Exception $e) {
            // If execution fails, still include the path but mark CLI as unavailable
            return [
                'path' => $path,
                'version' => 'Unknown',
                'version_output' => null,
                'cli_available' => false,
                'last_modified' => filemtime($path),
                'size' => filesize($path),
                'permissions' => substr(sprintf('%o', fileperms($path)), -4)
            ];
        }
        return false;
    };

    // Detect control panel environments
    $controlPanels = [
        'cpanel' => [
            'paths' => ['/usr/local/cpanel/version'],
            'php_paths' => ['/opt/cpanel/ea-php*/root/usr/bin/php']
        ],
        'plesk' => [
            'paths' => ['/usr/local/psa/version'],
            'php_paths' => ['/opt/plesk/php/*/bin/php']
        ],
        'directadmin' => [
            'paths' => ['/usr/local/directadmin/version'],
            'php_paths' => ['/usr/local/php*/bin/php']
        ],
        'cyberpanel' => [
            'paths' => ['/usr/local/CyberCP/version'],
            'php_paths' => ['/usr/local/lsws/lsphp*/bin/php']
        ],
        'virtualmin' => [
            'paths' => ['/etc/virtualmin/version'],
            'php_paths' => ['/usr/bin/php*', '/usr/local/bin/php*']
        ]
    ];

    $detectedPanel = null;
    foreach ($controlPanels as $panel => $info) {
        foreach ($info['paths'] as $versionPath) {
            if (file_exists($versionPath)) {
                $detectedPanel = $panel;
                $unixLocations = array_merge($unixLocations, $info['php_paths']);
                break 2;
            }
        }
    }

    // Search for PHP binaries
    if ($isWindows) {
        foreach ($windowsLocations as $location) {
            $windowsPaths = glob($location);
            foreach ($windowsPaths as $path) {
                if ($result = $validatePath($path)) {
                    $paths[] = $result;
                }
            }
        }
    } else {
        foreach ($unixLocations as $location) {
            $unixPaths = glob($location);
            foreach ($unixPaths as $path) {
                if ($result = $validatePath($path)) {
                    $paths[] = $result;
                }
            }
        }
    }

    // Check PATH environment
    $pathDirs = explode(PATH_SEPARATOR, getenv('PATH'));
    foreach ($pathDirs as $dir) {
        $phpPath = $isWindows ? $dir . '\\php.exe' : $dir . '/php';
        if ($result = $validatePath($phpPath)) {
            $paths[] = $result;
        }
    }

    // Additional environment checks
    $paths = array_filter(array_unique($paths, SORT_REGULAR));

    // Sort paths by version number (descending)
    usort($paths, function ($a, $b) {
        return version_compare($b['version'], $a['version']);
    });

    // Add environment information
    $result = [
        'php_paths' => $paths,
        'environment' => [
            'os' => PHP_OS,
            'os_family' => PHP_OS_FAMILY,
            'server_software' => $serverSoftware,
            'control_panel' => $detectedPanel,
            'is_windows' => $isWindows,
            'current_php_version' => PHP_VERSION,
            'scan_time' => date('Y-m-d H:i:s'),
            'path_environment' => getenv('PATH'),
        ]
    ];

    // Check for restricted environments
    $result['environment']['restrictions'] = [
        'safe_mode' => ini_get('safe_mode'),
        'disable_functions' => ini_get('disable_functions'),
        'open_basedir' => ini_get('open_basedir'),
        'can_execute_shell' => function_exists('shell_exec') && !in_array('shell_exec', explode(',', ini_get('disable_functions'))),
    ];

    return $result;
}

/**
 * Helper function to get the best available PHP path
 * 
 * @param array $paths Result from find_php_paths()
 * @param string $minVersion Minimum required PHP version
 * @return string|null Best matching PHP path or null if none found
 */
function get_best_php_path($paths, $minVersion = '8.1')
{
    if (empty($paths['php_paths'])) {
        return null;
    }

    foreach ($paths['php_paths'] as $path) {
        if (version_compare($path['version'], $minVersion, '>=') && $path['cli_available']) {
            return $path['path'];
        }
    }

    return $paths['php_paths'][0]['path']; // Return the first available path if no version matches
}

/**
 * Helper function to format PHP paths for display
 * 
 * @param array $paths Result from find_php_paths()
 * @return array Formatted paths for display
 */
function format_php_paths_for_display($paths)
{
    $formatted = [];

    foreach ($paths['php_paths'] as $path) {
        $formatted[] = [
            'path' => $path['path'],
            'version' => $path['version'],
            'display_name' => sprintf(
                'PHP %s (%s)',
                $path['version'],
                basename(dirname($path['path']))
            ),
            'is_cli_available' => $path['cli_available'],
            'last_modified' => date('Y-m-d H:i:s', $path['last_modified']),
            'size_formatted' => number_format($path['size'] / 1024, 2) . ' KB',
            'permissions' => $path['permissions']
        ];
    }

    return $formatted;
}


/**
 * get active plan with features
 */
function getActivePlanWithFeatuers($companyId)
{
    if ($companyId == null)
        return;

    return Company::with([
        'plans.planFeatures' // Load plans and their planFeatures
    ])->where('id', $companyId) // Use company_id from Auth
        ->first();
}

/**
 * get company name
 */
function getCompanyName($companyid = null)
{
    if (Auth::user()->is_tenant) {
        return Tenant::where('id', Auth::user()->tenant_id)->value('name');
    }

    if (is_null($companyid)) {
        return Company::where('id', Auth::user()->company_id)->value('name');
    }

    return Company::where('id', $companyid)->value('name');
}

/**
 * get team mates users lists
 */
function getTeamMates()
{
    if (Auth::user()->is_tenant) {
        return User::where('is_tenant', '1')->get();
    }

    return User::where('is_tenant', '0')
        ->where('company_id', Auth::user()->company_id)->get();

}

/**
 * formate date time as per user selected timezone
 */
if (!function_exists('formatDateTime')) {
    function formatDateTime($date, $timezone = null, $format = null)
    {
        if (Auth::check() && Auth::user()->is_tenant) {
            $timezone = getSettingValue('Panel Time Zone') ?: config('app.timezone');
            $format = getSettingValue('Panel Date Format') ?: 'd M Y';
        } else {
            $timezone = getSettingValue('Time Zone') ?: config('app.timezone');
            $format = getSettingValue('Date Format') ?: 'd M Y';
        }


        return $date ? \Carbon\Carbon::parse($date)->timezone($timezone)->format($format) : null;
    }
}

/**
 * truncate the file name with ...
 */
function truncateFileName($filename)
{
    // Get file extension
    $extension = pathinfo($filename, PATHINFO_EXTENSION);
    $nameWithoutExt = pathinfo($filename, PATHINFO_FILENAME);

    // If filename is too short, return as is
    if (strlen($nameWithoutExt) <= 6) {
        return $filename;
    }

    // Get first 3 and last 3 chars of the name without extension
    $first = substr($nameWithoutExt, 0, 3);
    $last = substr($nameWithoutExt, -3);

    return $first . '...' . $last . '.' . $extension;
}

/**
 * calculate the time diffrence
 */
function calculateTimeDifference($start_date, $end_date)
{
    // Convert the dates to Carbon instances
    $start = Carbon::parse($start_date);
    $end = Carbon::parse($end_date);

    // Calculate the difference in total minutes
    $totalMinutes = $end->diffInMinutes($start);

    // Convert minutes to hours and minutes
    $hours = intdiv($totalMinutes, 60);
    $minutes = $totalMinutes % 60;

    return [
        'hours' => $hours,
        'minutes' => $minutes,
        'duration' => $totalMinutes
    ];
}
/**
 * convert minutes to hours and min
 */
function convertMinutesToHoursAndMinutes($totalMinutes)
{
    // Convert minutes to hours and remaining minutes
    $hours = intdiv($totalMinutes, 60);
    $minutes = $totalMinutes % 60;

    // Format as "X hours and Y minutes"
    return sprintf("%d hours and %d minutes", $hours, $minutes);
}

/**
 * calculate cost per minute per hour rate basic
 */
function calculateCostFromMinutes($totalMinutes, $ratePerHour)
{
    // Convert minutes to hours and remaining minutes
    $hours = intdiv($totalMinutes, 60);
    $minutes = $totalMinutes % 60;

    // Calculate cost
    $totalCost = ($totalMinutes / 60) * $ratePerHour;

    // Format cost with breakdown
    $breakdown = sprintf("%d hours and %d minutes", $hours, $minutes);
    $formattedCost = number_format($totalCost, 2);

    return $totalCost;
}



/**
 * Check module type
 */
function getModule(int $tenant_id = 1)
{

    $cacheKey = "tenant_mode_1";

    // Use cache to retrieve or store the tenant's mode
    return Cache::remember($cacheKey, now()->addMinutes(30), function () use ($tenant_id) {
        // Retrieve the tenant
        $tenant = Tenant::find($tenant_id);
        // Return the tenant's mode or null if the tenant doesn't exist
        return $tenant ? $tenant->mode : null;
    });
}




