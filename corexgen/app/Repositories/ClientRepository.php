<?php

namespace App\Repositories;

use App\Models\CRM\CRMClients;

class ClientRepository
{
    // Your repository methods

    public function getClientsQuery($request)
    {
        $query = CRMClients::query()->with([
            'addresses' => function ($query) {
                $query->select('addresses.id', 'addresses.street_address', 'addresses.postal_code', 'addresses.city_id', 'addresses.country_id')
                    ->withPivot('type');
            }
        ]);
        // Dynamic filters
        return $this->applyFilters($query, $request);
    }
    protected function applyFilters($query, $request)
    {
        return $query
            ->when(
                $request->filled('name'),
                fn($q) => $q->where(function ($subQuery) use ($request) {
                    $subQuery->where('clients.first_name', 'LIKE', "%{$request->name}%")
                        ->orWhere('clients.middle_name', 'LIKE', "%{$request->name}%")
                        ->orWhere('clients.last_name', 'LIKE', "%{$request->name}%");
                })
            )
            ->when(
                $request->filled('email'),
                fn($q) => $q->whereJsonContains('clients.email', $request->email)
            )
            ->when(
                $request->filled('phone'),
                fn($q) => $q->whereJsonContains('clients.phone', $request->phone)
            )
            ->when(
                $request->filled('status') && $request->status != 0,
                fn($q) => $q->where('clients.status', $request->status)
            )
            ->when(
                $request->filled('start_date'),
                fn($q) => $q->whereDate('clients.created_at', '>=', $request->start_date)
            )
            ->when(
                $request->filled('end_date'),
                fn($q) => $q->whereDate('clients.created_at', '<=', $request->end_date)
            );
    }

}
