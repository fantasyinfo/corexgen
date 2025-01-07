<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskUser extends Model
{
    use HasFactory;

    const table = 'task_user';

    protected $table = self::table;

    protected $fillable = [
        'task_id',
        'user_id',
        'company_id',
    ];
}
