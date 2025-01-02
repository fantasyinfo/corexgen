<?php

namespace App\Repositories;

use App\Models\ProductsServices;
use App\Traits\TenantFilter;

class ProductServicesRepository
{
    use TenantFilter;
    // Your repository methods
    public function getProductsQuery($request)
    {
        $query = ProductsServices::query()->with(['category', 'tax']);

        $query = $this->applyTenantFilter($query);

        //return $query;
        // Dynamic filters
        return $this->applyFilters($query, $request);
    }

    protected function applyFilters($query, $request)
    {
        return $query
            ->when(
                $request->filled('title'),
                fn($q) => $q->where('title', 'LIKE', "%{$request->title}%")
            )
            ->when(
                $request->filled('type'),
                fn($q) => $q->where('type', "$request->type")
            )
            ->when(
                $request->filled('cgt_id'),
                fn($q) => $q->where('cgt_id', "$request->cgt_id")
            )
            ->when(
                $request->filled('tax_id'),
                fn($q) => $q->where('tax_id', "$request->tax_id")
            )
            ->when(
                $request->filled('status'),
                fn($q) => $q->where('status', $request->status)
            );
    }
}
