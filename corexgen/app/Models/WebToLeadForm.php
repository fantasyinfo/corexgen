<?php

namespace App\Models;

use App\Models\CRM\CRMLeads;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

/**
 * Leads table model handle all filters, observers, evenets, relatioships
 */
class WebToLeadForm extends Model
{
    use HasFactory;

    const table = 'web_to_leads_form';

    protected $table = self::table;

    protected $fillable = [
        'title',
        'uuid',
        'group_id',
        'source_id',
        'status_id',
        'company_id',
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

    // Belongs to leads
    public function leads()
    {
        return $this->hasMany(CRMLeads::class, 'web_to_leads_form_id');
    }

    /**
     * get leads count capture by this form
     */

    public function getTotalLeadsCaptureCount()
    {
        return $this->leads->count();
    }

    /**
     * Model boot method to set default values
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($form) {

            $form->uuid = (string) Str::uuid();
            if (Auth::check()) {
                $form->company_id = $form->company_id ?? Auth::user()->company_id;

            } else {
                $form->company_id = $form->company_id ?? null;

            }
        });
    }

}
