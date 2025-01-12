<?php

namespace App\Models;

use App\Models\CRM\CRMClients;
use App\Models\CRM\CRMLeads;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Address table model handle all filters, observers, evenets, relatioships
 */
class Address extends Model
{
    use HasFactory;

    const table = 'addresses';

    protected $table = self::table;

    protected $fillable = ['street_address', 'postal_code', 'city_id', 'country_id', 'address_type'];


    /**
     * Get the city associated with the address.
     */
    public function city()
    {
        return $this->belongsTo(City::class, 'city_id');
    }

    /**
     * Get the country associated with the address.
     */
    public function country()
    {
        return $this->belongsTo(Country::class, 'country_id');
    }


    /**
     * company relations with address table
     */
    public function companies()
    {
        return $this->hasMany(Company::class, 'address_id');
    }


    /**
     * user relations with address table
     */
    public function users()
    {
        return $this->hasMany(User::class, 'address_id');
    }


    /**
     * leads relations with address table
     */
    public function leads()
    {
        return $this->hasOne(CRMLeads::class, 'address_id');
    }



    /**
     * clients relations with address table
     */
    public function clients()
    {
        return $this->belongsToMany(CRMClients::class, 'client_addresses')
            ->withPivot('type')
            ->withTimestamps();
    }
}
