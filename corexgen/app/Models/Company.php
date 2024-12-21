<?php

namespace App\Models;

use App\Models\CRM\CRMClients;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use OwenIt\Auditing\Contracts\Auditable;

class Company extends Model implements Auditable
{
    use HasFactory;

    use SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    const table = 'companies';

    protected $table = self::table;

    protected $fillable = ['name', 'email', 'phone', 'status', 'tenant_id', 'address_id', 'plan_id', 'deleted_at'];


    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }


    public function addresses(): BelongsTo
    {
        return $this->belongsTo(Address::class, 'address_id');
    }

    public function plans(): BelongsTo
    {
        return $this->belongsTo(Plans::class, 'plan_id');
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    
    public function paymentTransactions(): HasMany
    {
        return $this->hasMany(PaymentTransaction::class);
    }

    public function latestSubscription(): HasOne
    {
        return $this->hasOne(Subscription::class)->latestOfMany();
    }


    public function clients(): HasMany
    {
        return $this->hasMany(CRMClients::class, 'company_id');
    }




    protected static function boot()
    {
        parent::boot();

        static::creating(function ($company) {
            // Set default values
            $company->status = $company->status ?? CRM_STATUS_TYPES['COMPANIES']['STATUS']['ONBOARDING'];
            $company->tenant_id = Auth::user()->tenant_id ?? '1';
        });

        static::deleting(function ($company) {
            // If it's a force delete, let Laravel handle the cascading
            if ($company->isForceDeleting()) {
                return;
            }

            // Soft delete associated records
            $company->paymentTransactions()->get()->each(function ($transaction) {
                $transaction->delete();
            });

            $company->subscriptions()->get()->each(function ($subscription) {
                $subscription->delete();
            });
        });

        // Handle restoration of soft-deleted records
        static::restoring(function ($company) {
            // Restore associated soft-deleted records
            $company->paymentTransactions()->withTrashed()->get()->each(function ($transaction) {
                $transaction->restore();
            });

            $company->subscriptions()->withTrashed()->get()->each(function ($subscription) {
                $subscription->restore();
            });
        });
    }
}
