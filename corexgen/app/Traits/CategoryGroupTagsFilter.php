<?php

namespace App\Traits;

use App\Models\CategoryGroupTag;
use Illuminate\Support\Facades\Auth;

trait CategoryGroupTagsFilter
{
    public function getCategoryGroupTags($type, $relation, $status = 'active')
    {
        return CategoryGroupTag::where('type', $type)->where('relation_type', $relation);
    }

    public function checkIsValidCGTID($id, $company_id, $type, $relation): bool
    {
        return CategoryGroupTag::where('type', $type)
            ->where('relation_type', $relation)
            ->where('company_id', $company_id)
            ->where('id', $id)->exists();


    }

}