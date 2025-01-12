<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Custom fields definations table model handle all filters, observers, evenets, relatioships
 */
class CustomFieldDefinition extends Model
{
    use HasFactory;


    const table = 'custom_field_definitions';

    protected $table = self::table;

    protected $fillable = [
        'company_id',
        'entity_type',
        'field_name',
        'field_type',
        'field_label',
        'is_required',
        'is_active',
        'options',
        'validation_rules',
    ];

    protected $casts = [
        'options' => 'array',
        'validation_rules' => 'array',
        'is_required' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * values relations with custom fields definations table
     */
    public function values()
    {
        return $this->hasMany(CustomFieldValue::class, 'definition_id');
    }

    /**
     * scope for company filters
     */
    public function scopeForCompany($query, $companyId = null)
    {
        return $query->where(function ($q) use ($companyId) {
            $q->where('company_id', $companyId)
                ->orWhereNull('company_id'); // Include tenant-wide fields
        });
    }
}
