<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plans extends Model
{
    use HasFactory;


    const table = 'plans';

    protected $table = self::table;

    protected $fillable = ['name', 'desc','price','offer_price','billing_cycle','status'];


    public function plans_features(){
       return $this->hasMany(PlansFeatures::class,'plan_id');
    }
  
}
