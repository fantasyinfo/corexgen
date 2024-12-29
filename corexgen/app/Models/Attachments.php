<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attachments extends Model
{
    use HasFactory;

    const table = 'attachments';

    protected $table = self::table;

    protected $fillable = [
        'attachable_type',
        'attachable_id',
        'file_name',
        'file_path',
        'file_type',
        'file_extension',
        'size',
        'company_id',
        'user_id'
    ];

    public function attachable()
    {
        return $this->morphTo();
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

}
