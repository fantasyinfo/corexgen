<?php

namespace App\Traits;

use App\Models\CategoryGroupTag;
use Illuminate\Support\Facades\Log;

trait StatusStatsFilter
{
    public function getGroupByStatusQuery($model, $isSoftDelete = true)
    {
        // Instantiate the model if a class name is passed
        if (is_string($model)) {
            $model = resolve($model);
        }

        // Base query for filtering soft deletes
        $baseQuery = $model->query();
        if ($isSoftDelete) {
            $baseQuery->where('deleted_at', null);
        }

        // Query for grouped counts with ORDER BY clause
        $groupedQuery = $baseQuery->clone()
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->orderBy('status', 'asc'); // Add ORDER BY clause here

        // Total count query
        $totalQuery = $baseQuery->clone();

        return [
            'groupQuery' => $groupedQuery,
            'totalQuery' => $totalQuery,
        ];
    }

    public function getGroupByStageQuery($model, $type, $relation)
    {
        // Instantiate the model if a class name is passed
        if (is_string($model)) {
            $model = resolve($model);
        }
    
        // Build the query for CategoryGroupTag
        $cgt = CategoryGroupTag::query()
            ->where('type', $type)
            ->where('relation_type', $relation);
        $cgt = $this->applyTenantFilter($cgt);
    
        // Base query for the main model
        $baseQuery = $model->query()
            ->joinSub($cgt, 'cgt', 'cgt.id', '=', "{$model->getTable()}.status_id");
    
        // Query for grouped counts with ORDER BY clause
        $groupedQuery = $baseQuery->clone()
            ->selectRaw('cgt.name as status, COUNT(*) as count')
            ->groupBy('cgt.name')
            ->orderBy('cgt.name', 'asc'); // Add ORDER BY clause here
    
        // Total count query for all status_id entries in the model
        $totalQuery = $baseQuery->clone();
    
        return [
            'groupQuery' => $groupedQuery,
            'totalQuery' => $totalQuery,
        ];
    }
    
}
