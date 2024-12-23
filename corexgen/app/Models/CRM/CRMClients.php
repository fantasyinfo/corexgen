<?php

namespace App\Models\CRM;

use App\Models\Address;
use App\Models\CategoryGroupTag;
use App\Models\Company;
use App\Traits\HasCustomFields;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Support\Str;

class CRMClients extends Model implements Auditable
{
    use HasFactory;
    use \OwenIt\Auditing\Auditable;
    use HasCustomFields;

    const table = 'clients';

    protected $table = self::table;

    protected $fillable = [
        'type',
        'title',
        'first_name',
        'middle_name',
        'last_name',
        'company_name',
        'email',
        'phone',
        'social_media',
        'cgt_id',
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

    public function categoryGroupTag()
    {
        return $this->belongsTo(CategoryGroupTag::class, 'cgt_id');
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($client) {
            // Set default values using null coalescing
            $client->status = $client->status ?? CRM_STATUS_TYPES['CLIENTS']['STATUS']['ACTIVE'];
            $client->uuid = (string) Str::uuid();
            if (Auth::check()) {
                $client->company_id = $client->company_id ?? Auth::user()->company_id;
                $client->created_by = $client->created_by ?? Auth::id();
                $client->updated_by = $client->updated_by ?? Auth::id();
            } else {
                // Handle cases where Auth is not available (e.g., background jobs)
                $client->company_id = $client->company_id ?? null;
                $client->created_by = $client->created_by ?? null;
                $client->updated_by = $client->updated_by ?? null;
            }
        });

        static::saving(function ($client) {
            if (is_array($client->email) && count($client->email) > 0) {
                $client->primary_email = $client->email[0];
            }
            if (is_array($client->phone) && count($client->phone) > 0) {
                $client->primary_phone = $client->phone[0];
            }
        });
    }


}
