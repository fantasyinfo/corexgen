<?php 
namespace App\Models;


use OwenIt\Auditing\Models\Audit as BaseAudit;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

/**
 * Audit table model handle all filters, observers, evenets, relatioships
 */
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

    /**
     * Audit table boot method

     */
    protected static function booted()
    {
        // Creating callback
        static::creating(function ($audit) {
            $user = Auth::user();

            // Only execute if there is an authenticated user
            if ($user) {
                // If the user is a tenant
                if (isset($user->is_tenant) && $user->is_tenant) {
                    $audit->tenant_id = $user->id;
                }

                // If the user is associated with a company
                if (!is_null($user->company_id)) {
                    $audit->company_id = $user->company_id;
                }

                // Set the user performing the action
                $audit->user_id = $user->id;
            }

            // Set additional metadata (these do not depend on Auth::user)
            $audit->ip_address = request()->ip() ?? '127.0.0.1';
            $audit->user_agent = request()->header('User-Agent', 'Unknown');
            $audit->url = request()->url();
        });

        // Add Global Scope
        static::addGlobalScope('tenantOrCompany', function (Builder $builder) {
            $user = Auth::user();

            if ($user) {
                if (isset($user->is_tenant) && $user->is_tenant) {
                    $builder->where('tenant_id', $user->id);
                }

                if (!is_null($user->company_id)) {
                    $builder->where('company_id', $user->company_id);
                }
            }
        });
    }
}
