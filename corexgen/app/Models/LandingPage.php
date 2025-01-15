<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Landing Page table model handle all filters, observers, evenets, relatioships
 */
class LandingPage extends Model
{
    use HasFactory;

    const table = 'landing_page';

    protected $table = self::table;

    protected $fillable = [
        'key',
        'type',
        'value',
    ];

    protected $casts = [
        'value' => 'array',
    ];

}
