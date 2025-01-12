<?php

namespace App\Traits;

use App\Models\Audit;
use App\Models\CategoryGroupTag;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

trait AuditFilter
{

    /**
     * get activites lists
     */
    public function getActivites($model, $id)
    {
        // Generate a unique cache key based on model and ID
        $cacheKey = "activities_{$model}_{$id}";

        // Try fetching from cache
        return Cache::remember($cacheKey, 3600, function () use ($model, $id) {
            // Fetch activities with eager-loaded user and necessary fields
            $query = Audit::with([
                'user' => fn($q) => $q->select('id', 'name', 'email', 'profile_photo_path'),
            ])
                ->where('auditable_type', $model)
                ->where('auditable_id', $id)
                ->select('old_values', 'new_values', 'created_at', 'id', 'user_type', 'user_id')
                ->latest()
                ->get();

            // Collect all IDs that need to be resolved for optimization
            $statusIds = [];
            $sourceIds = [];
            $groupIds = [];
            $assigneeIds = [];
            $cgtIds = [];

            foreach ($query as $activity) {
                $this->collectIds($activity->old_values, $statusIds, $sourceIds, $groupIds, $assigneeIds, $cgtIds);
                $this->collectIds($activity->new_values, $statusIds, $sourceIds, $groupIds, $assigneeIds, $cgtIds);
            }

            // Batch query the required data
            $statuses = CategoryGroupTag::whereIn('id', $statusIds)->get()->keyBy('id');
            $sources = CategoryGroupTag::whereIn('id', $sourceIds)->get()->keyBy('id');
            $groups = CategoryGroupTag::whereIn('id', $groupIds)->get()->keyBy('id');
            $cgts = CategoryGroupTag::whereIn('id', $cgtIds)->get()->keyBy('id');
            $assignees = User::whereIn('id', $assigneeIds)->get()->keyBy('id');

            // Enhance the activities
            return $query->map(function ($activity) use ($statuses, $sources, $groups, $assignees, $cgts) {
                $activity->old_values = $this->enhanceValues($activity->old_values, $statuses, $sources, $groups, $assignees, $cgts);
                $activity->new_values = $this->enhanceValues($activity->new_values, $statuses, $sources, $groups, $assignees, $cgts);
                return $activity;
            });
        });
    }


    /**
     * Collect all IDs from the values to batch fetch data.
     */
    private function collectIds($values, &$statusIds, &$sourceIds, &$groupIds, &$assigneeIds, &$cgtIds)
    {
        foreach ($values as $key => $value) {
            if ($key === 'assignees' && is_array($value)) {
                $assigneeIds = array_merge($assigneeIds, $value);
            } elseif ($key === 'status_id') {
                $statusIds[] = $value;
            } elseif ($key === 'source_id') {
                $sourceIds[] = $value;
            } elseif ($key === 'group_id') {
                $groupIds[] = $value;
            } elseif ($key === 'cgt_id') {
                $cgtIds[] = $value;
            }
        }
    }

    /**
     * Enhance values using preloaded data.
     */
    private function enhanceValues($values, $statuses, $sources, $groups, $assignees, $cgts)
    {
        $enhanced = [];
        foreach ($values as $key => $value) {
            if ($key === 'assignees' && is_array($value)) {
                $enhanced[$key] = array_map(fn($id) => $assignees[$id]->name ?? $id, $value);
            } elseif ($key === 'status_id') {
                $enhanced['Stage'] = isset($statuses[$value]) ? ['name' => $statuses[$value]->name] : $value;
            } elseif ($key === 'source_id') {
                $enhanced['Source'] = isset($sources[$value]) ? ['name' => $sources[$value]->name] : $value;
            } elseif ($key === 'group_id') {
                $enhanced['Group'] = isset($groups[$value]) ? ['name' => $groups[$value]->name] : $value;
            } elseif ($key === 'cgt_id') {
                $enhanced['Category'] = isset($groups[$value]) ? ['name' => $cgts[$value]->name] : $value;
            } else {
                $enhanced[$key] = $value;
            }
        }
        return $enhanced;
    }



}