<?php

namespace App\Models\CRM;

use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Support\Str;

/**
 * Contract table model handle all filters, observers, evenets, relatioships
 */
class CRMContract extends Model implements Auditable
{
    use HasFactory;
    use SoftDeletes;
    use \OwenIt\Auditing\Auditable;


    const table = 'contract';

    protected $table = self::table;

    protected $fillable = [
        '_prefix',
        '_id',
        'title',
        'url',
        'value',
        'details',
        'typable_type',
        'typable_id',
        'creating_date',
        'valid_date',
        'company_accepted_details',
        'accepted_details',
        'statusCompany',
        'status',
        'template_id',
        'assign_to',
        'company_id',
    ];

    protected $casts = [
        'accepted_details' => 'array',
        'company_accepted_details' => 'array',

    ];


    /**
     * typable relations with contract table
     */
    public function typable()
    {
        return $this->morphTo();
    }


    /**
     * company relations with contract table
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * user relations with contract table
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'assign_to');
    }

    /**
     * template relations with contract table
     */
    public function template()
    {
        return $this->belongsTo(CRMTemplates::class, 'template_id');
    }


    /**
     * Model boot method to set default values
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($contract) {

            $contract->uuid = (string) Str::uuid();
            if (Auth::check()) {
                $contract->company_id = $contract->company_id ?? Auth::user()->company_id;

            } else {
                $contract->company_id = $contract->company_id ?? null;

            }
        });
    }

}
