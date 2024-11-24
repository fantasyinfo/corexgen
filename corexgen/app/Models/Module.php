<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Module extends Model
{
    use HasFactory;

    const table = 'modules';

    protected $table = self::table;

    protected $fillable = ['name', 'version', 'description', 'providers', 'path', 'status'];
}
