<?php 

namespace App\Traits;

use App\Models\CustomFieldValue;

trait HasCustomFields
{
    public function customFields()
    {
        return $this->hasMany(CustomFieldValue::class, 'entity_id')
            ->whereHas('definition', function ($query) {
                $query->where('entity_type', $this->getCustomFieldEntityType());
            });
    }

    public function getCustomFieldEntityType(): string
    {
        $entityType = strtolower(class_basename($this));
        return $entityType;
    }
}