<?php

namespace App\Models\CRM;

use App\Models\Address;
use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use OwenIt\Auditing\Contracts\Auditable;

class CRMClients extends Model implements Auditable
{
    use HasFactory;
    use SoftDeletes;
    use \OwenIt\Auditing\Auditable;

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


    protected $casts = [
        'email' => 'array',
        'phone' => 'array',
        'social_media' => 'array',
        'tags' => 'array',
    ];

    public function addresses()
    {
        return $this->belongsToMany(
            Address::class,
            'client_addresses',
            'client_id',
            'address_id',
            'id',
            'id'
        )
            ->withPivot('type')
            ->withTimestamps();
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }


    protected static function boot()
    {
        parent::boot();

        static::creating(function ($client) {
            // Set default values
            $client->status = $client->status ?? CRM_STATUS_TYPES['CLIENTS']['STATUS']['ACTIVE'];
            $client->company_id = Auth::user()->company_id ?? null;
            $client->created_by = Auth::id() ?? null;
            $client->updated_by = Auth::id() ?? null;
        });
    }


}
