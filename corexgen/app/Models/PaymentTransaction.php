<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


/**
 * Payment Transation table model handle all filters, observers, evenets, relatioships
 */
class PaymentTransaction extends Model
{
    use HasFactory;
    use SoftDeletes;


    const table = 'payment_transactions';

    protected $table = self::table;

    protected $fillable = [
        'plan_id',
        'company_id',
        'amount',
        'currency',
        'payment_gateway',
        'payment_type',
        'transaction_reference',
        'status',
        'transaction_date'

    ];

    /**
     * company relations with payment transation table
     */
    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    /**
     * plans relations with payment transation table
     */
    public function plans()
    {
        return $this->belongsTo(Plans::class, 'plan_id');
    }

    /**
     * subscription relations with payment transation table
     */
    public function subscription()
    {
        return $this->hasOne(Subscription::class, 'payment_id');
    }

    /**
     * get monthly revenue
     */
    public function getMonthlyRevenue()
    {
        return self::where('status', CRM_STATUS_TYPES['PAYMENTSTRANSACTIONS']['STATUS']['SUCCESS']) // Ensure only successful transactions are counted
            ->whereMonth('transaction_date', Carbon::now()->month) // Filter for the current month
            ->whereYear('transaction_date', Carbon::now()->year) // Filter for the current year
            ->sum('amount'); // Sum up the amounts
    }


    /**
     * get last six month revenue
     */
    public function getLastSixMonthsRevenue()
    {
        // Get the current date
        $currentDate = Carbon::now();

        // Initialize an array to store monthly revenues
        $revenues = [];

        // Loop through the last 6 months
        for ($i = 5; $i >= 0; $i--) {
            // Clone the current date to prevent modification
            $date = (clone $currentDate)->subMonthsNoOverflow($i);

            $month = $date->month;
            $year = $date->year;

            // Calculate revenue for the month
            $monthlyRevenue = self::where('status', CRM_STATUS_TYPES['PAYMENTSTRANSACTIONS']['STATUS']['SUCCESS'])
                ->whereMonth('transaction_date', $month)
                ->whereYear('transaction_date', $year)
                ->sum('amount');

            // Store the revenue with the month name
            $revenues[] = [
                'month' => $date->format('M'), // e.g., Jan, Feb, etc.
                'revenue' => $monthlyRevenue,
            ];
        }

        // Prepare chart data
        $chartData = [
            'labels' => array_column($revenues, 'month'),
            'data' => array_column($revenues, 'revenue'),
        ];


        return $chartData;
    }



    // boot method
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($paymentTransaction) {
            // Set default values
            $paymentTransaction->status = $paymentTransaction->status ?? CRM_STATUS_TYPES['PAYMENTSTRANSACTIONS']['STATUS']['SUCCESS'];
        });
    }
}
