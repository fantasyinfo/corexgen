<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

/**
 * Timesheet table model handle all filters, observers, evenets, relatioships
 */
class Timesheet extends Model
{
    use HasFactory;
    use SoftDeletes;

    const table = 'timesheets';

    protected $table = self::table;

    protected $fillable = ['start_date', 'end_date', 'duration', 'notes', 'company_id', 'task_id', 'user_id', 'created_by', 'updated_by', 'invoice_generated'];


    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'invoice_generated' => 'boolean'
    ];


    /**
     * tasks relations with timesheet table
     */
    public function task()
    {
        return $this->belongsTo(Tasks::class, 'task_id');
    }


    /**
     * user relations with timesheet table
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * company relations with timesheet table
     */
    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    /**
     * update by users relations with timesheet table
     */
    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
    /**
     * created by users relations with timesheet table
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * invoice relations with timesheet table
     */
    public function invoice()
    {
        return $this->hasOne(Invoice::class, 'timesheet_id');
    }

    /**
     * boot method

     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($timesheet) {


            if (Auth::check()) {
                $timesheet->company_id = $timesheet->company_id ?? Auth::user()->company_id;
                $timesheet->created_by = $timesheet->created_by ?? Auth::id();
                $timesheet->updated_by = $timesheet->updated_by ?? Auth::id();

            } else {
                $timesheet->company_id = $timesheet->company_id ?? null;
                $timesheet->created_by = $timesheet->created_by ?? null;
                $timesheet->updated_by = $timesheet->updated_by ?? null;

            }
        });

        static::updating(function ($timesheet) {
            if (Auth::check()) {
                $timesheet->updated_by = Auth::id();
            }
        });
    }

}
