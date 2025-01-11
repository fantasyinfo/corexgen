<?php

namespace App\Models;

use App\Models\CRM\CRMClients;
use App\Models\CRM\CRMLeads;
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
    use HasFactory, SoftDeletes, \OwenIt\Auditing\Auditable;

    const TABLE = 'companies';

    protected $table = self::TABLE;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'status',
        'tenant_id',
        'address_id',
        'plan_id',
        'deleted_at',
    ];

    /**
     * Relationships
     */

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

    public function leads(): HasMany
    {
        return $this->hasMany(CRMLeads::class, 'company_id');
    }


    public function totalCompany()
    {
        return self::count();
    }
    
    /**
     * Boot method to handle model events
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($company) {
            $company->status = $company->status ?? CRM_STATUS_TYPES['COMPANIES']['STATUS']['ONBOARDING'];
            $company->tenant_id = Auth::user()->tenant_id ?? '1';
        });

        static::deleting(function ($company) {
            \Log::info('Deleting the company with ID: ' . $company->id);
        
            // Define all relationships that need cascading soft deletes
            $relationships = [
                'paymentTransactions' => 'Deleting payment transaction with ID: ',
                'subscriptions' => 'Deleting subscription with ID: ',
                'leads' => 'Deleting lead with ID: ',
                'users' => 'Deleting user with ID: ',
                'clients' => 'Deleting client with ID: ',
                'addresses' => 'Deleting address with ID: ',
            ];
        
            foreach ($relationships as $relation => $logMessage) {
                // Check if the relationship exists and has entries
                if ($company->$relation()->exists()) {
                    $company->$relation()->each(function ($relatedRecord) use ($logMessage) {
                        \Log::info($logMessage . $relatedRecord->id);
                        $relatedRecord->delete();
                    });
                } else {
                    \Log::info('No ' . $relation . ' associated with the company ID: ' . $company->id);
                }
            }
        });
        
    }

    // Helper method to check if a model should be force deleted
    public function shouldForceDelete(): bool
    {
        return $this->isForceDeleting();
    }
}
