<?php
namespace App\Helpers;

use App\Models\CustomFieldDefinition;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

/**
 * Dynamically valid the custom fields CustomFieldsValidation
 */
class CustomFieldsValidation
{
    /**
     * validate all fields types on custom fields
     */
    public function validate(array $customFields, string $entityType, int $companyId)
    {
        // Fetch all active custom field definitions for this entity type
        $fieldDefinitions = CustomFieldDefinition::where('entity_type', $entityType)
            ->where('is_active', true)
            ->where('company_id', $companyId)
            ->get()
            ->keyBy('id');  // Index by ID for easy lookup

        $validationRules = [];
        $messages = [];

        // Build validation rules for each custom field
        foreach ($fieldDefinitions as $id => $field) {
            $fieldKey = "custom_fields.$id";

            $rules = [];

            // Add required rule if field is mandatory
            if ($field->is_required) {
                $rules[] = 'required';
                $messages["$fieldKey.required"] = "The custom field '{$field->field_label}' is required.";
            } else {
                $rules[] = 'nullable';
            }

            // Add type-specific validation rules
            switch ($field->field_type) {
                case 'number':
                    $rules[] = 'numeric';
                    break;
                case 'date':
                    $rules[] = 'date';
                    break;
                case 'time':
                    $rules[] = 'date_format:H:i';
                    break;
                case 'select':
                    if (!empty($field->options)) {
                        $rules[] = 'in:' . implode(',', $field->options);
                    }
                    break;
            }

            if ($rules) {
                $validationRules[$fieldKey] = $rules;
            }
        }

        // Check for any custom fields submitted that don't exist in definitions
        $unknownFields = array_diff(array_keys($customFields), array_keys($fieldDefinitions->toArray()));
        if (!empty($unknownFields)) {
            throw ValidationException::withMessages([
                'custom_fields' => 'Invalid custom field IDs: ' . implode(', ', $unknownFields)
            ]);
        }

        // Perform validation
        return Validator::make(
            ['custom_fields' => $customFields],
            $validationRules,
            $messages
        )->validate();
    }
}