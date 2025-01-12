<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


/**
 * Country table model handle all filters, observers, evenets, relatioships
 */
class Country extends Model
{
    use HasFactory;

    const table = 'countries';

    protected $table = self::table;

    protected $fillable = ['name', 'code'];


    /**
     * cities relations with company country table
     */
    public function cities()
    {
        return $this->hasMany(City::class, 'country_id');
    }


    /**
     * address relations with company country table
     */
    public function addresses()
    {
        return $this->hasMany(Address::class, 'country_id');
    }
}
