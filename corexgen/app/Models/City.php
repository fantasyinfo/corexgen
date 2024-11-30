<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class City extends Model
{
    use HasFactory;

    const table = 'cities';

    protected $table = self::table;

    protected $fillable = ['name', 'country_id'];


    public function country(){
        return $this->belongsTo(Country::class,'country_id');
       }
}


