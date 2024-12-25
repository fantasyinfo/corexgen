<?php
namespace App\Models\CRM;

use App\Models\Address;
use App\Models\CategoryGroupTag;
use App\Models\Company;
use App\Models\User;
use App\Traits\HasCustomFields;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use OwenIt\Auditing\Contracts\Auditable;

class CRMLeads extends Model implements Auditable
{
    use HasFactory;
    use \OwenIt\Auditing\Auditable;
    use HasCustomFields;

    const table = 'leads';

    protected $table = self::table;

    protected $fillable = [
        'type',
        'company_name',
        'title',
        'value',
        'first_name',
        'last_name',
        'email',
        'phone',
        'details',
        'last_contacted_date',
        'last_activity_date',
        'priority',
        'preferred_contact_method',
        'score',
        'follow_up_date',
        'is_converted',
        'status',
        'updated_by',
        'created_by',
        'assign_by',
        'group_id',
        'source_id',
        'status_id',
        'address_id',
        'company_id',
    ];

    protected $casts = [
        'last_contacted_date' => 'datetime',
        'last_activity_date' => 'datetime',
        'follow_up_date' => 'datetime',
        'is_converted' => 'boolean',
        'value' => 'decimal:2',
    ];

    /**
     * Relationships
     */

    // Belongs to company
    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    // Belongs to group/category
    public function group()
    {
        return $this->belongsTo(CategoryGroupTag::class, 'group_id');
    }

    // Belongs to source
    public function source()
    {
        return $this->belongsTo(CategoryGroupTag::class, 'source_id');
    }

    // Belongs to status
    public function stage()
    {
        return $this->belongsTo(CategoryGroupTag::class, 'status_id');
    }

    // Belongs to address
    public function address()
    {
        return $this->belongsTo(Address::class, 'address_id');
    }

    // Belongs to user who assigned it
    public function assignedBy()
    {
        return $this->belongsTo(User::class, 'assign_by');
    }

    // Many-to-many relationship for multiple assignees
    public function assignees()
    {
        return $this->belongsToMany(User::class, 'lead_user', 'lead_id', 'user_id')
            ->withTimestamps()
            ->withPivot('company_id');
    }

    /**
     * Model boot method to set default values
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($lead) {
            $lead->status = $lead->status ?? CRM_STATUS_TYPES['LEADS']['STATUS']['ACTIVE'];

            if (Auth::check()) {
                $lead->company_id = $lead->company_id ?? Auth::user()->company_id;
                $lead->created_by = $lead->created_by ?? Auth::id();
                $lead->updated_by = $lead->updated_by ?? Auth::id();
            } else {
                $lead->company_id = $lead->company_id ?? null;
                $lead->created_by = $lead->created_by ?? null;
                $lead->updated_by = $lead->updated_by ?? null;
            }
        });
    }
}
