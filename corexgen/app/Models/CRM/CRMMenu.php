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
        'permission_id',
        'panel_type',
        'is_default',
        'feature_type'
    ];

    protected $table = self::table;


    protected static function boot(){
        parent::boot();

        static::creating(function($crmmenu){
            $crmmenu->panel_type = $crmmenu->panel_type ?? PANEL_TYPES['COMPANY_PANEL'];
        });

    }
}
