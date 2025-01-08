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
    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }


    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    // Belongs to status
    public function stage()
    {
        return $this->belongsTo(CategoryGroupTag::class, 'status_id');
    }


    public function milestone()
    {
        return $this->hasOne(Milestone::class, 'id','milestone_id');
    }


    // Belongs to user who assigned it
    public function assignedBy()
    {
        return $this->belongsTo(User::class, 'assign_by');
    }

    // Many-to-many relationship for multiple assignees
    public function assignees()
    {
        return $this->belongsToMany(User::class, 'task_user', 'task_id', 'user_id')
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
