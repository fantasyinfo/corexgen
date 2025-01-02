<?php 

namespace App\Traits;

use App\Models\CustomFieldValue;
use Illuminate\Support\Facades\Log;

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
        // Log::info('Custom Field Entity Type : ' . $entityType);
        return $entityType;
    }
}