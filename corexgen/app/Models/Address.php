<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
        return $this->belongsTo(City::class,'city_id');
    }

    /**
     * Get the country associated with the address.
     */
    public function country()
    {
        return $this->belongsTo(Country::class,'country_id');
    }


    public function companies(){
        return $this->hasMany(Company::class,'address_id');
    }
}
