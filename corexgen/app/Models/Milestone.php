<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Milestone extends Model
{
    use HasFactory;
    const table = 'milestones';

    protected $table = self::table;

    protected $fillable = ['name', 'color', 'status', 'project_id', 'company_id'];


    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

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
