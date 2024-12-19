<?php

namespace App\Repositories;

use App\Models\CRM\CRMClients;

class ClientRepository
{
    // Your repository methods

    public function getClientsQuery($request)
    {
        $query = CRMClients::query()->with(['addresses' => function ($query) {
            $query->select('addresses.id', 'addresses.street_address', 'addresses.postal_code', 'addresses.city_id', 'addresses.country_id')
                  ->withPivot('type');
        }]);
        // Dynamic filters
        return $query;
    }
}
