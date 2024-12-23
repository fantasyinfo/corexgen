<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomFieldValue extends Model
{
    use HasFactory;

    const table = 'custom_field_values';

    protected $table = self::table;

    protected $fillable = [
        'definition_id',
        'entity_id',
        'field_value',
    ];

    public function definition()
    {
        return $this->belongsTo(CustomFieldDefinition::class, 'definition_id');
    }
}

