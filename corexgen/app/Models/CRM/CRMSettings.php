<?php

namespace App\Models\CRM;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CRMSettings extends Model
{
    use HasFactory;

    const table = 'crm_settings';

    protected $fillable = [
        'key',
        'value',
        'is_media_setting',
        'media_id',
        'buyer_id',
        'is_super_user',
        'updated_by',
        'created_by',
    ];

    protected $table = self::table;
}
