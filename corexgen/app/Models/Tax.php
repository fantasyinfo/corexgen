<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tax extends Model
{
    use HasFactory;

    const table = 'tax_rates';

    protected $table = self::table;

    protected $fillable = ['name', 'tax_rate','tax_type','status','country_id'];


    public function country(){
        $this->belongsTo(Country::class,'country_id');
    }
}
