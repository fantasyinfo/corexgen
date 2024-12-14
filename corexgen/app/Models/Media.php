<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Media extends Model
{
    use HasFactory;

    const table = 'media';

    protected $table = self::table;

    protected $fillable = [
        'file_name',
        'file_path',
        'file_type',
        'file_extension',
        'size',
        'company_id',
        'is_tenant',
        'updated_by',
        'created_by',
        'status'
    ];
}
