<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


/**
 * Client Address table model handle all filters, observers, evenets, relatioships
 */
class ClientAddress extends Model
{
    use HasFactory;


    const table = 'client_addresses';

    protected $table = self::table;

    protected $fillable = ['client_id', 'address_id','type'];
}
