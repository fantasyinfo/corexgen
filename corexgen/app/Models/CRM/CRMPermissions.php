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
        'parent_menu',
        'parent_menu_id',
        'permission_id',
        'company_id'
    ];

    protected $table = self::table;


    protected static function boot(){
        parent::boot();

    }

}
