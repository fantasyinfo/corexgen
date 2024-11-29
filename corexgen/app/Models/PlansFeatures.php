<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlansFeatures extends Model
{
    use HasFactory;

    const table = 'plans_features';

    protected $table = self::table;

    protected $fillable = ['plan_id', 'module_name','value'];

    public function plan(){
       return $this->belongsTo(Plans::class,'plan_id');
    }
}
