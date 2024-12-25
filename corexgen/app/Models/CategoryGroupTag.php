<?php

namespace App\Models;

use App\Models\CRM\CRMClients;
use App\Models\CRM\CRMLeads;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class CategoryGroupTag extends Model
{
    use HasFactory;

    const table = 'category_group_tag';

    protected $table = self::table;

    protected $fillable = ['name', 'color', 'type', 'status', 'company_id'];


    public function clients()
    {
        return $this->hasMany(CRMClients::class, 'cgt_id');
    }


    public function leadsGroups()
    {
        return $this->hasMany(CRMLeads::class, 'group_id');
    }
    public function leadsSources()
    {
        return $this->hasMany(CRMLeads::class, 'source_id');
    }
    public function leadsStatus()
    {
        return $this->hasMany(CRMLeads::class, 'status_id');
    }




    protected static function boot()
    {
        parent::boot();

        static::creating(function ($cgt) {
            // Set default values
            $cgt->created_at = now();
            $cgt->updated_at = now();

        });
    }
}
