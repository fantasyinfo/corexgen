<?php

namespace App\Helpers;

/**
 * PermissionsHelper Class
 * All App Permissions Defined here
 */
class PermissionsHelper
{
    /**
     * Permission of PERMISSIONS_IDS for menu
     * @var array
     */
    public static $PERMISSIONS_IDS = [
        'DASHBOARD' => [501 => 'READ', 502 => 'READ_ALL'],
        'ROLE' => [551 => 'CREATE', 552 => 'READ', 553 => 'READ_ALL', 554 => 'UPDATE', 555 => 'DELETE', 556 => 'IMPORT', 557 => 'EXPORT', 558 => 'FILTER', 559 => 'CHANGE_STATUS',],
        'USERS' => [601 => 'CREATE', 602 => 'READ', 603 => 'READ_ALL', 604 => 'UPDATE', 605 => 'DELETE', 606 => 'IMPORT', 607 => 'EXPORT', 608 => 'FILTER', 609 => 'CHANGE_STATUS',],
        'PERMISSIONS' => [651 => 'CREATE', 652 => 'READ', 653 => 'READ_ALL', 654 => 'UPDATE', 655 => 'DELETE'],
        'SETTINGS' => [701 => 'READ', 702 => 'UPDATE',],
        'MODULES' => [751 => 'CREATE', 752 => 'READ', 753 => 'READ_ALL', 754 => 'UPDATE', 755 => 'DELETE', 756 => 'IMPORT', 757 => 'EXPORT', 758 => 'FILTER', 759 => 'CHANGE_STATUS',],

    ];


    /**
     * Summary of getPermissionsArray getting the permission array values
     * @param mixed $type
     * @return array
     */
    public static function getPermissionsArray($type)
    {
        $permissions = self::$PERMISSIONS_IDS[$type]; // Original array
        return array_fill_keys(array_values($permissions), true);
    }




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
