<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeadUser extends Model
{
    use HasFactory;

    const table = 'lead_user';

    protected $table = self::table;

    protected $fillable = ['lead_id', 'user_id','company_id'];
}
