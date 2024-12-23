<?php

namespace App\Traits;

use App\Models\CategoryGroupTag;
use Illuminate\Support\Facades\Auth;

trait CategoryGroupTagsFilter
{
    public function getCategoryGroupTags($type, $relation, $status = 'active')
    {
        return CategoryGroupTag::where('type', $type)->where('relation_type', $relation)->where('status', $status);
    }

}