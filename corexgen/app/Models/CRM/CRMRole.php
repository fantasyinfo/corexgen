<?php

namespace App\Models\CRM;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CRMRole extends Model
{
    use HasFactory;

    const table = 'crm_roles';

    protected $fillable = ['role_name', 'role_desc','buyer_id','created_by','status'];

    protected $table = self::table;


    public function users()
    {
        return $this->hasMany(User::class, 'role_id');
    }


}
