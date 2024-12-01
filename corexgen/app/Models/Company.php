<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

class Company extends Model
{
    use HasFactory;

    const table = 'companies';

    protected $table = self::table;

    protected $fillable = ['name', 'email', 'phone', 'status', 'tenant_id', 'address_id','plan_id'];


    public function tenant(){
       return  $this->belongsTo(Tenant::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }


    public function addresses(){
        return $this->belongsTo(Address::class,'address_id');
    }

    public function plans(){
        return $this->belongsTo(Plans::class,'plan_id');
    }




    protected static function boot(){
        parent::boot();

        static::creating(function ($company) {
            // Set default values
            $company->status = $company->status ?? CRM_STATUS_TYPES['COMPANIES']['STATUS']['ACTIVE'];
            $company->tenant_id = Auth::user()->tenant_id;
        });
    }
}
