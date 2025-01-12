<?php
namespace App\Models;


use App\Models\Attachments;
use App\Models\CategoryGroupTag;
use App\Models\CommentNote;
use App\Models\Company;
use App\Models\User;
use App\Traits\HasCustomFields;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use OwenIt\Auditing\Contracts\Auditable;



/**
 * Tasks table model handle all filters, observers, evenets, relatioships
 */
class Tasks extends Model implements Auditable
{
    use HasFactory;
    use \OwenIt\Auditing\Auditable;
    use HasCustomFields;
    use SoftDeletes;

    const table = 'tasks';

    protected $table = self::table;

    protected $fillable = [
        'title',
        'description',
        'billable',
        'hourly_rate',
        'related_to',
        'start_date',
        'due_date',
        'priority',
        'visible_to_client',
        'project_id',
        'milestone_id',
        'status_id',
        'company_id',
    ];

    protected $casts = [
        'visible_to_client' => 'boolean',
        'billable' => 'boolean',
    ];

    /**
     * Relationships
     */

    // Belongs to company

    /**
     * company relations with Tasks table
     */
    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    /**
     * project relations with Tasks table
     */
    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    // Belongs to status
    /**
     * stage relations with Tasks table
     */
    public function stage()
    {
        return $this->belongsTo(CategoryGroupTag::class, 'status_id');
    }

    /**
     * milestone relations with Tasks table
     */
    public function milestone()
    {
        return $this->hasOne(Milestone::class, 'id', 'milestone_id');
    }

    /**
     * timesheet relations with Tasks table
     */
    public function timeSheets()
    {
        return $this->hasMany(Timesheet::class, 'task_id');
    }

    /**
     * assigned by user relations with Tasks table
     */
    // Belongs to user who assigned it
    public function assignedBy()
    {
        return $this->belongsTo(User::class, 'assign_by');
    }

    // Many-to-many relationship for multiple assignees
    /**
     * assignees user relations with Tasks table
     */
    public function assignees()
    {
        return $this->belongsToMany(User::class, 'task_user', 'task_id', 'user_id')
            ->withTimestamps()
            ->withPivot('company_id');
    }

    /**
     * comments relations with Tasks table
     */
    public function comments()
    {
        return $this->morphMany(CommentNote::class, 'commentable')
            ->with('user:id,name,profile_photo_path') // Eager load only needed user fields
            ->latest('created_at');
    }

    /**
     * attachments relations with Tasks table
     */
    public function attachments()
    {
        return $this->morphMany(Attachments::class, 'attachable')->latest('created_at');

    }



    /**
     * get Active tasks

     */
    public function getActiveTasks()
    {
        $currentMonth = now()->startOfMonth();
        $lastMonth = now()->subMonth()->startOfMonth();

        // Check current month's tasks
        $thisMonthCount = self::whereHas('stage', function ($query) {
            $query->where('name', 'New') // Ensure 'New' exists in the related table
                ->where('type', 'tasks_status'); // Ensure 'tasks' exists in the related table
        })
            ->where('company_id', Auth::user()->company_id)
            ->whereBetween('start_date', [$currentMonth, now()]) // Check 'start_date' is valid
            ->count();

        // Check last month's tasks
        $lastMonthCount = self::whereHas('stage', function ($query) {
            $query->where('name', 'New')
                ->where('type', 'tasks_status');
        })
            ->where('company_id', Auth::user()->company_id)
            ->whereBetween('start_date', [$lastMonth, $currentMonth]) // Use correct date field
            ->count();

        // Calculate percentage change
        $percentageChange = $lastMonthCount > 0
            ? (($thisMonthCount - $lastMonthCount) / $lastMonthCount) * 100
            : 100; // Handle no tasks last month

        return [
            'current_month' => $thisMonthCount,
            'last_month' => $lastMonthCount,
            'percentage_change' => round($percentageChange, 2),
            'trend' => $percentageChange >= 0 ? 'up' : 'down',
        ];
    }



    /**
     * 
     * get tasks counts
     */
    public function getTasksCounts()
    {
        $taskStages = CategoryGroupTag::where('type', 'tasks_status') // Filter only task-related stages
            ->withCount([
                'tasks as task_count' => function ($query) {
                    $query->whereNull('deleted_at'); // Exclude soft-deleted tasks
                }
            ])
            ->where('company_id', Auth::user()->company_id)
            ->get();

        return [
            'labels' => $taskStages->pluck('name')->toArray(), // Stage names
            'data' => $taskStages->pluck('task_count')->toArray(), // Task counts
        ];
    }



    /**
     * Model boot method to set default values
     */
    protected static function boot()
    {
        parent::boot();


        // Add a global scope to filter by status = 'active'
        // static::addGlobalScope('active', function (Builder $builder) {
        //     $builder->where('leads.status', CRM_STATUS_TYPES['LEADS']['STATUS']['ACTIVE']);
        // });


        static::creating(function ($task) {

            if (Auth::check()) {
                $task->company_id = $task->company_id ?? Auth::user()->company_id;
                $task->assign_by = $task->assign_by ?? Auth::id();
            } else {
                $task->company_id = $task->company_id ?? null;
                $task->assign_by = $task->assign_by ?? null;
            }
        });
    }
}
