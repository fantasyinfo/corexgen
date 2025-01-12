<?php

namespace App\Models\CRM;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Permissions table model handle all filters, observers, evenets, relatioships
 */
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


    /**
     * Model boot method to set default values
     */
    protected static function boot()
    {
        parent::boot();

    }

}
