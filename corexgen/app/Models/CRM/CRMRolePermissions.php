<?php

namespace App\Models\CRM;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


/**
 * Role Permissions table model handle all filters, observers, evenets, relatioships 
 */
class CRMRolePermissions extends Model
{
    use HasFactory;

    const table = 'crm_role_permissions';

    protected $fillable = [
        'role_id', 
        'permission_id',
        'company_id'
    ];

    protected $table = self::table;

}
