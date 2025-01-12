<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


/**
 * Custom fields values table model handle all filters, observers, evenets, relatioships
 */
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


    /**
     * definations relations with custom fields values table
     */
    public function definition()
    {
        return $this->belongsTo(CustomFieldDefinition::class, 'definition_id');
    }
}

