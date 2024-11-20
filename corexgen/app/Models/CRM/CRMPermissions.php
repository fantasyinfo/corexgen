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
        'buyer_id'
    ];

    protected $table = self::table;


}
