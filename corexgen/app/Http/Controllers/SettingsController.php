<?php

namespace App\Http\Controllers;

use App\Helpers\PermissionsHelper;
use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Models\City;
use App\Models\Company;
use App\Models\CompanyOnboarding;
use App\Models\Country;
use App\Models\CRM\CRMSettings;
use App\Models\LandingPage;
use App\Models\Media;
use App\Models\Tenant;
use App\Models\WebToLeadForm;
use App\Services\LeadsService;
use App\Services\MailConfigService;
use App\Traits\IsSMTPValid;
use App\Traits\MediaTrait;
use App\Traits\TenantFilter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SettingsController extends Controller
{
    //
    use TenantFilter;
    use MediaTrait;
    use IsSMTPValid;
    /**
     * Display a listing of the resource.
     */

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
    private $viewDir = 'dashboard.settings.';

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

    /**
     * get general settings
     */
    public function general()
    {
        $this->tenantRoute = $this->getTenantRoute();


        $general_settings = $this->applyTenantFilter(CRMSettings::with('media')->where('type', 'General'))->get()->toArray();
        $company = null;

        if (Auth::user()->is_tenant) {

            $defaultSettings = Tenant::find(Auth::user()->tenant_id);
        } else if (Auth::user()->company_id != null) {
            $defaultSettings = CompanyOnboarding::where('company_id', Auth::user()->company_id)->first();
            $company = Company::find(Auth::user()->company_id);
        }


        return view($this->getViewFilePath('general'), [
            'general_settings' => $general_settings,
            'title' => 'General Settings',
            'permissions' => PermissionsHelper::getPermissionsArray('SETTINGS_GENERAL'),
            'module' => PANEL_MODULES[$this->getPanelModule()]['settings'],
            'defaultSettings' => $defaultSettings,
            'is_tenant' => Auth::user()->is_tenant,
            'company_id' => Auth::user()->company_id,
            'dateTimeFormats' => $this->defaultDateTimeFormats(),
            'company' => $company,
            'countries' => Country::all(),
        ]);
    }

    /**
     * get default timezone format
     */
    public function defaultDateTimeFormats(): array
    {
        return [
            'Y-m-d H:i:s' => now()->format('Y-m-d H:i:s'),
            'd-m-Y H:i' => now()->format('d-m-Y H:i'),
            'm/d/Y g:i A' => now()->format('m/d/Y g:i A'),
            'D, M j, Y h:i A' => now()->format('D, M j, Y h:i A'),
            'l, F j, Y h:i A' => now()->format('l, F j, Y h:i A'),
            'Y-m-d' => now()->format('Y-m-d'),
            'd/m/Y' => now()->format('d/m/Y'),
            'M j, Y h:i A' => now()->format('M j, Y h:i A'),
            'c' => now()->format('c'),
            'r' => now()->format('r'),
            'U' => now()->format('U'),
        ];
    }

    /**
     * update general settings
     */
    public function generalUpdate(Request $request)
    {
        if ($request->is_tenant) {
            $validatedData = $request->validate([
                'tenant_company_name' => 'required|string|max:255',
                'tenant_company_tagline' => 'nullable|string|max:255',
                'tenant_company_date_format' => 'required|string',
                'tenant_company_time_zone' => 'required|string',
                'tenant_company_currency_symbol' => 'required|string|max:10',
                'tenant_company_currency_code' => 'required|string|max:10',
                'tenant_company_logo' => 'nullable|image|max:2048',
            ]);

            // Update the settings in the database
            $settings = [
                'tenant_company_name' => $request->input('tenant_company_name'),
                'tenant_company_tagline' => $request->input('tenant_company_tagline'),
                'tenant_company_date_format' => $request->input('tenant_company_date_format'),
                'tenant_company_time_zone' => $request->input('tenant_company_time_zone'),
                'tenant_company_currency_symbol' => $request->input('tenant_company_currency_symbol'),
                'tenant_company_currency_code' => $request->input('tenant_company_currency_code'),
            ];

            foreach ($settings as $key => $value) {
                $setting = CRMSettings::where('name', $key)->where('is_tenant', '1')->first();
                if ($setting) {
                    $setting->update(['value' => $value]);
                }
            }
        } else {

            $validatedData = $request->validate([
                'client_company_name' => 'required|string|max:255',
                'client_company_tagline' => 'nullable|string|max:255',
                'client_company_date_format' => 'required|string',
                'client_company_time_zone' => 'required|string',
                'client_company_currency_symbol' => 'required|string|max:10',
                'client_company_currency_code' => 'required|string|max:10',
                'client_company_logo' => 'nullable|image|max:2048',
                'client_company_address_street_address' => 'required|string|max:255',
                'client_company_address_city_name' => 'required|string|max:100',
                'client_company_address_pincode' => 'required|string|max:10',
                'client_company_address_country_id' => 'required|exists:countries,id',
            ]);

            // Update the settings in the database
            $settings = [
                'client_company_name' => $request->input('client_company_name'),
                'client_company_tagline' => $request->input('client_company_tagline'),
                'client_company_date_format' => $request->input('client_company_date_format'),
                'client_company_time_zone' => $request->input('client_company_time_zone'),
                'client_company_currency_symbol' => $request->input('client_company_currency_symbol'),
                'client_company_currency_code' => $request->input('client_company_currency_code'),
                'client_company_address_street_address' => $request->input('client_company_address_street_address'),
                'client_company_address_city_name' => $request->input('client_company_address_city_name'),
                'client_company_address_pincode' => $request->input('client_company_address_pincode'),
                'client_company_address_country_id' => $request->input('client_company_address_country_id'),
            ];


            $addressData = [
                'address_street_address' => $request->input('client_company_address_street_address'),
                'address_city_name' => $request->input('client_company_address_city_name'),
                'address_pincode' => $request->input('client_company_address_pincode'),
                'address_country_id' => $request->input('client_company_address_country_id'),
            ];
            $this->createAddressIfProvided($addressData);


            foreach ($settings as $key => $value) {
                $setting = CRMSettings::where('name', $key)->where('company_id', Auth::user()->company_id)->first();
                if ($setting) {
                    $setting->update(['value' => $value]);
                }
            }
        }

        // Handle the logo upload
        $this->updateCompanyLogo($request, $request->is_tenant);

        clearSettingsCache();
        return redirect()->back()->with('success', 'Settings updated successfully');
    }

    /**
     * update company logo file path
     */
    public function updateCompanyLogo(Request $request, $is_tenant)
    {
        // Determine the logo setting name based on tenant status
        $logoSettingName = $is_tenant ? 'tenant_company_logo' : 'client_company_logo';

        // Check if logo file is present
        $logoFile = $request->file($logoSettingName);
        if (!$logoFile) {
            return;
        }

        try {
            // Find or create the CRMSetting
            $logoSetting = CRMSettings::firstOrCreate(
                ['name' => $logoSettingName, 'company_id' => Auth::user()->company_id],
                ['value' => null, 'is_media_setting' => true]
            );

            // Log the retrieved or created setting
            \Log::info('Logo Settings', $logoSetting->toArray());

            // Get the old media (if exists)
            $oldMedia = $logoSetting->media ?? null;

            // Update media using the trait
            $media = $this->updateMedia(
                $logoFile,
                $oldMedia,
                [
                    'folder' => 'logos',
                    'company_id' => Auth::user()->company_id,
                    'is_tenant' => Auth::user()->is_tenant,
                    'updated_by' => Auth::id(),
                    'created_by' => Auth::id(),
                ]
            );

            // Update the CRMSetting with the new media ID
            $logoSetting->update([
                'is_media_setting' => true,
                'media_id' => $media->id,
            ]);
            Cache::forget('tenant_company_logo');

            \Log::info('Company logo updated successfully', [
                'media_id' => $media->id,
                'setting' => $logoSetting
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to update company logo', [
                'error' => $e->getMessage(),
                'is_tenant' => $is_tenant
            ]);

            // Optionally, you could throw the exception or handle it as needed
            // throw $e;
        }
    }

    /**
     * get mail settings
     */
    public function mail()
    {
        $this->tenantRoute = $this->getTenantRoute();


        $mail_settings = $this->applyTenantFilter(CRMSettings::where('type', 'Mail'))->get()->toArray();
        $company = null;

        $defaultSettings = [];
        if (Auth::user()->is_tenant) {
            $defaultSettings = Tenant::find(Auth::user()->tenant_id);
        }


        return view($this->getViewFilePath('mail'), [
            'mail_settings' => $mail_settings,
            'title' => 'Mail Settings',
            'permissions' => PermissionsHelper::getPermissionsArray('SETTINGS_MAIL'),
            'module' => PANEL_MODULES[$this->getPanelModule()]['settings'],
            'defaultSettings' => json_decode(@$defaultSettings->settings, true),
            'is_tenant' => Auth::user()->is_tenant,
            'company_id' => Auth::user()->company_id,
            'company' => $company,
            'encryption' => ['ssl', 'tls', 'no', 'none']
        ]);
    }

    public function mailUpdate(Request $request)
    {
        if ($request->is_tenant) {
            $validatedData = $request->validate([
                'tenant_mail_provider' => 'required|string|max:255',
                'tenant_mail_host' => 'required|string',
                'tenant_mail_port' => 'required|numeric',
                'tenant_mail_username' => 'required|string',
                'tenant_mail_password' => 'required|string',
                'tenant_mail_encryption' => 'required|string',
                'tenant_mail_from_address' => 'required|email',
                'tenant_mail_from_name' => 'required|string',
            ]);

            // Update the settings in the database
            $settings = [
                'tenant_mail_provider' => $request->input('tenant_mail_provider'),
                'tenant_mail_host' => $request->input('tenant_mail_host'),
                'tenant_mail_port' => $request->input('tenant_mail_port'),
                'tenant_mail_username' => $request->input('tenant_mail_username'),
                'tenant_mail_password' => $request->input('tenant_mail_password'),
                'tenant_mail_encryption' => $request->input('tenant_mail_encryption'),
                'tenant_mail_from_address' => $request->input('tenant_mail_from_address'),
                'tenant_mail_from_name' => $request->input('tenant_mail_from_name'),
            ];

            foreach ($settings as $key => $value) {
                $setting = CRMSettings::where('name', $key)->where('is_tenant', '1')->first();
                if ($setting) {
                    $setting->update(['value' => $value]);
                }
            }

            MailConfigService::updateMailConfig();
            // Update mail configuration


        } else {
            $validatedData = $request->validate([
                'client_mail_provider' => 'required|string|max:255',
                'client_mail_host' => 'required|string',
                'client_mail_port' => 'required|numeric',
                'client_mail_username' => 'required|string',
                'client_mail_password' => 'required|string',
                'client_mail_encryption' => 'required|string',
                'client_mail_from_address' => 'required|email',
                'client_mail_from_name' => 'required|string',
            ]);

            // Update the settings in the database
            $settings = [
                'client_mail_provider' => $request->input('client_mail_provider'),
                'client_mail_host' => $request->input('client_mail_host'),
                'client_mail_port' => $request->input('client_mail_port'),
                'client_mail_username' => $request->input('client_mail_username'),
                'client_mail_password' => $request->input('client_mail_password'),
                'client_mail_encryption' => $request->input('client_mail_encryption'),
                'client_mail_from_address' => $request->input('client_mail_from_address'),
                'client_mail_from_name' => $request->input('client_mail_from_name'),
            ];

            foreach ($settings as $key => $value) {
                $setting = CRMSettings::where('name', $key)->where('company_id', Auth::user()->company_id)->first();
                if ($setting) {
                    $setting->update(['value' => $value]);
                }
            }

            if (getModule() == 'company') {
                MailConfigService::updateMailConfig([
                    'client_mail_provider' => 'required|string|max:255',
                    'client_mail_host' => 'required|string',
                    'client_mail_port' => 'required|numeric',
                    'client_mail_username' => 'required|string',
                    'client_mail_password' => 'required|string',
                    'client_mail_encryption' => 'required|string',
                    'client_mail_from_address' => 'required|email',
                    'client_mail_from_name' => 'required|string',
                ]);
            }
        }

        return redirect()->back()->with('success', 'Settings updated successfully');
    }




    /**
     * test mail connections if valid
     */
    public function testMailConnection(Request $request)
    {
        try {

            if ($request->is_tenant) {

                $validatedData = $request->validate([
                    'tenant_mail_provider' => 'required|string|max:255',
                    'tenant_mail_host' => 'required|string',
                    'tenant_mail_port' => 'required|numeric',
                    'tenant_mail_username' => 'required|string',
                    'tenant_mail_password' => 'required|string',
                    'tenant_mail_encryption' => 'required|string',
                    'tenant_mail_from_address' => 'required|email',
                    'tenant_mail_from_name' => 'required|string',
                ]);

                // Collect SMTP settings from the form
                $mailSettings = $request->only([
                    'tenant_mail_host',
                    'tenant_mail_port',
                    'tenant_mail_username',
                    'tenant_mail_password',
                    'tenant_mail_encryption',
                    'tenant_mail_from_address',
                    'tenant_mail_from_name'
                ]);

                // Validate SMTP settings using the trait
                $isValid = $this->_isSMTPValid([
                    'Mail Host' => $mailSettings['tenant_mail_host'],
                    'Mail Port' => $mailSettings['tenant_mail_port'],
                    'Mail Username' => $mailSettings['tenant_mail_username'],
                    'Mail Password' => $mailSettings['tenant_mail_password'],
                    'Mail Encryption' => $mailSettings['tenant_mail_encryption'],
                    'Mail From Address' => $mailSettings['tenant_mail_from_address'],
                    'Mail From Name' => $mailSettings['tenant_mail_from_name']
                ]);

                \Log::info("Tenant Mail Settings are Valid or Not:", [
                    'Mail Host' => $mailSettings['tenant_mail_host'],
                    'Mail Port' => $mailSettings['tenant_mail_port'],
                    'Mail Username' => $mailSettings['tenant_mail_username'],
                    'Mail Password' => $mailSettings['tenant_mail_password'],
                    'Mail Encryption' => $mailSettings['tenant_mail_encryption'],
                    'Mail From Address' => $mailSettings['tenant_mail_from_address'],
                    'Mail From Name' => $mailSettings['tenant_mail_from_name'],
                    'Data' => $isValid
                ]);

                if ($isValid['status']) {
                    return response()->json(['success' => true, 'message' => 'SMTP settings are valid.']);
                }
            } else {
                $validatedData = $request->validate([
                    'client_mail_provider' => 'required|string|max:255',
                    'client_mail_host' => 'required|string',
                    'client_mail_port' => 'required|numeric',
                    'client_mail_username' => 'required|string',
                    'client_mail_password' => 'required|string',
                    'client_mail_encryption' => 'required|string',
                    'client_mail_from_address' => 'required|email',
                    'client_mail_from_name' => 'required|string',
                ]);

                // Collect SMTP settings from the form
                $mailSettings = $request->only([
                    'client_mail_host',
                    'client_mail_port',
                    'client_mail_username',
                    'client_mail_password',
                    'client_mail_encryption',
                    'client_mail_from_address',
                    'client_mail_from_name'
                ]);

                // Validate SMTP settings using the trait
                $isValid = $this->_isSMTPValid([
                    'Mail Host' => $mailSettings['client_mail_host'],
                    'Mail Port' => $mailSettings['client_mail_port'],
                    'Mail Username' => $mailSettings['client_mail_username'],
                    'Mail Password' => $mailSettings['client_mail_password'],
                    'Mail Encryption' => $mailSettings['client_mail_encryption'],
                    'Mail From Address' => $mailSettings['client_mail_from_address'],
                    'Mail From Name' => $mailSettings['client_mail_from_name']
                ]);

                \Log::info("Company Mail Settings are Valid or Not:", [
                    'Mail Host' => $mailSettings['client_mail_host'],
                    'Mail Port' => $mailSettings['client_mail_port'],
                    'Mail Username' => $mailSettings['client_mail_username'],
                    'Mail Password' => $mailSettings['client_mail_password'],
                    'Mail Encryption' => $mailSettings['client_mail_encryption'],
                    'Mail From Address' => $mailSettings['client_mail_from_address'],
                    'Mail From Name' => $mailSettings['client_mail_from_name'],
                    'Data' => $isValid
                ]);
                if ($isValid['status']) {
                    return response()->json(['success' => true, 'message' => 'SMTP settings are valid.']);
                }
            }


            return response()->json(['success' => false, 'message' => 'SMTP settings are invalid.' . $isValid['error']]);
        } catch (\Exception $e) {
            \Log::error('Test Mail Connection Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }


    /**
     * get cronjob settings
     */
    public function cron()
    {
        $this->tenantRoute = $this->getTenantRoute();
        return view($this->getViewFilePath('cron'), [
            'title' => 'Cron Settings',
            'permissions' => PermissionsHelper::getPermissionsArray('SETTINGS_CRON'),
            'module' => PANEL_MODULES[$this->getPanelModule()]['settings'],
        ]);
    }

    /**
     * get one word settings
     */
    public function oneWord()
    {
        $this->tenantRoute = $this->getTenantRoute();

        $oneWordSettings = $this->applyTenantFilter(CRMSettings::where('type', 'OneWord'))->get()->toArray();


        return view($this->getViewFilePath('oneWord'), [
            'title' => 'One Word Settings',
            'permissions' => PermissionsHelper::getPermissionsArray('SETTINGS_ONEWORD'),
            'module' => PANEL_MODULES[$this->getPanelModule()]['settings'],
            'oneWordSettings' => $oneWordSettings
        ]);
    }

    /**
     * update one word settings
     */
    public function oneWordUpdate(Request $request)
    {
        $validatedData = $request->validate([
            'client_proposal_prefix' => 'required|string|max:20',
            'client_contract_prefix' => 'required|string|max:20',
            'client_estimate_prefix' => 'required|string|max:20',
            'client_invoice_prefix' => 'required|string|max:20',
        ]);

        $settings = [
            'client_proposal_prefix' => $request->input('client_proposal_prefix'),
            'client_contract_prefix' => $request->input('client_contract_prefix'),
            'client_estimate_prefix' => $request->input('client_estimate_prefix'),
            'client_invoice_prefix' => $request->input('client_invoice_prefix'),
        ];

        $companyId = Auth::user()->company_id;

        $existingSettings = CRMSettings::where('company_id', $companyId)
            ->whereIn('name', array_keys($settings))
            ->get()
            ->keyBy('name');

        $updatedSettings = [];
        foreach ($settings as $key => $value) {
            if (isset($existingSettings[$key])) {
                $existingSettings[$key]->update(['value' => $value]);
                $updatedSettings[] = $key;
            }
        }

        if (count($updatedSettings) > 0) {
            return redirect()->back()->with('success', 'Settings updated: ' . implode(', ', $updatedSettings));
        } else {
            return redirect()->back()->withErrors('No settings were updated.');
        }
    }

    /**
     * find or create city
     */
    private function findOrCreateCity($cityName, $countryId)
    {
        $city = City::firstOrCreate(
            ['name' => $cityName, 'country_id' => $countryId],
            ['name' => $cityName, 'country_id' => $countryId]
        );

        return $city->id;
    }

    /**
     * create address if provided
     */
    private function createAddressIfProvided(array $data)
    {
        // Find or create the city ID
        $cityId = $this->findOrCreateCity($data['address_city_name'], $data['address_country_id']);

        // Check if the address already exists
        $address = Address::where([
            'street_address' => $data['address_street_address'],
            'postal_code' => $data['address_pincode'],
            'city_id' => $cityId,
            'country_id' => $data['address_country_id'],
            'address_type' => ADDRESS_TYPES['USER']['SHOW']['HOME'],
        ])->first();

        // If the address doesn't exist, create it
        if (!$address) {
            $address = Address::create([
                'street_address' => $data['address_street_address'],
                'postal_code' => $data['address_pincode'],
                'city_id' => $cityId,
                'country_id' => $data['address_country_id'],
                'address_type' => ADDRESS_TYPES['USER']['SHOW']['HOME'],
            ]);
        }

        // Update the company's address_id
        Company::find(Auth::user()->company_id)->update(['address_id' => $address->id]);
    }


    /**
     * leadFormSetting view 
     */
    public function leadFormSetting(LeadsService $leadsService)
    {

        return view($this->getViewFilePath('leadFormLists'), [
            'title' => 'Lead Web Forms Lists',
            'module' => PANEL_MODULES[$this->getPanelModule()]['settings'],
            'leadsStatus' => $leadsService->getLeadsStatus(),
            'leadsGroups' => $leadsService->getLeadsGroups(),
            'leadsSources' => $leadsService->getLeadsSources(),
            'teamMates' => getTeamMates()
        ]);
    }
    /**
     * leadFormSetting fetch list
     */
    public function leadFormSettingFetch(Request $request)
    {
        $data = $this->applyTenantFilter(
            WebToLeadForm::query()
                ->withCount('leads as leads_count')
                ->with(['stage', 'source', 'group'])
        )->latest()->get();

        return response()->json($data);
    }

    /**
     * create and store leadFormSettingStore

     */
    public function leadFormSettingStore(Request $request)
    {

        try {
            // Validate incoming request data
            $validatedData = $request->validate([
                'title' => 'required|string|max:255',
                'group_id' => 'required|exists:category_group_tag,id',
                'source_id' => 'required|exists:category_group_tag,id',
                'status_id' => 'required|exists:category_group_tag,id',
            ]);


            // Create the new WebToLeadForm entry
            $form = WebToLeadForm::create($validatedData);

            // Return success response
            return response()->json(['success' => true, 'form' => $form], 201);
        } catch (\Exception $e) {
            // Log the error (if logging is desired)
            Log::error('Error in leadFormSettingStore:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Return error response
            return response()->json([
                'success' => false,
                'message' => 'Failed to create the lead form setting.',
                'error' => $e->getMessage(), // For production, you may want to hide the actual error message.
            ], 500);
        }
    }

    /**
     * update and save leadFormSettingUpdate

     */
    public function leadFormSettingUpdate(Request $request)
    {

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'group_id' => 'required|exists:category_group_tag,id',
            'source_id' => 'required|exists:category_group_tag,id',
            'status_id' => 'required|exists:category_group_tag,id',
            'id' => 'required|exists:web_to_leads_form,id'
        ]);

        $form = WebToLeadForm::where('company_id', Auth::user()->company_id)->findOrFail($validated['id']);
        $form->update($validated);


        return response()->json(['success' => true, 'form' => $form]);
    }

    /**
     * Remove the lead form delete
     */
    public function leadFormSettingDestory($id)
    {
        $form = WebToLeadForm::where('company_id', Auth::user()->company_id)->findOrFail($id);
        $form->delete();

        return response()->json(['success' => true, 'message' => 'Deleted successfully']);
    }

    /**
     * Generate the form based on the source, groups, status, and title
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function leadFormSettingGenerate($id)
    {
        try {
            // Fetch the form or fail with 404 if not found
            $form = WebToLeadForm::where('company_id', Auth::user()->company_id)
                ->where('id', $id)
                ->firstOrFail();

            return redirect()->route('leadForm', ['id' => $form->uuid]);
        } catch (\Exception $e) {
            // (Optional) Log the exception
            Log::error('Error generating lead form:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Redirect back or to some error page with a friendly error message
            return redirect()->back()->with([
                'error' => 'An error occurred while trying to generate the lead form.',
            ]);
        }
    }


    /**
     * Show Front End Landing Page Settings View
     *
     */
    public function frontend()
    {
        return view($this->getViewFilePath('frontEnd'), [
            'title' => 'Front End Settings',
            'module' => PANEL_MODULES[$this->getPanelModule()]['settings'],
            'settings' => LandingPage::all(),
        ]);
    }

    /**
     * Update Frontend Settings
     * 
     * @param Request $request
     */
    public function frontendUpdate(Request $request)
    {
        try {
            // Validate the request data
            $validated = $this->validateFrontendSettings($request);

            // Start database transaction
            DB::beginTransaction();

            try {
                // Update Hero Section
                $this->updateSection('hero', $validated['hero']);

                // Update Features Section
                $this->updateFeaturesSection($validated['features']);

                // Update Solutions Section
                $this->updateSection('solutions', $validated['solutions']);

                // Update Pricing Section (maps to 'plans' in db)
                $this->updateSection('plans', $validated['pricing']);

                // Update Testimonials Section
                $this->updateTestimonialsSection($validated['testimonials']);

                // Update Footer Section
                $this->updateSection('footer', $validated['footer']);

                // Commit transaction
                DB::commit();

                return redirect()->back()->with('success', 'Frontend settings updated successfully');
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Frontend settings update failed: ' . $e->getMessage());
                throw $e;
            }
        } catch (ValidationException $e) {
            return redirect()->back()->with('error', 'Validation failed')->withInput();
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage())->withInput();
        }
    }

    /**
     * Validate frontend settings
     * 
     * @param Request $request
     * @return array
     */
    private function validateFrontendSettings(Request $request): array
    {
        return $request->validate([
            'hero' => 'required|array',
            'hero.Heading' => 'required|string|max:255',
            'hero.SubHeading' => 'required|string',

            'features' => 'required|array',
            'features.Heading' => 'required|string|max:255',
            'features.SubHeading' => 'required|string',
            'features.Options.Heading.*' => 'required|string|max:255',
            'features.Options.SubHeading.*' => 'required|string',

            'solutions' => 'required|array',
            'solutions.Heading' => 'required|string|max:255',
            'solutions.SubHeading' => 'required|string',
            'solutions.Options.*' => 'required|string|max:255',

            'pricing' => 'required|array',
            'pricing.Heading' => 'required|string|max:255',
            'pricing.SubHeading' => 'required|string',

            'testimonials' => 'required|array',
            'testimonials.Heading' => 'required|string|max:255',
            'testimonials.SubHeading' => 'required|string',
            'testimonials.Options.Message.*' => 'required|string',
            'testimonials.Options.Customer Name.*' => 'required|string|max:255',
            'testimonials.Options.Position.*' => 'required|string|max:255',
            'testimonials.Options.Company.*' => 'required|string|max:255',
            'testimonials.Options.LOGO.*' => 'required|string',

            'footer' => 'required|array',
            'footer.Heading' => 'required|string|max:255',
            'footer.SubHeading' => 'required|string',
        ]);
    }

    /**
     * Update a single section
     * 
     * @param string $key
     * @param array $value
     * @return void
     */
    private function updateSection(string $key, array $value): void
    {
        LandingPage::where('key', $key)
            ->where('type', 'section')
            ->firstOrFail()
            ->update(['value' => $value]);
    }

    /**
     * Update features section with restructured data
     * 
     * @param array $features
     * @return void
     */
    private function updateFeaturesSection(array $features): void
    {
        // Restructure the features options to match the original format
        $restructuredOptions = [];

        foreach ($features['Options']['Heading'] as $index => $heading) {
            $restructuredOptions[] = [
                'Heading' => $heading,
                'SubHeading' => $features['Options']['SubHeading'][$index]
            ];
        }

        $features['Options'] = $restructuredOptions;

        $this->updateSection('features', $features);
    }

    /**
     * Update testimonials section with restructured data
     * 
     * @param array $testimonials
     * @return void
     */
    private function updateTestimonialsSection(array $testimonials): void
    {
        // Restructure the testimonials options to match the original format
        $restructuredOptions = [];

        foreach ($testimonials['Options']['Message'] as $index => $message) {
            $restructuredOptions[] = [
                'Message' => $message,
                'Customer Name' => $testimonials['Options']['Customer Name'][$index],
                'Position' => $testimonials['Options']['Position'][$index],
                'Company' => $testimonials['Options']['Company'][$index],
                'LOGO' => $testimonials['Options']['LOGO'][$index]
            ];
        }

        $testimonials['Options'] = $restructuredOptions;

        $this->updateSection('testimonials', $testimonials);
    }


    /**
     * Get Theme Customize Settings
     */
    public function theme()
    {

        $this->tenantRoute = $this->getTenantRoute();

        $theme_settings = $this->applyTenantFilter(CRMSettings::where('type', 'Theme'))->get()->toArray();

        return view($this->getViewFilePath('theme'), [
            'theme_settings' => $theme_settings,
            'title' => 'Theme Settings',
            'permissions' => PermissionsHelper::getPermissionsArray('THEME_CUSTOMIZE'),
            'module' => PANEL_MODULES[$this->getPanelModule()]['settings'],
            'is_tenant' => Auth::user()->is_tenant,
            'company_id' => Auth::user()->company_id,
        ]);
    }
    /**
     * Update Theme Customize Settings
     */
    public function themeUpdate(Request $request)
    {

        if ($request->is_tenant) {
            $validatedData = $request->validate([
                "primary-color" => 'required|string|max:30',
                "primary-hover" => 'required|string|max:30',
                "secondary-color" => 'required|string|max:30',
                "success-color" => 'required|string|max:30',
                "danger-color" => 'required|string|max:30',
                "warning-color" => 'required|string|max:30',
                "info-color" => 'required|string|max:30',
                "light-color" => 'required|string|max:30',
                "dark-color" => 'required|string|max:30',
                "body-bg" => 'required|string|max:30',
                "body-color" => 'required|string|max:30',
                "border-color" => 'required|string|max:30',
                "card-bg" => 'required|string|max:30',
                "input-bg" => 'required|string|max:30',
                "input-border" => 'required|string|max:30',
                "neutral-gray" => 'required|string|max:30',
                "sidebar-bg" => 'required|string|max:30',
                "sidebar-diff-bg" => 'required|string|max:30',
                "task-columns-cards-border" => 'required|string|max:30',
                "primary-color-d" => 'required|string|max:30',
                "primary-hover-d" => 'required|string|max:30',
                "secondary-color-d" => 'required|string|max:30',
                "success-color-d" => 'required|string|max:30',
                "danger-color-d" => 'required|string|max:30',
                "warning-color-d" => 'required|string|max:30',
                "info-color-d" => 'required|string|max:30',
                "light-color-d" => 'required|string|max:30',
                "dark-color-d" => 'required|string|max:30',
                "body-bg-d" => 'required|string|max:30',
                "body-color-d" => 'required|string|max:30',
                "border-color-d" => 'required|string|max:30',
                "card-bg-d" => 'required|string|max:30',
                "input-bg-d" => 'required|string|max:30',
                "input-border-d" => 'required|string|max:30',
                "neutral-gray-d" => 'required|string|max:30',
                "sidebar-bg-d" => 'required|string|max:30',
                "sidebar-diff-bg-d" => 'required|string|max:30',
                "task-columns-cards-border-d" => 'required|string|max:30',
            ]);

            // Update the settings in the database
            // Automatically build the $settings array dynamically
            $settings = collect($validatedData)->mapWithKeys(function ($value, $key) use ($request) {
                return [$key => $request->input($key)];
            })->toArray();

            foreach ($settings as $key => $value) {
                $setting = CRMSettings::where('name', $key)->where('is_tenant', '1')->first();
                if ($setting) {
                    $setting->update(['value' => $value]);
                }
            }
        } else {


            $validatedData = $request->validate([
                "primary-color-company" => 'required|string|max:30',
                "primary-hover-company" => 'required|string|max:30',
                "secondary-color-company" => 'required|string|max:30',
                "success-color-company" => 'required|string|max:30',
                "danger-color-company" => 'required|string|max:30',
                "warning-color-company" => 'required|string|max:30',
                "info-color-company" => 'required|string|max:30',
                "light-color-company" => 'required|string|max:30',
                "dark-color-company" => 'required|string|max:30',
                "body-bg-company" => 'required|string|max:30',
                "body-color-company" => 'required|string|max:30',
                "border-color-company" => 'required|string|max:30',
                "card-bg-company" => 'required|string|max:30',
                "input-bg-company" => 'required|string|max:30',
                "input-border-company" => 'required|string|max:30',
                "neutral-gray-company" => 'required|string|max:30',
                "sidebar-bg-company" => 'required|string|max:30',
                "sidebar-diff-bg-company" => 'required|string|max:30',
                "task-columns-cards-border-company" => 'required|string|max:30',
                "primary-color-d-company" => 'required|string|max:30',
                "primary-hover-d-company" => 'required|string|max:30',
                "secondary-color-d-company" => 'required|string|max:30',
                "success-color-d-company" => 'required|string|max:30',
                "danger-color-d-company" => 'required|string|max:30',
                "warning-color-d-company" => 'required|string|max:30',
                "info-color-d-company" => 'required|string|max:30',
                "light-color-d-company" => 'required|string|max:30',
                "dark-color-d-company" => 'required|string|max:30',
                "body-bg-d-company" => 'required|string|max:30',
                "body-color-d-company" => 'required|string|max:30',
                "border-color-d-company" => 'required|string|max:30',
                "card-bg-d-company" => 'required|string|max:30',
                "input-bg-d-company" => 'required|string|max:30',
                "input-border-d-company" => 'required|string|max:30',
                "neutral-gray-d-company" => 'required|string|max:30',
                "sidebar-bg-d-company" => 'required|string|max:30',
                "sidebar-diff-bg-d-company" => 'required|string|max:30',
                "task-columns-cards-border-d-company" => 'required|string|max:30',
            ]);
            // Automatically build the $settings array dynamically
            $settings = collect($validatedData)->mapWithKeys(function ($value, $key) use ($request) {
                return [$key => $request->input($key)];
            })->toArray();

            foreach ($settings as $key => $value) {
                $setting = CRMSettings::where('name', $key)->where('company_id', Auth::user()->company_id)->first();
                if ($setting) {
                    $setting->update(['value' => $value]);
                }
            }
        }

        // Clear the cache for all users/companies affected
        $cacheKey = 'theme_settings_user_' . Auth::id() . '_company_' . (Auth::user()->company_id ?? 'global');
        Cache::forget($cacheKey);

        return redirect()->back()->with('success', 'Settings updated successfully');
    }
}
