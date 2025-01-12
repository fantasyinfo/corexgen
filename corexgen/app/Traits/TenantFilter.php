<?php

namespace App\Traits;

use Illuminate\Support\Facades\Auth;

trait TenantFilter
{
    private $user;
    private $panelAccess;


    /**
     * apply user scope filters by company id or either tenant id 
     */

    public function applyTenantFilter($query, $table = null)
    {

        $this->user = Auth::user();
        $this->panelAccess = panelAccess();

        if ($this->user && $table == null) {
            if ($this->user->is_tenant && $this->panelAccess === PANEL_TYPES['SUPER_PANEL']) {
                $query->where('company_id', null);
            } elseif ($this->user->company_id !== null && $this->panelAccess === PANEL_TYPES['COMPANY_PANEL']) {
                $query->where('company_id', $this->user->company_id);
            }
        } else if ($this->user && $table != null) {
            if ($this->user->is_tenant && $this->panelAccess === PANEL_TYPES['SUPER_PANEL']) {
                $query->where($table . '.company_id', null);
            } elseif ($this->user->company_id !== null && $this->panelAccess === PANEL_TYPES['COMPANY_PANEL']) {
                $query->where($table . '.company_id', $this->user->company_id);
            }
        }

        return $query;
    }

    /**
     * get tenant route as per login admin panel
     */
    public function getTenantRoute()
    {
        $this->user = Auth::user();
        $this->panelAccess = panelAccess();

        if ($this->user) {
            if ($this->user->is_tenant && $this->panelAccess === PANEL_TYPES['SUPER_PANEL']) {
                return getPanelUrl(PANEL_TYPES['SUPER_PANEL']) . '.';
            }
        }

        return getPanelUrl(PANEL_TYPES['COMPANY_PANEL']) . '.';
    }

    /**
     * get current panel module name
     */
    public function getPanelModule()
    {
        $this->user = Auth::user();
        $this->panelAccess = panelAccess();

        if ($this->user) {
            if ($this->user->is_tenant && $this->panelAccess === PANEL_TYPES['SUPER_PANEL']) {
                return 'SUPER_PANEL';
            }
        }

        return 'COMPANY_PANEL';
    }
}
