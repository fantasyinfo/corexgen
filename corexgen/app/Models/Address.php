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
}
