<?php

namespace App\Models\CRM;

use App\Models\Media;
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
        'is_tenant',
        'company_id',
        'input_type',
        'value_type',
        'name',
        'placeholder',
        'type',
        'updated_by',
        'created_by',
    ];

    protected $table = self::table;


    public function media(){
        return $this->belongsTo(Media::class,'media_id');
    }
}
