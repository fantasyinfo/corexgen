<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Traits\CategoryGroupTagsFilter;
use App\Traits\TenantFilter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DownloadController extends Controller
{
    //
    use TenantFilter;
    use CategoryGroupTagsFilter;

    /**
     * download countires lists in csv
     */
    public function countries()
    {
        $countries = Country::all(['id', 'name']);
        $csvData = "ID,Name\n";

        foreach ($countries as $country) {
            $csvData .= "{$country->id},{$country->name}\n";
        }

        $filename = $this->getFileNameWithCompany('countries');
        return response($csvData)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', "attachment; filename=$filename");
    }


    /**
     * download group category tags lists in csv
     */
    public function cgt($type, $relation)
    {

        $categoryQuery = $this->getCategoryGroupTags($type, $relation);
        $categoryQuery = $this->applyTenantFilter($categoryQuery);
        $categories = $categoryQuery->select(['id', 'name'])->get();

        $csvData = "ID,Name\n";
        foreach ($categories as $category) {
            $csvData .= "{$category->id},{$category->name}\n";
        }

        $filename = $this->getFileNameWithCompany('categories_groups_tags');
        return response($csvData)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', "attachment; filename=$filename");
    }


    /**
     * get file name with company
     */
    private function getFileNameWithCompany($filename)
    {
        $company_id = !is_null(Auth::user()->company_id) ? Auth::user()->company_id : time(); // time for tenant
        return $filename . "_" . $company_id . ".csv";
    }
}
