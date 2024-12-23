<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CategoryGroupTag extends Model
{
    use HasFactory;

    const table = 'category_group_tag';

    protected $table = self::table;

    protected $fillable = ['name', 'color','type','status','company_id'];
}
