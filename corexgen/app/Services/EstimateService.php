<?php

namespace App\Services;


use App\Models\CRM\CRMClients;
use App\Models\CRM\CRMEstimate;
use App\Models\CRM\CRMLeads;
use App\Traits\IsSMTPValid;
use App\Traits\TenantFilter;
use Yajra\DataTables\Facades\DataTables;
use App\Helpers\PermissionsHelper;
use App\Jobs\SendEstimate;
use App\Repositories\EsitmateRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class EstimateService
{


    use TenantFilter;
    use IsSMTPValid;

    protected $estimateRepository;

    private $tenantRoute;
    private $productServicesService;

    public function __construct(EsitmateRepository $estimateRepository,ProductServicesService $productServicesService)
    {
        $this->estimateRepository = $estimateRepository;
        $this->productServicesService = $productServicesService;
        $this->tenantRoute = $this->getTenantRoute();
    }


    /**
     *create estimate
     */
    public function createEstimate($data)
    {
        // Generate a unique URL slug
        $data['url'] = Str::slug($data['title'] . '-' . $data['_id'], '-');

        // Handle `_id` and `_prefix`
        if (isset($data['_prefix'], $data['_id']) && Str::contains($data['_id'], $data['_prefix'])) {
            $data['_id'] = Str::replace($data['_prefix'], '', $data['_id']);
        }

        // Set the typable relationship data before creation
        if ($data['type'] == 'client') {
            $data['typable_type'] = CRMClients::class;
            $data['typable_id'] = $data['client_id'];
        } elseif ($data['type'] == 'lead') {
            $data['typable_type'] = CRMLeads::class;
            $data['typable_id'] = $data['lead_id'];
        }

        $data['company_id'] = Auth::user()->company_id;
        // Create the proposal with all required fields
        $proposal = CRMEstimate::create($data);

        $this->checkIFProductAddedThenAdd($data, $proposal);
        return $proposal;
    }

    /**
     *update estimate
     */
    public function updateEstimate($data)
    {

        $query = $this->applyTenantFilter(CRMEstimate::where('id', $data['id']));
        $proposal = $query->first();


        // Set the typable relationship data before creation
        if ($data['type'] == 'client') {
            $data['typable_type'] = CRMClients::class;
            $data['typable_id'] = $data['client_id'];
        } elseif ($data['type'] == 'lead') {
            $data['typable_type'] = CRMLeads::class;
            $data['typable_id'] = $data['lead_id'];
        }

        // Create the proposal with all required fields
        $proposal->update($data);

        $this->checkIFProductAddedThenAdd($data, $proposal);

        return $proposal;
    }

    /**
     *check if product added then add 
     */
    public function checkIfProductAddedThenAdd($data, $proposal)
    {
        $product_details = [];
        $json_data = ['products' => []];

        if (
            !empty($data['product_title']) &&
            is_array($data['product_title']) &&
            array_filter($data['product_title'], 'trim')
        ) {
            foreach ($data['product_title'] as $k => $title) {
                $trimmedTitle = trim($title);
                if ($trimmedTitle !== '') {
                    $taxData = $this->productServicesService->getProductTaxes('name', $data['product_tax'][$k]);
                    // info('product id', [$data['product_id'][$k]]);
                    $product_details[] = [
                        'title' => $trimmedTitle,
                        'description' => trim($data['product_description'][$k] ?? ''),
                        'qty' => (float) ($data['product_qty'][$k] ?? 0),
                        'rate' => (float) ($data['product_rate'][$k] ?? 0.00),
                        'tax' => @$taxData[0]?->name ?? null,
                        'tax_id' => @$taxData[0]?->id ?? null,
                        'product_id' => (int) (@$data['product_id'][$k] ?? 0)
                    ];
                }
            }

            // Only add additional fields if there are valid products
            if (!empty($product_details)) {
                $json_data = [
                    'products' => $product_details,
                    'additional_fields' => [
                        'discount' => (float) ($data['discount'] ?? 0),
                        'adjustment' => (float) ($data['adjustment'] ?? 0),
                        'currency_code' => getSettingValue('Currency Code'),
                        'currency_symbol' => getSettingValue('Currency Symbol')
                    ],
                ];
            }
        }

        $proposal->update([
            'product_details' => json_encode($json_data)
        ]);
    }

    /**
     *get estimates
     */
    public function getEstimates($typable_type, $typable_id)
    {
        return $this->applyTenantFilter(CRMEstimate::query()->where('typable_type', $typable_type)->where('typable_id', $typable_id)->latest()->get());
    }

    /**
     *send estimate on email
     */
    public function sendEstimateOnEmail(CRMEstimate $estimate, $view = "dashboard.crm.estimates.print"): bool
    {
        try {
            $mailSettings = $this->_getMailSettings();

            $toEmail = $estimate->typable_type === CRMLeads::class
                ? $estimate->typable->email
                : $estimate->typable->primary_email;

            // Prepare email details
            $emailDetails = [
                'from' => $mailSettings['Mail From Address'],
                'to' => $toEmail,
                'subject' => $estimate->title,
                'details' => $estimate->details,
                'template' => $estimate->template?->template_details
            ];

            // Dispatch the job
            SendEstimate::dispatch(
                $mailSettings,
                $emailDetails,
                $estimate,
                $view
            );
            return true;
        } catch (\Throwable $e) {
            // Log the error or handle it as needed
            \Log::error('Failed to send estimate email', [
                'error' => $e->getMessage(),
                'estimate_id' => $estimate->id ?? null,
            ]);

            // Optionally, you can rethrow the exception if needed
            // throw $e;

            return false; // Return false or a default response to indicate failure
        }
    }


    /**
     * get dt table response
     */
    public function getDatatablesResponse($request)
    {
        $query = $this->estimateRepository->getEstimateQuery($request);

        // Eager load the relationships with specific email field selection
        $query->with([
            'typable' => function ($query) {
                $query->when($query->getModel() instanceof CRMLeads, function ($q) {
                    $q->select('id', 'email'); // Select single email field for leads
                })->when($query->getModel() instanceof CRMClients, function ($q) {
                    $q->select('id', 'primary_email');
                });
            }
        ]);

        $module = PANEL_MODULES[$this->getPanelModule()]['estimates'];
        $lmodule = PANEL_MODULES[$this->getPanelModule()]['leads'];
        $cmodule = PANEL_MODULES[$this->getPanelModule()]['clients'];
        $this->tenantRoute = $this->getTenantRoute();

        return DataTables::of($query)
            ->addColumn('actions', function ($estimate) {
                return $this->renderActionsColumn($estimate);
            })
            ->editColumn('title', function ($estimate) use ($module) {
                return "<a class='dt-link' href='" . route($this->tenantRoute . $module . '.view', $estimate->id) . "' target='_blank'>$estimate->title</a>";
            })
            ->editColumn('to', function ($estimate) use ($lmodule, $cmodule) {
                if (!$estimate->typable) {
                    return "<span>No recipient found</span>";
                }

                $module = $estimate->typable_type === CRMLeads::class ? $lmodule : $cmodule;
                $type = $estimate->typable_type === CRMLeads::class ? "Leads" : "Clients";

                // Handle email retrieval based on model type
                $email = $estimate->typable_type === CRMLeads::class
                    ? $estimate->typable->email  // Direct email field for leads
                    : $estimate->typable->primary_email; // Primary email for clients
    
                $email = $email ?? 'No email found';

                return "<a class='dt-link' href='" . route($this->tenantRoute . $module . '.view', $estimate->typable->id) . "' target='_blank'>$type [$email]</a>";
            })
            ->editColumn('_id', function ($estimate) {
                return "$estimate->_prefix $estimate->_id ";
            })
            ->editColumn('value', function ($estimate) {
                return $estimate?->value ? getSettingValue('Currency Symbol') . ' ' . number_format($estimate->value) : "0";
            })
            ->editColumn('created_at', fn($estimate) => $estimate?->created_at ? formatDateTime($estimate->created_at) : '')
            ->editColumn('creating_date', fn($estimate) => $estimate?->creating_date ? formatDateTime($estimate->creating_date) : '')
            ->editColumn('valid_date', fn($estimate) => $estimate?->valid_date ? formatDateTime($estimate->valid_date) : '')
            ->editColumn('status', function ($estimate) {
                return "<span class='badge bg-" . CRM_STATUS_TYPES['ESTIMATES']['BT_CLASSES'][$estimate->status] . "'>$estimate->status</span>";
            })
            ->rawColumns(['actions', 'value', 'to', 'title', 'status', 'name'])
            ->make(true);
    }

    /**
     * get action col
     */
    protected function renderActionsColumn($estimate)
    {
        $module = PANEL_MODULES[$this->getPanelModule()]['estimates'];
        $permissions = PermissionsHelper::getPermissionsArray('ESTIMATES');
        $id = $estimate->id;
        $tenantRoute = $this->tenantRoute;


        // ['DRAFT', 'SENT', 'OPEN', 'DECLINED', 'ACCEPTED', 'EXPIRED','REVISED']


        $action = '<div class="dropdown text-end">
            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fas fa-ellipsis-h"></i>
            </button>';

        $action .= '<ul class="dropdown-menu dropdown-menu-end">';


        $action .= '<li class="m-1 p-1">
                <a class="dropdown-item" href="' . route($tenantRoute . $module . '.changeStatusAction', ['id' => $id, 'action' => 'SENT']) . '" data-toggle="tooltip" title="Edit">
                <i class="fas fa-paper-plane me-2"></i> Send on Email
                </a>
            </li>';



        if ($estimate->status !== 'ACCEPTED') {


            $action .= '<li class="m-1 p-1">
            <a class="dropdown-item" href="' . route($tenantRoute . $module . '.changeStatusAction', ['id' => $id, 'action' => 'ACCEPTED']) . '" data-toggle="tooltip" title="Edit">
            <i class="fas fa-check me-2"></i> Mark Accepted
            </a>
        </li>';


            $action .= '<li class="m-1 p-1">
            <a class="dropdown-item" href="' . route($tenantRoute . $module . '.changeStatusAction', ['id' => $id, 'action' => 'DECLINED']) . '" data-toggle="tooltip" title="Edit">
            <i class="fas fa-times me-2"></i> Mark Decline
            </a>
        </li>';


            $action .= '<li class="m-1 p-1">
            <a class="dropdown-item" href="' . route($tenantRoute . $module . '.changeStatusAction', ['id' => $id, 'action' => 'REVISED']) . '" data-toggle="tooltip" title="Edit">
            <i class="fas fa-file-alt me-2"></i>Mark Revised
            </a>
        </li>';
        }



        // Check if the user has permission to update
        if (hasPermission(strtoupper($module) . '.' . $permissions['UPDATE']['KEY']) && $estimate->status !== 'ACCEPTED') {
            $action .= '<li class="m-1 p-1">
                <a class="dropdown-item" href="' . route($tenantRoute . $module . '.edit', $id) . '" data-toggle="tooltip" title="Edit">
                 <i class="fas fa-pencil-alt me-2"></i> Edit
                </a>
            </li>';
        }

        // Check if the user has permission to delete
        if (hasPermission(strtoupper($module) . '.' . $permissions['DELETE']['KEY'])) {
            $action .= '<li class="m-1 p-1">
                <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#deleteModal" 
                    data-id="' . $id . '" data-route="' . route($tenantRoute . $module . '.destroy', $id) . '" data-toggle="tooltip" title="Delete">
                  <i class="fas fa-trash-alt me-2"></i> Delete
                </a>
            </li>';
        }







        $action .= '</ul></div>';

        return $action;
    }


}