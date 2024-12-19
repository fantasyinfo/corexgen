<?php

namespace App\Services;

use App\Models\CRM\CRMClients;
use Illuminate\Support\Facades\DB;



class ClientService
{
    public function handle()
    {
        // Add your logic here
    }

    public function createClient(array $validatedData)
    {
        return DB::transaction(function () use ($validatedData) {

            $client_address = $this->createAddressIfProvided($validatedData);
            $client = CRMClients::create($validatedData);

            return [
                'client' => $client,
                'client_address' => $client_address
            ];
        });
    }


    public function createAddressIfProvided($validatedData){
        return [];
    }
}