<?php

namespace App\Http\Controllers\CRM;

use App\Helpers\PermissionsHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\ClientRequest;
use App\Http\Requests\ClientsEditRequest;
use App\Models\Country;
use App\Models\CRM\CRMClients;
use App\Repositories\ClientRepository;
use App\Services\ClientService;
use App\Traits\TenantFilter;
use Illuminate\Http\Request;
use App\Traits\SubscriptionUsageFilter;
use Illuminate\Support\Facades\Auth;

class ClientsController extends Controller
{

    use TenantFilter;
    use SubscriptionUsageFilter;
    //
    /**
     * Number of items per page for pagination
     * @var int
     */
    protected $perPage = 10;

    /**
     * Tenant-specific route prefix
     * @var string
     */
    private $tenantRoute;

    /**
     * Base directory for view files
     * @var string
     */
    private $viewDir = 'dashboard.crm.clients.';

    /**
     * Generate full view file path
     * 
     * @param string $filename
     * @return string
     */
    private function getViewFilePath($filename)
    {
        return $this->viewDir . $filename;
    }


    protected $clientRepository;
    protected $clientService;

    public function __construct(
        ClientRepository $clientRepository,
        ClientService $clientService
    ) {
        $this->clientRepository = $clientRepository;
        $this->clientService = $clientService;
    }


    public function index(Request $request)
    {
        $this->tenantRoute = $this->getTenantRoute();


        // Server-side DataTables response
        if ($request->ajax()) {
            return $this->clientService->getDatatablesResponse($request);
        }

        return view($this->getViewFilePath('index'), [
            'filters' => $request->all(),
            'title' => 'Clients Management',
            'permissions' => PermissionsHelper::getPermissionsArray('CLIENTS'),
            'module' => PANEL_MODULES[$this->getPanelModule()]['clients'],
        ]);
    }
    public function store(ClientRequest $request)
    {

        try {
            // Create client
            $client = $this->clientService->createClient($request->validated());


            $this->updateUsage(strtolower(PLANS_FEATURES[PermissionsHelper::$plansPermissionsKeys['CLIENTS']]), '+', '1');

            return redirect()
                ->route($this->getTenantRoute() . 'clients.index')
                ->with('success', 'Client created successfully.');

        } catch (\Exception $e) {
            \Log::error('Client creation failed', [
                'error' => $e->getMessage(),
                'data' => $request->validated()
            ]);
            return redirect()
                ->back()
                ->withInput()
                ->with('active_tab', $request->input('active_tab', 'general'))
                ->with('error', $e->getMessage());
        }

    }
    public function create()
    {
        $countries = Country::all();
        return view($this->getViewFilePath('create'), [
            'title' => 'Create Client',
            'countries' => $countries,
            'module' => PANEL_MODULES[$this->getPanelModule()]['clients'],

        ]);
    }

    public function update(ClientsEditRequest $request)
    {
        $this->tenantRoute = $this->getTenantRoute();

        try {
            $this->clientService->updateClient($request->validated());

            // If validation fails, it will throw an exception
            return redirect()
                ->route($this->tenantRoute . 'clients.index')
                ->with('success', 'Client updated successfully.');
        } catch (\Exception $e) {
            \Log::error('Client updation failed', [
                'error' => $e->getMessage(),
                'data' => $request->validated()
            ]);

            return redirect()
                ->back()
                ->with('error', 'An error occurred while updating the client. ' . $e->getMessage());
        }

    }
    public function edit($id)
    {
        $query = CRMClients::query()->with([
            'addresses' => function ($query) {
                $query->select('addresses.id', 'addresses.street_address', 'addresses.postal_code', 'addresses.city_id', 'addresses.country_id')
                    ->withPivot('type');
            }
        ])->where('id', $id);

        $query = $this->applyTenantFilter($query, 'clients');

        $client = $query->firstOrFail();


        $countries = Country::all();


        return view($this->getViewFilePath('edit'), [

            'title' => 'Edit Client',
            'client' => $client,
            'countries' => $countries,
            'module' => PANEL_MODULES[$this->getPanelModule()]['clients'],
        ]);
    }
    public function destroy($id)
    {
        try {
            // Delete the user
            CRMClients::query()->where('id', '=', $id)->delete();
            // Return success response
            $this->updateUsage(strtolower(PLANS_FEATURES[PermissionsHelper::$plansPermissionsKeys['CLIENTS']]), '-', '1');
            return redirect()->back()->with('success', 'Client deleted successfully.');
        } catch (\Exception $e) {
            // Handle any exceptions
            return redirect()->back()->with('error', 'Failed to delete the client: ' . $e->getMessage());
        }
    }
    public function export(Request $request)
    {
        // Apply filters and fetch clients
        $clients = CRMClients::query()
            ->where('company_id', Auth::user()->company_id)
            ->with([
                'addresses' => function ($query) {
                    $query->with(['country', 'city'])
                        ->select('addresses.id', 'addresses.street_address', 'addresses.postal_code', 'addresses.city_id', 'addresses.country_id');
                }
            ])
            ->when(
                $request->filled('name'),
                fn($q) => $q->where(function ($subQuery) use ($request) {
                    $subQuery->where('first_name', 'LIKE', "%{$request->name}%")
                        ->orWhere('middle_name', 'LIKE', "%{$request->name}%")
                        ->orWhere('last_name', 'LIKE', "%{$request->name}%");
                })
            )
            ->when(
                $request->filled('email'),
                fn($q) => $q->whereJsonContains('email', $request->email)
            )
            ->when(
                $request->filled('phone'),
                fn($q) => $q->whereJsonContains('phone', $request->phone)
            )
            ->when(
                $request->filled('status') && $request->status != 0,
                fn($q) => $q->where('status', $request->status)
            )
            ->when(
                $request->filled('start_date'),
                fn($q) => $q->whereDate('created_at', '>=', $request->start_date)
            )
            ->when(
                $request->filled('end_date'),
                fn($q) => $q->whereDate('created_at', '<=', $request->end_date)
            )
            ->get();

        $csvData = $this->generateCSVForClients($clients);
        return response($csvData['csvContent'])
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', "attachment; filename={$csvData['file']}");
    }

    public function generateCSVForClients($clients)
    {

        // Prepare CSV data
        $csvData = [];
        $csvData[] = [
            'Client ID',
            'Type',
            'Title',
            'First Name',
            'Middle Name',
            'Last Name',
            'Emails',
            'Phones',
            'Social Media Links',
            'Category',
            'Street Address',
            'City ID',
            'City Name',
            'Country ID',
            'Country Name',
            'Pincode',
            'Status',
            'Created At',
        ]; // CSV headers

        foreach ($clients as $client) {
            // Prepare consolidated data
            $emails = isset($client->email) ? implode('; ', $client->email) : '';
            $phones = isset($client->phone) ? implode('; ', $client->phone) : '';
            $socialMedia = isset($client->social_media)
                ? implode('; ', array_map(fn($key, $value) => "$key: $value", array_keys($client->social_media), $client->social_media))
                : '';

            // Use the first address as a representative for each client
            $address = $client->addresses->first();

            $csvData[] = [
                $client->id,
                $client->type,
                $client->title,
                $client->first_name,
                $client->middle_name,
                $client->last_name,
                $emails,
                $phones,
                $socialMedia,
                $client->category,
                $address?->street_address ?? 'N/A',
                $address?->city_id ?? 'N/A',
                $address?->city?->name ?? 'N/A',
                $address?->country_id ?? 'N/A',
                $address?->country?->name ?? 'N/A',
                $address?->postal_code ?? 'N/A',
                $client->status,
                $client?->created_at?->format('Y-m-d H:i:s') ?? 'N/A',
            ];
        }

        // Convert data to CSV string
        $csvContent = '';
        foreach ($csvData as $row) {
            $csvContent .= implode(',', array_map(fn($value) => '"' . str_replace('"', '""', $value) . '"', $row)) . "\n";
        }

        // Return the response with the CSV content as a file
        $fileName = 'clients_export_' . now()->format('Y_m_d_H_i_s') . '.csv';

        return [
            'file' => $fileName,
            'csvContent' => $csvContent
        ];
    }

    public function import()
    {

    }
    public function bulkDelete(Request $request)
    {

        $ids = $request->input('ids');

        try {
            // Delete the companies

            if (is_array($ids) && count($ids) > 0) {
                // Validate ownership/permissions if necessary
                CRMClients::query()->whereIn('id', $ids)->delete();

                $this->updateUsage(strtolower(PLANS_FEATURES[PermissionsHelper::$plansPermissionsKeys['CLIENTS']]), '-', count($ids));

                return response()->json(['message' => 'Selected clients deleted successfully.'], 200);
            }

            return response()->json(['message' => 'No clients selected for deletion.'], 400);





        } catch (\Exception $e) {
            // Handle any exceptions
            return redirect()->back()->with('error', 'Failed to delete the clients: ' . $e->getMessage());
        }
    }
    public function view()
    {

    }
    public function profile()
    {

    }

    public function changeStatus($id, $status)
    {
        try {
            CRMClients::query()->where('id', '=', $id)->update(['status' => $status]);
            // Return success response
            return redirect()->back()->with('success', 'Clients status changed successfully.');
        } catch (\Exception $e) {
            // Handle any exceptions
            return redirect()->back()->with('error', 'Failed to changed the clients status: ' . $e->getMessage());
        }
    }




}

