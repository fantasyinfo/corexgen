<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    use HasFactory;

    const table = 'countries';

    protected $table = self::table;

    protected $fillable = ['name', 'code'];


    public function cities()
    {
        return $this->hasMany(City::class, 'country_id');
    }

    public function addresses()
    {
        return $this->hasMany(Address::class,'country_id');
    }
}
