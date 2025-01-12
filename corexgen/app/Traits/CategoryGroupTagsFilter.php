<?php

namespace App\Traits;

use App\Models\CategoryGroupTag;
use Illuminate\Support\Facades\Auth;

trait CategoryGroupTagsFilter
{
    /**
     * get category groupts tags filtes lists
     */
    public function getCategoryGroupTags($type, $relation, $status = 'active')
    {
        return CategoryGroupTag::where('type', $type)->where('relation_type', $relation);
    }

     /**
     * check valid CGT ID (Category Group Tax ID)
     */
    public function checkIsValidCGTID($id, $company_id, $type, $relation): bool
    {
        return CategoryGroupTag::where('type', $type)
            ->where('relation_type', $relation)
            ->where('company_id', $company_id)
            ->where('id', $id)->exists();


    }

}