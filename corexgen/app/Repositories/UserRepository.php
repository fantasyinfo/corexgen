<?php

namespace App\Repositories;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Traits\TenantFilter;
class UserRepository
{
    use TenantFilter;
    // Your repository methods

    /**
     * get users lists query
     */
    public function getUsersQuery($request)
    {
        $query = User::query()->with('role')
            ->where('id', '!=', Auth::user()->id)
            ->where('role_id', '!=', null);

        // Tenant filtering
        if (panelAccess() == PANEL_TYPES['SUPER_PANEL']) {
            $query->where('is_tenant', '=', '1');
        }

        $query = $this->applyTenantFilter($query);

        // Dynamic filters
        return $this->applyFilters($query, $request);
    }

    /**
     * get users lists query filters
     */
    protected function applyFilters($query, $request)
    {
        return $query
            ->when(
                $request->filled('name'),
                fn($q) => $q->where('name', 'LIKE', "%{$request->name}%")
            )
            ->when(
                $request->filled('email'),
                fn($q) => $q->where('email', 'LIKE', "%{$request->email}%")
            )
            ->when(
                $request->filled('role_id') && $request->role_id != '0',
                fn($q) => $q->where('role_id', $request->role_id)
            )
            ->when(
                $request->filled('status'),
                fn($q) => $q->where('status', $request->status)
            )
            ->when(
                $request->filled('start_date'),
                fn($q) => $q->whereDate('created_at', '>=', $request->start_date)
            )
            ->when(
                $request->filled('end_date'),
                fn($q) => $q->whereDate('created_at', '<=', $request->end_date)
            );
    }
}
