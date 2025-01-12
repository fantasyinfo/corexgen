<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\CRM\CRMLeads;
use App\Models\CRM\CRMRole;
use App\Models\Buyer;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Auth;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Traits\HasCustomFields;

/**
 * Users table model handle all filters, observers, evenets, relatioships
 */
class User extends Authenticatable implements Auditable
{
    use HasApiTokens;
    use HasFactory;
    use HasProfilePhoto;
    use Notifiable;
    use TwoFactorAuthenticatable;
    use SoftDeletes;
    use \OwenIt\Auditing\Auditable;
    use HasCustomFields;
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'status',
        'role_id',
        'is_tenant',
        'tenant_id',
        'company_id',
        'address_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];



    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'profile_photo_url',
    ];

    /**
     * role relations with users table
     */
    public function role()
    {
        return $this->belongsTo(CRMRole::class, 'role_id');  // The correct foreign key is 'role_id'
    }

    /**
     * tenant relations with users table
     */
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * company relations with users table
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * address relations with users table
     */
    public function addresses()
    {
        return $this->belongsTo(Address::class, 'address_id');
    }

    // Leads assigned by this user
    /**
     * leads assing users relations with users table
     */
    public function assignedLeads()
    {
        return $this->hasMany(CRMLeads::class, 'assign_by');
    }

    // Leads this user is associated with (via pivot table)
    /**
     * associated leads relations with users table
     */
    public function associatedLeads()
    {
        return $this->belongsToMany(CRMLeads::class, 'lead_user', 'user_id', 'lead_id')
            ->withTimestamps()
            ->withPivot('company_id');
    }

    /**
     * get total users
     */
    public function totalUsers($company_id = null)
    {
        return self::where('company_id', $company_id)->count();
    }

    /**
     * boot method

     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($user) {
            $user->status = $user->status ?? CRM_STATUS_TYPES['USERS']['STATUS']['ACTIVE'];
        });

    }

}
