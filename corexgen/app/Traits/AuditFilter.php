<?php

namespace App\Traits;

use App\Models\Audit;
use App\Models\CategoryGroupTag;
use App\Models\User;

trait AuditFilter
{

    public function getActivites($model, $id)
    {
        $query = Audit::with([
            'user' => fn($q) => $q->select('id', 'name', 'email', 'profile_photo_path'),
        ])
            ->where('auditable_type', $model)
            ->where('auditable_id', $id)
            ->select('old_values', 'new_values', 'created_at', 'id', 'user_type', 'user_id')
            ->latest()
            ->get();
        ;

        // Enhance the activities with meaningful names/colors
        return $query->map(function ($activity) {
            $activity->old_values = $this->enhanceValues($activity->old_values);
            $activity->new_values = $this->enhanceValues($activity->new_values);
            return $activity;
        });
    }

    /**
     * Enhance values to replace IDs with meaningful names/colors.
     */
    private function enhanceValues($values)
    {
        $enhanced = [];
        foreach ($values as $key => $value) {
            if ($key === 'assignees' && is_array($value)) {
                // Replace assignee IDs with names
                $enhanced[$key] = User::whereIn('id', $value)->pluck('name')->toArray();

            } elseif ($key === 'status_id' || $key === 'source_id' || $key === 'group_id') {
                // Replace status ID with name and color
                $status = CategoryGroupTag::find($value);
                if ($key === 'status_id') {
                    $key = 'Stage';
                } else if ($key === 'source_id') {
                    $key = 'Source';
                } else if ($key === 'group_id') {
                    $key = 'Group';
                }
                $enhanced[$key] = $status ? ['name' => $status->name] : $value;
            } else {
                // For other fields, keep the value as-is
                $enhanced[$key] = $value;
            }
        }
        return $enhanced;
    }



}