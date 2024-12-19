<?php

namespace App\Models\CRM;

use App\Models\Address;
use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CRMClients extends Model
{
    use HasFactory;
    use SoftDeletes;


    const table = 'clients';

    protected $table = self::table;

    protected $fillable = [
        'type', 
        'title', 
        'first_name', 
        'middle_name', 
        'last_name',
        'email',
        'phone',
        'social_media',
        'category',
        'details',
        'tags',
        'birthdate',
        'company_id',
        'status',
        'created_by',
        'updated_by',
    ];


    public function addresses()
    {
        return $this->belongsToMany(Address::class, 'client_addresses')
                    ->withPivot('type')
                    ->withTimestamps();
    }

    public function company(){
        return $this->belongsTo(Company::class,'company_id');
    }


}
