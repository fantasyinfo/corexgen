<?php

namespace App\Http\Controllers;

use App\Helpers\PermissionsHelper;
use App\Http\Controllers\Controller;
use App\Models\CRM\CRMSettings;
use App\Models\Media;
use App\Traits\TenantFilter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SettingsController extends Controller
{
    //
    use TenantFilter;
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

        $general_settings = CRMSettings::where('type', 'General')->get()->toArray();

        return view($this->getViewFilePath('general'), [
            'general_settings' => $general_settings,
            'title' => 'General Settings',
            'permissions' => PermissionsHelper::getPermissionsArray('SETTINGS'),
            'module' => PANEL_MODULES[$this->getPanelModule()]['settings'],
        ]);
    }


    public function generalUpdate(Request $request)
    {
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
            $setting = CRMSettings::where('name', $key)->first();
            if ($setting) {
                $setting->update(['value' => $value]);
            }
        }

        // Handle the logo upload
        // Handle the logo upload
        if ($request->hasFile('tenant_company_logo')) {
            $logo = $request->file('tenant_company_logo');
            $logoFileName = $logo->getClientOriginalName();
            $logoPath = $logo->store('logos', 'public');

            $media = Media::create([
                'file_name' => $logoFileName,
                'file_path' => $logoPath,
                'file_type' => $logo->getMimeType(),
                'file_extension' => $logo->getClientOriginalExtension(),
                'size' => $logo->getSize(),
                'company_id' => Auth::user()->company_id ?? null, // Assuming no company ID is required
                'is_tenant' => Auth::user()->is_tenant,
                'updated_by' => auth()->id(),
                'created_by' => auth()->id(),
            ]);

            $logoSetting = CRMSettings::where('name', 'tenant_company_logo')->first();
            if ($logoSetting) {
                $logoSetting->update([
                    'value' => 'logo',
                    'is_media_setting' => true,
                    'media_id' => $media->id,
                ]);
            }
        }

        return redirect()->back()->with('success', 'Settings updated successfully');
    }
}
