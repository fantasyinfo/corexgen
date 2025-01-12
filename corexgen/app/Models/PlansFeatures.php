<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


/**
 * Plans Featuers table model handle all filters, observers, evenets, relatioships
 */
class PlansFeatures extends Model
{
    use HasFactory;

    const table = 'plans_features';

    protected $table = self::table;

    protected $fillable = ['plan_id', 'module_name', 'value'];


    /**
     * plan  relations with plans features table
     */
    public function plan()
    {
        return $this->belongsTo(Plans::class, 'plan_id');
    }
}
