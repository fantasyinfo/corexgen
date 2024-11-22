<?php

namespace App\Models\CRM;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CRMMenu extends Model
{
    use HasFactory;
    const table = 'crm_menu';

    protected $fillable = [
        'menu_name', 
        'menu_url',
        'parent_menu',
        'parent_menu_id',
        'menu_icon',
        'buyer_id',
        'permission_id',
        'is_super_user'
    ];

    protected $table = self::table;
}
