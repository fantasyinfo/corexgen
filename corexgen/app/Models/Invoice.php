<?php

namespace App\Models;

use App\Models\Company;
use App\Models\CRM\CRMClients;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use OwenIt\Auditing\Contracts\Auditable;


class Invoice extends Model implements Auditable
{
    use HasFactory;
    use SoftDeletes;
    use \OwenIt\Auditing\Auditable;


    const table = 'invoices';

    protected $table = self::table;

    protected $fillable = [
        '_prefix',
        '_id',
        'issue_date',
        'due_date',
        'total_amount',
        'notes',
        'product_details',
        'payment_details',
        'status',
        'client_id',
        'company_id',
        'project_id',
        'task_id',
        'timesheet_id'
    ];

    protected $casts = [
        'product_details' => 'array',
        'payment_details' => 'array'

    ];



    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }
    public function task()
    {
        return $this->belongsTo(Tasks::class, 'task_id');
    }

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    public function client()
    {
        return $this->belongsTo(CRMClients::class, 'client_id');
    }
    public function timesheet()
    {
        return $this->belongsTo(Timesheet::class, 'timesheet_id');
    }


    /**
     * Get total revenue stats for this month and last month.
     *
     * @return array
     */
    public function getTotalRevenueStats()
    {
        $currentMonth = now()->startOfMonth();
        $lastMonth = now()->subMonth()->startOfMonth();

        // Calculate revenue for this month
        $thisMonthRevenue = self::where('status', 'SUCCESS')
        ->where('company_id', Auth::user()->company_id)
            ->whereBetween('issue_date', [$currentMonth, now()])
            ->sum('total_amount');

        // Calculate revenue for last month
        $lastMonthRevenue = self::where('status', 'SUCCESS')
        ->where('company_id', Auth::user()->company_id)
            ->whereBetween('issue_date', [$lastMonth, $currentMonth])
            ->sum('total_amount');

        // Calculate percentage change
        $percentageChange = $lastMonthRevenue > 0
            ? (($thisMonthRevenue - $lastMonthRevenue) / $lastMonthRevenue) * 100
            : 100; // 100% increase if no revenue last month

        return [
            'current_month' => $thisMonthRevenue,
            'last_month' => $lastMonthRevenue,
            'percentage_change' => round($percentageChange, 2),
            'trend' => $percentageChange >= 0 ? 'up' : 'down',
        ];
    }


    protected static function boot()
    {
        parent::boot();

        static::creating(function ($invoice) {
            $invoice->status = $invoice->status ?? CRM_STATUS_TYPES['INVOICES']['STATUS']['PENDING'];

            if (Auth::check()) {
                $invoice->company_id = $invoice->company_id ?? Auth::user()->company_id;

            } else {
                $invoice->company_id = $invoice->company_id ?? null;

            }
        });
    }

}
