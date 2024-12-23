<?php

namespace App\Models;

use App\Models\CRM\CRMClients;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CategoryGroupTag extends Model
{
    use HasFactory;

    const table = 'category_group_tag';

    protected $table = self::table;

    protected $fillable = ['name', 'color','type','status','company_id'];


    public function clients()
    {
        return $this->hasMany(CRMClients::class, 'cgt_id');
    }
}
