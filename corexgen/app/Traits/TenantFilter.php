<?php

namespace App\Traits;

use Illuminate\Support\Facades\Auth;

trait TenantFilter
{
    private $user;
    private $panelAccess;



    public function applyTenantFilter($query)
    {

        $this->user = Auth::user();
        $this->panelAccess = panelAccess();

        if ($this->user) {
            if ($this->user->is_tenant && $this->panelAccess === PANEL_TYPES['SUPER_PANEL']) {
                $query->where('company_id', null);
            } elseif ($this->user->company_id !== null && $this->panelAccess === PANEL_TYPES['COMPANY_PANEL']) {
                $query->where('company_id', $this->user->company_id);
            }
        }

        return $query;
    }

    public function getTenantRoute()
    {
        $this->user = Auth::user();
        $this->panelAccess = panelAccess();

        if ($this->user) {
            if ($this->user->is_tenant && $this->panelAccess === PANEL_TYPES['SUPER_PANEL']) {
                return PANEL_TYPES['SUPER_PANEL'] . '.';
            }
        }

        return PANEL_TYPES['COMPANY_PANEL'] . '.';
    }
}
