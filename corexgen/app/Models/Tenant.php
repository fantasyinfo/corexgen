<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


/**
 * Tenant table model handle all filters, observers, evenets, relatioships
 */
class Tenant extends Model
{
    use HasFactory;

    const table = 'tenants';

    protected $table = self::table;

    protected $fillable = ['name', 'domain', 'settings', 'status', 'currency_code', 'currency_symbol', 'timezone','mode'];


    /**
     * companies relations with Tennant table
     */
    public function companies()
    {
        return $this->hasMany(Company::class);
    }

    /**
     * users relations with Tennant table
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }



    // boot method
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($tenant) {
            // Set default values
            $tenant->status = $tenant->status ?? CRM_STATUS_TYPES['TENANTS']['STATUS']['ACTIVE'];
        });
    }
}
