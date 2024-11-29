<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plans extends Model
{
    use HasFactory;


    const table = 'plans';

    protected $table = self::table;

    protected $fillable = ['name', 'desc','users_limit','roles_limit','price','offer_price','billing_cycle','status','tax_rates_id'];


    public function tax()
    {
        return $this->belongsTo(Tax::class, 'tax_rate_id');
    }
}
