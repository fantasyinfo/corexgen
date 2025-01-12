<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


/**
 * City table model handle all filters, observers, evenets, relatioships
 */
class City extends Model
{
    use HasFactory;

    const table = 'cities';

    protected $table = self::table;

    protected $fillable = ['name', 'country_id'];


    /**
     * country relations with city table
     */
    public function country()
    {
        return $this->belongsTo(Country::class, 'country_id');
    }

    /**
     * address relations with city table
     */
    public function addresses()
    {
        return $this->hasMany(Address::class, 'city_id');
    }
}


