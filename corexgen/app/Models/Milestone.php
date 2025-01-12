<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;


/**
 * Milestone table model handle all filters, observers, evenets, relatioships
 */
class Milestone extends Model
{
    use HasFactory;
    const table = 'milestones';

    protected $table = self::table;

    protected $fillable = ['name', 'color', 'status', 'project_id', 'company_id'];



    /**
     * company relations with milestone table
     */
    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }


    /**
     * project relations with milestone table
     */
    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id');
    }


    /**
     * tasks relations with milestone table
     */
    public function tasks()
    {
        return $this->belongsTo(Tasks::class, 'milestone_id');
    }

    /**
     * boot method
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($milestone) {
            $milestone->status = $milestone->status ?? CRM_STATUS_TYPES['MILESTONES']['STATUS']['PENDING'];

            if (Auth::check()) {
                $milestone->company_id = $milestone->company_id ?? Auth::user()->company_id;

            } else {
                $milestone->company_id = $milestone->company_id ?? null;

            }
        });
    }

}
