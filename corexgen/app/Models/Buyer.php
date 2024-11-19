<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Buyer extends Model
{
    use HasFactory;

    const table ='buyers';

    protected $table = self::table;

    protected $fillable = ['name','email','password','buyer_id','status'];
}
