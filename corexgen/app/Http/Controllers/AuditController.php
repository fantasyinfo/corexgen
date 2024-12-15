<?php

namespace App\Http\Controllers;

use App\Models\Audit;
use App\Traits\TenantFilter;
use Illuminate\Http\Request;

class AuditController extends Controller
{
    //
    use TenantFilter;

    private $tenantRoute;

    /**
     * Base directory for view files
     * @var string
     */
    private $viewDir = 'dashboard.audit.';

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

    public function index()
    {
        $audits = Audit::latest()->limit(50)->get();
        return view(
            $this->getViewFilePath('index'),
            [
                'audits' => $audits,
                'title' => 'Audit Logs',
            ]
        );
    }
}
