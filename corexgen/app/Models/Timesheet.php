<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class Timesheet extends Model
{
    use HasFactory;
    use SoftDeletes;

    const table = 'timesheets';

    protected $table = self::table;

    protected $fillable = ['start_date', 'end_date', 'duration', 'notes', 'company_id', 'task_id', 'user_id', 'created_by', 'updated_by'];


    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
    ];

    public function task()
    {
        return $this->belongsTo(Tasks::class, 'task_id');
    }


    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

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
