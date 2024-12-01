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

     // these keys must match with the plans featuers otherwise permission not work!!
    public static $PERMISSIONS_IDS = [
        'DASHBOARD' => [501 => 'READ', 502 => 'READ_ALL'],
        'ROLE' => [551 => 'CREATE', 552 => 'READ', 553 => 'READ_ALL', 554 => 'UPDATE', 555 => 'DELETE', 556 => 'IMPORT', 557 => 'EXPORT', 558 => 'FILTER', 559 => 'CHANGE_STATUS',],
        'USERS' => [601 => 'CREATE', 602 => 'READ', 603 => 'READ_ALL', 604 => 'UPDATE', 605 => 'DELETE', 606 => 'IMPORT', 607 => 'EXPORT', 608 => 'FILTER', 609 => 'CHANGE_STATUS', 610 => 'BULK_DELETE'],
        'PERMISSIONS' => [651 => 'CREATE', 652 => 'READ', 653 => 'READ_ALL', 654 => 'UPDATE', 655 => 'DELETE'],
        'SETTINGS' => [701 => 'READ', 702 => 'UPDATE',],
        'MODULES' => [751 => 'CREATE', 752 => 'READ', 753 => 'READ_ALL', 754 => 'UPDATE', 755 => 'DELETE', 756 => 'IMPORT', 757 => 'EXPORT', 758 => 'FILTER', 759 => 'CHANGE_STATUS',],
        'APPUPDATES' => [801 => 'CREATE', 802 => 'READ', 803 => 'READ_ALL', 804 => 'UPDATE', 805 => 'DELETE', 806 => 'IMPORT', 807 => 'EXPORT', 808 => 'FILTER', 809 => 'CHANGE_STATUS',],
        'COMPANIES' => [851 => 'CREATE', 852 => 'READ', 853 => 'READ_ALL', 854 => 'UPDATE', 855 => 'DELETE', 856 => 'IMPORT', 857 => 'EXPORT', 858 => 'FILTER', 859 => 'CHANGE_STATUS', 860 => 'VIEW'],
        'PLANS' => [901 => 'CREATE', 902 => 'READ', 903 => 'READ_ALL', 904 => 'UPDATE', 905 => 'DELETE', 906 => 'CHANGE_STATUS',],
       


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

    public static function getParentPermissionId($key)
    {
        $parentPermissionId = array_combine(range(1, 500), range(1, 500));
        return $parentPermissionId[$key];

    }

    // Method to find the key for a given value in a specific category
    public static function findPermissionKey($category, $permission)
    {
        if (isset(self::$PERMISSIONS_IDS[$category])) {
            return array_search($permission, self::$PERMISSIONS_IDS[$category], true);
        }
        return null; // Return null if category or permission not found
    }
}
