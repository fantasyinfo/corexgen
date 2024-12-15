<?php 
namespace App\Models;

use OwenIt\Auditing\Models\Audit as BaseAudit;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

class Audit extends BaseAudit
{
    protected $fillable = [
        'tenant_id',
        'company_id',
        'event',
        'auditable_type',
        'auditable_id',
        'old_values',
        'new_values',
        'user_id',
        'user_type',
        'url',
        'ip_address',
        'user_agent',
        'tags',
    ];

    protected static function booted()
    {
        static::creating(function ($audit) {
            $user = Auth::user();

            // If the user is a tenant
            if ($user->is_tenant) {
                $audit->tenant_id = $user->id;
            }

            // If the user is associated with a company
            if ($user->company_id !== null) {
                $audit->company_id = $user->company_id;
            }

            // Set the user performing the action
            $audit->user_id = $user->id;

            // Set additional metadata
            $audit->ip_address = request()->ip();
            $audit->user_agent = request()->header('User-Agent');
            $audit->url = request()->url();
        });

        static::addGlobalScope('tenantOrCompany', function (Builder $builder) {
            $user = Auth::user();

            if ($user->is_tenant) {
                $builder->where('tenant_id', $user->id);
            }

            if ($user->company_id !== null) {
                $builder->where('company_id', $user->company_id);
            }
        });
    }
}
