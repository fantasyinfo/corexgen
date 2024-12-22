<?php

namespace App\Http\Controllers;

use App\Helpers\PermissionsHelper;
use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\CompanyOnboarding;
use App\Models\CRM\CRMSettings;
use App\Models\Media;
use App\Models\Tenant;
use App\Traits\MediaTrait;
use App\Traits\TenantFilter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class SettingsController extends Controller
{
    //
    use TenantFilter;
    use MediaTrait;
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
            'company' => $company
        ]);
    }

    public function defaultDateTimeFormats(): array
    {
        return [
            'Y-m-d H:i:s' => 'Y-m-d H:i:s | 2024-12-14 12:34:56',
            'd-m-Y H:i' => 'd-m-Y H:i | 14-12-2024 12:34',
            'm/d/Y g:i A' => 'm/d/Y g:i A | 12/14/2024 12:34 PM',
            'D, M j, Y' => 'D, M j, Y | Sat, Dec 14, 2024',
            'l, F j, Y' => 'l, F j, Y | Saturday, December 14, 2024',
            'c' => 'c | ISO 8601: 2024-12-14T12:34:56+00:00',
            'r' => 'r | RFC 2822: Sat, 14 Dec 2024 12:34:56 +0000',
        ];
    }

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
            ]);

            // Update the settings in the database
            $settings = [
                'client_company_name' => $request->input('client_company_name'),
                'client_company_tagline' => $request->input('client_company_tagline'),
                'client_company_date_format' => $request->input('client_company_date_format'),
                'client_company_time_zone' => $request->input('client_company_time_zone'),
                'client_company_currency_symbol' => $request->input('client_company_currency_symbol'),
                'client_company_currency_code' => $request->input('client_company_currency_code'),
            ];

            foreach ($settings as $key => $value) {
                $setting = CRMSettings::where('name', $key)->where('company_id', Auth::user()->company_id)->first();
                if ($setting) {
                    $setting->update(['value' => $value]);
                }
            }
        }

        // Handle the logo upload
        $this->updateCompanyLogo($request, $request->is_tenant);

        return redirect()->back()->with('success', 'Settings updated successfully');
    }

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
        }

        return redirect()->back()->with('success', 'Settings updated successfully');
    }

    public function cron()
    {
        $this->tenantRoute = $this->getTenantRoute();
        return view($this->getViewFilePath('cron'), [
            'title' => 'Cron Settings',
            'permissions' => PermissionsHelper::getPermissionsArray('SETTINGS_CRON'),
            'module' => PANEL_MODULES[$this->getPanelModule()]['settings'],
        ]);
    }

}
