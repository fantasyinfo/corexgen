<?php

namespace App\Models\CRM;

use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use SebastianBergmann\Template\Template;

/**
 * Estimate table model handle all filters, observers, evenets, relatioships
 */
class CRMEstimate extends Model implements Auditable
{
    use HasFactory;
    use SoftDeletes;
    use \OwenIt\Auditing\Auditable;


    const table = 'estimate';

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
     * typable relations with estimate table
     */
    public function typable()
    {
        return $this->morphTo();
    }


    /**
     * company relations with estimate table
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * user relations with estimate table
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'assign_to');
    }

    /**
     * template relations with estimate table
     */
    public function template()
    {
        return $this->belongsTo(CRMTemplates::class, 'template_id');
    }


}
