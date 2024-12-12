<?php

namespace App\Helpers;

/**
 * PermissionsHelper Class
 * All App Permissions Defined here
 */
class PermissionsHelper
{

    public static $plansPermissionsKeys = [
        'DASHBOARD' => 'DASHBOARD',
        'ROLE' => 'ROLE',
        'USERS' => 'USERS',
        'PERMISSIONS' => 'PERMISSIONS',
        'SETTINGS' => 'SETTINGS',
        'MODULES' => 'MODULES',
        'APPUPDATES' => 'APPUPDATES',
        'COMPANIES' => 'COMPANIES',
        'PLANS' => 'PLANS',
        'PAYMENTSTRANSACTIONS' => 'PAYMENTSTRANSACTIONS',
        'PLANUPGRADE' => 'PLANUPGRADE',
        'PAYMENTGATEWAYS' => 'PAYMENTGATEWAYS',
        'SUBSCRIPTIONS' => 'SUBSCRIPTIONS'
    ];





    /**
     * Permission of PERMISSIONS_IDS for menu
     * @var array
     */

    // these keys must match with the plans featuers otherwise permission not work!!

    public static $PERMISSIONS_IDS = [];

    public static function initializePermissions()
    {
        self::$PERMISSIONS_IDS = [
            self::$plansPermissionsKeys['DASHBOARD'] => [501 => 'READ', 502 => 'READ_ALL'],

            self::$plansPermissionsKeys['ROLE'] => [551 => 'CREATE', 552 => 'READ', 553 => 'READ_ALL', 554 => 'UPDATE', 555 => 'DELETE', 556 => 'IMPORT', 557 => 'EXPORT', 558 => 'FILTER', 559 => 'CHANGE_STATUS', 560 => 'BULK_DELETE'],

            self::$plansPermissionsKeys['USERS'] => [601 => 'CREATE', 602 => 'READ', 603 => 'READ_ALL', 604 => 'UPDATE', 605 => 'DELETE', 606 => 'IMPORT', 607 => 'EXPORT', 608 => 'FILTER', 609 => 'CHANGE_STATUS', 610 => 'BULK_DELETE', 611 => 'CHANGE_PASSWORD', 612 => 'VIEW'],

            self::$plansPermissionsKeys['PERMISSIONS'] => [651 => 'CREATE', 652 => 'READ', 653 => 'READ_ALL', 654 => 'UPDATE', 655 => 'DELETE'],

            self::$plansPermissionsKeys['SETTINGS'] => [701 => 'READ', 702 => 'UPDATE'],

            self::$plansPermissionsKeys['MODULES'] => [751 => 'CREATE', 752 => 'READ', 753 => 'READ_ALL', 754 => 'UPDATE', 755 => 'DELETE', 756 => 'IMPORT', 757 => 'EXPORT', 758 => 'FILTER', 759 => 'CHANGE_STATUS'],

            self::$plansPermissionsKeys['APPUPDATES'] => [801 => 'CREATE', 802 => 'READ', 803 => 'READ_ALL', 804 => 'UPDATE', 805 => 'DELETE', 806 => 'IMPORT', 807 => 'EXPORT', 808 => 'FILTER', 809 => 'CHANGE_STATUS'],

            self::$plansPermissionsKeys['COMPANIES'] => [851 => 'CREATE', 852 => 'READ', 853 => 'READ_ALL', 854 => 'UPDATE', 855 => 'DELETE', 856 => 'IMPORT', 857 => 'EXPORT', 858 => 'FILTER', 859 => 'CHANGE_STATUS', 860 => 'VIEW', 861 => 'LOGIN_AS', 862 => 'BULK_DELETE', 863 => 'CHANGE_PASSWORD'],

            self::$plansPermissionsKeys['PLANS'] => [901 => 'CREATE', 902 => 'READ', 903 => 'READ_ALL', 904 => 'UPDATE', 905 => 'DELETE', 906 => 'CHANGE_STATUS'],


            self::$plansPermissionsKeys['PAYMENTSTRANSACTIONS'] => [952 => 'READ', 953 => 'READ_ALL'],

            self::$plansPermissionsKeys['PLANUPGRADE'] => [1000 => 'READ', 1001 => 'READ_ALL', 1002 => 'UPGRADE'],

            self::$plansPermissionsKeys['PAYMENTGATEWAYS'] => [1051 => 'READ', 1052 => 'READ_ALL', 1053 => 'UPDATE', 1055 => 'FILTER', 1056 => 'CHANGE_STATUS'],

            self::$plansPermissionsKeys['SUBSCRIPTIONS'] => [1101 => 'READ', 1102 => 'READ_ALL'],
        ];
    }


    /**
     * Summary of getPermissionsArray getting the permission array values
     * @param mixed $type
     * @return array
     */
    public static function getPermissionsArray($type)
    {
        $permissions = self::$PERMISSIONS_IDS[$type]; // Original array
        $result = [];

        foreach ($permissions as $permission) {
            $result[$permission] = [
                'KEY' => $permission,
                'VALUE' => true,
            ];
        }

        return $result;
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
