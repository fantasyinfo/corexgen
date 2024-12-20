<?php
namespace App\Services;

use Illuminate\Support\Facades\Auth;

class ClientCSVRowProcessor
{
    /**
     * Process a single row of CSV data.
     */
    public function processRow($row, $userContext)
    {

        // Use the passed user context
        $companyId = $userContext['company_id'];
        $userId = $userContext['user_id'];

     
        // Parse JSON fields
        $emails = explode(';', $row['Emails']);
        $phones = explode(';', $row['Phones']);
        $socialMedia = $this->parseSocialMedia($row['Social Media Links'] ?? ''); // Handle missing social media links gracefully
        $address = !empty($row['Street Address']) || !empty($row['City Name']) || !empty($row['Country ID']) || !empty($row['Pincode'])
            ? [
                [
                    'type' => 'home',
                    'city' => $row['City Name'] ?? null,
                    'country_id' => $row['Country ID'] ?? null,
                    'street_address' => $row['Street Address'] ?? null,
                    'pincode' => $row['Pincode'] ?? null,
                ],
            ]
            : []; // Empty array if no address is provided

        // Use your service or model to create the client
        app('App\Services\ClientService')->createClient([
            'type' => $row['Type'],
            'title' => $row['Title'],
            'first_name' => $row['First Name'],
            'middle_name' => $row['Middle Name'],
            'last_name' => $row['Last Name'],
            'email' => $emails,
            'phone' => $phones,
            'social_media' => $socialMedia,
            'category' => $row['Category'],
            'addresses' => $address,
            'company_id' => $companyId,
            'created_by' => $userId,
            'updated_by' => $userId,
        ]);
    }

    /**
     * Parse social media links into key-value JSON
     */
    private function parseSocialMedia($links)
    {
        $result = [];
        if (empty($links))
            return $result; // Return empty array if no links provided
        $pairs = explode(';', $links);

        foreach ($pairs as $pair) {
            $keyValue = explode("':", $pair);
            if (count($keyValue) === 2) {
                $key = trim($keyValue[0], " '");
                $value = trim($keyValue[1]);
                $result[$key] = $value;
            }
        }

        return $result;
    }
}
