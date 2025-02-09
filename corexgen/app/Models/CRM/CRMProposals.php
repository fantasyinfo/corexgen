<?php

namespace App\Models\CRM;

use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;


/**
 * Proposal table model handle all filters, observers, evenets, relatioships 
 */
class CRMProposals extends Model implements Auditable
{
    use HasFactory;
    use SoftDeletes;
    use \OwenIt\Auditing\Auditable;


    const table = 'proposals';

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
        'accepted_details',
        'product_details',
        'status',
        'template_id',
        'assign_to',
        'company_id',
    ];

    protected $casts = [
        'accepted_details' => 'array',
        'product_details' => 'array',
    ];


    /**
     * typable relations with proposal table
     */

    public function typable()
    {
        return $this->morphTo();
    }



    /**
     * company relations with proposal table
     */

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * user relations with proposal table
     */

    public function user()
    {
        return $this->belongsTo(User::class, 'assign_to');
    }

    /**
     * template relations with proposal table
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

        static::creating(function ($proposal) {

            $proposal->uuid = (string) Str::uuid();
            if (Auth::check()) {
                $proposal->company_id = $proposal->company_id ?? Auth::user()->company_id;

            } else {
                $proposal->company_id = $proposal->company_id ?? null;

            }
        });
    }

}
