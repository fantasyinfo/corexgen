<?php


namespace App\Enums;

enum RoleStatus: string
{
    case SuperAdmin = 'super_admin';
    case Admin = 'admin';
    case Employee = 'employee';
}
