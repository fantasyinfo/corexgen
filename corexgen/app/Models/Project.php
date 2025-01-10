<?php

namespace App\Models;



use App\Models\Attachments;
use App\Models\CommentNote;
use App\Models\Company;
use App\Models\CRM\CRMClients;
use App\Models\User;
use App\Traits\HasCustomFields;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Support\Facades\DB;

class Project extends Model implements Auditable
{
    use HasFactory;
    use \OwenIt\Auditing\Auditable;
    use HasCustomFields;
    use SoftDeletes;

    const table = 'projects';

    protected $table = self::table;

    protected $fillable = [
        'title',
        'description',
        'billing_type',
        'start_date',
        'due_date',
        'deadline',
        'estimated_hours',
        'time_spent',
        'status',
        'client_id',
        'company_id',
        'one_time_cost',
        'per_hour_cost',
        'progress'
    ];

  

    /**
     * Relationships
     */

    // Belongs to company
    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }


    public function client()
    {
        return $this->belongsTo(CRMClients::class, 'client_id');
    }

    // Many-to-many relationship for multiple assignees
    public function assignees()
    {
        return $this->belongsToMany(User::class, 'project_user', 'project_id', 'user_id')
            ->withTimestamps()
            ->withPivot('company_id');
    }


    public function comments()
    {
        return $this->morphMany(CommentNote::class, 'commentable')
            ->with('user:id,name,profile_photo_path') // Eager load only needed user fields
            ->latest('created_at');
    }

    public function attachments()
    {
        return $this->morphMany(Attachments::class, 'attachable')->latest('created_at');

    }


    /**
     * Get the total time spent on tasks for the project.
     *
     * @return int Total minutes spent.
     */
    public function getTotalTimeSpentOnTasks()
    {
        return Timesheet::whereIn('task_id', $this->tasks()->pluck('id'))
            ->sum(DB::raw('TIMESTAMPDIFF(MINUTE, start_date, end_date)'));
    }


    public function getTimeSheet()
    {
        return Timesheet::whereIn('task_id', $this->tasks()->pluck('id'))->count();
    }


    /**
     * Get the total number of tasks for the project.
     *
     * @return int Total tasks count.
     */
    public function getTotalTasksCount()
    {
        return $this->tasks()->count();
    }

    /**
     * Get the total number of attachments for the project.
     *
     * @return int Total attachments count.
     */
    public function getTotalAttachmentsCount()
    {
        return $this->attachments()->count();
    }

    /**
     * Get the total number of notes for the project.
     *
     * @return int Total notes count.
     */
    public function getTotalNotesCount()
    {
        return $this->comments()->count();
    }

  
    public function getFilteredData(array $filters = [])
    {
        $tasksQuery = $this->tasks();
        $timesheetsQuery = Timesheet::whereIn('task_id', $tasksQuery->pluck('id'));

        // Apply filters to tasks
        if (isset($filters['status'])) {
            $tasksQuery->where('status', $filters['status']);
        }

        // Apply filters to timesheets
        if (isset($filters['date_from'], $filters['date_to'])) {
            $timesheetsQuery->whereBetween('start_date', [$filters['date_from'], $filters['date_to']]);
        }

        return [
            'tasks' => $tasksQuery->get(),
            'timesheets' => $timesheetsQuery->get(),
        ];
    }

    // Define the relationship for tasks
    public function tasks()
    {
        return $this->hasMany(Tasks::class, 'project_id');
    }

  


    /**
     * Model boot method to set default values
     */
    protected static function boot()
    {
        parent::boot();


        // Add a global scope to filter by status = 'active'
        // static::addGlobalScope('active', function (Builder $builder) {
        //     $builder->where('projects.status', CRM_STATUS_TYPES['PROJECTS']['STATUS']['ACTIVE']);
        // });


        static::creating(function ($project) {
            $project->status = $project->status ?? CRM_STATUS_TYPES['PROJECTS']['STATUS']['ACTIVE'];

            if (Auth::check()) {
                $project->company_id = $project->company_id ?? Auth::user()->company_id;
           
            } else {
                $project->company_id = $project->company_id ?? null;
        
            }
        });
    }
}
