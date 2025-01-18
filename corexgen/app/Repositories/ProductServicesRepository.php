<?php

namespace App\Repositories;

use App\Models\ProductsServices;
use App\Traits\TenantFilter;

class ProductServicesRepository
{
    use TenantFilter;
    // Your repository methods

    /**
     * get products lists query
     */
    public function getProductsQuery($request)
    {
        $query = ProductsServices::query()->with(['category', 'tax'])->latest();

        $query = $this->applyTenantFilter($query);

        //return $query;
        // Dynamic filters
        return $this->applyFilters($query, $request);
    }

    /**
     * get products lists query filters
     */
    protected function applyFilters($query, $request)
    {
        return $query
            ->when(
                $request->filled('title'),
                fn($q) => $q->where('title', 'LIKE', "%{$request->title}%")
            )
            ->when(
                $request->filled('type') && $request->type != '0',
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
