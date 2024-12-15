<?php

namespace App\Models\CRM;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
class CRMRole extends Model implements Auditable
{
    use HasFactory;
    use \OwenIt\Auditing\Auditable;

    const table = 'crm_roles';

    protected $fillable = [
        'role_name', 
        'role_desc',
        'company_id',
        'status',
    ];

    protected $table = self::table;


    public function users()
    {
        return $this->hasMany(User::class, 'role_id');
    }
    

    protected static function boot(){
        parent::boot();

        static::creating(function($crmrole){
            $crmrole->status = $crmrole->status ?? CRM_STATUS_TYPES['CRM_ROLES']['STATUS']['ACTIVE'];
        });

    }


}
