<?php

namespace App\Models\CRM;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CRMPermissions extends Model
{
    use HasFactory;

    const table = 'crm_permissions';

    protected $fillable = [
        'name', 
        'buyer_id',
        'parent_menu',
        'parent_menu_id',
        'permission_id',
        'is_super_user'
    ];

    protected $table = self::table;


}
