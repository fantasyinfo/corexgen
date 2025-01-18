<?php

namespace App\Repositories;

use App\Models\CRM\CRMClients;

class ClientRepository
{
    /**
     * get clients lists query
     */
    public function getClientsQuery($request)
    {
        $query = CRMClients::query()->latest()
            ->leftJoin('category_group_tag', 'clients.cgt_id', '=', 'category_group_tag.id')
            ->select('clients.*', 'category_group_tag.name as category_name', 'category_group_tag.color as category_color')
            ->with([
                'categoryGroupTag' => function ($query) {
                    $query->where('status', 'active')
                        ->where('relation_type', CATEGORY_GROUP_TAGS_RELATIONS['STATUS']['clients'])
                        ->where('type', CATEGORY_GROUP_TAGS_TYPES['STATUS']['categories']);
                },
                'addresses' => function ($query) {
                    $query->select('addresses.id', 'addresses.street_address', 'addresses.postal_code', 'addresses.city_id', 'addresses.country_id')
                        ->withPivot('type');
                }
            ]);

        // Dynamic filters
        return $this->applyFilters($query, $request);
    }

    /**
     * filters clients lists query
     */
    protected function applyFilters($query, $request)
    {


        return $query
            ->when(
                $request->filled('search'),
                fn($q) => $q->where(function ($subQuery) use ($request) {
                    $searchTerm = strtolower($request->search['value']);
                    $subQuery->where('clients.type', 'LIKE', "%{$searchTerm}%")
                        ->orWhere('clients.company_name', 'LIKE', "%{$searchTerm}%")
                        ->orWhere('clients.title', 'LIKE', "%{$searchTerm}%")
                        ->orWhere('clients.first_name', 'LIKE', "%{$searchTerm}%")
                        ->orWhere('category_group_tag.name', 'LIKE', "%{$searchTerm}%")
                        ->orWhere('clients.primary_email', 'LIKE', "%{$searchTerm}%")
                        ->orWhere('clients.primary_phone', 'LIKE', "%{$searchTerm}%")
                        ->orWhere('clients.created_at', 'LIKE', "%{$searchTerm}%");
                })
            )
            ->when(
                $request->filled('name'),
                fn($q) => $q->where(function ($subQuery) use ($request) {
                    $subQuery->where('clients.first_name', 'LIKE', "%{$request->name}%")
                        ->orWhere('clients.middle_name', 'LIKE', "%{$request->name}%")
                        ->orWhere('clients.last_name', 'LIKE', "%{$request->name}%");
                })
            )
            // ->when(
            //     $request->filled('email'),
            //     fn($q) => $q->whereJsonContains('clients.email', $request->email)
            // )
            // ->when(
            //     $request->filled('phone'),
            //     fn($q) => $q->whereJsonContains('clients.phone', $request->phone)
            // )
            ->when(
                $request->filled('email'),
                fn($q) => $q->where('clients.primary_email', 'LIKE', "%{$request->email}%")
            )
            ->when(
                $request->filled('phone'),
                fn($q) => $q->where('clients.primary_phone', 'LIKE', "%{$request->phone}%")
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
