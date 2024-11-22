<?php

namespace App\Models\CRM;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CRMRolePermissions extends Model
{
    use HasFactory;

    const table = 'crm_role_permissions';

    protected $fillable = [
        'role_id', 
        'buyer_id',
        'permission_id',
        'is_super_user'
    ];

    protected $table = self::table;

}
