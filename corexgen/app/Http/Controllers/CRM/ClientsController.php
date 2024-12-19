<?php

namespace App\Http\Controllers\CRM;

use App\Helpers\PermissionsHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\ClientRequest;
use App\Models\Country;
use App\Models\CRM\CRMClients;
use App\Repositories\ClientRepository;
use App\Services\ClientService;
use App\Traits\TenantFilter;
use Illuminate\Http\Request;

class ClientsController extends Controller
{

    use TenantFilter;
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

    public function update()
    {

    }
    public function edit()
    {

    }
    public function destroy()
    {

    }
    public function export()
    {

    }
    public function import()
    {

    }
    public function bulkDelete()
    {

    }
    public function view()
    {

    }
    public function profile()
    {

    }



}

